<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Routing\Controller as BaseController;

class CriarSenhaController extends BaseController
{
    public function show()
    {
        return view('criar-senha');
    }

    public function store(Request $request)
    {
        $request->validate([
            'senha'             => 'required|string|min:8|same:senha_confirmacao',
            'senha_confirmacao' => 'required|string|min:8',
        ]);

        $userId = session('pending_password_creation');
        $role   = session('pending_password_role');

        [$modelClass, $guard, $redirect] = match ($role) {
            'aluno'     => [AlunoModel::class,     'alunos',     'dashboard.aluno'],
            'professor' => [ProfessorModel::class,  'professores', 'dashboard.professor'],
            'master'    => [UsuarioMaster::class,   'masters',    'dashboard.master'],
            default     => [null, null, null],
        };

        if (!$modelClass) {
            session()->forget(['pending_password_creation', 'pending_password_role']);
            $request->session()->regenerate();
            return redirect()->route('login_form')->withErrors(['erro' => 'Sessão inválida. Faça o login novamente.']);
        }

        $user = DB::transaction(function () use ($modelClass, $userId, $request) {
            $user = $modelClass::lockForUpdate()->find($userId);

            if (!$user || is_null($user->remember_token)) {
                return null;
            }

            // Cast 'hashed' no model garante o bcrypt — não usar Hash::make() aqui
            $user->password       = $request->senha;
            $user->remember_token = null;
            $user->save();

            return $user;
        });

        if (!$user) {
            session()->forget(['pending_password_creation', 'pending_password_role']);
            $request->session()->regenerate();
            return redirect()->route('login_form')->withErrors(['erro' => 'Token inválido ou já utilizado.']);
        }

        session()->forget(['pending_password_creation', 'pending_password_role']);
        Auth::guard($guard)->login($user);
        $request->session()->regenerate();

        return redirect()->route($redirect);
    }
}
