<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\AlunoModel;
use Illuminate\Routing\Controller as BaseController;

class AlunoLoginController extends BaseController
{
    public function showLoginForm()
    {
        return view('aluno.login');
    }

    public function attemptAuthentication(Request $request)
    {
        $request->validate([
            'ra_email_cpf' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $login = $request->input('ra_email_cpf');
        $password = $request->input('password');
        $remember = $request->has('remember');

        // Tentativa como Aluno usando Blind Indexes para busca em dados criptografados
        $searchFields = ['ra_search', 'cpf_search', 'email_search'];
        $loginHash = AlunoModel::generateBlindIndex($login);

        $aluno = null;
        foreach ($searchFields as $searchColumn) {
            $aluno = AlunoModel::where($searchColumn, $loginHash)->first();
            if ($aluno) break;
        }

        // Verificação de primeiro acesso via remember_token (hash_equals evita timing attack)
        if ($aluno && !is_null($aluno->remember_token) && hash_equals((string) $aluno->remember_token, $password)) {
            session([
                'pending_password_creation' => $aluno->id,
                'pending_password_role'     => 'aluno',
            ]);
            return redirect()->route('criar-senha.show');
        }

        if ($aluno && Hash::check($password, $aluno->password)) {
            Auth::guard('professores')->logout();
            Auth::guard('masters')->logout();

            // Invalidar token pendente se o usuário logou com senha normal
            if (!is_null($aluno->remember_token)) {
                $aluno->remember_token = null;
                $aluno->save();
            }

            Auth::guard('alunos')->login($aluno, $remember);
            $request->session()->regenerate();

            $pendingCode = session()->pull('pending_attendance_code');
            if ($pendingCode) {
                return redirect()->route('presenca.confirmar', $pendingCode);
            }

            return redirect()->route('dashboard.aluno');
        }

        Log::warning('Login failed (aluno)', [
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login.aluno.form')
            ->withErrors([
                'ra_email_cpf' => 'Credenciais de acesso fornecidas são inválidas.',
            ])->withInput();
    }
}
