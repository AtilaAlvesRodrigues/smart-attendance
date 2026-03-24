<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Models\ProfessorModel;
use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;

class MasterSearchController extends BaseController
{

    public function searchProfessores(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $query = $request->input('q');

        $professores = ProfessorModel::with('materias');

        if ($query) {
            $professores->where(function($q) use ($query) {
                $q->where('nome', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%')
                  ->orWhere('cpf', 'like', '%' . $query . '%');
            });
        }

        $results = $professores->orderBy('nome')->limit(50)->get();

        return response()->json($results);
    }

    public function searchAlunos(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $query = $request->input('q');

        $alunos = AlunoModel::with('materias');

        if ($query) {
            $alunos->where(function($q) use ($query) {
                $q->where('nome', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%')
                  ->orWhere('ra', 'like', '%' . $query . '%')
                  ->orWhere('cpf', 'like', '%' . $query . '%');
            });
        }

        $results = $alunos->orderBy('nome')->limit(50)->get();

        return response()->json($results);
    }

    public function searchMaterias(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);
        $query = $request->input('q');

        $materias = Materia::with(['professores', 'alunos'])
            ->withCount(['professores', 'alunos']);

        if ($query) {
            $materias->where(function($q) use ($query) {
                $q->where('nome', 'like', '%' . $query . '%')
                  ->orWhere('sala', 'like', '%' . $query . '%');
            });
        }
        
        $results = $materias->orderBy('nome')->limit(50)->get();

        return response()->json($results);
    }

    public function searchPresencas(Request $request)
    {
        $presencas = Presenca::with(['aluno', 'aluno.materias', 'materia', 'professor']);

        if ($request->filled('professor')) {
            $presencas->whereHas('professor', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->professor . '%')
                  ->orWhere('cpf', 'like', '%' . $request->professor . '%');
            });
        }

        if ($request->filled('materia')) {
            $presencas->whereHas('materia', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->materia . '%')
                  ->orWhere('sala', 'like', '%' . $request->materia . '%');
            });
        }

        if ($request->filled('aluno')) {
            $presencas->whereHas('aluno', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->aluno . '%')
                  ->orWhere('ra', 'like', '%' . $request->aluno . '%');
            });
        }

        // Limitamos para evitar sobrecarga no front e mantemos a data
        $presencas = $presencas->orderBy('created_at', 'desc')->limit(50)->get();

        $results = $presencas->map(function ($p) {
            $materiaPivot = $p->aluno ? $p->aluno->materias->where('id', $p->materia_id)->first()->pivot ?? null : null;
            $media = null;
            if ($materiaPivot) {
                $notas = collect([$materiaPivot->prova1, $materiaPivot->prova2, $materiaPivot->trabalho1, $materiaPivot->trabalho2])
                    ->filter(fn($n) => !is_null($n));
                if ($notas->isNotEmpty()) {
                    $media = round($notas->avg(), 1);
                }
            }

            $totalAulas = $p->materia->total_aulas ?? 0;
            $presencasAluno = Presenca::where('materia_id', $p->materia_id)
                ->where('aluno_ra', $p->aluno_ra)
                ->count();
            $faltas = max(0, $totalAulas - $presencasAluno);

            return [
                'id' => $p->id,
                'data_aula_formatted' => \Carbon\Carbon::parse($p->data_aula)->format('d/m/Y'),
                'created_time' => $p->created_at->format('H:i'),
                'aluno_nome' => $p->aluno->nome ?? 'N/A',
                'aluno_ra' => $p->aluno_ra,
                'materia_nome' => $p->materia->nome ?? 'N/A',
                'materia_sala' => $p->materia->sala ?? '',
                'professor_nome' => $p->professor->nome ?? 'N/A',
                'professor_cpf' => $p->professor_cpf,
                'faltas' => $faltas,
                'total_aulas' => $totalAulas,
                'media' => $media
            ];
        });

        return response()->json($results);
    }
}
