<?php

namespace App\Http\Controllers;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Vínculos de uma matéria: professores responsáveis e alunos matriculados.
 *
 * Sem esta tela, o que o Master cadastrava não podia ser usado: um professor
 * novo não tinha matéria para fazer chamada e um aluno novo recebia "não
 * matriculado" ao escanear o QR Code.
 */
class MasterTurmaController extends Controller
{
    public function show(Materia $materia)
    {
        $materia->load(['professores:id', 'alunos:id']);

        return view('master.turma', [
            'materia'        => $materia,
            'professores'    => ProfessorModel::orderBy('nome')->get(['id', 'nome']),
            'alunos'         => AlunoModel::orderBy('nome')->get(['id', 'nome', 'ra']),
            'professoresIds' => $materia->professores->pluck('id')->all(),
            'alunosIds'      => $materia->alunos->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Materia $materia)
    {
        $dados = $request->validate([
            'professores'   => ['array'],
            'professores.*' => ['integer', 'exists:professores,id'],
            'alunos'        => ['array'],
            'alunos.*'      => ['integer', 'exists:alunos,id'],
        ], [
            'professores.*.exists' => 'Um dos professores selecionados não existe mais.',
            'alunos.*.exists'      => 'Um dos alunos selecionados não existe mais.',
        ]);

        $professores = array_map('intval', $dados['professores'] ?? []);
        $alunos = array_map('intval', $dados['alunos'] ?? []);

        DB::transaction(function () use ($materia, $professores, $alunos) {
            $materia->professores()->sync($professores);
            // sync() mantém a linha (e as notas) de quem continua matriculado
            $materia->alunos()->sync($alunos);
        });

        $totalAlunos = count($alunos);
        $totalProfs = count($professores);

        return redirect()
            ->route('master.turma', $materia)
            ->with('success', "Turma salva: {$totalProfs} " . ($totalProfs === 1 ? 'professor' : 'professores')
                . " e {$totalAlunos} " . ($totalAlunos === 1 ? 'aluno matriculado' : 'alunos matriculados') . '.');
    }
}
