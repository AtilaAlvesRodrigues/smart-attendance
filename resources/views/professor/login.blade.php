@extends('layouts.main')

@section('title', 'Login Professor - Smart Attendance')

@section('body-class', 'gradient-bg relative')

@section('content')
    <div class="flex-grow flex items-center justify-center p-4 relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <div class="w-full max-w-sm glass p-8 rounded-3xl shadow-2xl transition-all duration-700 animate-reveal border border-white/20 relative z-10 backdrop-blur-xl">

            <div class="text-center mb-8">
                <div class="inline-block px-3 py-1 mb-3 rounded-full bg-white/10 border border-white/10 text-[10px] font-bold tracking-widest text-indigo-300 uppercase">
                    Painel do Docente
                </div>
                <h1 class="text-4xl font-black tracking-tighter text-white mb-1 italic">CEUB</h1>
                <h2 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-purple-200">
                    Smart Attendance
                </h2>
                <div class="h-0.5 w-12 bg-gradient-to-r from-indigo-500 to-purple-500 mx-auto my-4 rounded-full opacity-50"></div>
            </div>

            <form method="POST" action="{{ route('login.professor') }}" class="flex flex-col space-y-5">
                @csrf

                @error('ra_email_cpf')
                    <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-xl font-medium text-xs text-center animate-shake">
                        {{ $message }}
                    </div>
                @enderror

                <div class="group">
                    <input type="text" name="ra_email_cpf" placeholder="CPF ou E-Mail"
                        class="w-full p-4 bg-white/5 border border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300 text-white placeholder-white/30 text-sm group-hover:bg-white/10"
                        required value="{{ old('ra_email_cpf') }}" />
                </div>

                <div class="relative w-full group">
                    <input type="password" name="password" id="password" placeholder="Senha"
                        class="w-full p-4 bg-white/5 border border-white/10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all duration-300 text-white pr-12 placeholder-white/30 text-sm group-hover:bg-white/10"
                        required />
                    <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/30 hover:text-white transition-colors">
                        <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <label class="flex items-center space-x-3 text-white/60 text-xs cursor-pointer hover:text-white transition duration-200 group">
                    <input type="checkbox" name="remember" class="form-checkbox bg-white/5 border-white/20 text-indigo-500 rounded-md focus:ring-indigo-500/50 w-4 h-4" />
                    <span class="font-medium">Permanecer conectado</span>
                </label>

                <button type="submit"
                    class="mt-4 w-full p-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-2xl flex items-center justify-center space-x-2 
                            shadow-lg hover:shadow-indigo-500/25 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 
                            focus:outline-none ring-offset-2 ring-offset-[#1e1b4b] focus:ring-2 focus:ring-indigo-500">
                    <span class="text-lg">Entrar no Painel</span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="{{ route('login_form') }}" class="text-white/40 text-xs hover:text-white transition-all inline-flex items-center space-x-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Trocar Perfil</span>
                </a>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/toggle_password.js') }}"></script>
@endpush
