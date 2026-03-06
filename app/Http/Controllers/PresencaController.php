<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presenca;
use App\Models\Materia;
use App\Models\AlunoModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controller;
use Illuminate\Support\Facades\Cache;

class PresencaController extends Controller
{
    /**
     * Exibe a página de seleção de matéria para gerar QR Code.
     */
    public function index()
    {
        $professor = Auth::guard('professores')->user();
        if (!$professor) {
            return redirect()->route('login.professor.form');
        }

        $materias = $professor->materias;

        foreach ($materias as $materia) {
            $cacheKey = 'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d');
            $cacheData = Cache::get($cacheKey);

            if (is_array($cacheData)) {
                $materia->active_code = $cacheData['codigo'];
            } elseif (is_string($cacheData)) {
                Cache::forget($cacheKey);
                $materia->active_code = null;
            } else {
                $materia->active_code = null;
            }
        }

        return view('professor.presenca.index', compact('materias'));
    }

    /**
     * Gera o QR Code e exibe a lista de presença em tempo real.
     */
    public function gerarQr(Request $request, $materia_id)
    {
        $professor = Auth::guard('professores')->user();

        if (!$professor->materias()->where('materias.id', $materia_id)->exists()) {
            abort(403, 'Você não tem permissão para gerar frequência desta matéria.');
        }

        $materia = Materia::findOrFail($materia_id);

        $cacheKey = 'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d');
        $cacheData = Cache::get($cacheKey);

        if (is_string($cacheData)) {
            Cache::forget($cacheKey);
            $cacheData = null;
        }

        if (!$cacheData) {
            $codigo_aula = $materia->id . '-' . now()->timestamp . '-' . Str::random(4);
            $expiraEm = now()->addHours(2);
            $cacheData = [
                'codigo' => $codigo_aula,
                'expira_em' => $expiraEm->timestamp,
                'professor_cpf' => $professor->cpf,
            ];
            Cache::put($cacheKey, $cacheData, $expiraEm);
        }

        $codigo_aula = $cacheData['codigo'];
        $expiraEmTimestamp = $cacheData['expira_em'];

        $semestre = (now()->month <= 6 ? '1' : '2') . '/' . now()->year;

        $hora = now()->hour;
        $horario = 'N';
        if ($hora < 12) $horario = 'M';
        elseif ($hora < 18) $horario = 'V';

        return view('professor.presenca.gerar', compact('materia', 'codigo_aula', 'semestre', 'horario', 'expiraEmTimestamp'));
    }

    /**
     * Rota chamada pelo Aluno ao escanear o QR Code.
     */
    public function confirmarPresenca(Request $request, $codigo_aula)
    {
        if (!Auth::guard('alunos')->check()) {
            session(['pending_attendance_code' => $codigo_aula]);
            return redirect()->route('login.aluno.form')->with('info', 'Faça login para confirmar sua presença.');
        }

        $aluno = Auth::guard('alunos')->user();

        $parts = explode('-', $codigo_aula);
        if (count($parts) < 3 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return view('aluno.presenca.erro', ['mensagem' => 'Código de aula inválido.']);
        }

        $materia_id = (int) $parts[0];
        $timestamp = (int) $parts[1];

        $cacheKey = 'aula_materia_' . $materia_id . '_' . Carbon::createFromTimestamp($timestamp)->format('Y-m-d');
        $cacheData = Cache::get($cacheKey);

        if (!$cacheData || !is_array($cacheData) || ($cacheData['codigo'] ?? '') !== $codigo_aula) {
            return view('aluno.presenca.erro', ['mensagem' => 'Este código de presença expirou ou é inválido.']);
        }

        if (!$aluno->materias()->where('materia_id', $materia_id)->exists()) {
            return view('aluno.presenca.erro', ['mensagem' => 'Você não está matriculado nesta disciplina.']);
        }

        if (Presenca::where('aluno_ra', $aluno->ra)->where('codigo_aula', $codigo_aula)->exists()) {
            return view('aluno.presenca.ja_registrado');
        }

        $data_aula = Carbon::createFromTimestamp($timestamp)->format('Y-m-d');
        $semestre = (now()->month <= 6 ? '1' : '2') . '/' . now()->year;
        $hora = now()->hour;
        $horario = $hora < 12 ? 'M' : ($hora < 18 ? 'V' : 'N');

        $professor_cpf = $cacheData['professor_cpf'] ?? null;

        if (!$professor_cpf) {
            
            $materia = Materia::with('professores')->find($materia_id);
            if (!$materia || $materia->professores->isEmpty()) {
                return view('aluno.presenca.erro', ['mensagem' => 'Erro: Matéria sem professor vinculado.']);
            }
            $professor_cpf = $materia->professores->first()->cpf;
        }

        $presenca = Presenca::create([
            'aluno_ra' => $aluno->ra,
            'professor_cpf' => $professor_cpf,
            'materia_id' => $materia_id,
            'data_aula' => $data_aula,
            'semestre' => $semestre,
            'horario' => $horario,
            'codigo_aula' => $codigo_aula,
        ]);

        $materia = Materia::find($materia_id);

        return view('aluno.presenca.sucesso', [
            'mensagem' => 'Presença confirmada com sucesso!',
            'presenca' => $presenca,
            'materia' => $materia,
        ]);
    }

    /**
     * Retorna JSON com alunos presentes (polling do professor).
     */
    public function getPresencas(Request $request, $codigo_aula)
    {
        $presencas = Presenca::with('aluno')
            ->where('codigo_aula', $codigo_aula)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($presencas);
    }
}
