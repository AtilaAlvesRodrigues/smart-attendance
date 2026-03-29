<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Routing\Controller as BaseController;

class ProfessorLoginController extends BaseController
{
    public function showLoginForm()
    {
        return view('professor.login');
    }

    public function attemptAuthentication(Request $request)
    {
        $request->validate([
            'cpf_email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $login = $request->input('cpf_email');
        $password = $request->input('password');
        $remember = $request->has('remember');

        // Tentativa como Professor usando Blind Indexes
        $loginHash = ProfessorModel::generateBlindIndex($login);

        $professor = null;
        foreach (['cpf_search', 'email_search'] as $searchColumn) {
            $professor = ProfessorModel::where($searchColumn, $loginHash)->first();
            if ($professor) break;
        }

        // Verificação de primeiro acesso (professor) via remember_token (hash_equals evita timing attack)
        if ($professor && !is_null($professor->remember_token) && hash_equals((string) $professor->remember_token, $password)) {
            session([
                'pending_password_creation' => $professor->id,
                'pending_password_role'     => 'professor',
            ]);
            return redirect()->route('criar-senha.show');
        }

        if ($professor && Hash::check($password, $professor->password)) {
            Auth::guard('alunos')->logout();
            Auth::guard('masters')->logout();

            // Invalidar token pendente se o usuário logou com senha normal
            if (!is_null($professor->remember_token)) {
                $professor->remember_token = null;
                $professor->save();
            }

            Auth::guard('professores')->login($professor, $remember);
            $request->session()->regenerate();
            return redirect()->route('dashboard.professor');
        }

        // Tentativa como Master
        $master = UsuarioMaster::where('email_search', $loginHash)->first();

        // Verificação de primeiro acesso (master) via remember_token (hash_equals evita timing attack)
        if ($master && !is_null($master->remember_token) && hash_equals((string) $master->remember_token, $password)) {
            session([
                'pending_password_creation' => $master->id,
                'pending_password_role'     => 'master',
            ]);
            return redirect()->route('criar-senha.show');
        }

        if ($master && Hash::check($password, $master->password)) {
            Auth::guard('professores')->logout();
            Auth::guard('alunos')->logout();

            // Invalidar token pendente se o usuário logou com senha normal
            if (!is_null($master->remember_token)) {
                $master->remember_token = null;
                $master->save();
            }

            Auth::guard('masters')->login($master, $remember);
            $request->session()->regenerate();
            return redirect()->route('dashboard.master');
        }

        Log::warning('Login failed (professor/master)', [
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login.professor.form')
            ->withErrors([
                'cpf_email' => 'Credenciais de acesso fornecidas são inválidas.',
            ])->withInput();
    }
}
