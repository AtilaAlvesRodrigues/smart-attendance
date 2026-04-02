<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Presenca
 *
 * Registra cada confirmação de presença de um aluno em uma aula.
 *
 * CAMPOS IMPORTANTES:
 * - codigo_aula: código único do QR Code gerado para aquela sessão de aula.
 *   Serve como chave de idempotência — um aluno só pode registrar presença
 *   uma vez por codigo_aula (verificado no PresencaController).
 * - semestre: ex: "1/2026" — calculado automaticamente no momento do registro.
 * - horario: 'M' (Matutino), 'V' (Vespertino) ou 'N' (Noturno).
 * - data_aula: data em que a aula ocorreu (derivada do timestamp do QR Code).
 */
class Presenca extends Model
{
    use HasFactory;

    protected $table = 'presencas';

    protected $fillable = [
        'aluno_id',
        'professor_id',
        'materia_id',
        'data_aula',
        'semestre',
        'horario',
        'codigo_aula',
    ];

    public function aluno()
    {
        return $this->belongsTo(AlunoModel::class, 'aluno_id');
    }

    public function professor()
    {
        return $this->belongsTo(ProfessorModel::class, 'professor_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
