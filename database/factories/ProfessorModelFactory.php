<?php

namespace Database\Factories;

use App\Models\ProfessorModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory para ProfessorModel.
 *
 * Os campos 'email' e 'cpf' são armazenados com cast 'encrypted',
 * mas a factory pode receber valores em texto plano — o Eloquent aplica
 * a criptografia automaticamente ao persistir.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfessorModel>
 */
class ProfessorModelFactory extends Factory
{
    protected $model = ProfessorModel::class;

    public function definition(): array
    {
        return [
            'nome'           => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'cpf'            => $this->faker->unique()->numerify('###.###.###-##'),
            'password'       => bcrypt('senha123'),
            'role'           => 'professor',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Estado "pendente de ativação" — sem senha definida.
     * Representa um professor que ainda não fez o primeiro acesso.
     */
    public function pendenteDeAtivacao(): static
    {
        return $this->state(fn (array $attributes) => [
            'password'       => null,
            'remember_token' => Str::random(20),
        ]);
    }
}
