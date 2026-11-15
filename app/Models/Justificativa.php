<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Justificativa de falta: o aluno explica (e pode anexar atestado) e o
 * professor da matéria aprova ou recusa. Aprovada, vira uma presença com
 * origem 'justificativa' (abono), e a falta deixa de contar.
 */
class Justificativa extends Model
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_RECUSADA = 'recusada';

    public const DISCO = 'justificativas';

    protected $table = 'justificativas';

    protected $fillable = [
        'aluno_id',
        'materia_id',
        'codigo_aula',
        'data_aula',
        'motivo',
        'anexo_path',
        'anexo_nome',
        'anexo_mime',
        'status',
    ];

    protected $casts = [
        'data_aula'   => 'date',
        'decidida_em' => 'datetime',
    ];

    public function aluno()
    {
        // Inclui cadastros excluídos (soft delete): o histórico continua legível
        return $this->belongsTo(AlunoModel::class, 'aluno_id')->withTrashed();
    }

    public function materia()
    {
        // Inclui cadastros excluídos (soft delete): o histórico continua legível
        return $this->belongsTo(Materia::class, 'materia_id')->withTrashed();
    }

    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function estaPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }

    public function rotuloStatus(): string
    {
        return match ($this->status) {
            self::STATUS_APROVADA => 'Aprovada',
            self::STATUS_RECUSADA => 'Recusada',
            default               => 'Em análise',
        };
    }

    /**
     * Upload de atestado disponível? Na Vercel (produção ou preview) o disco
     * local é somente leitura e efêmero: sem o Supabase Storage configurado, a
     * justificativa segue só com texto em vez de quebrar.
     */
    public static function anexoHabilitado(): bool
    {
        $driver = config('filesystems.disks.' . self::DISCO . '.driver');

        $naVercel = ! empty($_SERVER['VERCEL'] ?? $_ENV['VERCEL'] ?? getenv('VERCEL'));

        return ! ($driver === 'local' && ($naVercel || app()->environment('production')));
    }
}
