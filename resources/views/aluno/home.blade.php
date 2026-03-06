@extends('layouts.main')

@section('title', 'Como Funciona? - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('footer-class', 'py-6 text-center text-sm text-white/60')

@section('content')
    <div class="flex-grow flex flex-col items-center p-6 relative overflow-hidden">
        
        <!-- Profile Button -->
        <button id="open-profile" class="fixed top-8 right-8 z-50 w-14 h-14 glass rounded-2xl flex items-center justify-center border border-white/20 shadow-xl hover:scale-110 active:scale-95 transition-all group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white group-hover:text-purple-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <div class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full border-2 border-indigo-900 animate-pulse"></div>
        </button>

        <!-- Profile Modal -->
        <div id="profile-modal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="close-modal-overlay"></div>
            
            <div class="glass w-full max-w-2xl rounded-3xl border border-white/20 shadow-2xl relative z-10 overflow-hidden animate-reveal">
                <!-- Modal Header -->
                <div class="p-8 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <div>
                        <h2 class="text-3xl font-black text-white italic tracking-tighter">Meu Perfil</h2>
                        <p class="text-purple-300 text-xs font-bold uppercase tracking-widest mt-1">Informações Acadêmicas</p>
                    </div>
                    <button id="close-profile" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white hover:bg-red-500/20 hover:text-red-400 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-8 max-h-[70vh] overflow-y-auto">
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest block mb-1">Nome Completo</span>
                            <span class="text-white font-bold">{{ $aluno->nome }}</span>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest block mb-1">E-mail Institucional</span>
                            <span class="text-white font-bold">{{ $aluno->email }}</span>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest block mb-1">Matrícula (RA)</span>
                            <span class="text-white font-mono font-bold">{{ $aluno->ra }}</span>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest block mb-1">CPF</span>
                            <span class="text-white font-mono font-bold">{{ $aluno->cpf }}</span>
                        </div>
                    </div>

                    <!-- Subjects & Absences -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-2 h-8 bg-purple-500 rounded-full"></span>
                            Matérias e Frequência
                        </h3>
                        
                        <div class="space-y-4">
                            @forelse($aluno->materias as $materia)
                                <div class="glass bg-white/5 p-6 rounded-2xl border border-white/5 hover:border-purple-500/30 transition-all group">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex-grow">
                                            <h4 class="text-lg font-bold text-white group-hover:text-purple-300 transition-colors">{{ $materia->nome }}</h4>
                                            <p class="text-white/40 text-xs mt-1">Carga Horária: {{ $materia->carga_horaria }}h | Total de Aulas: {{ $materia->total_aulas }}</p>
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <span class="block text-white font-black text-2xl {{ $materia->faltas >= $materia->limite_faltas ? 'text-red-400' : 'text-green-400' }}">
                                                    {{ $materia->faltas }}
                                                </span>
                                                <span class="text-[10px] font-bold text-white/40 uppercase tracking-tighter">Faltas Atuais</span>
                                            </div>
                                            
                                            <div class="w-px h-10 bg-white/10"></div>
                                            
                                            <div class="text-right">
                                                <span class="block text-white/80 font-bold text-lg">
                                                    {{ $materia->limite_faltas }}
                                                </span>
                                                <span class="text-[10px] font-bold text-white/40 uppercase tracking-tighter">Limite Permitido</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-4 h-2 w-full bg-white/5 rounded-full overflow-hidden">
                                        @php
                                            $percent = $materia->limite_faltas > 0 ? ($materia->faltas / $materia->limite_faltas) * 100 : 0;
                                            $percent = min(100, $percent);
                                            $barColor = $percent > 80 ? 'bg-red-500' : ($percent > 50 ? 'bg-yellow-500' : 'bg-green-500');
                                        @endphp
                                        <div class="{{ $barColor }} h-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                                    </div>
                                    @if($materia->faltas >= $materia->limite_faltas)
                                        <p class="text-red-400 text-[10px] font-black uppercase mt-2 animate-pulse">⚠️ ALERTA: LIMITE DE FALTAS ATINGIDO OU EXCEDIDO</p>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-10 glass rounded-3xl border border-dashed border-white/20">
                                    <p class="text-white/40 font-medium">Você ainda não possui matérias vinculadas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

@push('scripts')
<script>
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-modal-overlay');

    function toggleModal(show) {
        if (show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    openBtn.addEventListener('click', () => toggleModal(true));
    closeBtn.addEventListener('click', () => toggleModal(false));
    overlay.addEventListener('click', () => toggleModal(false));

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            toggleModal(false);
        }
    });
</script>
@endpush
