<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // O correto é usar este, não o Model padrão

use App\Traits\HasBlindIndex;

use Illuminate\Database\Eloquent\SoftDeletes;

class AlunoModel extends Authenticatable
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
            'ra' => 'ra_search',
            'nome' => 'nome_search',
        ];
    }

    /**
     * Define o nome da tabela no banco de dados.
     * @var string
     */
    protected $table = 'alunos';

    // Chave Primária Interna (Surrogate)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'ra',
        'cpf',
        'nome',
        'email',
        'password',
        'role',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser ocultados para serialização.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', 
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     * @var array<string, string>
     */
    protected $casts = [
        'password'       => 'hashed',
        'email'          => 'encrypted',
        'ra'             => 'encrypted',
        'cpf'            => 'encrypted',
        'remember_token' => 'encrypted',
    ];

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'aluno_materia', 'aluno_id', 'materia_id')
            ->withPivot('prova1', 'trabalho1', 'trabalho2', 'prova2', 'id')
            ->withTimestamps();
    }
}
