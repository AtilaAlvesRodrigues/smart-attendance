<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Materia;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;

class GerenciarMateriaController extends BaseController
{
    public function index()
    {
        $professor = Auth::guard('professores')->user();
        $materias = $professor->materias()->withCount('alunos')->get();

        return view('professor.gerenciar.index', compact('professor', 'materias'));
    }

    public function show($materia_id)
    {
        $professor = Auth::guard('professores')->user();

        $materia = $professor->materias()->where('materias.id', $materia_id)->first();

        if (!$materia) {
            abort(403, 'Você não tem permissão para acessar esta matéria.');
        }

        $alunos = $materia->alunos()->orderBy('nome')->get();

        // Total de aulas previstas no semestre (pré-definido no cadastro da matéria)
        $totalAulas = $materia->total_aulas;

        // Aulas já realizadas (chamadas efetivamente feitas)
        $aulasRealizadas = Presenca::where('materia_id', $materia_id)
            ->distinct('data_aula')
            ->count('data_aula');

        $presencasPorAluno = Presenca::where('materia_id', $materia_id)
            ->select('aluno_id', DB::raw('COUNT(*) as total_presencas'))
            ->groupBy('aluno_id')
            ->pluck('total_presencas', 'aluno_id');

        return view('professor.gerenciar.materia', compact(
            'professor', 'materia', 'alunos', 'totalAulas', 'aulasRealizadas', 'presencasPorAluno'
        ));
    }

    public function relatorios(Request $request)
    {
        $professor = Auth::guard('professores')->user();
        $materias = $professor->materias;

        $query = Presenca::where('professor_id', $professor->id)
            ->with(['aluno', 'materia']);

        if ($request->filled('materia_id')) {
            $query->where('materia_id', $request->materia_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $presencas = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('professor.relatorios', compact('professor', 'materias', 'presencas'));
    }

    public function salvarNotas(Request $request, $materia_id)
    {
        $professor = Auth::guard('professores')->user();

        $materia = $professor->materias()->where('materias.id', $materia_id)->first();

        if (!$materia) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $request->validate([
            'aluno_ra' => 'required|string|max:50',
            'campo' => 'required|in:prova1,trabalho1,trabalho2,prova2',
            'valor' => 'nullable|numeric|min:0|max:10',
        ]);

        // Resolver RA para ID usando Blind Index para segurança
        $aluno = \App\Models\AlunoModel::where('ra_search', \App\Models\AlunoModel::generateBlindIndex($request->aluno_ra))->first();

        if (!$aluno) {
            return response()->json(['error' => 'Aluno não encontrado'], 404);
        }

        // Verifica se o aluno está matriculado nesta matéria
        $exists = DB::table('aluno_materia')
            ->where('aluno_id', $aluno->id)
            ->where('materia_id', $materia_id)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Aluno não matriculado nesta matéria'], 404);
        }

        $allowedFields = ['prova1', 'trabalho1', 'trabalho2', 'prova2'];
        $campo = in_array($request->campo, $allowedFields, true) ? $request->campo : null;

        if (!$campo) {
            return response()->json(['error' => 'Campo inválido'], 422);
        }

        DB::table('aluno_materia')
            ->where('aluno_id', $aluno->id)
            ->where('materia_id', $materia_id)
            ->update([
                $campo => $request->valor,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }
}
