<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nome',
        'horario_matutino',
        'horario_vespertino',
        'horario_noturno',
        'sala',
        'carga_horaria',
        'total_aulas',
    ];

    public function alunos()
    {
        return $this->belongsToMany(AlunoModel::class, 'aluno_materia', 'materia_id', 'aluno_id')
            ->withPivot('prova1', 'trabalho1', 'trabalho2', 'prova2', 'id')
            ->withTimestamps();
    }

    public function professores()
    {
        return $this->belongsToMany(ProfessorModel::class, 'materia_professor', 'materia_id', 'professor_id');
    }
}
