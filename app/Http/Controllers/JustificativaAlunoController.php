<?php

namespace App\Http\Controllers;

use App\Models\Justificativa;
use App\Support\AulasDaMateria;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Lado do aluno: ver as faltas, enviar justificativa (com atestado opcional)
 * e acompanhar a decisão do professor.
 */
class JustificativaAlunoController extends BaseController
{
    public function index()
    {
        $aluno = Auth::guard('alunos')->user();
        $justificativas = Justificativa::with('materia')
            ->where('aluno_id', $aluno->id)
            ->latest()
            ->get();
        $jaJustificadas = $justificativas->map(fn ($j) => $j->materia_id . '|' . $j->codigo_aula)->flip();

        // Faltas ainda sem justificativa, agrupadas por matéria
        $faltasPorMateria = $aluno->materias()->orderBy('nome')->get()
            ->map(fn ($materia) => [
                'materia' => $materia,
                'faltas'  => AulasDaMateria::faltasDoAluno($materia->id, $aluno->id)
                    ->reject(fn ($aula) => isset($jaJustificadas[$materia->id . '|' . $aula->codigo_aula]))
                    ->values(),
            ])
            ->filter(fn ($grupo) => $grupo['faltas']->isNotEmpty())
            ->values();

        return view('aluno.justificativas', [
            'aluno'            => $aluno,
            'faltasPorMateria' => $faltasPorMateria,
            'justificativas'   => $justificativas,
            'anexoHabilitado'  => Justificativa::anexoHabilitado(),
        ]);
    }

    public function store(Request $request)
    {
        $aluno = Auth::guard('alunos')->user();
        $anexoHabilitado = Justificativa::anexoHabilitado();

        $dados = $request->validate([
            'materia_id'  => 'required|integer',
            'codigo_aula' => 'required|string|max:100',
            'motivo'      => 'required|string|min:10|max:2000',
            'anexo'       => $anexoHabilitado
                ? 'nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120'
                : 'prohibited',
        ], [
            'motivo.min'     => 'Explique o motivo com pelo menos 10 caracteres.',
            'anexo.mimes'    => 'O atestado precisa ser PDF, JPG ou PNG.',
            'anexo.mimetypes'=> 'O atestado precisa ser PDF, JPG ou PNG.',
            'anexo.max'      => 'O atestado pode ter no máximo 5 MB.',
        ]);

        if (! $aluno->materias()->where('materias.id', $dados['materia_id'])->exists()) {
            throw ValidationException::withMessages(['materia_id' => 'Você não está matriculado nesta matéria.']);
        }

        $falta = AulasDaMateria::faltasDoAluno((int) $dados['materia_id'], $aluno->id)
            ->firstWhere('codigo_aula', $dados['codigo_aula']);

        if (! $falta) {
            throw ValidationException::withMessages(['codigo_aula' => 'Não há falta nesta aula para justificar.']);
        }

        if (Justificativa::where('aluno_id', $aluno->id)->where('materia_id', (int) $dados['materia_id'])->where('codigo_aula', $falta->codigo_aula)->exists()) {
            throw ValidationException::withMessages(['codigo_aula' => 'Você já enviou uma justificativa para esta falta.']);
        }

        $anexo = $request->file('anexo');
        $caminho = $anexo
            ? Storage::disk(Justificativa::DISCO)->putFile(now()->format('Y/m'), $anexo)
            : null;

        Justificativa::create([
            'aluno_id'    => $aluno->id,
            'materia_id'  => (int) $dados['materia_id'],
            'codigo_aula' => $falta->codigo_aula,
            'data_aula'   => $falta->data_aula,
            'motivo'      => $dados['motivo'],
            'anexo_path'  => $caminho,
            'anexo_nome'  => $anexo ? mb_substr($anexo->getClientOriginalName(), 0, 200) : null,
            'anexo_mime'  => $anexo?->getMimeType(),
            'status'      => Justificativa::STATUS_PENDENTE,
        ]);

        return redirect()->route('aluno.justificativas')
            ->with('status', 'Justificativa enviada. Você recebe um e-mail quando o professor decidir.');
    }

    public function anexo(Justificativa $justificativa)
    {
        abort_unless($justificativa->aluno_id === Auth::guard('alunos')->id(), 403);

        return self::baixarAnexo($justificativa);
    }

    public static function baixarAnexo(Justificativa $justificativa)
    {
        abort_unless($justificativa->anexo_path, 404);

        $disco = Storage::disk(Justificativa::DISCO);
        abort_unless($disco->exists($justificativa->anexo_path), 404);

        return $disco->download($justificativa->anexo_path, $justificativa->anexo_nome ?: 'atestado', [
            'Content-Type'           => $justificativa->anexo_mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
