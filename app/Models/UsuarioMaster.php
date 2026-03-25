<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Traits\HasBlindIndex;

use Illuminate\Database\Eloquent\SoftDeletes;

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
        'password' => 'hashed',
        'nome' => 'encrypted',
        'email' => 'encrypted',
    ];
}
