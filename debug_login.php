<?php
// Debug script - delete after use
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Support\Facades\Hash;

echo "=== DEBUG LOGIN ===" . PHP_EOL;

// Test Aluno
$ra = '100000000';
$hash = AlunoModel::generateBlindIndex($ra);
echo "Blind Index para RA '$ra': " . substr($hash, 0, 20) . "..." . PHP_EOL;

$aluno = AlunoModel::where('ra_search', $hash)->first();
if ($aluno) {
    echo "ALUNO ENCONTRADO: " . $aluno->nome . PHP_EOL;
    $check = Hash::check('senha123', $aluno->password);
    echo "Hash::check('senha123') => " . ($check ? 'TRUE ✅' : 'FALSE ❌') . PHP_EOL;
} else {
    echo "ALUNO NAO ENCONTRADO via ra_search" . PHP_EOL;
    echo "Total alunos no banco: " . AlunoModel::count() . PHP_EOL;

    // Check raw DB
    $raw = \Illuminate\Support\Facades\DB::table('alunos')->select('ra_search')->first();
    echo "ra_search primeira linha: " . ($raw->ra_search ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL;

// Test Professor
$email = 'professor@teste.com';
$emailHash = ProfessorModel::generateBlindIndex($email);
$prof = ProfessorModel::where('email_search', $emailHash)->first();
if ($prof) {
    echo "PROFESSOR ENCONTRADO: " . $prof->nome . PHP_EOL;
    $check = Hash::check('senha123', $prof->password);
    echo "Hash::check('senha123') => " . ($check ? 'TRUE ✅' : 'FALSE ❌') . PHP_EOL;
} else {
    echo "PROFESSOR NAO ENCONTRADO via email_search" . PHP_EOL;
    echo "Total professores no banco: " . ProfessorModel::count() . PHP_EOL;
}

echo PHP_EOL;

// Test Master
$master = UsuarioMaster::where('email', 'master@admin.com')->first();
if ($master) {
    echo "MASTER ENCONTRADO" . PHP_EOL;
    $check = Hash::check('senha123', $master->password);
    echo "Hash::check('senha123') => " . ($check ? 'TRUE ✅' : 'FALSE ❌') . PHP_EOL;
} else {
    echo "MASTER NAO ENCONTRADO" . PHP_EOL;
}
