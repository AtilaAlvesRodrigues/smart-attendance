<?php

namespace App\Http\Controllers;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use App\Support\LoginPendente;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Autenticação em dois fatores: desafio no login, ativação, desativação e
 * reset pelo Master.
 */
class DoisFatoresController extends BaseController
{
    private const GUARDS = ['masters', 'professores', 'alunos'];

    // ---- Desafio (depois da senha) -------------------------------------

    public function desafio(Request $request)
    {
        if (! LoginPendente::atual($request)) {
            return redirect()->route('login_form')->with('status', 'Sua sessão de login expirou. Entre novamente.');
        }

        return view('dois-fatores.desafio');
    }

    public function verificarDesafio(Request $request)
    {
        $pendente = LoginPendente::atual($request);

        if (! $pendente) {
            return redirect()->route('login_form')->with('status', 'Sua sessão de login expirou. Entre novamente.');
        }

        $request->validate(['codigo' => 'required|string|max:32']);

        $usuario = LoginPendente::usuario($pendente);

        if (! $usuario || ! $usuario->verificarCodigoDoisFatores($request->input('codigo'))) {
            Log::warning('Código 2FA inválido', ['guard' => $pendente['guard'], 'ip' => $request->ip()]);

            throw ValidationException::withMessages(['codigo' => 'Código inválido. Confira o app autenticador e tente de novo.']);
        }

        return LoginPendente::concluir($request, $pendente);
    }

    // ---- Configuração (usuário logado) ---------------------------------

    public function configurar(Request $request)
    {
        [$guard, $usuario] = $this->usuarioLogado();

        $dados = [
            'usuario'      => $usuario,
            'guard'        => $guard,
            'obrigatorio'  => $guard === 'masters' && config('seguranca.2fa_obrigatorio_master'),
            'codigos'      => session('codigos_recuperacao'),
            'painel'       => $this->painel($guard),
        ];

        if (! $usuario->temDoisFatores()) {
            $segredo = $request->session()->get('2fa.segredo_novo') ?: $usuario::novoSegredoDoisFatores();
            $request->session()->put('2fa.segredo_novo', $segredo);

            // O rótulo usa o nome, nunca o e-mail (PII fica fora do QR e do app)
            $dados['segredo'] = $segredo;
            $dados['otpauth'] = (new Google2FA())->getQRCodeUrl('Smart Attendance', $usuario->nome ?: 'Usuario', $segredo);
        }

        return view('dois-fatores.configurar', $dados);
    }

    public function confirmar(Request $request)
    {
        [, $usuario] = $this->usuarioLogado();

        // Com o 2FA já ativo, trocar o segredo exige desativar antes (com senha)
        abort_if($usuario->temDoisFatores(), 409, 'A verificação em duas etapas já está ativa.');

        $request->validate([
            'codigo' => 'required|string|max:10',
            'senha'  => 'required|string|max:255',
        ], ['senha.required' => 'Informe sua senha para ativar.']);

        // Reautenticação: uma sessão roubada não consegue prender o dono fora da conta
        if (! Hash::check($request->input('senha'), $usuario->password)) {
            throw ValidationException::withMessages(['senha' => 'Senha incorreta.']);
        }

        $segredo = $request->session()->get('2fa.segredo_novo');

        if (! $segredo || ! $usuario->confirmarDoisFatores($segredo, $request->input('codigo'))) {
            throw ValidationException::withMessages(['codigo' => 'Código inválido. Digite os 6 números que aparecem agora no app.']);
        }

        $request->session()->forget('2fa.segredo_novo');
        $codigos = $usuario->gerarCodigosRecuperacao();

        return redirect()->route('dois-fatores.configurar')
            ->with('codigos_recuperacao', $codigos)
            ->with('status', 'Autenticação em dois fatores ativada.');
    }

    public function desativar(Request $request)
    {
        [$guard, $usuario] = $this->usuarioLogado();
        $request->validate(['senha' => 'required|string|max:255']);

        if (! Hash::check($request->input('senha'), $usuario->password)) {
            throw ValidationException::withMessages(['senha' => 'Senha incorreta.']);
        }

        if ($guard === 'masters' && config('seguranca.2fa_obrigatorio_master')) {
            throw ValidationException::withMessages(['senha' => 'Para o Master, a autenticação em dois fatores é obrigatória.']);
        }

        $usuario->desativarDoisFatores();

        return redirect()->route('dois-fatores.configurar')->with('status', 'Autenticação em dois fatores desativada.');
    }

    // ---- Reset pelo Master ----------------------------------------------

    public function resetar(string $tipo, int $id)
    {
        $modelo = match ($tipo) {
            'aluno'     => AlunoModel::class,
            'professor' => ProfessorModel::class,
            'master'    => UsuarioMaster::class,
        };

        $usuario = $modelo::findOrFail($id);
        $usuario->desativarDoisFatores();

        Log::info('2FA resetado pelo Master', [
            'master_id' => Auth::guard('masters')->id(),
            'tipo'      => $tipo,
            'id'        => $id,
        ]);

        return back()->with('status', 'Autenticação em dois fatores removida. No próximo login, a pessoa entra só com a senha.');
    }

    // ---------------------------------------------------------------------

    private function usuarioLogado(): array
    {
        foreach (self::GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                return [$guard, Auth::guard($guard)->user()];
            }
        }

        abort(401);
    }

    private function painel(string $guard): string
    {
        return match ($guard) {
            'masters'     => route('dashboard.master'),
            'professores' => route('dashboard.professor'),
            default       => route('dashboard.aluno'),
        };
    }
}
