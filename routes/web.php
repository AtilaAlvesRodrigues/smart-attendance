<?php

use App\Http\Controllers\AlunoLoginController;
use App\Http\Controllers\ProfessorLoginController;
use App\Http\Controllers\LoginRouterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AmbienteController;
use App\Http\Controllers\PdfTesteVulnerabilidadeController;
use App\Http\Controllers\MasterSearchController;
use App\Http\Controllers\PresencaController;
use App\Http\Controllers\GerenciarMateriaController;
use App\Http\Controllers\CriarSenhaController;
use App\Http\Controllers\SolicitacaoAcessoController;
use App\Http\Controllers\EsqueciSenhaController;
use App\Http\Controllers\MasterCadastroController;
use App\Http\Controllers\MasterTurmaController;
use App\Http\Controllers\CorrecaoPresencaController;
use App\Http\Controllers\ExportacaoController;
use App\Http\Controllers\MasterEdicaoController;
use App\Http\Controllers\GraficosController;
use App\Http\Controllers\DoisFatoresController;
use App\Http\Controllers\MasterEmailController;
use App\Http\Controllers\JustificativaAlunoController;
use App\Http\Controllers\JustificativaProfessorController;
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

// Agendado na Vercel (vercel.json → crons), 1x por dia: uma consulta simples
// evita que o banco gratuito do Supabase seja pausado por falta de uso.
// A Vercel envia "Authorization: Bearer <CRON_SECRET>".
Route::get('/cron/manter-banco-ativo', function (Request $request) {
    $segredo = config('services.cron.secret');
    abort_unless($segredo && hash_equals('Bearer ' . $segredo, (string) $request->header('Authorization')), 404);

    \Illuminate\Support\Facades\DB::select('select 1');

    return response()->noContent();
})->middleware('throttle:5,1')->name('cron.manter-banco');
Route::get('/login', [LoginRouterController::class, 'showLoginForm'])->name('login_form');

Route::get('/login/aluno', [AlunoLoginController::class, 'showLoginForm'])->name('login.aluno.form');
Route::post('/login/aluno', [AlunoLoginController::class, 'attemptAuthentication'])
    ->middleware('throttle:login')
    ->name('login.aluno');

Route::get('/login/professor', [ProfessorLoginController::class, 'showLoginForm'])->name('login.professor.form');
Route::post('/login/professor', [ProfessorLoginController::class, 'attemptAuthentication'])
    ->middleware('throttle:login')
    ->name('login.professor'); 

// Informational "Saiba Mais" Pages
Route::get('/aluno/saiba-mais', function () { return view('pages.aluno-info'); })->name('aluno.info');
Route::get('/professor/saiba-mais', function () { return view('pages.professor-info'); })->name('professor.info');

// Interactive Demonstration Dashboards
Route::get('/aluno/demonstracao', function () { return view('pages.aluno-demo'); })->name('aluno.demo');
Route::get('/professor/demonstracao', function () { return view('pages.professor-demo'); })->name('professor.demo');

use App\Http\Controllers\EventoController;

Route::get('/evento/checkin', [EventoController::class, 'checkinForm'])->middleware('throttle:evento-formulario')->name('evento.checkin');
Route::post('/evento/checkin/process', [EventoController::class, 'processCheckin'])->middleware('throttle:evento-checkin')->name('evento.checkin.process');

Route::get('/professor/evento/presenca', [EventoController::class, 'presencaDashboard'])->middleware('auth:professores')->name('professor.evento.presenca');
Route::post('/professor/evento/encerrar', [EventoController::class, 'encerrarSessao'])->middleware('auth:professores')->name('professor.evento.encerrar');
Route::get('/professor/evento/checkins/{token}', [EventoController::class, 'getCheckins'])->middleware('auth:professores')->name('professor.evento.checkins');

Route::post('/login', function (Request $request) {
    return redirect()->route('login.aluno');
})->name('login');

Route::get('/presenca/confirmar/{codigo_aula}', [PresencaController::class, 'confirmarPresenca'])
    ->name('presenca.confirmar');


// Esqueci Minha Senha (público)
Route::get('/esqueci-senha/{tipo}', [EsqueciSenhaController::class, 'show'])->name('esqueci-senha.show');
Route::post('/esqueci-senha/{tipo}', [EsqueciSenhaController::class, 'send'])->middleware('throttle:recuperar-senha')->name('esqueci-senha.send');

// Solicitação de Acesso (público) — pedido de cadastro analisado pelo Master
Route::get('/solicitar-acesso/{tipo}', [SolicitacaoAcessoController::class, 'show'])->name('solicitar-acesso.show');
Route::post('/solicitar-acesso/{tipo}', [SolicitacaoAcessoController::class, 'store'])->middleware('throttle:solicitar-acesso')->name('solicitar-acesso.store');
Route::post('/solicitar-acesso/{tipo}/verificar-email', [SolicitacaoAcessoController::class, 'verificarEmail'])->middleware('throttle:20,1')->name('solicitar-acesso.verificar-email');

// Primeiro Acesso — Criação de senha definitiva
Route::middleware(['primeiro-acesso', 'throttle:criar-senha'])->group(function () {
    Route::get('/criar-senha', [CriarSenhaController::class, 'show'])->name('criar-senha.show');
    Route::post('/criar-senha', [CriarSenhaController::class, 'store'])->name('criar-senha.store');
});

// Autenticação em dois fatores: o desafio vem antes do login ser concluído
Route::get('/2fa/desafio', [DoisFatoresController::class, 'desafio'])->name('dois-fatores.desafio');
Route::post('/2fa/desafio', [DoisFatoresController::class, 'verificarDesafio'])->middleware('throttle:2fa-desafio')->name('dois-fatores.verificar');

Route::middleware(['auth:professores,alunos,masters'])->group(function () {

    Route::get('/2fa/configurar', [DoisFatoresController::class, 'configurar'])->name('dois-fatores.configurar');
    Route::post('/2fa/confirmar', [DoisFatoresController::class, 'confirmar'])->middleware('throttle:10,1')->name('dois-fatores.confirmar');
    Route::post('/2fa/desativar', [DoisFatoresController::class, 'desativar'])->middleware('throttle:5,1')->name('dois-fatores.desativar');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    Route::middleware(['auth:alunos', 'role:aluno'])->group(function () {
        Route::get('/dashboard/aluno', [DashboardController::class, 'alunoIndex'])->name('dashboard.aluno');

        // Justificativa de faltas
        Route::get('/aluno/justificativas', [JustificativaAlunoController::class, 'index'])->name('aluno.justificativas');
        Route::post('/aluno/justificativas', [JustificativaAlunoController::class, 'store'])->middleware('throttle:10,1')->name('aluno.justificativas.store');
        Route::get('/aluno/justificativas/{justificativa}/anexo', [JustificativaAlunoController::class, 'anexo'])->name('aluno.justificativas.anexo');
    });

    Route::middleware(['auth:professores', 'role:professor'])->prefix('professor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'professorIndex'])->name('dashboard.professor');

        Route::get('/justificativas', [JustificativaProfessorController::class, 'index'])->name('professor.justificativas');
        Route::post('/justificativas/{justificativa}/decidir', [JustificativaProfessorController::class, 'decidir'])->middleware('throttle:60,1')->name('professor.justificativas.decidir');
        Route::get('/justificativas/{justificativa}/anexo', [JustificativaProfessorController::class, 'anexo'])->name('professor.justificativas.anexo');

        Route::prefix('presenca')->group(function () {
            Route::get('/', [PresencaController::class, 'index'])->name('professor.presenca.index');
            Route::get('/gerar/{materia_id}', [PresencaController::class, 'gerarQr'])->name('professor.presenca.gerar');
            Route::get('/check/{codigo_aula}', [PresencaController::class, 'getPresencas'])->name('professor.presenca.check');
            Route::get('/token/{codigo_aula}', [PresencaController::class, 'tokenQr'])->middleware('throttle:60,1')->name('professor.presenca.token');
        });

        Route::prefix('gerenciar')->group(function () {
            Route::get('/', [GerenciarMateriaController::class, 'index'])->name('professor.gerenciar.index');
            Route::get('/relatorios', [GerenciarMateriaController::class, 'relatorios'])->name('professor.relatorios');
            Route::get('/relatorios/exportar', [ExportacaoController::class, 'professor'])->middleware('throttle:10,1')->name('professor.relatorios.exportar');
            Route::get('/{materia_id}', [GerenciarMateriaController::class, 'show'])->name('professor.gerenciar.materia');
            Route::post('/{materia_id}/notas', [GerenciarMateriaController::class, 'salvarNotas'])->name('professor.gerenciar.salvar_notas');

            Route::get('/{materia_id}/graficos', [GraficosController::class, 'professor'])->whereNumber('materia_id')->name('professor.graficos');
            Route::get('/{materia_id}/graficos.json', [GraficosController::class, 'professorJson'])->whereNumber('materia_id')->name('professor.graficos.json');

            // Chamadas já realizadas e correção manual de presença (com motivo e auditoria)
            Route::get('/{materia_id}/chamadas', [CorrecaoPresencaController::class, 'index'])->whereNumber('materia_id')->name('professor.chamadas');
            Route::get('/{materia_id}/chamadas/{codigo_aula}', [CorrecaoPresencaController::class, 'show'])->whereNumber('materia_id')->name('professor.chamadas.show');
            Route::post('/{materia_id}/chamadas/{codigo_aula}/presenca', [CorrecaoPresencaController::class, 'atualizar'])
                ->whereNumber('materia_id')->middleware('throttle:60,1')->name('professor.chamadas.presenca');
        });
    });

    Route::middleware(['auth:masters', 'role:master', 'exigir-2fa'])->prefix('dashboard/master')->group(function () {
         
        Route::get('/', [DashboardController::class, 'masterIndex'])->name('dashboard.master');

        // Relatório interno de testes de segurança: lista vulnerabilidades, então só o Master vê
        Route::get('/relatorio-seguranca', [PdfTesteVulnerabilidadeController::class, 'index'])->name('pdf.teste.vulnerabilidade');

        // Versão do PHP em execução e se já tem a correção das CVEs críticas (SEC-11). JSON, só Master.
        Route::get('/ambiente/php', [AmbienteController::class, 'php'])->middleware('throttle:10,1')->name('master.ambiente.php');

        // Editar e excluir (soft delete) alunos, professores e matérias
        Route::get('/{tipo}/{id}/editar', [MasterEdicaoController::class, 'editar'])
            ->whereIn('tipo', ['aluno', 'professor', 'materia'])->whereNumber('id')->name('master.editar');
        Route::put('/{tipo}/{id}', [MasterEdicaoController::class, 'atualizar'])
            ->whereIn('tipo', ['aluno', 'professor', 'materia'])->whereNumber('id')->name('master.atualizar');
        Route::delete('/{tipo}/{id}', [MasterEdicaoController::class, 'excluir'])
            ->whereIn('tipo', ['aluno', 'professor', 'materia'])->whereNumber('id')->name('master.excluir');

        Route::post('/{tipo}/{id}/resetar-2fa', [DoisFatoresController::class, 'resetar'])
            ->whereIn('tipo', ['aluno', 'professor', 'master'])->whereNumber('id')->name('master.resetar-2fa');

        Route::get('/graficos', [GraficosController::class, 'master'])->name('master.graficos');
        Route::get('/graficos.json', [GraficosController::class, 'masterJson'])->name('master.graficos.json');

        // Teste do servidor de e-mail institucional (SMTP)
        Route::post('/email/teste', [MasterEmailController::class, 'teste'])->middleware('throttle:3,1')->name('master.email.teste');

        Route::get('/professores', [DashboardController::class, 'masterProfessores'])->name('master.professores');
        Route::get('/alunos', [DashboardController::class, 'masterAlunos'])->name('master.alunos');
        Route::get('/materias', [DashboardController::class, 'masterMaterias'])->name('master.materias');
        Route::get('/presenca', [DashboardController::class, 'masterPresenca'])->name('master.presenca');
        Route::get('/presenca/exportar', [ExportacaoController::class, 'master'])->middleware('throttle:10,1')->name('master.presenca.exportar');
        Route::get('/cadastrar', fn () => view('master.cadastrar'))->name('master.cadastrar');
        Route::post('/cadastrar/aluno', [MasterCadastroController::class, 'cadastrarAluno'])->name('master.cadastrar.aluno');
        Route::post('/cadastrar/professor', [MasterCadastroController::class, 'cadastrarProfessor'])->name('master.cadastrar.professor');
        Route::post('/cadastrar/materia', [MasterCadastroController::class, 'cadastrarMateria'])->name('master.cadastrar.materia');

        Route::get('/materias/{materia}/turma', [MasterTurmaController::class, 'show'])->name('master.turma');
        Route::put('/materias/{materia}/turma', [MasterTurmaController::class, 'update'])->name('master.turma.update');

        Route::get('/solicitacoes', [SolicitacaoAcessoController::class, 'index'])->name('master.solicitacoes');
        Route::post('/solicitacoes/{solicitacao}/aprovar', [SolicitacaoAcessoController::class, 'aprovar'])->name('master.solicitacoes.aprovar');
        Route::post('/solicitacoes/{solicitacao}/rejeitar', [SolicitacaoAcessoController::class, 'rejeitar'])->name('master.solicitacoes.rejeitar');

        Route::prefix('search')->middleware('throttle:30,1')->group(function () {
            Route::get('/professores', [MasterSearchController::class, 'searchProfessores'])->name('master.search.professores');
            Route::get('/alunos', [MasterSearchController::class, 'searchAlunos'])->name('master.search.alunos');
            Route::get('/materias', [MasterSearchController::class, 'searchMaterias'])->name('master.search.materias');
            Route::get('/presencas', [MasterSearchController::class, 'searchPresencas'])->name('master.search.presencas');
        });
    });

});
