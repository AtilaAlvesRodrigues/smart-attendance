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
                $hash = ProfessorModel::generateBlindIndex($query);
                // Busca parcial por nome (não criptografado) OU exata por campos sensíveis (Blind Index)
                $q->where('nome', 'like', '%' . $query . '%')
                  ->orWhere('cpf_search', $hash)
                  ->orWhere('email_search', $hash)
                  ->orWhere('nome_search', $hash);
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
                $hash = AlunoModel::generateBlindIndex($query);
                // Busca parcial por nome (não criptografado) OU exata por campos sensíveis (Blind Index)
                $q->where('nome', 'like', '%' . $query . '%')
                  ->orWhere('ra_search', $hash)
                  ->orWhere('cpf_search', $hash)
                  ->orWhere('email_search', $hash)
                  ->orWhere('nome_search', $hash);
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
        // Mesma regra de filtro da exportação em planilha (DashboardController::filtrarPresencasMaster)
        $presencas = DashboardController::filtrarPresencasMaster($request)
            ->with(['aluno', 'aluno.materias', 'materia', 'professor']);

        // Limitamos para evitar sobrecarga no front e mantemos a data
        $presencas = $presencas->limit(50)->get();

        $results = $presencas->map(function ($p) {
            $materiaPivot = $p->aluno ? $p->aluno->materias->where('id', $p->materia_id)->first()->pivot ?? null : null;
            $sit = \App\Support\SituacaoAcademica::doAluno(
                $p->materia_id, $p->aluno_id, (int) ($p->materia->total_aulas ?? 0),
                $materiaPivot ? [$materiaPivot->prova1, $materiaPivot->trabalho1, $materiaPivot->trabalho2, $materiaPivot->prova2] : [],
            );
            $media = $sit->media;

            return [
                'id' => $p->id,
                'data_aula_formatted' => \Carbon\Carbon::parse($p->data_aula)->format('d/m/Y'),
                'created_time' => $p->created_at->format('H:i'),
                'aluno_nome' => $p->aluno->nome ?? 'N/A',
                'aluno_ra' => $p->aluno ? $p->aluno->ra : 'N/A',
                'materia_nome' => $p->materia->nome ?? 'N/A',
                'materia_sala' => $p->materia->sala ?? '',
                'professor_nome' => $p->professor->nome ?? 'N/A',
                'professor_cpf' => $p->professor ? $p->professor->cpf : 'N/A',
                'faltas' => $sit->faltas,
                'limite_faltas' => $sit->limiteFaltas,
                'reprovado_falta' => $sit->status === 'reprovado_falta',
                'media' => $media
            ];
        });

        return response()->json($results);
    }
}
