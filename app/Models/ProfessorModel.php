<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


use App\Traits\HasBlindIndex;


use Illuminate\Database\Eloquent\SoftDeletes;


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
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'nome' => 'encrypted',
        'email' => 'encrypted',
        'cpf' => 'encrypted',
    ];

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_professor', 'professor_id', 'materia_id');
    }
}
