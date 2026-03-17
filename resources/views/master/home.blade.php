@extends('layouts.theme')

@section('title', 'Painel Master - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
@endpush

@section('nav-left')
    {{-- Root dashboard --}}
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Acesso Root</span>
        <span class="pal-nav-user-name">{{ $master->nome ?? 'Administrador' }}</span>
    </div>
@endsection

@section('content')
<main class="pal-main animate-reveal">

    {{-- Header --}}
    <div class="border-b border-white/5 pb-8 mb-10">
        <p class="font-mono text-xs font-bold tracking-[0.2em] uppercase text-white/50 mb-2">Admin Master</p>
        <h1 class="pal-always-white" style="font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.04em; margin:0;">Visão Geral do Sistema</h1>
        <p class="pal-page-sub text-white/60">Controle total de usuários, turmas e registros de presença.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        @foreach([
            [$professoresCount, 'Professores'],
            [$alunosCount, 'Alunos'],
            [$materiasCount, 'Matérias'],
        ] as [$count, $label])
        <div class="p-8 rounded-sm shadow-xl transition-all animate-fade-in" style="background-color: var(--pal-white); color: var(--pal-black);">
            <p class="text-5xl font-black tracking-tighter leading-none mb-3">{{ $count }}</p>
            <p class="font-mono text-xs font-bold tracking-widest uppercase opacity-70 m-0">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Action Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach([
            [route('master.professores'), 'Professores', 'Visualizar e gerenciar docentes do sistema.'],
            [route('master.alunos'), 'Alunos', 'Gerenciar matrículas e acesso dos estudantes.'],
            [route('master.materias'), 'Matérias', 'Configurar disciplinas e vínculos curriculares.'],
        ] as $index => $item)
        @php
            [$url, $title, $desc] = $item;
        @endphp
        <a href="{{ $url }}" class="group glass block p-8 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline hover:-translate-y-1 pal-card-delay-{{ $index + 1 }}">
            <h3 class="text-lg font-black tracking-tight text-white mb-2">{{ $title }}</h3>
            <p class="text-sm text-white/60 leading-relaxed mb-6">{{ $desc }}</p>
            <span class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase text-white/50 group-hover:text-white transition-colors border-b border-white/20 pb-1">Explorar &rarr;</span>
        </a>
        @endforeach
    </div>

    {{-- Large Action --}}
    <a href="{{ route('master.presenca') }}" class="group glass block p-10 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline hover:-translate-y-1">
        <div class="flex items-center justify-between gap-8 flex-wrap">
            <div>
                <p class="font-mono text-xs font-bold tracking-widest uppercase text-white/50 mb-3">Módulo</p>
                <h3 class="text-3xl font-black tracking-tighter text-white mb-3">Central de Chamada QR Code</h3>
                <p class="text-sm text-white/60 m-0">Monitore presenças em tempo real e acesse o log completo de atividades.</p>
            </div>
            <span class="bg-white text-black px-8 py-4 text-sm font-bold tracking-wide rounded-sm whitespace-nowrap group-hover:bg-gray-100 transition-colors">Acessar Central &rarr;</span>
        </div>
    </a>

</main>
@endsection
