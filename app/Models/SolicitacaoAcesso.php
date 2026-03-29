<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasBlindIndex;

class SolicitacaoAcesso extends Model
{
    use HasBlindIndex;

    protected $table = 'solicitacoes_acesso';

    protected $fillable = [
        'tipo',
        'nome',
        'nome_search',
        'email',
        'email_search',
        'cpf',
        'cpf_search',
        'ra',
        'ra_search',
        'status',
        'motivo_rejeicao',
        'aprovado_por',
    ];

    protected $casts = [
        'nome'  => 'encrypted',
        'email' => 'encrypted',
        'cpf'   => 'encrypted',
        'ra'    => 'encrypted',
    ];

    public function getBlindIndexFields(): array
    {
        return [
            'nome'  => 'nome_search',
            'email' => 'email_search',
            'cpf'   => 'cpf_search',
            'ra'    => 'ra_search',
        ];
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(UsuarioMaster::class, 'aprovado_por');
    }

    public function scopePendente($query)
    {
        return $query->where('status', 'pendente');
    }
}
