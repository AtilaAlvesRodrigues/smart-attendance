<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\SolicitacaoAcesso;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Mail\PrimeiroAcessoMail;
use Illuminate\Routing\Controller as BaseController;

class SolicitacaoAcessoController extends BaseController
{
    // ─── Público ──────────────────────────────────────────────────────────────

    public function show(string $tipo)
    {
        abort_unless(in_array($tipo, ['aluno', 'professor']), 404);
        return view('solicitar-acesso', compact('tipo'));
    }

    public function store(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, ['aluno', 'professor']), 404);

        $rules = [
            'nome'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cpf'   => 'required|string|max:20',
        ];

        if ($tipo === 'aluno') {
            $rules['ra'] = 'required|string|max:50';
        }

        $request->validate($rules);

        $emailHash = SolicitacaoAcesso::generateBlindIndex($request->email);
        $cpfHash   = SolicitacaoAcesso::generateBlindIndex($request->cpf);

        // Verificar se já existe solicitação pendente com mesmo email
        $jaPendente = SolicitacaoAcesso::where('email_search', $emailHash)
            ->where('status', 'pendente')
            ->exists();

        if ($jaPendente) {
            return redirect()->back()
                ->withErrors(['email' => 'Já existe uma solicitação pendente com este e-mail.'])
                ->withInput();
        }

        // Verificar se já existe conta ativa com o mesmo email ou CPF
        $modelClass = $tipo === 'aluno' ? AlunoModel::class : ProfessorModel::class;

        if ($modelClass::where('email_search', $emailHash)->exists()) {
            return redirect()->back()
                ->withErrors(['email' => 'Este e-mail já está cadastrado no sistema.'])
                ->withInput();
        }

        if ($modelClass::where('cpf_search', $cpfHash)->exists()) {
            return redirect()->back()
                ->withErrors(['cpf' => 'Este CPF já está cadastrado no sistema.'])
                ->withInput();
        }

        $data = [
            'tipo'         => $tipo,
            'nome'         => $request->nome,
            'nome_search'  => SolicitacaoAcesso::generateBlindIndex($request->nome),
            'email'        => $request->email,
            'email_search' => $emailHash,
            'cpf'          => $request->cpf,
            'cpf_search'   => $cpfHash,
            'status'       => 'pendente',
        ];

        if ($tipo === 'aluno') {
            $data['ra']        = $request->ra;
            $data['ra_search'] = SolicitacaoAcesso::generateBlindIndex($request->ra);
        }

        SolicitacaoAcesso::create($data);

        return redirect()->back()->with('success',
            'Solicitação enviada! O administrador analisará seu pedido e você receberá as instruções por e-mail.'
        );
    }

    public function verificarEmail(Request $request, string $tipo): \Illuminate\Http\JsonResponse
    {
        abort_unless(in_array($tipo, ['aluno', 'professor']), 404);

        $request->validate(['email' => 'required|email|max:255']);

        $emailHash  = SolicitacaoAcesso::generateBlindIndex($request->email);
        $modelClass = $tipo === 'aluno' ? AlunoModel::class : ProfessorModel::class;

        if ($modelClass::where('email_search', $emailHash)->exists()) {
            return response()->json([
                'status'  => 'existe',
                'message' => 'Este e-mail já possui uma conta ativa no sistema. Use a opção "Esqueci minha senha" se precisar recuperar o acesso.',
            ]);
        }

        $pendente = SolicitacaoAcesso::where('email_search', $emailHash)
            ->where('status', 'pendente')
            ->exists();

        if ($pendente) {
            return response()->json([
                'status'  => 'pendente',
                'message' => 'Já existe uma solicitação pendente com este e-mail. Aguarde a análise do administrador.',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    // ─── Master ────────────────────────────────────────────────────────────────

    public function index()
    {
        $solicitacoes = SolicitacaoAcesso::orderByRaw("CASE status WHEN 'pendente' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('master.solicitacoes', compact('solicitacoes'));
    }

    public function aprovar(Request $request, SolicitacaoAcesso $solicitacao)
    {
        if ($solicitacao->status !== 'pendente') {
            return redirect()->back()->withErrors(['erro' => 'Esta solicitação já foi processada.']);
        }

        $emailHash = SolicitacaoAcesso::generateBlindIndex($solicitacao->email);
        $modelClass = $solicitacao->tipo === 'aluno' ? AlunoModel::class : ProfessorModel::class;

        // Verificar duplicata no momento da aprovação (RN-SOL-04)
        if ($modelClass::where('email_search', $emailHash)->exists()) {
            $solicitacao->update(['status' => 'rejeitado']);
            return redirect()->back()->withErrors([
                'erro' => 'Já existe uma conta com este e-mail. Solicitação rejeitada automaticamente.'
            ]);
        }

        $cpfHashCheck = $modelClass::generateBlindIndex($solicitacao->cpf ?? '');
        if ($modelClass::where('cpf_search', $cpfHashCheck)->exists()) {
            $solicitacao->update(['status' => 'rejeitado']);
            return redirect()->back()->withErrors([
                'erro' => 'Já existe uma conta com este CPF. Solicitação rejeitada automaticamente.'
            ]);
        }

        $token = Str::random(32);

        if ($solicitacao->tipo === 'aluno') {
            $cpfHash = AlunoModel::generateBlindIndex($solicitacao->cpf ?? '');
            $raHash  = AlunoModel::generateBlindIndex($solicitacao->ra ?? '');

            $user = AlunoModel::create([
                'nome'         => $solicitacao->nome,
                'nome_search'  => AlunoModel::generateBlindIndex($solicitacao->nome),
                'email'        => $solicitacao->email,
                'email_search' => $emailHash,
                'cpf'          => $solicitacao->cpf,
                'cpf_search'   => $cpfHash,
                'ra'           => $solicitacao->ra,
                'ra_search'    => $raHash,
                'password'     => Str::random(40), // senha temporária bloqueada
                'remember_token' => $token,
                'role'         => 'aluno',
            ]);

            $loginUrl = route('login.aluno.form');
        } else {
            $cpfHash = ProfessorModel::generateBlindIndex($solicitacao->cpf ?? '');

            $user = ProfessorModel::create([
                'nome'         => $solicitacao->nome,
                'nome_search'  => ProfessorModel::generateBlindIndex($solicitacao->nome),
                'email'        => $solicitacao->email,
                'email_search' => $emailHash,
                'cpf'          => $solicitacao->cpf,
                'cpf_search'   => $cpfHash,
                'password'     => Str::random(40),
                'remember_token' => $token,
                'role'         => 'professor',
            ]);

            $loginUrl = route('login.professor.form');
        }

        $masterId = Auth::guard('masters')->id();
        $solicitacao->update(['status' => 'aprovado', 'aprovado_por' => $masterId]);

        Mail::to($user->email)->send(new PrimeiroAcessoMail(
            nomeUsuario: $user->nome,
            emailUsuario: $user->email,
            token: $token,
            loginUrl: $loginUrl,
        ));

        return redirect()->back()->with('success', "Conta criada e e-mail de acesso enviado para {$solicitacao->email}.");
    }

    public function rejeitar(Request $request, SolicitacaoAcesso $solicitacao)
    {
        if ($solicitacao->status !== 'pendente') {
            return redirect()->back()->withErrors(['erro' => 'Esta solicitação já foi processada.']);
        }

        $masterId = Auth::guard('masters')->id();
        $solicitacao->update([
            'status'           => 'rejeitado',
            'motivo_rejeicao'  => $request->input('motivo'),
            'aprovado_por'     => $masterId,
        ]);

        return redirect()->back()->with('success', 'Solicitação rejeitada.');
    }
}
