@extends('layouts.main')

@section('title', 'Relatórios de Presença - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@push('head-scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
@endpush

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .gradient-bg { background: white !important; min-height: auto !important; }
        .glass { 
            background: white !important; 
            backdrop-filter: none !important; 
            border: none !important; 
            box-shadow: none !important;
            color: black !important;
        }
        .text-white, .text-white\/40, .text-indigo-300, .text-indigo-400 { color: black !important; }
        .blob, .blob-2, #cursor-glow, #theme-toggle { display: none !important; }
        .printable-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        table { color: black !important; border-collapse: collapse !important; width: 100% !important; }
        th, td { border: 1px solid #ddd !important; padding: 8px !important; color: black !important; }
        .bg-white\/5, .bg-white\/10 { background: transparent !important; }
    }

    /* Custom Flatpickr Glass Theme (Dark - Default) */
    .flatpickr-calendar {
        background: rgba(30, 20, 50, 0.8) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        color: white !important;
        border-radius: 1.5rem !important;
        margin-top: 10px !important;
    }
    .flatpickr-month, .flatpickr-weekday, .flatpickr-current-month, .flatpickr-monthDropdown-months {
        color: white !important;
        fill: white !important;
    }
    .flatpickr-day {
        color: rgba(255, 255, 255, 0.8) !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-day:hover {
        background: rgba(255, 255, 255, 0.1) !important;
    }
    .flatpickr-day.selected {
        background: #6366f1 !important;
        border-color: #6366f1 !important;
        color: white !important;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.4) !important;
    }
    .flatpickr-day.today {
        border-color: #a855f7 !important;
        color: #a855f7 !important;
    }
    .flatpickr-day.flatpickr-disabled {
        color: rgba(255, 255, 255, 0.1) !important;
    }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
        color: white !important;
        fill: white !important;
    }
    .flatpickr-innerContainer, .flatpickr-rContainer {
        width: 100% !important;
    }
    .flatpickr-days { width: 100% !important; }
    .dayContainer { width: 100% !important; min-width: 100% !important; max-width: 100% !important; }

    /* Light Theme Overrides for Flatpickr */
    body.theme-light .flatpickr-calendar {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }
    body.theme-light .flatpickr-month, 
    body.theme-light .flatpickr-weekday, 
    body.theme-light .flatpickr-current-month, 
    body.theme-light .flatpickr-monthDropdown-months {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    body.theme-light .flatpickr-day {
        color: #1e293b !important;
    }
    body.theme-light .flatpickr-day:hover {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    body.theme-light .flatpickr-months .flatpickr-prev-month, 
    body.theme-light .flatpickr-months .flatpickr-next-month {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    body.theme-light .flatpickr-day.flatpickr-disabled {
        color: rgba(0, 0, 0, 0.1) !important;
    }

    /* Select Options Theme Fix */
    select option {
        background-color: #1e1b4b !important; /* Indigo Escuro para Tema Escuro */
        color: white !important;
    }
    body.theme-light select option {
        background-color: white !important;
        color: #1e293b !important;
    }

    /* Custom Flatpickr Glass Theme (Dark - Default) */
    .flatpickr-calendar {
        background: rgba(30, 20, 50, 0.9) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        color: white !important;
        border-radius: 1.5rem !important;
        margin-top: 10px !important;
    }
    .flatpickr-month, .flatpickr-weekday, .flatpickr-current-month, .flatpickr-monthDropdown-months {
        color: white !important;
        fill: white !important;
    }
    .flatpickr-day {
        color: rgba(255, 255, 255, 0.8) !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-day:hover {
        background: rgba(255, 255, 255, 0.1) !important;
    }
    .flatpickr-day.selected {
        background: #6366f1 !important;
        border-color: #6366f1 !important;
        color: white !important;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.4) !important;
    }
    .flatpickr-day.today {
        border-color: #a855f7 !important;
        color: #a855f7 !important;
    }
    .flatpickr-day.flatpickr-disabled {
        color: rgba(255, 255, 255, 0.1) !important;
    }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
        color: white !important;
        fill: white !important;
    }
    .flatpickr-innerContainer, .flatpickr-rContainer {
        width: 100% !important;
    }
    .flatpickr-days { width: 100% !important; }
    .dayContainer { width: 100% !important; min-width: 100% !important; max-width: 100% !important; }

    /* Light Theme Overrides for Flatpickr */
    body.theme-light .flatpickr-calendar {
        background: white !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }
    body.theme-light .flatpickr-month, 
    body.theme-light .flatpickr-weekday, 
    body.theme-light .flatpickr-current-month, 
    body.theme-light .flatpickr-monthDropdown-months {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    body.theme-light .flatpickr-day {
        color: #1e293b !important;
    }
    body.theme-light .flatpickr-day:hover {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    body.theme-light .flatpickr-months .flatpickr-prev-month, 
    body.theme-light .flatpickr-months .flatpickr-next-month {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    body.theme-light .flatpickr-day.flatpickr-disabled {
        color: rgba(0, 0, 0, 0.1) !important;
    }
    body.theme-light .flatpickr-day.selected {
        background: #4f46e5 !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden printable-content">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px] no-print"></div>
        <div class="blob-2 no-print"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal no-print">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.professor') }}" class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-indigo-500/50 hover:scale-110 transition-transform">
                        R
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none uppercase">Relatórios</h1>
                        <a href="{{ route('dashboard.professor') }}" class="text-[10px] font-bold text-white/40 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Gerar PDF / Imprimir
                </button>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms] no-print">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Histórico de Presenças</h2>
                <p class="text-white/40 font-medium">Filtre por matéria e período para gerar seu relatório.</p>
            </div>

            <!-- Filtros -->
            <div class="glass p-6 rounded-3xl border border-white/10 mb-8 animate-reveal [animation-delay:300ms] no-print">
                <form action="{{ route('professor.relatorios') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-2 block">Matéria</label>
                        <select name="materia_id" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            <option value="">Todas as Matérias</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                    {{ $materia->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-2 block">Data Início</label>
                        <input type="text" name="data_inicio" value="{{ request('data_inicio') }}" placeholder="DD/MM/AAAA"
                            class="datepicker w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all cursor-pointer">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-white/30 uppercase tracking-widest mb-2 block">Data Fim</label>
                        <input type="text" name="data_fim" value="{{ request('data_fim') }}" placeholder="DD/MM/AAAA"
                            class="datepicker w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all cursor-pointer">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-grow bg-white/10 hover:bg-white/20 text-white font-bold py-2.5 rounded-xl transition-all border border-white/10">
                            Filtrar
                        </button>
                        <a href="{{ route('professor.relatorios') }}" class="bg-white/5 hover:bg-white/10 text-white/40 hover:text-white p-2.5 rounded-xl border border-white/10 transition-all" title="Resetar Filtros">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabela de Resultados -->
            <div class="glass rounded-3xl border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <h3 class="text-xl font-black text-white italic tracking-tighter">Registros Encontrados</h3>
                    <div class="text-[10px] font-black text-white/40 uppercase tracking-widest">
                        Total: {{ $presencas->total() }} registros
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Data / Hora</th>
                                <th class="py-4 px-6">Aluno</th>
                                <th class="py-4 px-6">Matéria</th>
                                <th class="py-4 px-6 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($presencas as $presenca)
                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $presenca->created_at->format('d/m/Y') }}</span>
                                        <span class="block text-[10px] text-white/30 font-mono">{{ $presenca->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $presenca->aluno->nome }}</span>
                                        <span class="block text-[10px] text-white/30 font-mono">{{ $presenca->aluno_ra }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-lg text-[10px] font-black border border-indigo-500/20 uppercase tracking-widest">
                                            {{ $presenca->materia->nome }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <span class="px-3 py-1 bg-green-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-green-500/30">
                                            Presente
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-white/20 font-bold italic">
                                        Nenhum registro de presença encontrado para os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($presencas->hasPages())
                    <div class="px-8 py-6 border-t border-white/10 no-print">
                        {{ $presencas->links() }}
                    </div>
                @endif
            </div>

            <!-- Footer do Relatório (Apenas Impressão) -->
            <div class="hidden print:block mt-12 pt-8 border-t border-gray-200">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">Responsável</p>
                        <p class="font-bold text-gray-800">{{ $professor->nome }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 font-mono">Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
                        <p class="text-[10px] text-gray-400 font-mono italic">Smart Attendance System</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datepicker", {
            locale: "pt",
            dateFormat: "Y-m-d", // Mantém formato do banco no value
            altInput: true,
            altFormat: "d/m/Y", // Mostra formato bonito para o usuário
            disableMobile: "true",
            animate: true
        });
    });
</script>
@endpush
@endsection
