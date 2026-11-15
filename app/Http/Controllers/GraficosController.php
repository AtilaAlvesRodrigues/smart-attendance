<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Support\EstatisticasTurma;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard com gráficos de presença: por matéria (professor) e por turma (Master).
 */
class GraficosController extends BaseController
{
    public function professor(int $materia_id)
    {
        $materia = $this->materiaDoProfessor($materia_id);

        return view('professor.gerenciar.graficos', [
            'professor'   => Auth::guard('professores')->user(),
            'materia'     => $materia,
            'dados'       => EstatisticasTurma::daMateria($materia),
            'totalAlunos' => $materia->alunos()->count(),
        ]);
    }

    public function professorJson(int $materia_id)
    {
        return response()->json(EstatisticasTurma::daMateria($this->materiaDoProfessor($materia_id)));
    }

    public function master()
    {
        $turmas = EstatisticasTurma::geral();

        return view('master.graficos', [
            'master'    => Auth::guard('masters')->user(),
            'turmas'    => $turmas,
            'situacoes' => EstatisticasTurma::situacoesTotais($turmas),
        ]);
    }

    public function masterJson()
    {
        $turmas = EstatisticasTurma::geral();

        return response()->json(['turmas' => $turmas, 'situacoes' => EstatisticasTurma::situacoesTotais($turmas)]);
    }

    private function materiaDoProfessor(int $materiaId): Materia
    {
        $materia = Auth::guard('professores')->user()->materias()->where('materias.id', $materiaId)->first();
        abort_unless($materia, 403, 'Você não tem permissão para acessar esta matéria.');

        return $materia;
    }
}
