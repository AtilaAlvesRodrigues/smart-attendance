<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Criar Matérias
        $materias = [
            [
                'nome' => 'Matemática Discreta',
                'horario_matutino' => null,
                'horario_vespertino' => null,
                'horario_noturno' => '19:00 - 21:30',
                'sala' => 'B-101',
                'carga_horaria' => 60,
            ],
            [
                'nome' => 'Algoritmos e Estrutura de Dados',
                'horario_matutino' => '08:00 - 10:30',
                'horario_vespertino' => null,
                'horario_noturno' => '20:40 - 22:30',
                'sala' => 'C-205',
                'carga_horaria' => 80,
            ],
            [
                'nome' => 'Banco de Dados',
                'horario_matutino' => '08:00 - 10:30',
                'horario_vespertino' => '14:00 - 16:30',
                'horario_noturno' => null,
                'sala' => 'Lab-03',
                'carga_horaria' => 60,
            ],
            [
                'nome' => 'Engenharia de Software',
                'horario_matutino' => null,
                'horario_vespertino' => null,
                'horario_noturno' => '20:40 - 22:30',
                'sala' => 'C-208',
                'carga_horaria' => 60,
            ],
            [
                'nome' => 'Inteligência Artificial',
                'horario_matutino' => null,
                'horario_vespertino' => '14:00 - 16:30',
                'horario_noturno' => '19:00 - 21:30',
                'sala' => 'Lab-05',
                'carga_horaria' => 60,
            ],
            [
                'nome' => 'Redes de Computadores',
                'horario_matutino' => null,
                'horario_vespertino' => null,
                'horario_noturno' => '20:40 - 22:30',
                'sala' => 'Lab-02',
                'carga_horaria' => 60,
            ],
            [
                'nome' => 'Sistemas Operacionais',
                'horario_matutino' => '08:00 - 10:50',
                'horario_vespertino' => null,
                'horario_noturno' => null,
                'sala' => 'Lab-01',
                'carga_horaria' => 60,
            ],
        ];

        foreach ($materias as $materiaData) {
            Materia::firstOrCreate(['nome' => $materiaData['nome']], $materiaData);
        }

        // 2. Vincular Professores e Alunos às Matérias (Aleatoriamente)
        // O usuário pediu "o professor so tem 2 ou 3 materias não todas"

        $todasMaterias = Materia::all();
        $professores = ProfessorModel::all();
        $alunos = AlunoModel::all();

        if ($professores->isEmpty() || $alunos->isEmpty()) {
            $this->command->info('Atenção: Não há professores ou alunos para vincular.');
            return;
        }

        // Lógica Inversa: Para cada Professor, vincular 1 a 3 matérias
        foreach ($professores as $prof) {
            $materiasParaVincular = $todasMaterias->random(rand(2, 3)); 
            // rand(2, 3) garante "2 ou 3" matérias
            foreach ($materiasParaVincular as $mat) {
                 $mat->professores()->syncWithoutDetaching([$prof->id]);
            }
        }

        // Para Alunos, vincular a 3 a 5 matérias
        foreach ($alunos as $aluno) {
            $materiasParaVincular = $todasMaterias->random(rand(3, 5));
            foreach ($materiasParaVincular as $mat) {
                $mat->alunos()->syncWithoutDetaching([$aluno->id]);
            }
        }
    }
}
