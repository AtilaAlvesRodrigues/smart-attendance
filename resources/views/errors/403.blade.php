@extends('layouts.theme')

@section('title', 'Acesso não permitido (403) - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col justify-center items-center')
@section('no-nav', true)

@section('content')
    <div class="flex-grow flex flex-col items-center justify-center p-6 relative">
        <div class="blob top-[-100px] left-[-100px] opacity-20"></div>
        <div class="blob-2 opacity-20"></div>

        <div class="glass p-12 rounded-sm border border-white/10 max-w-lg w-full text-center animate-reveal z-10">
            <div class="w-16 h-16 bg-white/5 border border-white/12 rounded-sm flex items-center justify-center text-3xl mx-auto mb-8 shadow-xl" aria-hidden="true">
                🔒
            </div>

            <p class="pal-eyebrow" style="color:#f87171; margin-bottom:0.5rem;">Acesso não permitido</p>
            <h1 class="pal-title mb-4">Esta página não é sua</h1>

            <p class="pal-subtitle mb-10">
                Você está conectado, mas este conteúdo pertence a outro professor ou a outro perfil.
                Se acha que deveria ter acesso, fale com a coordenação.
            </p>

            <div class="flex flex-col gap-4 w-full">
                <a href="{{ url('/dashboard') }}" class="pal-btn-primary w-full justify-center py-4">
                    Voltar ao meu painel
                </a>
                <button type="button" onclick="window.history.back()" class="pal-btn-outline w-full justify-center py-3">
                    Voltar à página anterior
                </button>
            </div>
        </div>
    </div>
@endsection
