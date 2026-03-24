<?php

use App\Http\Controllers\AlunoLoginController;
use App\Http\Controllers\ProfessorLoginController;
use App\Http\Controllers\LoginRouterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PdfTesteVulnerabilidadeController;
use App\Http\Controllers\MasterSearchController;
use App\Http\Controllers\PresencaController;
use App\Http\Controllers\GerenciarMateriaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::post('/logout', function (Request $request) {
    Auth::guard('professores')->logout();
    Auth::guard('alunos')->logout();
    Auth::guard('masters')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login_form');
})->name('logout');

Route::post('/force-logout-beacon', function (Request $request) {
    Auth::guard('professores')->logout();
    Auth::guard('alunos')->logout();
    Auth::guard('masters')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->noContent();
});

Route::post('/force-logout', function (Request $request) {
    Auth::guard('professores')->logout();
    Auth::guard('alunos')->logout();
    Auth::guard('masters')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login_form')->with('status', 'Sessão forçada a expirar.');
});

Route::get('/', function () { return redirect()->route('login_form'); });
Route::get('/login', [LoginRouterController::class, 'showLoginForm'])->name('login_form');

Route::get('/login/aluno', [AlunoLoginController::class, 'showLoginForm'])->name('login.aluno.form');
Route::post('/login/aluno', [AlunoLoginController::class, 'attemptAuthentication'])     
    ->middleware('throttle:10,3')
    ->name('login.aluno');

Route::get('/login/professor', [ProfessorLoginController::class, 'showLoginForm'])->name('login.professor.form');
Route::post('/login/professor', [ProfessorLoginController::class, 'attemptAuthentication'])
    ->middleware('throttle:10,3')
    ->name('login.professor');

Route::post('/login', function (Request $request) {
    return redirect()->route('login.aluno');
})->name('login');

Route::get('/presenca/confirmar/{codigo_aula}', [PresencaController::class, 'confirmarPresenca'])
    ->name('presenca.confirmar');

Route::middleware(['auth:professores,alunos,masters'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    Route::get('/pdf-teste-vulnerabilidade', [PdfTesteVulnerabilidadeController::class, 'index'])->name('pdf.teste.vulnerabilidade');

    Route::middleware(['auth:alunos', 'role:aluno'])->group(function () {
        Route::get('/dashboard/aluno', [DashboardController::class, 'alunoIndex'])->name('dashboard.aluno');
    });

    Route::middleware(['auth:professores', 'role:professor'])->prefix('professor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'professorIndex'])->name('dashboard.professor');

        Route::prefix('presenca')->group(function () {
            Route::get('/', [PresencaController::class, 'index'])->name('professor.presenca.index');
            Route::get('/gerar/{materia_id}', [PresencaController::class, 'gerarQr'])->name('professor.presenca.gerar');
            Route::get('/check/{codigo_aula}', [PresencaController::class, 'getPresencas'])->name('professor.presenca.check');
        });

        Route::prefix('gerenciar')->group(function () {
            Route::get('/', [GerenciarMateriaController::class, 'index'])->name('professor.gerenciar.index');
            Route::get('/relatorios', [GerenciarMateriaController::class, 'relatorios'])->name('professor.relatorios');
            Route::get('/{materia_id}', [GerenciarMateriaController::class, 'show'])->name('professor.gerenciar.materia');
            Route::post('/{materia_id}/notas', [GerenciarMateriaController::class, 'salvarNotas'])->name('professor.gerenciar.salvar_notas');
        });
    });

    Route::middleware(['auth:masters'])->prefix('dashboard/master')->group(function () {
         
        Route::get('/', [DashboardController::class, 'masterIndex'])->name('dashboard.master');

        Route::get('/professores', [DashboardController::class, 'masterProfessores'])->name('master.professores');
        Route::get('/alunos', [DashboardController::class, 'masterAlunos'])->name('master.alunos');
        Route::get('/materias', [DashboardController::class, 'masterMaterias'])->name('master.materias');
        Route::get('/presenca', [DashboardController::class, 'masterPresenca'])->name('master.presenca');

        Route::prefix('search')->group(function () {
            Route::get('/professores', [MasterSearchController::class, 'searchProfessores'])->name('master.search.professores');
            Route::get('/alunos', [MasterSearchController::class, 'searchAlunos'])->name('master.search.alunos');
            Route::get('/materias', [MasterSearchController::class, 'searchMaterias'])->name('master.search.materias');
            Route::get('/presencas', [MasterSearchController::class, 'searchPresencas'])->name('master.search.presencas');
        });
    });

});
