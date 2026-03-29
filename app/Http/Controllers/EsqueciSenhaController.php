<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Mail\PrimeiroAcessoMail;
use Illuminate\Routing\Controller as BaseController;

class EsqueciSenhaController extends BaseController
{
    public function show(string $tipo)
    {
        abort_unless(in_array($tipo, ['aluno', 'professor']), 404);
        return view('esqueci-senha', compact('tipo'));
    }

    public function send(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, ['aluno', 'professor']), 404);

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $emailHash = match ($tipo) {
            'aluno'     => AlunoModel::generateBlindIndex($request->email),
            'professor' => ProfessorModel::generateBlindIndex($request->email),
        };

        $user = match ($tipo) {
            'aluno'     => AlunoModel::where('email_search', $emailHash)->first(),
            'professor' => ProfessorModel::where('email_search', $emailHash)->first(),
        };

        // Resposta genérica — não revela se o e-mail existe ou não (RN-ESQ-03)
        $successMessage = 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.';

        if ($user) {
            $token = Str::random(12);
            $user->remember_token = $token;
            $user->save();

            $loginRoute = $tipo === 'aluno'
                ? route('login.aluno.form')
                : route('login.professor.form');

            Mail::to($user->email)->send(new PrimeiroAcessoMail(
                nomeUsuario: $user->nome,
                emailUsuario: $user->email,
                token: $token,
                loginUrl: $loginRoute,
            ));
        }

        return redirect()->back()->with('success', $successMessage);
    }
}
