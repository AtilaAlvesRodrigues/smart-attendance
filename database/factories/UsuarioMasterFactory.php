<?php

namespace Database\Factories;

use App\Models\UsuarioMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para UsuarioMaster.
 *
 * 'nome' e 'email' usam cast 'encrypted' — o Eloquent cifra ao persistir e
 * o trait HasBlindIndex preenche 'nome_search' / 'email_search'.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsuarioMaster>
 */
class UsuarioMasterFactory extends Factory
{
    protected $model = UsuarioMaster::class;

    public function definition(): array
    {
        return [
            'nome'     => $this->faker->name(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => 'senha123',
            'role'     => 'master',
        ];
    }
}
