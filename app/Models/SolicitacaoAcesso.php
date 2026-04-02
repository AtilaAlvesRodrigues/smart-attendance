<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasBlindIndex;

/**
 * Model SolicitacaoAcesso
 *
 * Representa um pedido de cadastro enviado publicamente por um aluno ou professor
 * antes de ter uma conta no sistema.
 *
 * FLUXO:
 * 1. Usuário acessa /solicitar-acesso/aluno (ou /professor) e preenche o formulário.
 * 2. Registro criado com status 'pendente'.
 * 3. Master acessa /dashboard/master/solicitacoes e aprova ou rejeita.
 * 4. Se aprovado: conta criada + e-mail de primeiro acesso enviado automaticamente.
 *
 * SEGURANÇA: Campos PII (nome, email, cpf, ra) são criptografados com AES-256.
 * Blind Indexes permitem ao Master verificar duplicatas sem descriptografar.
 *
 * STATUS possíveis: 'pendente' | 'aprovado' | 'rejeitado'
 */
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
