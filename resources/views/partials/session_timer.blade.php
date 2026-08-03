@php
    $isAuth = auth('alunos')->check() || auth('professores')->check() || auth('masters')->check();
    if ($isAuth && !session()->has('login_time')) {
        session(['login_time' => time()]);
    }
    $loginTime = $isAuth ? session('login_time', time()) : null;
    // 1 hora de limite de sessão = 3600 segundos
    $expiresAtMs = $loginTime ? ($loginTime + 3600) * 1000 : null;
@endphp

@if($isAuth && $expiresAtMs)
<!-- Container do Timer de Sessão (Renderizado dinamicamente no slot da Sidebar ou no Topo) -->
<div id="session-timer-widget" class="flex items-center gap-2 px-2.5 py-1 rounded-lg text-xs font-mono font-medium shadow-sm backdrop-blur-md transition-all duration-300 bg-purple-950/40 text-purple-300 border border-purple-500/30">
    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse flex-shrink-0" id="session-timer-dot"></span>
    <span class="text-gray-300 font-sans hidden sm:inline timer-label" id="session-timer-label">Sessão:</span>
    <span id="session-timer-display" class="font-bold tracking-wider text-white">59:59</span>
</div>

<!-- Modal de Timeout de Sessão -->
<div id="session-timeout-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-opacity duration-300">
    <div class="max-w-md w-full rounded-3xl p-8 border border-white/20 shadow-2xl text-center space-y-6 bg-slate-900 text-white">
        <div class="w-16 h-16 bg-purple-500/20 text-purple-400 rounded-2xl flex items-center justify-center mx-auto text-3xl shadow-inner border border-purple-500/30">
            ⏳
        </div>
        <div>
            <h3 class="text-2xl font-bold tracking-tight text-white mb-2">Sessão Expirada</h3>
            <p class="text-sm text-gray-300 leading-relaxed">
                Sua sessão atingiu o tempo limite de <strong>1 hora</strong> por motivos de segurança. Você será redirecionado para a página inicial.
            </p>
        </div>
        <div class="pt-2">
            <button id="session-timeout-btn" class="w-full py-3 px-6 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold shadow-lg shadow-purple-600/30 transition-all duration-200 cursor-pointer">
                Voltar para a Página Inicial
            </button>
        </div>
    </div>
</div>

<style>
/* Ajuste de estilização quando o timer fica dentro da Sidebar */
#sidebar-timer-slot #session-timer-widget {
    position: static !important;
    box-shadow: none;
}
html.sidebar-collapsed #sidebar-timer-slot .timer-label {
    display: none !important;
}
/* Posicionamento fallback caso a página não tenha sidebar */
body:not(.pal-has-sidebar) #session-timer-widget,
#session-timer-widget.fallback-fixed {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(168, 85, 247, 0.3);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}
</style>

<script>
(function() {
    const serverExpiresAt = Number("{{ $expiresAtMs }}");
    const LOCAL_STORAGE_KEY = 'smart_session_expires_at';
    const TIMED_OUT_KEY = 'smart_session_timed_out';
    const LOGIN_PAGE_URL = "{{ route('login_form') }}";
    const LOGOUT_BEACON_URL = "{{ url('/force-logout-beacon') }}";
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    // Reposiciona o widget para o slot da sidebar (se existir na página)
    function mountWidgetInSidebar() {
        const widget = document.getElementById('session-timer-widget');
        const slot = document.getElementById('sidebar-timer-slot');
        if (widget && slot) {
            slot.appendChild(widget);
            widget.classList.remove('fallback-fixed');
        } else if (widget) {
            widget.classList.add('fallback-fixed');
        }
    }

    // Tenta encaixar na sidebar no carregamento do DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountWidgetInSidebar);
    } else {
        mountWidgetInSidebar();
    }

    // Sincronização e expiração no localStorage
    let expiresAt = serverExpiresAt;
    if (serverExpiresAt) {
        localStorage.setItem(LOCAL_STORAGE_KEY, serverExpiresAt.toString());
    } else {
        const stored = localStorage.getItem(LOCAL_STORAGE_KEY);
        if (stored) expiresAt = parseInt(stored, 10);
    }

    let isTimedOut = false;

    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;

        if (h > 0) {
            return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }

    function executeLogoutAndRedirect() {
        if (CSRF_TOKEN) {
            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            navigator.sendBeacon(LOGOUT_BEACON_URL, formData);
        }
        localStorage.removeItem(LOCAL_STORAGE_KEY);
        localStorage.removeItem(TIMED_OUT_KEY);
        window.location.href = LOGIN_PAGE_URL;
    }

    function showTimeoutModal() {
        if (isTimedOut) return;
        isTimedOut = true;

        const modal = document.getElementById('session-timeout-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        const btn = document.getElementById('session-timeout-btn');
        if (btn) {
            btn.addEventListener('click', executeLogoutAndRedirect);
        }

        setTimeout(executeLogoutAndRedirect, 4000);
    }

    function checkTimer() {
        const storedExpiresAt = parseInt(localStorage.getItem(LOCAL_STORAGE_KEY) || expiresAt, 10);
        if (!storedExpiresAt) return;

        const remainingMs = storedExpiresAt - Date.now();
        const remainingSec = Math.max(0, Math.floor(remainingMs / 1000));

        const display = document.getElementById('session-timer-display');
        const dot = document.getElementById('session-timer-dot');
        const widget = document.getElementById('session-timer-widget');

        if (display) {
            display.textContent = formatTime(remainingSec);
        }

        // Alertas visuais
        if (remainingSec <= 300 && remainingSec > 60) {
            // Últimos 5 minutos: Amber
            if (dot) dot.className = "inline-block w-2 h-2 rounded-full bg-amber-400 animate-ping flex-shrink-0";
            if (widget) {
                widget.style.borderColor = "rgba(245, 158, 11, 0.6)";
                widget.style.backgroundColor = "rgba(120, 53, 15, 0.4)";
                widget.style.color = "#fcd34d";
            }
        } else if (remainingSec <= 60 && remainingSec > 0) {
            // Último 1 minuto: Vermelho
            if (dot) dot.className = "inline-block w-2 h-2 rounded-full bg-rose-500 animate-ping flex-shrink-0";
            if (widget) {
                widget.style.borderColor = "rgba(244, 63, 94, 0.7)";
                widget.style.backgroundColor = "rgba(136, 19, 55, 0.5)";
                widget.style.color = "#fca5a5";
            }
        }

        if (remainingSec <= 0) {
            localStorage.setItem(TIMED_OUT_KEY, Date.now().toString());
            showTimeoutModal();
        }
    }

    const interval = setInterval(checkTimer, 1000);
    checkTimer();

    window.addEventListener('storage', function(e) {
        if (e.key === TIMED_OUT_KEY && e.newValue) {
            showTimeoutModal();
        }
        if (e.key === LOCAL_STORAGE_KEY && e.newValue) {
            expiresAt = parseInt(e.newValue, 10);
            checkTimer();
        }
    });

    window.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            checkTimer();
        }
    });
})();
</script>
@else
<script>
    localStorage.removeItem('smart_session_expires_at');
    localStorage.removeItem('smart_session_timed_out');
</script>
@endif
