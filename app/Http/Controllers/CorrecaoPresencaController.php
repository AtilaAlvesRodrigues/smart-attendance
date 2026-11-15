<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Presenca;
use App\Models\PresencaAjuste;
use App\Support\AulasDaMateria;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Chamadas já realizadas de uma matéria e correção manual de presença.
 *
 * O professor marca ou desmarca a presença de um aluno numa aula que já
 * aconteceu, sempre com motivo. Toda alteração fica em presenca_ajustes.
 */
class CorrecaoPresencaController extends BaseController
{
    public function index(int $materia_id)
    {
        $materia = $this->materiaDoProfessor($materia_id);
        $aulas = AulasDaMateria::listar($materia->id);
        $totalAlunos = $materia->alunos()->count();

        return view('professor.gerenciar.chamadas', [
            'professor'   => Auth::guard('professores')->user(),
            'materia'     => $materia,
            'aulas'       => $aulas,
            'totalAlunos' => $totalAlunos,
        ]);
    }

    public function show(int $materia_id, string $codigo_aula)
    {
        $materia = $this->materiaDoProfessor($materia_id);
        $aula = AulasDaMateria::aula($materia->id, $codigo_aula) ?? abort(404);

        $presencas = Presenca::where('materia_id', $materia->id)
            ->where('codigo_aula', $codigo_aula)
            ->get(['aluno_id', 'origem', 'created_at'])
            ->keyBy('aluno_id');

        $ajustes = PresencaAjuste::with('aluno')
            ->where('materia_id', $materia->id)
            ->where('codigo_aula', $codigo_aula)
            ->latest('created_at')
            ->get();

        return view('professor.gerenciar.chamada', [
            'professor' => Auth::guard('professores')->user(),
            'materia'   => $materia,
            'aula'      => $aula,
            'alunos'    => $materia->alunos()->orderBy('nome')->get(),
            'presencas' => $presencas,
            'ajustes'   => $ajustes,
        ]);
    }

    public function atualizar(Request $request, int $materia_id, string $codigo_aula)
    {
        $materia = $this->materiaDoProfessor($materia_id);
        $aula = AulasDaMateria::aula($materia->id, $codigo_aula) ?? abort(404);

        $dados = $request->validate([
            'aluno_id' => 'required|integer',
            'presente' => 'required|boolean',
            'motivo'   => 'required|string|min:3|max:500',
        ], [
            'motivo.required' => 'Informe o motivo da correção.',
            'motivo.min'      => 'Descreva o motivo com pelo menos 3 caracteres.',
        ]);

        if (! $materia->alunos()->where('alunos.id', $dados['aluno_id'])->exists()) {
            throw ValidationException::withMessages(['aluno_id' => 'Este aluno não está matriculado na matéria.']);
        }

        $professor = Auth::guard('professores')->user();
        $presente = (bool) $dados['presente'];

        try {
            $this->aplicarCorrecao($materia, $aula, $dados, $presente, $professor);
        } catch (UniqueConstraintViolationException) {
            // Outra requisição (QR, outra aba) registrou a mesma presença ao mesmo tempo: o estado pedido já vale
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'presente' => $presente]);
        }

        return back()->with('status', $presente ? 'Presença marcada.' : 'Presença removida.');
    }

    private function aplicarCorrecao(Materia $materia, object $aula, array $dados, bool $presente, $professor): void
    {
        DB::transaction(function () use ($materia, $aula, $dados, $presente, $professor) {
            $existente = Presenca::where('materia_id', $materia->id)
                ->where('codigo_aula', $aula->codigo_aula)
                ->where('aluno_id', $dados['aluno_id'])
                ->lockForUpdate()
                ->first();

            // Já está no estado pedido: nada a fazer nem a auditar
            if ($presente === (bool) $existente) {
                return;
            }

            if ($presente) {
                $presenca = Presenca::create([
                    'aluno_id'     => $dados['aluno_id'],
                    'professor_id' => $aula->professor_id ?: $professor->id,
                    'materia_id'   => $materia->id,
                    'data_aula'    => $aula->data_aula,
                    'semestre'     => $aula->semestre,
                    'horario'      => $aula->horario,
                    'codigo_aula'  => $aula->codigo_aula,
                    'origem'       => Presenca::ORIGEM_CORRECAO,
                ]);
                $presencaId = $presenca->id;
                $acao = PresencaAjuste::ACAO_MARCOU;
            } else {
                // A aula só existe enquanto houver ao menos uma presença com o código dela.
                // Apagar a última faria a aula sumir e mudaria as faltas da turma inteira.
                // Trava as linhas e conta em PHP: o PostgreSQL não aceita FOR UPDATE com COUNT(*)
                $restantes = Presenca::where('materia_id', $materia->id)
                    ->where('codigo_aula', $aula->codigo_aula)
                    ->lockForUpdate()
                    ->get(['id'])
                    ->count();

                if ($restantes <= 1) {
                    throw ValidationException::withMessages([
                        'presente' => 'Não é possível remover a única presença desta aula: a aula deixaria de existir para a turma.',
                    ]);
                }

                $presencaId = $existente->id;
                $existente->delete();
                $acao = PresencaAjuste::ACAO_DESMARCOU;
            }

            PresencaAjuste::create([
                'presenca_id' => $presencaId,
                'materia_id'  => $materia->id,
                'aluno_id'    => $dados['aluno_id'],
                'codigo_aula' => $aula->codigo_aula,
                'acao'        => $acao,
                'motivo'      => $dados['motivo'],
                'autor_tipo'  => 'professor',
                'autor_id'    => $professor->id,
            ]);
        });
    }

    private function materiaDoProfessor(int $materiaId): Materia
    {
        $materia = Auth::guard('professores')->user()
            ->materias()
            ->where('materias.id', $materiaId)
            ->first();

        abort_unless($materia, 403, 'Você não tem permissão para acessar esta matéria.');

        return $materia;
    }
}
