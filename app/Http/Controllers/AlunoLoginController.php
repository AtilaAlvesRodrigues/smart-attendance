<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $searchFields = ['ra', 'cpf', 'email'];

        $aluno = null;
        foreach ($searchFields as $field) {  
            $aluno = AlunoModel::where($field, $login)->first();
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

        return redirect()->route('login.aluno.form')
            ->withErrors([
                'ra_email_cpf' => 'Credenciais de acesso fornecidas são inválidas.',
            ])->withInput();
    }
}
