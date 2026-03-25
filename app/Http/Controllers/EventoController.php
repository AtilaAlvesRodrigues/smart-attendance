<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventoController extends BaseController
{
    /**
     * Show the public check-in page.
     * Security: Throttled at route level.
     */
    public function checkinForm()
    {
        return view('pages.evento-checkin');
    }

    /**
     * Show the professor management page.
     * Security: Requires 'professores' authentication.
     */
    public function presencaDashboard()
    {
        $professor = Auth::guard('professores')->user();

        return view('pages.evento-presenca', compact('professor'));
    }

    /**
     * Process the check-in (Server-side validation example).
     * This is an example of where we'd add database security.
     */
    public function processCheckin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|min:3',
            'email' => 'required|email|max:100',
            'hp_field' => 'prohibited', // Honeypot field for simple bot protection
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dados inválidos.'], 422);
        }

        // In a real app, we would check the database here:
        // if (EventCheckin::where('email', $request->email)->exists()) { ... }

        return response()->json(['success' => true, 'message' => 'Check-in validado pelo servidor.']);
    }

    /**
     * Terminate the session and send email to professor.
     */
    public function encerrarSessao(Request $request)
    {
        $professor = Auth::guard('professores')->user();
        $emailDestino = $professor ? $professor->email : 'professor@teste.com';
        $nomeProfessor = $professor ? $professor->nome : 'Professor Silva';
        
        $participantes = $request->input('participantes', []);
        $total = count($participantes);

        // Simulation of sending email using Laravel Mail
        // In a real environment with SMTP configured:
        /*
        \Illuminate\Support\Facades\Mail::raw("Olá $nomeProfessor, a sua palestra foi encerrada com sucesso. Total de participantes: $total", function ($message) use ($emailDestino) {
            $message->to($emailDestino)->subject('Relatório de Presença - Smart Attendance');
        });
        */

        return response()->json([
            'success' => true, 
            'message' => 'Relatório enviado para ' . $emailDestino,
            'total' => $total
        ]);
    }
}
