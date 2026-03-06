@extends('layouts.main')

@section('title', 'Como Funciona? - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('footer-class', 'py-6 text-center text-sm text-white/60')

@section('content')
    <div class="flex-grow flex flex-col items-center p-6 relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <div class="w-full max-w-4xl relative z-10 animate-reveal">
            
            @if(session('pending_attendance_code'))
                <div class="glass p-8 rounded-3xl mb-12 border-2 border-green-500/30 overflow-hidden relative group">
                    <div class="absolute inset-0 bg-green-500/10 opacity-50"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 bg-green-500/20 rounded-2xl flex items-center justify-center text-3xl animate-bounce">
                                ✅
                            </div>
                            <div class="text-center md:text-left">
                                <h2 class="text-2xl font-black text-white tracking-tight">Presença Pendente!</h2>
                                <p class="text-green-200/80 font-medium">Você tem uma aula aguardando confirmação.</p>
                            </div>
                        </div>
                        <a href="{{ route('presenca.confirmar', session('pending_attendance_code')) }}"
                            class="bg-green-500 hover:bg-green-400 text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-green-500/20 transition-all hover:scale-105 active:scale-95 whitespace-nowrap">
                            Confirmar Agora
                        </a>
                    </div>
                </div>
            @endif

            <div class="text-center mb-16">
                <div class="inline-block px-4 py-1.5 mb-4 rounded-full bg-white/10 border border-white/10 text-xs font-bold tracking-widest text-purple-300 uppercase">
                    Guia de Utilização
                </div>
                <h1 class="text-5xl font-black tracking-tighter text-white mb-4 italic">Como funciona?</h1>
                <div class="h-1 w-24 bg-gradient-to-r from-purple-500 to-indigo-500 mx-auto rounded-full opacity-50"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                
                <!-- Passo 1 -->
                <div class="glass p-8 rounded-3xl border border-white/10 hover:border-purple-400/30 transition-all group">
                    <div class="w-14 h-14 bg-purple-600/20 rounded-2xl flex items-center justify-center text-2xl font-black text-purple-400 mb-6 group-hover:scale-110 transition-transform">
                        01
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3 tracking-tight">Acesso Rápido</h3>
                    <p class="text-white/60 text-sm leading-relaxed">
                        O Aluno acessa o Smart Attendance utilizando seu <span class="text-purple-300 font-bold">RA, e-mail institucional ou CPF</span> de forma segura.
                    </p>
                </div>

                <!-- Passo 2 -->
                <div class="glass p-8 rounded-3xl border border-white/10 hover:border-indigo-400/30 transition-all group">
                    <div class="w-14 h-14 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-2xl font-black text-indigo-400 mb-6 group-hover:scale-110 transition-transform">
                        02
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3 tracking-tight">QR Code Dinâmico</h3>
                    <p class="text-white/60 text-sm leading-relaxed">
                        Localize o <span class="text-indigo-300 font-bold">QR Code</span> projetado pelo professor ou exibido no dispositivo do docente durante a aula.
                    </p>
                </div>

                <!-- Passo 3 -->
                <div class="glass p-8 rounded-3xl border border-white/10 hover:border-fuchsia-400/30 transition-all group">
                    <div class="w-14 h-14 bg-fuchsia-600/20 rounded-2xl flex items-center justify-center text-2xl font-black text-fuchsia-400 mb-6 group-hover:scale-110 transition-transform">
                        03
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3 tracking-tight">Confirmação</h3>
                    <p class="text-white/60 text-sm leading-relaxed">
                        Escaneie o código com a câmera do seu dispositivo e receba a <span class="text-fuchsia-300 font-bold">confirmação instantânea</span> de presença.
                    </p>
                </div>

            </div>

            <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                <a href="{{ Auth::check() ? route('dashboard') : route('login_form') }}"
                    class="w-full md:w-auto px-12 py-5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-xl rounded-2xl shadow-xl shadow-purple-900/40 hover:scale-105 active:scale-95 transition-all text-center">
                    Prosseguir
                </a>

                @auth
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="w-full md:w-auto px-12 py-5 border-2 border-white/10 text-white/60 font-bold text-xl rounded-2xl hover:bg-white/5 hover:text-white transition-all text-center">
                        Encerrar Sessão
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @endauth
            </div>

        </div>
    </div>
@endsection
