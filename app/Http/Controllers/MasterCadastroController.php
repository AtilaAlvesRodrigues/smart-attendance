<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\Materia;
use App\Mail\PrimeiroAcessoMail;

class MasterCadastroController extends BaseController
{
    /**
     * Cadastra um novo aluno e envia e-mail de primeiro acesso.
     */
    public function cadastrarAluno(Request $request)
    {
        try {
            $request->validate([
                'nome'  => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'cpf'   => 'required|string|max:20',
                'ra'    => 'required|string|max:50',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Verificar unicidade via blind index
        if (AlunoModel::where('email_search', AlunoModel::generateBlindIndex($request->email))->exists()) {
            return response()->json(['errors' => ['email' => ['Este e-mail já está cadastrado.']]], 422);
        }

        if (AlunoModel::where('cpf_search', AlunoModel::generateBlindIndex($request->cpf))->exists()) {
            return response()->json(['errors' => ['cpf' => ['Este CPF já está cadastrado.']]], 422);
        }

        if (AlunoModel::where('ra_search', AlunoModel::generateBlindIndex($request->ra))->exists()) {
            return response()->json(['errors' => ['ra' => ['Este RA já está cadastrado.']]], 422);
        }

        $token = Str::random(40);

        $user = DB::transaction(function () use ($request, $token) {
            return AlunoModel::create([
                'nome'           => $request->nome,
                'email'          => $request->email,
                'cpf'            => $request->cpf,
                'ra'             => $request->ra,
                'password'       => Str::random(40),
                'remember_token' => $token,
                'role'           => 'aluno',
            ]);
        });

        try {
            Mail::to($request->email)->send(new PrimeiroAcessoMail(
                nomeUsuario: $user->nome,
                emailUsuario: $user->email,
                token: $token,
                loginUrl: route('login.aluno.form'),
            ));
        } catch (\Throwable $e) {
            \Log::error('Falha ao enviar e-mail de primeiro acesso para aluno ID ' . $user->id . ' [' . get_class($e) . ']');
        }

        return response()->json([
            'success' => true,
            'message' => "Aluno {$user->nome} cadastrado e e-mail enviado.",
        ]);
    }

    /**
     * Cadastra um novo professor e envia e-mail de primeiro acesso.
     */
    public function cadastrarProfessor(Request $request)
    {
        try {
            $request->validate([
                'nome'  => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'cpf'   => 'required|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Verificar unicidade via blind index
        if (ProfessorModel::where('email_search', ProfessorModel::generateBlindIndex($request->email))->exists()) {
            return response()->json(['errors' => ['email' => ['Este e-mail já está cadastrado.']]], 422);
        }

        if (ProfessorModel::where('cpf_search', ProfessorModel::generateBlindIndex($request->cpf))->exists()) {
            return response()->json(['errors' => ['cpf' => ['Este CPF já está cadastrado.']]], 422);
        }

        $token = Str::random(40);

        $user = DB::transaction(function () use ($request, $token) {
            return ProfessorModel::create([
                'nome'           => $request->nome,
                'email'          => $request->email,
                'cpf'            => $request->cpf,
                'password'       => Str::random(40),
                'remember_token' => $token,
                'role'           => 'professor',
            ]);
        });

        try {
            Mail::to($request->email)->send(new PrimeiroAcessoMail(
                nomeUsuario: $user->nome,
                emailUsuario: $user->email,
                token: $token,
                loginUrl: route('login.professor.form'),
            ));
        } catch (\Throwable $e) {
            \Log::error('Falha ao enviar e-mail de primeiro acesso para professor ID ' . $user->id . ': ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Professor {$user->nome} cadastrado e e-mail enviado.",
        ]);
    }

    /**
     * Cadastra uma nova matéria.
     */
    public function cadastrarMateria(Request $request)
    {
        try {
            $request->validate([
                'nome'          => 'required|string|max:255',
                'sala'          => 'nullable|string|max:100',
                'carga_horaria' => 'nullable|integer|min:1',
                'total_aulas'   => 'nullable|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        if (Materia::where('nome', $request->nome)->exists()) {
            return response()->json(['errors' => ['nome' => ['Já existe uma matéria com este nome.']]], 422);
        }

        $materia = Materia::create([
            'nome'          => $request->nome,
            'sala'          => $request->sala,
            'carga_horaria' => $request->carga_horaria,
            'total_aulas'   => $request->total_aulas,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Matéria {$materia->nome} cadastrada com sucesso.",
        ]);
    }
}
