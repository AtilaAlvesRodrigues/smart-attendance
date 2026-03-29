<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventoController extends BaseController
{
    public function checkinForm(Request $request)
    {
        $token = $request->query('token', '');
        return view('pages.evento-checkin', compact('token'));
    }

    public function presencaDashboard()
    {
        $professor  = Auth::guard('professores')->user();
        $cacheKey   = "professor_evento_{$professor->id}";

        // Reutiliza token ativo do professor (sobrevive a logout/fechamento de aba)
        $sessionToken = Cache::get($cacheKey) ?: Str::random(16);
        Cache::put($cacheKey, $sessionToken, now()->addHours(8));

        return view('pages.evento-presenca', compact('professor', 'sessionToken'));
    }

    public function processCheckin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100|min:3',
            'email'    => 'required|email|max:100',
            'token'    => 'required|string|max:32',
            'hp_field' => 'prohibited',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dados inválidos.'], 422);
        }

        $token    = $request->input('token');
        $email    = strtolower($request->input('email'));
        $cacheKey = "evento_checkin_{$token}";

        $checkins = Cache::get($cacheKey, []);

        $jaRegistrado = collect($checkins)->contains(
            fn($c) => strtolower($c['email']) === $email
        );

        if ($jaRegistrado) {
            return response()->json(['error' => 'E-mail já registrado nesta palestra.'], 409);
        }

        $checkins[] = [
            'name'  => $request->input('name'),
            'email' => $email,
            'time'  => now()->toISOString(),
        ];

        Cache::put($cacheKey, $checkins, now()->addHours(8));

        return response()->json(['success' => true]);
    }

    public function getCheckins(string $token)
    {
        $checkins = Cache::get("evento_checkin_{$token}", []);
        return response()->json($checkins);
    }

    public function encerrarSessao(Request $request)
    {
        $professor     = Auth::guard('professores')->user();
        $token         = $request->input('token', '');
        $participantes = $request->input('participantes', []);

        if ($token) {
            Cache::forget("evento_checkin_{$token}");
        }

        if ($professor) {
            Cache::forget("professor_evento_{$professor->id}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Sessão encerrada.',
            'total'   => \count($participantes),
        ]);
    }
}
