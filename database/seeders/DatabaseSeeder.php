<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UsuarioMaster;
use App\Models\ProfessorModel;
use App\Models\AlunoModel;
use App\Models\Materia;
use Illuminate\Support\Facades\DB;
use App\Models\Presenca; // Importar model Presenca

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UsuarioMaster::create([
            'nome' => 'Master Admin',
            'email' => 'master@admin.com',
            'password' => 'senha123',
        ]);

        $professorPrincipal = ProfessorModel::create([
            'nome' => 'Professor Silva',
            'email' => 'professor@teste.com',
            'cpf' => '12345678900', 
            'password' => 'senha123',
        ]);

        $professoresExtras = [];
        for ($i = 1; $i <= 5; $i++) {
            $professoresExtras[] = ProfessorModel::create([
                'nome' => "Professor Extra $i",
                'email' => "prof$i@teste.com",
                'cpf' => "1000000000$i",
                'password' => 'senha123',
            ]);
        }

        $alunoPrincipal = AlunoModel::create([
            'nome' => 'João Teste', 
            'ra' => '100000000', 
            'cpf' => '00000000000', 
            'email' => 'aluno.teste@site.com',
            'password' => 'senha123',
        ]);

        $alunosExtras = [];
        for ($i = 1; $i <= 20; $i++) {
            $alunosExtras[] = AlunoModel::create([
                'nome' => "Aluno Extra $i",
                'ra' => "20000000$i",
                'cpf' => "300000000$i", 
                'email' => "aluno$i@teste.com",
                'password' => 'senha123',
            ]);
        }

        $materiaWeb = Materia::create([
            'nome' => 'Desenvolvimento Web - Full Stack',
            'sala' => 'Lab 01',
            'carga_horaria' => 80,
            'total_aulas' => 40,
        ]);

        $materiaAvancada = Materia::create([
            'nome' => 'DevOps Avançado - Containers',
            'sala' => 'Lab 02',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);
        
        $materiasExtras = [];
        $nomesMaterias = ['Banco de Dados', 'Engenharia de Software', 'Inteligência Artificial', 'Redes de Computadores', 'Sistemas Operacionais'];
        foreach ($nomesMaterias as $nome) {
            $materiasExtras[] = Materia::create([
                'nome' => $nome,
                'sala' => 'Sala ' . rand(100, 200),
                'carga_horaria' => 60,
                'total_aulas' => 30,
            ]);
        }
        
        $todasMaterias = array_merge([$materiaWeb, $materiaAvancada], $materiasExtras);
        $todosAlunos = array_merge([$alunoPrincipal], $alunosExtras);
        $todosProfessores = array_merge([$professorPrincipal], $professoresExtras);

        $materiaWeb->professores()->syncWithoutDetaching([$professorPrincipal->id]);
        $materiaWeb->alunos()->syncWithoutDetaching([$alunoPrincipal->id]);

        for ($i = 0; $i < 15; $i++) {
            Presenca::create([
                'aluno_id' => $alunoPrincipal->id,
                'professor_id' => $professorPrincipal->id,
                'materia_id' => $materiaWeb->id,
                'data_aula' => now()->subDays($i * 2)->format('Y-m-d'),
                'semestre' => '2026/1',
                'horario' => 'N', 
                'codigo_aula' => 'AULA' . ($i + 1),
            ]);
        }
        
        $materiaOutra = $materiasExtras[0];
        $professorOutro = $professoresExtras[0]; 
        
        $materiaOutra->professores()->syncWithoutDetaching([$professorOutro->id]);
        $materiaOutra->alunos()->syncWithoutDetaching([$alunoPrincipal->id]); 

        array_shift($materiasExtras); 

        foreach ($materiasExtras as $materia) {
            $profAleatorio = $todosProfessores[array_rand($todosProfessores)];
            $materia->professores()->syncWithoutDetaching([$profAleatorio->id]);

            $alunosAleatoriosChaves = array_rand($todosAlunos, 10);
            foreach ($alunosAleatoriosChaves as $key) {
                $aluno = $todosAlunos[$key];
                $materia->alunos()->syncWithoutDetaching([$aluno->id]);

                if (rand(0, 1)) {
                    for ($p = 0; $p < 5; $p++) {
                        Presenca::create([
                            'aluno_id' => $aluno->id,
                            'professor_id' => $profAleatorio->id,
                            'materia_id' => $materia->id,
                            'data_aula' => now()->subDays($p * 7)->format('Y-m-d'),
                            'semestre' => '2026/1',
                            'horario' => 'M', 
                            'codigo_aula' => 'GEN' . $p,
                        ]);
                    }
                }
            }
        }
    }
}
