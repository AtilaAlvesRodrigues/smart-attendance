@extends('layouts.main')

@section('title', 'Presença já Registrada - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex items-center justify-center p-6 relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <div class="glass p-12 rounded-[3rem] border border-white/10 max-w-md w-full text-center relative z-10 animate-reveal">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 blur-3xl"></div>
            
            <div class="mb-10 relative inline-block group">
                <div class="absolute -inset-4 bg-blue-500/20 rounded-full blur-2xl group-hover:bg-blue-500/30 transition-all duration-500"></div>
                <div class="relative w-24 h-24 bg-blue-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-blue-500/40 rotate-12 transition-transform group-hover:rotate-0">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-4xl font-black text-white tracking-tighter mb-4 italic">Tudo Certo!</h1>
            <p class="text-white/40 font-medium leading-relaxed mb-10">Você já registrou sua presença nesta sessão anteriormente. Não é necessário realizar o procedimento novamente.</p>

            <div class="glass p-6 rounded-2xl border border-white/5 mb-10 text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest block mb-1">Status do Registro</span>
                <p class="font-black text-white text-xl tracking-tight">Presença Confirmada ✅</p>
            </div>

            <a href="{{ route('dashboard.aluno') }}" 
               class="block w-full bg-white text-dark_purple hover:scale-[1.02] active:scale-95 transition-all font-black py-4 rounded-xl text-lg shadow-2xl">
                Voltar ao Início
            </a>
            
            <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em] mt-6">
                Sincronizado com o sistema docente
            </p>
        </div>

    </div>
@endsection
