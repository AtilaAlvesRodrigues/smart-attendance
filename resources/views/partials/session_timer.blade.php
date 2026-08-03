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
<!-- Container do Timer de Sessão -->
<div id="session-timer-widget" class="inline-flex items-center justify-center gap-2 px-4 py-1.5 rounded-full text-xs font-mono font-medium shadow-sm backdrop-blur-md transition-all duration-300 bg-purple-950/40 text-purple-300 border border-purple-500/30 text-center">
    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse flex-shrink-0" id="session-timer-dot"></span>
    <span class="text-gray-300 font-sans hidden sm:inline timer-label" id="session-timer-label">Sessão:</span>
    <span id="session-timer-display" class="font-bold tracking-wider text-white tabular-nums inline-block text-center">59:59</span>
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
/* Centralização e alinhamento do timer */
#session-timer-widget {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
}

#session-timer-display {
    font-variant-numeric: tabular-nums;
    text-align: center;
}

/* Estilos para quando o timer é embutido na Sidebar ou no Slot do Aluno */
#sidebar-timer-slot #session-timer-widget,
#aluno-timer-slot #session-timer-widget {
    position: static !important;
    box-shadow: none;
    margin: 0 auto;
}

#aluno-timer-slot {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    width: 100%;
}

html.sidebar-collapsed #sidebar-timer-slot .timer-label {
    display: none !important;
}

/* Posicionamento fallback para evitar sobreposição da navbar */
#session-timer-widget.fallback-fixed {
    position: fixed;
    top: 4.5rem;
    right: 1.5rem;
    z-index: 9999;
    background: rgba(15, 23, 42, 0.92);
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

    // Tenta encaixar no slot da sidebar ou no slot dedicado da página do aluno
    function mountWidgetInPage() {
        const widget = document.getElementById('session-timer-widget');
        const sidebarSlot = document.getElementById('sidebar-timer-slot');
        const alunoSlot = document.getElementById('aluno-timer-slot');

        if (widget && sidebarSlot) {
            sidebarSlot.appendChild(widget);
            widget.classList.remove('fallback-fixed');
        } else if (widget && alunoSlot) {
            alunoSlot.appendChild(widget);
            widget.classList.remove('fallback-fixed');
        } else if (widget) {
            widget.classList.add('fallback-fixed');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountWidgetInPage);
    } else {
        mountWidgetInPage();
    }

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
            if (dot) dot.className = "inline-block w-2 h-2 rounded-full bg-amber-400 animate-ping flex-shrink-0";
            if (widget) {
                widget.style.borderColor = "rgba(245, 158, 11, 0.6)";
                widget.style.backgroundColor = "rgba(120, 53, 15, 0.4)";
                widget.style.color = "#fcd34d";
            }
        } else if (remainingSec <= 60 && remainingSec > 0) {
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
