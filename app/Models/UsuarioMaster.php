<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Traits\HasBlindIndex;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model UsuarioMaster
 *
 * Representa o administrador do sistema (papel: 'master').
 * Guard: 'masters' (isolado dos guards de alunos e professores).
 *
 * RESPONSABILIDADES:
 * - Cadastrar alunos, professores e matérias diretamente.
 * - Aprovar ou rejeitar solicitações de acesso enviadas via formulário público.
 * - Visualizar todos os relatórios de presença consolidados.
 * - Realizar buscas por qualquer usuário ou registro no sistema.
 *
 * NOTA: Nas notas do projeto este perfil é chamado de "Coordenador",
 * mas no código o modelo e a tabela usam o nome "Master".
 */
class UsuarioMaster extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UsuarioMasterFactory> */
    use HasFactory, HasBlindIndex, SoftDeletes;

    /**
     * Define which fields have blind indexes for searching.
     */
    public function getBlindIndexFields(): array
    {
        return [
            'email' => 'email_search',
            'nome' => 'nome_search',
        ];
    }

    protected $table = 'usuario_masters';

    protected $fillable = [
        'nome',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'       => 'hashed',
        'nome'           => 'encrypted',
        'email'          => 'encrypted',
        'remember_token' => 'encrypted',
    ];
}
