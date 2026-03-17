@extends('layouts.theme')

@section('title', 'Sessão Expirada (419) - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col justify-center items-center')
@section('no-nav')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden w-full h-full items-center justify-center p-6">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px] opacity-20"></div>
        <div class="blob-2 opacity-20"></div>

        <div class="glass p-12 rounded-sm border-2 border-white/10 max-w-lg w-full text-center relative overflow-hidden animate-reveal z-10">
            <!-- Glitchy background effect -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-yellow-500/10 blur-3xl"></div>
            
            <div class="w-24 h-24 bg-yellow-500/20 rounded-sm flex items-center justify-center text-5xl mx-auto mb-8 animate-pulse border border-yellow-500/30 shadow-lg shadow-yellow-500/20">
                ⌛
            </div>
            
            <p class="pal-overline mb-2 text-yellow-500/80">Erro 419</p>
            <h1 class="text-4xl font-black text-white tracking-tighter mb-4 italic">Sessão Expirada</h1>
            
            <p class="text-white/70 leading-relaxed mb-10 font-medium">
                Sua sessão de segurança expirou por inatividade ou por um problema técnico. Para proteger seus dados, é necessário refazer o login.
            </p>
            
            <div class="flex flex-col gap-4 w-full">
                <a href="{{ url('/login') }}" 
                   class="block w-full bg-white text-black hover:scale-[1.02] active:scale-95 transition-all font-black py-4 rounded-sm text-lg shadow-2xl border border-white/20">
                    Voltar ao Login
                </a>
                <button onclick="window.history.back()" 
                   class="block w-full bg-white/5 text-white/70 hover:bg-white/10 hover:text-white transition-all font-bold py-3 rounded-sm text-sm border border-white/10">
                    Tentar Novamente
                </button>
            </div>
        </div>
    </div>
@endsection
