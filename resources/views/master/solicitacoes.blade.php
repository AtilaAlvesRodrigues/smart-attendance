@extends('layouts.theme')

@section('title', 'Solicitações de Acesso - Smart Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
@endpush

@section('nav-user')
    <div class="pal-nav-actions" style="gap:0.5rem">
        <div class="pal-nav-user">
            <span class="pal-nav-user-role">Acesso Root</span>
            <span class="pal-nav-user-name">Administrador</span>
        </div>
    </div>
@endsection

@section('content')
<main class="pal-main animate-reveal">

    <div class="border-b border-white/5 pb-8 mb-10">
        <p class="pal-eyebrow mb-2">Admin Master</p>
        <h1 class="pal-title">Solicitações de Acesso</h1>
        <p class="pal-subtitle">Gerencie pedidos de registro de alunos e professores.</p>
    </div>

    @if (session('success'))
        <div style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25); border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #22c55e; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #ef4444; margin-bottom: 24px;">
            {{ $errors->first() }}
        </div>
    @endif

    @forelse ($solicitacoes as $s)
    <div class="glass" style="border-radius: 12px; padding: 24px 28px; margin-bottom: 16px; border: 1px solid
        @if($s->status === 'pendente') rgba(168,85,247,0.2)
        @elseif($s->status === 'aprovado') rgba(34,197,94,0.15)
        @else rgba(239,68,68,0.15)
        @endif;">

        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">

            {{-- Info --}}
            <div style="flex: 1; min-width: 240px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 10px; border-radius: 20px;
                        @if($s->tipo === 'aluno') background: rgba(99,102,241,0.15); color: #818cf8;
                        @else background: rgba(20,184,166,0.15); color: #2dd4bf;
                        @endif">
                        {{ ucfirst($s->tipo) }}
                    </span>
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 10px; border-radius: 20px;
                        @if($s->status === 'pendente') background: rgba(168,85,247,0.15); color: #c084fc;
                        @elseif($s->status === 'aprovado') background: rgba(34,197,94,0.12); color: #4ade80;
                        @else background: rgba(239,68,68,0.12); color: #f87171;
                        @endif">
                        {{ ucfirst($s->status) }}
                    </span>
                </div>

                <h3 class="pal-text" style="font-size: 1rem; font-weight: 800; margin: 0 0 4px;">{{ $s->nome }}</h3>
                <p class="pal-subtitle" style="font-size: 0.8rem; margin: 0 0 4px;">{{ $s->email }}</p>
                @if ($s->cpf)
                    <p class="pal-subtitle" style="font-size: 0.75rem; margin: 0;">CPF: {{ $s->cpf }}</p>
                @endif
                @if ($s->ra)
                    <p class="pal-subtitle" style="font-size: 0.75rem; margin: 0;">RA: {{ $s->ra }}</p>
                @endif
                <p class="pal-subtitle" style="font-size: 0.72rem; margin: 6px 0 0; opacity: 0.6;">
                    Solicitado em {{ $s->created_at->format('d/m/Y \à\s H:i') }}
                </p>
                @if ($s->motivo_rejeicao)
                    <p style="font-size: 0.72rem; color: #f87171; margin: 4px 0 0;">
                        Motivo: {{ $s->motivo_rejeicao }}
                    </p>
                @endif
            </div>

            {{-- Ações --}}
            @if ($s->status === 'pendente')
            <div style="display: flex; gap: 10px; align-items: center; flex-shrink: 0;">
                <form method="POST" action="{{ route('master.solicitacoes.aprovar', $s->id) }}">
                    @csrf
                    <button type="submit" style="background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='rgba(34,197,94,0.25)'"
                        onmouseout="this.style.background='rgba(34,197,94,0.15)'">
                        Aprovar
                    </button>
                </form>
                <form method="POST" action="{{ route('master.solicitacoes.rejeitar', $s->id) }}" style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                    @csrf
                    <input type="text" name="motivo" placeholder="Motivo (opcional)"
                        style="font-size:12px; padding:6px 12px; border-radius:6px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); color:#ccc; width:200px;"
                        maxlength="255">
                    <button type="submit" style="background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.25); padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; width:100%;"
                        onmouseover="this.style.background='rgba(239,68,68,0.2)'"
                        onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                        Rejeitar
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
    @empty
        <div class="glass" style="border-radius: 12px; padding: 48px; text-align: center; border: 1px solid rgba(255,255,255,0.06);">
            <p class="pal-subtitle" style="margin: 0;">Nenhuma solicitação encontrada.</p>
        </div>
    @endforelse

    <div style="margin-top: 24px;">
        {{ $solicitacoes->links() }}
    </div>

</main>
@endsection
