@extends('layouts.theme')

@section('title', 'Sessão Expirada (419) - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col justify-center items-center')
@section('no-nav')

@section('content')
    <div class="flex-grow flex flex-col items-center justify-center p-6 relative">
        {{-- Blobs --}}
        <div class="blob top-[-100px] left-[-100px] opacity-20"></div>
        <div class="blob-2 opacity-20"></div>

        <div class="glass p-12 rounded-sm border border-white/10 max-w-lg w-full text-center animate-reveal z-10">
            {{-- Icon Container (Rule 4: 64x64px, borda 12% branca) --}}
            <div class="w-16 h-16 bg-white/5 border border-white/12 rounded-sm flex items-center justify-center text-3xl mx-auto mb-8 shadow-xl">
                ⌛
            </div>
            
            {{-- Typography (Rule 4: Overline -> H1 -> P) --}}
            <p class="pal-eyebrow" style="color:#eab308; margin-bottom:0.5rem;">Sessão Encerrada</p>
            <h1 class="pal-title mb-4">Página Expirada</h1>
            
            <p class="pal-subtitle mb-10">
                Por questões de segurança, sua sessão foi interrompida. Por favor, volte ao login ou tente atualizar a página para continuar.
            </p>
            
            {{-- Buttons (Rule 6: Solid Hover, Contrast) --}}
            <div class="flex flex-col gap-4 w-full">
                <a href="{{ url('/login') }}" class="pal-btn-primary w-full justify-center py-4">
                    Voltar ao Login
                </a>
                <button onclick="window.history.back()" class="pal-btn-outline w-full justify-center py-3">
                    Tentar Novamente
                </button>
            </div>
        </div>
    </div>
@endsection
