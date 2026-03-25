@extends('layouts.theme')

@section('title', 'Gestão de Palestra - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('dashboard.professor') }}" class="pal-nav-btn pal-nav-btn-ghost">
        🏠 Painel Professor
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/evento-presenca.css') }}">
@endpush

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-7xl mx-auto w-full p-6 mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10 flex-grow">
        
        <div class="lg:col-span-12 animate-reveal event-management-header">
            <p class="pal-eyebrow" style="margin-bottom:0.5rem">Sessão Externa — Sem Limite de Tempo</p>
            <h2 class="pal-title">Evento: Palestra / Apresentação</h2>
        </div>

        {{-- QR Code Column --}}
        <div class="lg:col-span-5 flex flex-col gap-8 animate-reveal [animation-delay:200ms]">
            <div class="glass p-10 rounded-sm border border-white/10 flex flex-col items-center justify-center text-center">
                <h2 class="text-2xl font-black pal-text tracking-tight mb-8">QR Code do Evento</h2>
                <div id="qrcode" class="event-qr-box"></div>
                
                <div class="w-full flex flex-col gap-4">
                    <p class="text-white/70 text-xs font-bold uppercase tracking-widest leading-relaxed">
                        Link de Acesso Direto (Teste):
                    </p>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ route('evento.checkin') }}" 
                               class="pal-filter-input flex-grow text-[11px] font-mono" id="copy-link">
                        <button onclick="copyLink()" class="pal-nav-btn pal-nav-btn-solid text-[10px] px-3">Copiar</button>
                    </div>
                </div>
            </div>

            <div class="glass p-8 rounded-sm border border-white/10">
                <h3 class="font-black text-white mb-6 text-sm uppercase tracking-widest italic">Ações do Gestor</h3>
                <div class="event-actions-box">
                    <button onclick="generatePDF()" class="pal-btn-action w-full justify-center event-btn-pdf flex items-center gap-3">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Gerar Lista (PDF)</span>
                    </button>
                    <button onclick="closeSession()" class="pal-btn-action w-full justify-center event-btn-close flex items-center gap-3">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Encerrar Palestra</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- List Column --}}
        <div class="lg:col-span-7 flex flex-col gap-6 animate-reveal [animation-delay:400ms]">
            <div class="glass p-8 rounded-sm border border-white/10 flex-grow flex flex-col min-h-[500px]">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black pal-text tracking-tighter">Participantes</h2>
                        <p class="pal-subtitle mt-1">Check-ins em tempo real.</p>
                    </div>
                    <div class="pal-count-badge p-3 transform -rotate-3">
                        <span class="text-3xl font-black" id="visitor-count">0</span>
                    </div>
                </div>

                <div class="flex-grow overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse event-visitor-table">
                        <thead>
                            <tr class="text-xs font-black text-white/70 uppercase tracking-[0.2em] border-b border-white/5">
                                <th class="pb-4 px-2">Nome</th>
                                <th class="pb-4 px-2">E-mail (Protegido)</th>
                            </tr>
                        </thead>
                        <tbody id="visitor-list" class="divide-y divide-white/5">
                            <tr id="empty-state">
                                <td colspan="2" class="py-20 text-center opacity-20">
                                    <span class="text-xs font-black uppercase tracking-[0.2em]">Nenhum participante ainda...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {{-- Toast Notification --}}
    <div id="copy-toast" class="hidden fixed inset-0 z-[150] flex items-center justify-center pointer-events-none p-6">
        <div class="glass px-6 py-3 border border-white/10 flex items-center gap-3 animate-reveal shadow-2xl" 
             style="border-radius:2px; background:rgba(255,255,255,0.05); animation: toastPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;">
            <span class="text-lg">✅</span>
            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white">Link Copiado</span>
        </div>
    </div>
</main>

<style>
@keyframes toastPop {
    0% { opacity: 0; transform: translateY(-30px) scale(0.9); }
    100% { opacity: 1; transform: translateY(-80px) scale(1); }
}
</style>
</div>

{{-- Confirmation Overlay --}}
<div id="confirm-overlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 backdrop-blur-md bg-black/40">
    <div class="glass p-8 border border-white/10 max-w-md w-full text-left animate-reveal" style="border-radius:4px; padding:2.5rem;">
        <div style="display:flex; align-items:flex-start; gap:1.5rem; margin-bottom:2rem;">
            <div style="width:48px; height:48px; background:rgba(239, 68, 68, 0.1); border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#ef4444;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="pal-text font-black text-lg mb-1 uppercase tracking-wider">Finalizar Sessão</h3>
                <p class="pal-subtitle text-xs leading-relaxed">
                    Você está prestes a encerrar a lista de presença desta palestra. Esta ação enviará o relatório final e fechará o acesso externo.
                </p>
            </div>
        </div>
        
        <div class="flex gap-4">
            <button onclick="closeConfirmModal()" class="pal-nav-btn pal-nav-btn-ghost flex-grow justify-center py-3" style="border-radius:2px;">Cancelar</button>
            <button onclick="executeCloseSession()" class="pal-btn-action flex-grow justify-center bg-red-600 border-red-500 py-3" style="border-radius:2px;">Confirmar Encerramento</button>
        </div>
    </div>
</div>

{{-- Success Overlay --}}
<div id="close-overlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 backdrop-blur-xl bg-black/60">
    <div class="glass p-12 border-2 border-white/10 max-w-md w-full text-center animate-reveal" style="border-radius:4px;">
        <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center text-3xl mx-auto mb-8">✅</div>
        <h2 class="pal-title mb-4" style="font-size:1.5rem;">PALESTRA ENCERRADA</h2>
        <p class="pal-subtitle mb-10 text-sm">
            O relatório de presença foi gerado e enviado para seu e-mail institucional.
        </p>
        <a href="{{ route('dashboard.professor') }}" class="pal-btn-primary w-full justify-center py-4" style="border-radius:2px;">VOLTAR AO PAINEL</a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="{{ asset('js/pages/evento-presenca.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Professor info for PDF
        const profInfo = {
            name: "{{ $professor->nome ?? 'Docente' }}",
            email: "{{ $professor->email ?? 'N/A' }}",
            cpf: "{{ $professor->cpf ?? 'N/A' }}"
        };
        
        initEventPresenca("{{ route('evento.checkin') }}", profInfo);
    });
</script>
@endpush
