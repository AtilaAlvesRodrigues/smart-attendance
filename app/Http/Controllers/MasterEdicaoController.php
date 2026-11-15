<?php

namespace App\Http\Controllers;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Rules\UnicoPorBlindIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Edição e exclusão de alunos, professores e matérias pelo Master.
 *
 * A exclusão é reversível (soft delete): o registro some das telas, mas o
 * histórico de presenças e notas continua no banco (LGPD / auditoria).
 * Campos nunca editáveis aqui: role, senha e colunas de 2FA.
 */
class MasterEdicaoController extends BaseController
{
    private const MODELOS = [
        'aluno'     => AlunoModel::class,
        'professor' => ProfessorModel::class,
        'materia'   => Materia::class,
    ];

    private const LISTAGENS = [
        'aluno'     => 'master.alunos',
        'professor' => 'master.professores',
        'materia'   => 'master.materias',
    ];

    public function editar(string $tipo, int $id)
    {
        return view('master.editar', [
            'master'   => Auth::guard('masters')->user(),
            'tipo'     => $tipo,
            'registro' => $this->buscar($tipo, $id),
            'voltar'   => route(self::LISTAGENS[$tipo]),
        ]);
    }

    public function atualizar(Request $request, string $tipo, int $id)
    {
        $registro = $this->buscar($tipo, $id);
        $dados = $request->validate($this->regras($tipo, $registro), $this->mensagens());

        $registro->fill($dados)->save();

        Log::info('Cadastro editado pelo Master', [
            'master_id' => Auth::guard('masters')->id(),
            'tipo'      => $tipo,
            'id'        => $registro->id,
            'campos'    => array_keys($dados),
        ]);

        return redirect()->route('master.editar', [$tipo, $registro->id])->with('status', 'Alterações salvas.');
    }

    public function excluir(string $tipo, int $id)
    {
        $registro = $this->buscar($tipo, $id);
        $nome = $registro->nome;
        $registro->delete();

        Log::info('Cadastro excluído (soft delete) pelo Master', [
            'master_id' => Auth::guard('masters')->id(),
            'tipo'      => $tipo,
            'id'        => $id,
        ]);

        return redirect()->route(self::LISTAGENS[$tipo])->with('status', "{$nome} foi excluído(a).");
    }

    private function buscar(string $tipo, int $id): Model
    {
        $modelo = self::MODELOS[$tipo] ?? abort(404);

        return $modelo::findOrFail($id);
    }

    private function regras(string $tipo, Model $registro): array
    {
        return match ($tipo) {
            'aluno' => [
                'nome'  => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', new UnicoPorBlindIndex(AlunoModel::class, 'email_search', $registro->id, 'Este e-mail já está cadastrado.')],
                'cpf'   => ['required', 'string', 'max:20', new UnicoPorBlindIndex(AlunoModel::class, 'cpf_search', $registro->id, 'Este CPF já está cadastrado.')],
                'ra'    => ['required', 'string', 'max:50', new UnicoPorBlindIndex(AlunoModel::class, 'ra_search', $registro->id, 'Este RA já está cadastrado.')],
            ],
            'professor' => [
                'nome'  => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', new UnicoPorBlindIndex(ProfessorModel::class, 'email_search', $registro->id, 'Este e-mail já está cadastrado.')],
                'cpf'   => ['required', 'string', 'max:20', $this->cpfValido(), new UnicoPorBlindIndex(ProfessorModel::class, 'cpf_search', $registro->id, 'Este CPF já está cadastrado.')],
            ],
            'materia' => [
                'nome'          => ['required', 'string', 'max:255', Rule::unique('materias', 'nome')->ignore($registro->id)],
                'sala'          => 'nullable|string|max:100',
                'carga_horaria' => 'nullable|integer|min:1|max:2000',
                'total_aulas'   => 'required|integer|min:1|max:500',
            ],
        };
    }

    /** Mesma regra do cadastro de professor: 11 dígitos, não todos iguais. */
    private function cpfValido(): \Closure
    {
        return function ($attr, $value, $fail) {
            $digitos = preg_replace('/\D/', '', (string) $value);
            if (strlen($digitos) !== 11) {
                $fail('O CPF deve conter exatamente 11 dígitos.');
            } elseif (preg_match('/^(.)\1+$/', $digitos)) {
                $fail('O CPF informado é inválido (dígitos repetidos).');
            }
        };
    }

    private function mensagens(): array
    {
        return [
            'nome.required'        => 'O nome é obrigatório.',
            'nome.unique'          => 'Já existe uma matéria com este nome.',
            'email.required'       => 'O e-mail é obrigatório.',
            'email.email'          => 'Informe um e-mail válido.',
            'cpf.required'         => 'O CPF é obrigatório.',
            'ra.required'          => 'O RA é obrigatório.',
            'total_aulas.required' => 'Informe o total de aulas previstas.',
            'total_aulas.min'      => 'O total de aulas deve ser de pelo menos 1.',
        ];
    }
}
