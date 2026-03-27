<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\Materia;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $counts = [];

        if (Auth::guard('masters')->check()) {
            $counts = Cache::remember('sidebar_counts_master', 60, function () {
                return [
                    'total_professores' => ProfessorModel::count(),
                    'total_alunos'      => AlunoModel::count(),
                    'total_materias'    => Materia::count(),
                ];
            });
        } elseif (Auth::guard('professores')->check()) {
            $professor = Auth::guard('professores')->user();
            if ($professor) {
                $counts['minhas_materias'] = $professor->materias()->count();
            }
        }

        $view->with('sidebarCounts', $counts);
    }
}
