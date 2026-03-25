<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessorModel;
use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

class DashboardController extends BaseController
{
    public function professorIndex()
    {
        $professor = Auth::guard('professores')->user();
        
        $activeMateria = null;
        $activeCode = null;

        if ($professor) {
            foreach ($professor->materias as $materia) {
                $cacheKey = 'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d');
                $cacheData = Cache::get($cacheKey);

                if (is_array($cacheData)) {
                    $activeMateria = $materia;
                    $activeCode = $cacheData['codigo'];
                    break;
                }
            }
        }

        return view('professor.home', compact('professor', 'activeMateria', 'activeCode'));
    }

    public function alunoIndex()
    {
        $aluno = Auth::guard('alunos')->user()->load('materias');
        
        foreach ($aluno->materias as $materia) {
            // Conta quantas aulas (códigos únicos) existiram para essa matéria até agora
            $total_sessoes = Presenca::where('materia_id', $materia->id)
                ->distinct('codigo_aula')
                ->count('codigo_aula');
            
            // Conta as presenças do aluno
            $presencas_aluno = Presenca::where('materia_id', $materia->id)
                ->where('aluno_id', $aluno->id)
                ->count();
            
            // Faltas = Total de sessões que ocorreram - presenças confirmadas
            $materia->faltas = max(0, $total_sessoes - $presencas_aluno);
            
            // Limite de faltas (25% da carga horária total de aulas)
            $materia->limite_faltas = floor(($materia->total_aulas ?? 0) * 0.25);
        }

        return view('aluno.home', compact('aluno'));
    }

    public function masterIndex()
    {
        $master = Auth::guard('masters')->user();

        $professoresCount = ProfessorModel::count();
        $alunosCount = AlunoModel::count();
        $materiasCount = Materia::count();

        return view('master.home', compact(
            'master', 'professoresCount', 'alunosCount', 'materiasCount'
        ));
    }

    public function masterProfessores()
    {
        $professores = ProfessorModel::with('materias')->withCount('materias')->orderBy('nome')->paginate(10);
        return view('master.professores', compact('professores'));
    }

    public function masterAlunos()
    {
        $alunos = AlunoModel::with('materias')->withCount('materias')->orderBy('nome')->paginate(10);
        return view('master.alunos', compact('alunos'));
    }

    public function masterMaterias()
    {
        $materias = Materia::with('professores')->withCount(['professores', 'alunos'])->orderBy('nome')->paginate(10);
        return view('master.materias', compact('materias'));
    }

    public function masterPresenca(Request $request)
    {
        $query = Presenca::with(['aluno', 'aluno.materias', 'materia', 'professor']);

        if ($request->filled('professor')) {
            $query->whereHas('professor', function ($q) use ($request) {
                $hash = ProfessorModel::generateBlindIndex($request->professor);
                $q->where('nome_search', $hash)
                  ->orWhere('cpf_search', $hash)
                  ->orWhere('email_search', $hash);
            });
        }

        if ($request->filled('materia')) {
            $query->whereHas('materia', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->materia . '%');
            });
        }

        if ($request->filled('aluno')) {
            $query->whereHas('aluno', function ($q) use ($request) {
                $hash = AlunoModel::generateBlindIndex($request->aluno);
                $q->where('nome_search', $hash)
                  ->orWhere('ra_search', $hash)
                  ->orWhere('cpf_search', $hash);
            });
        }

        $presencas = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('master.presenca', compact('presencas'));
    }

    public function index()
    {
        if (Auth::guard('professores')->check()) {
            return redirect()->route('dashboard.professor');
        }

        if (Auth::guard('masters')->check()) {
            return redirect()->route('dashboard.master');
        }

        if (Auth::guard('alunos')->check()) {
            return redirect()->route('dashboard.aluno');
        }

        return redirect()->route('login_form');
    }
}
