<?php

namespace App\Http\Controllers;

use App\Mail\JustificativaDecididaMail;
use App\Models\Justificativa;
use App\Models\Presenca;
use App\Models\PresencaAjuste;
use App\Support\AulasDaMateria;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Lado do professor: analisar as justificativas das suas matérias.
 * Aprovar abona a falta (cria a presença com origem 'justificativa').
 */
class JustificativaProfessorController extends BaseController
{
    public function index()
    {
        $professor = Auth::guard('professores')->user();
        $materiaIds = $professor->materias()->pluck('materias.id');

        $base = Justificativa::with(['aluno', 'materia'])->whereIn('materia_id', $materiaIds);

        return view('professor.justificativas', [
            'professor'  => $professor,
            'pendentes'  => (clone $base)->pendentes()->oldest()->get(),
            'decididas'  => (clone $base)->where('status', '!=', Justificativa::STATUS_PENDENTE)
                ->latest('decidida_em')->limit(30)->get(),
        ]);
    }

    public function decidir(Request $request, Justificativa $justificativa)
    {
        $professor = Auth::guard('professores')->user();
        $this->autorizar($justificativa);

        $dados = $request->validate([
            'decisao'    => 'required|in:aprovar,recusar',
            'comentario' => 'nullable|string|max:1000',
        ]);

        try {
            $this->registrarDecisao($justificativa, $dados, $professor);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // O aluno já tinha presença nesta aula (QR ou correção simultânea): grava a decisão sem novo abono
            $this->registrarDecisao($justificativa, $dados, $professor, abonar: false);
        }

        $justificativa->refresh()->load(['aluno', 'materia']);
        $this->notificarAluno($justificativa);

        return back()->with('status', $justificativa->status === Justificativa::STATUS_APROVADA
            ? 'Justificativa aprovada. A falta foi abonada.'
            : 'Justificativa recusada.');
    }

    private function registrarDecisao(Justificativa $justificativa, array $dados, $professor, bool $abonar = true): void
    {
        DB::transaction(function () use ($justificativa, $dados, $professor, $abonar) {
            $j = Justificativa::whereKey($justificativa->id)->lockForUpdate()->first();

            if (! $j->estaPendente()) {
                throw ValidationException::withMessages(['decisao' => 'Esta justificativa já foi decidida.']);
            }

            $aprovar = $dados['decisao'] === 'aprovar';

            $j->forceFill([
                'status'               => $aprovar ? Justificativa::STATUS_APROVADA : Justificativa::STATUS_RECUSADA,
                'comentario_professor' => $dados['comentario'] ?? null,
                'decidida_por'         => $professor->id,
                'decidida_em'          => now(),
            ])->save();

            if ($aprovar && $abonar) {
                $this->abonar($j, $professor->id);
            }
        });
    }

    public function anexo(Justificativa $justificativa)
    {
        $this->autorizar($justificativa);

        return JustificativaAlunoController::baixarAnexo($justificativa);
    }

    private function abonar(Justificativa $j, int $professorId): void
    {
        $jaPresente = Presenca::where('materia_id', $j->materia_id)
            ->where('codigo_aula', $j->codigo_aula)
            ->where('aluno_id', $j->aluno_id)
            ->exists();

        // A aula pode ter sido corrigida pelo professor antes da decisão
        if ($jaPresente) {
            return;
        }

        $aula = AulasDaMateria::aula($j->materia_id, $j->codigo_aula);

        $presenca = Presenca::create([
            'aluno_id'     => $j->aluno_id,
            'professor_id' => $aula?->professor_id ?: $professorId,
            'materia_id'   => $j->materia_id,
            'data_aula'    => $aula?->data_aula ?? $j->data_aula,
            'semestre'     => $aula?->semestre,
            'horario'      => $aula?->horario,
            'codigo_aula'  => $j->codigo_aula,
            'origem'       => Presenca::ORIGEM_JUSTIFICATIVA,
        ]);

        PresencaAjuste::create([
            'presenca_id' => $presenca->id,
            'materia_id'  => $j->materia_id,
            'aluno_id'    => $j->aluno_id,
            'codigo_aula' => $j->codigo_aula,
            'acao'        => PresencaAjuste::ACAO_ABONOU,
            'motivo'      => 'Justificativa #' . $j->id . ' aprovada',
            'autor_tipo'  => 'professor',
            'autor_id'    => $professorId,
        ]);
    }

    /** O e-mail é um aviso: se o SMTP falhar, a decisão continua valendo. */
    private function notificarAluno(Justificativa $j): void
    {
        try {
            Mail::to($j->aluno->email)->send(new JustificativaDecididaMail($j));
        } catch (\Throwable $e) {
            // Só a classe da exceção: o texto do servidor de e-mail pode trazer o endereço do aluno
            Log::warning('Falha ao enviar e-mail de decisão da justificativa', [
                'justificativa_id' => $j->id,
                'excecao'          => $e::class,
            ]);
        }
    }

    private function autorizar(Justificativa $justificativa): void
    {
        $dono = Auth::guard('professores')->user()
            ->materias()
            ->where('materias.id', $justificativa->materia_id)
            ->exists();

        abort_unless($dono, 403, 'Esta justificativa é de uma matéria que não é sua.');
    }
}
