<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


use App\Traits\HasBlindIndex;


use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Model ProfessorModel
 *
 * Representa um professor no sistema Smart Attendance.
 *
 * SEGURANÇA: Mesma arquitetura do AlunoModel — criptografia AES-256 nos campos PII
 * e Blind Indexes para busca. Guard isolado 'professores'.
 *
 * RESPONSABILIDADES:
 * - Gerar QR Codes de chamada para suas matérias.
 * - Visualizar presenças em tempo real no painel.
 * - Gerenciar notas e relatórios das turmas.
 */
class ProfessorModel extends Authenticatable
{
    use HasFactory, HasBlindIndex, SoftDeletes;

    /**
     * Define which fields have blind indexes for searching.
     */
    public function getBlindIndexFields(): array
    {
        return [
            'email' => 'email_search',
            'cpf' => 'cpf_search',
            'nome' => 'nome_search',
        ];
    }

    /**
     * Define o nome da tabela no banco de dados.
     * @var string
     */
    protected $table = 'professores';

    /**
     * Chave Primária Interna (Surrogate)
     */
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cpf',
        'nome',
        'email',
        'password',
        'role',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser ocultados para serialização, incluindo remember_token.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', // ⬅️ Essencial para a função "Lembrar de Mim"
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     * @var array<string, string>
     */
    protected $casts = [
        'password'          => 'hashed',
        'email_verified_at' => 'datetime',
        'email'             => 'encrypted',
        'cpf'               => 'encrypted',
        'remember_token'    => 'encrypted',
    ];

    /**
     * Matérias ministradas pelo professor.
     * Relação via tabela pivot 'materia_professor'.
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_professor', 'professor_id', 'materia_id');
    }
}
