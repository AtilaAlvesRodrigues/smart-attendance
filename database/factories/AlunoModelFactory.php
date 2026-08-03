<?php

namespace Database\Factories;

use App\Models\AlunoModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory para AlunoModel.
 *
 * Os campos 'email', 'ra' e 'cpf' são armazenados com cast 'encrypted',
 * mas a factory pode receber valores em texto plano — o Eloquent aplica
 * a criptografia automaticamente ao persistir.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlunoModel>
 */
class AlunoModelFactory extends Factory
{
    protected $model = AlunoModel::class;

    public function definition(): array
    {
        // RA no formato típico de universidades brasileiras: 8 dígitos
        $ra = $this->faker->unique()->numerify('########');

        return [
            'nome'           => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'ra'             => $ra,
            'cpf'            => $this->faker->unique()->numerify('###.###.###-##'),
            'password'       => bcrypt('senha123'),
            'role'           => 'aluno',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Estado "pendente de ativação" — sem senha definida.
     * Representa um aluno que ainda não fez o primeiro acesso.
     */
    public function pendenteDeAtivacao(): static
    {
        // O estado "pendente" é identificado pelo remember_token preenchido.
        // O CriarSenhaController verifica: is_null($user->remember_token) para
        // saber se o token já foi consumido — nunca usa password para essa checagem.
        // Não passamos password => null pois a coluna tem NOT NULL no banco.
        return $this->state(fn (array $attributes) => [
            'remember_token' => Str::random(20),
        ]);
    }
}
