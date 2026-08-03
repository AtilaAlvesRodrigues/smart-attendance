<?php

namespace Database\Factories;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Materia.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Materia>
 */
class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    private static array $disciplinas = [
        'Algoritmos e Programação',
        'Estruturas de Dados',
        'Banco de Dados',
        'Engenharia de Software',
        'Redes de Computadores',
        'Sistemas Operacionais',
        'Cálculo I',
        'Álgebra Linear',
        'Inteligência Artificial',
        'Segurança da Informação',
    ];

    public function definition(): array
    {
        return [
            'nome'                => $this->faker->unique()->randomElement(self::$disciplinas)
                                    . ' ' . $this->faker->randomLetter(),
            'carga_horaria'       => $this->faker->randomElement([60, 80, 120]),
            'total_aulas'         => $this->faker->numberBetween(20, 40),
            'sala'                => $this->faker->bothify('##?'),
            'horario_matutino'    => $this->faker->optional()->time('H:i'),
            'horario_vespertino'  => $this->faker->optional()->time('H:i'),
            'horario_noturno'     => $this->faker->optional()->time('H:i'),
        ];
    }
}
