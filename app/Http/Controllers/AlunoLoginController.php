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
        $searchFields = ['ra' => 'ra_search', 'cpf' => 'cpf_search', 'email' => 'email_search'];
        $loginHash = AlunoModel::generateBlindIndex($login);

        $aluno = null;
        foreach ($searchFields as $field => $searchColumn) {  
            $aluno = AlunoModel::where($searchColumn, $loginHash)->first();
            if ($aluno) break;
        }

        if ($aluno && Hash::check($password, $aluno->password)) {   
            Auth::guard('professores')->logout();
            Auth::guard('masters')->logout();

            Auth::guard('alunos')->login($aluno, $remember);
            $request->session()->regenerate();

            $pendingCode = session()->pull('pending_attendance_code');
            if ($pendingCode) {
                return redirect()->route('presenca.confirmar', $pendingCode);
            }

            return redirect()->route('dashboard.aluno');
        }

        Log::warning('Login failed (aluno)', [
            'ip'    => $request->ip(),
            'input' => $request->input('ra_email_cpf'),
        ]);

        return redirect()->route('login.aluno.form')
            ->withErrors([
                'ra_email_cpf' => 'Credenciais de acesso fornecidas são inválidas.',
            ])->withInput();
    }
}
