<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Materia
 *
 * Representa uma disciplina do curso no sistema Smart Attendance.
 *
 * CAMPOS IMPORTANTES:
 * - total_aulas: total de aulas previstas no semestre (usado para calcular o limite de faltas = 25%).
 * - horario_matutino / horario_vespertino / horario_noturno: horários configurados por turno.
 * - sala: identificador da sala (pode ser usado futuramente para validação de geolocalização).
 *
 * SoftDeletes: matérias nunca são apagadas fisicamente.
 */
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
