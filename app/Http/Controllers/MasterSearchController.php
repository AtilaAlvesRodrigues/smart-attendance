<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Models\ProfessorModel;
use App\Models\AlunoModel;
use App\Models\Materia;

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
}
