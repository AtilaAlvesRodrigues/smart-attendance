@extends('layouts.main')

@section('title', 'Erro na Presença - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex items-center justify-center p-6 relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <div class="glass p-12 rounded-[3rem] border border-white/10 max-w-md w-full text-center relative z-10 animate-reveal">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 blur-3xl"></div>
            
            <div class="mb-10 relative inline-block group">
                <div class="absolute -inset-4 bg-red-500/20 rounded-full blur-2xl group-hover:bg-red-500/30 transition-all duration-500"></div>
                <div class="relative w-24 h-24 bg-red-500 rounded-3xl flex items-center justify-center shadow-2xl shadow-red-500/40 -rotate-12 transition-transform group-hover:rotate-0">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-4xl font-black text-white tracking-tighter mb-4 italic">Ops! Algo deu errado</h1>
            <p class="text-red-400 font-bold leading-relaxed mb-10 px-4">{{ $mensagem ?? 'Ocorreu um erro ao confirmar sua presença. Tente novamente ou procure o professor.' }}</p>

            <div class="flex flex-col gap-4">
                <a href="{{ route('dashboard.aluno') }}" 
                   class="block w-full bg-white/10 hover:bg-white text-white hover:text-dark_purple border border-white/10 hover:scale-[1.02] active:scale-95 transition-all font-black py-4 rounded-xl text-lg shadow-2xl">
                    Voltar ao Início
                </a>
                
                <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em] mt-2">
                    Código do erro: SEC-PRES-01
                </p>
            </div>
        </div>

    </div>
@endsection
