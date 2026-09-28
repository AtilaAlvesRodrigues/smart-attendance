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

        $emailEntregue = $this->enviarPrimeiroAcesso($user, $token, route('login.aluno.form'), 'aluno');

        return response()->json($this->respostaCadastro("Aluno {$user->nome} cadastrado.", $emailEntregue, $token));
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
                'cpf'   => ['required', 'string', 'max:20', function ($attr, $value, $fail) {
                    $digits = preg_replace('/\D/', '', $value);
                    if (strlen($digits) !== 11) {
                        $fail('O CPF deve conter exatamente 11 dígitos.');
                        return;
                    }
                    if (preg_match('/^(.)\1+$/', $digits)) {
                        $fail('O CPF informado é inválido (dígitos repetidos).');
                    }
                }],
            ], [
                'nome.required'  => 'O nome é obrigatório.',
                'nome.max'       => 'O nome deve ter no máximo 255 caracteres.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email'    => 'Informe um e-mail válido.',
                'cpf.required'   => 'O CPF é obrigatório.',
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

        $emailEntregue = $this->enviarPrimeiroAcesso($user, $token, route('login.professor.form'), 'professor');

        return response()->json($this->respostaCadastro("Professor {$user->nome} cadastrado.", $emailEntregue, $token));
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
            ], [
                'nome.required'         => 'O nome da matéria é obrigatório.',
                'nome.max'              => 'O nome deve ter no máximo 255 caracteres.',
                'sala.max'              => 'A sala deve ter no máximo 100 caracteres.',
                'carga_horaria.integer' => 'A carga horária deve ser um número inteiro.',
                'carga_horaria.min'     => 'A carga horária deve ser de pelo menos 1 hora.',
                'total_aulas.integer'   => 'O total de aulas deve ser um número inteiro.',
                'total_aulas.min'       => 'O total de aulas deve ser de pelo menos 1.',
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
            'message' => "Matéria {$materia->nome} cadastrada. Agora defina o professor e os alunos da turma.",
            'turma_url' => route('master.turma', $materia),
        ]);
    }

    /**
     * Envia o e-mail de primeiro acesso. Retorna false quando o e-mail não sai
     * de verdade: envio desativado (mailer "log", como na demonstração) ou falha.
     */
    private function enviarPrimeiroAcesso($user, string $token, string $loginUrl, string $tipo): bool
    {
        if (config('mail.default') === 'log') {
            return false;
        }

        try {
            Mail::to($user->email)->send(new PrimeiroAcessoMail(
                nomeUsuario: $user->nome,
                emailUsuario: $user->email,
                token: $token,
                loginUrl: $loginUrl,
            ));
            return true;
        } catch (\Throwable $e) {
            \Log::error('Falha ao enviar e-mail de primeiro acesso para ' . $tipo . ' ID ' . $user->id . ' [' . get_class($e) . ']');
            return false;
        }
    }

    /**
     * Sem e-mail entregue, o Master recebe o token para repassar à pessoa;
     * caso contrário ela nunca conseguiria fazer o primeiro acesso.
     */
    private function respostaCadastro(string $mensagem, bool $emailEntregue, string $token): array
    {
        if ($emailEntregue) {
            return ['success' => true, 'message' => $mensagem . ' E-mail de primeiro acesso enviado.'];
        }

        return [
            'success' => true,
            'message' => $mensagem . ' O e-mail não foi enviado: entregue o token abaixo à pessoa. Ela usa o token como senha no primeiro login e depois cria a senha definitiva.',
            'token_provisorio' => $token,
            'proximo_passo' => 'Para participar das aulas, matricule em Matérias → Gerenciar turma.',
        ];
    }
}
