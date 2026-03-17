<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class LoginRouterController extends BaseController
{
    public function showLoginForm()
    {
        $activeCodes = [];
        $materias = \App\Models\Materia::all();
        
        foreach ($materias as $materia) {
            $cacheKey = 'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d');
            $cacheData = \Illuminate\Support\Facades\Cache::get($cacheKey);

            if (is_array($cacheData) && isset($cacheData['codigo'])) {
                $activeCodes[] = [
                    'materia_nome' => $materia->nome,
                    'codigo' => $cacheData['codigo'],
                    'sala' => $materia->sala
                ];
            }
        }

        return view('index', compact('activeCodes'));
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'ra_email_cpf' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'user_role' => 'required|in:aluno,professor',
        ], [
            'ra_email_cpf.required' => 'O campo de acesso é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
            'user_role.required' => 'Selecione se você é Aluno ou Professor.',
        ]);

        $role = $request->input('user_role');

        if ($role === 'aluno') {
            return app(AlunoLoginController::class)->attemptAuthentication($request);
        }

        if ($role === 'professor') {
            return app(ProfessorLoginController::class)->attemptAuthentication($request);
        }

        return back()->withErrors([
            'ra_email_cpf' => 'Perfil de acesso inválido.',
        ])->onlyInput('ra_email_cpf');
    }
}
