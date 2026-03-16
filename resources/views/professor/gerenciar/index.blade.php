@extends('layouts.theme')

@section('title', 'Minhas Turmas - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('no-nav')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.professor') }}" class="w-10 h-10 bg-white/5 border border-white/10 rounded-lg flex items-center justify-center text-white font-black text-sm hover:border-white/30 transition">
                        P
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none uppercase">Minhas Turmas</h1>
                        <a href="{{ route('dashboard.professor') }}" class="text-xs font-bold text-white/70 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-6">
                    <span class="text-white/70 text-xs font-bold uppercase tracking-widest">{{ count($materias) }} Matérias</span>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Selecione uma Disciplina</h2>
                <p class="text-white/70 font-medium">Visualize alunos matriculados, gerencie notas e acompanhe faltas.</p>
            </div>

            @if($materias->isEmpty())
                <div class="glass p-12 rounded-3xl border border-white/10 text-center animate-reveal [animation-delay:400ms]">
                    <div class="w-20 h-20 bg-white/5 rounded-2xl flex items-center justify-center text-4xl mx-auto mb-6">📭</div>
                    <h3 class="text-2xl font-black text-white mb-2">Nenhuma matéria encontrada</h3>
                    <p class="text-white/70">Você não possui turmas vinculadas ao seu perfil no momento.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-reveal [animation-delay:400ms]">
                    @foreach($materias as $index => $materia)
                        <a href="{{ route('professor.gerenciar.materia', $materia->id) }}" 
                           class="group glass p-8 rounded-[2.5rem] border border-white/10 hover:border-indigo-400/30 transition-all duration-500 hover:-translate-y-2 relative overflow-hidden flex flex-col h-full [animation-delay:{{ ($index + 4) * 100 }}ms] animate-reveal">
                            
                            <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-600/10 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>

                            <div class="flex items-start justify-between mb-8">
                                <div class="w-14 h-14 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-500 scale-100 group-hover:scale-110">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <span class="px-3 py-1 bg-white/5 rounded-lg text-xs font-black text-white/70 group-hover:text-white/80 transition-colors uppercase tracking-widest border border-white/5">
                                    {{ $materia->alunos_count }} alunos
                                </span>
                            </div>

                            <h3 class="text-2xl font-black text-white mb-4 tracking-tight group-hover:text-indigo-300 transition-colors">{{ $materia->nome }}</h3>
                            
                            <div class="flex flex-wrap gap-2 mb-6">
                                @if($materia->horario_matutino)
                                    <span class="text-[9px] font-black uppercase tracking-wider bg-yellow-500/20 text-yellow-400 px-2.5 py-1 rounded-lg border border-yellow-500/20">☀️ Matutino</span>
                                @endif
                                @if($materia->horario_vespertino)
                                    <span class="text-[9px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 px-2.5 py-1 rounded-lg border border-orange-500/20">⛅ Vespertino</span>
                                @endif
                                @if($materia->horario_noturno)
                                    <span class="text-[9px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-400 px-2.5 py-1 rounded-lg border border-indigo-500/20">🌙 Noturno</span>
                                @endif
                            </div>

                            <div class="mt-auto pt-6 border-t border-white/5">
                                <p class="text-xs font-black text-white/70 uppercase tracking-widest mb-1">Localização e Carga</p>
                                <div class="flex justify-between items-center">
                                    <p class="text-sm text-white/60 font-medium">SALA {{ $materia->sala }}</p>
                                    <p class="text-xs text-indigo-400/80 font-bold">{{ $materia->carga_horaria }}h • {{ $materia->total_aulas }} aulas</p>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center justify-between opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-500">
                                <span class="text-xs text-indigo-400 font-black uppercase tracking-widest">Ver detalhes da turma →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
@endsection
