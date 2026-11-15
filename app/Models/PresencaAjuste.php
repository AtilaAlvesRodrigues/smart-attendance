<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de auditoria de uma presença alterada fora do QR Code.
 * Nunca é editado nem apagado pela aplicação.
 */
class PresencaAjuste extends Model
{
    public const UPDATED_AT = null;

    public const ACAO_MARCOU = 'marcou';
    public const ACAO_DESMARCOU = 'desmarcou';
    public const ACAO_ABONOU = 'abonou';

    protected $table = 'presenca_ajustes';

    protected $fillable = [
        'presenca_id',
        'materia_id',
        'aluno_id',
        'codigo_aula',
        'acao',
        'motivo',
        'autor_tipo',
        'autor_id',
    ];

    public function aluno()
    {
        return $this->belongsTo(AlunoModel::class, 'aluno_id');
    }
}
