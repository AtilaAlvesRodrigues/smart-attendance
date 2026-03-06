@extends('layouts.main')

@section('title', 'Seleção de Perfil - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex items-center justify-center p-4 relative overflow-hidden">
        
        <!-- Animated Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>
        <div class="absolute top-[20%] right-[10%] w-32 h-32 bg-purple-400/20 blur-3xl rounded-full animate-pulse"></div>

        <div class="w-full max-w-2xl glass p-10 rounded-3xl shadow-2xl transition-all duration-700 
                    border border-white/20 relative z-10 backdrop-blur-xl animate-reveal">

            <div class="text-center mb-12 animate-fade-in [animation-delay:200ms]">
                <div class="inline-block px-4 py-1.5 mb-4 rounded-full bg-white/10 border border-white/10 text-xs font-bold tracking-widest text-purple-300 uppercase">
                    Sistema de Presença Inteligente
                </div>
                <h1 class="text-5xl font-black tracking-tighter text-white mb-2 italic">CEUB</h1>
                <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-300 to-indigo-200">
                    Smart Attendance
                </h2>
                <div class="h-1 w-24 bg-gradient-to-r from-purple-500 to-indigo-500 mx-auto my-6 rounded-full opacity-50"></div>
                <p class="text-white/80 font-medium text-lg">Selecione seu perfil para continuar</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-center">

                {{-- Card de ALUNO --}}
                <a href="{{ route('login.aluno.form') }}"
                    class="tilt-card group relative flex flex-col items-center justify-center p-8 bg-white/5 rounded-2xl 
                           border border-white/10 hover:border-purple-400/50 transition-all duration-500 
                           hover:bg-white/10 cursor-pointer overflow-hidden text-center">
                    
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="relative z-10 mb-6 transition-transform duration-500 group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <h3 class="relative z-10 text-2xl font-black text-white tracking-tight">ALUNO</h3>
                    <p class="relative z-10 text-sm text-purple-200/60 font-medium mt-1">Acesso Estudantil</p>
                </a>

                {{-- Card de PROFESSOR --}}
                <a href="{{ route('login.professor.form') }}"
                    class="tilt-card group relative flex flex-col items-center justify-center p-8 bg-white/5 rounded-2xl 
                           border border-white/10 hover:border-indigo-400/50 transition-all duration-500 
                           hover:bg-white/10 cursor-pointer overflow-hidden text-center">
                    
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="relative z-10 mb-6 transition-transform duration-500 group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </div>
                    <h3 class="relative z-10 text-2xl font-black text-white tracking-tight">PROFESSOR</h3>
                    <p class="relative z-10 text-sm text-indigo-200/60 font-medium mt-1">Painel Docente</p>
                </a>
            </div>

            <p class="text-center text-xs text-white/40 mt-10 font-medium tracking-wide">
                © {{ date('Y') }} Smart Attendance. Todos os direitos reservados.
            </p>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.tilt-card');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 8;
                const rotateY = (centerX - x) / 8;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
            });
        });
    });
</script>
<style>
    .tilt-card {
        transform-style: preserve-3d;
        will-change: transform;
    }
</style>
@endpush