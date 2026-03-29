async function handleCheckin(e) {
    e.preventDefault();

    const name     = document.getElementById('visitor-name').value.trim();
    const email    = document.getElementById('visitor-email').value.trim().toLowerCase();
    const errorMsg = document.getElementById('error-msg');
    const btn      = e.target.querySelector('button[type="submit"]');

    // Proteção honeypot
    if (document.getElementById('hp_field')?.value) return;

    // Bloquear duplo envio
    if (btn) btn.disabled = true;

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const res = await fetch('/evento/checkin/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ name, email, token: window.eventoToken || '', hp_field: '' }),
        });

        if (res.status === 409) {
            errorMsg.style.display = 'block';
            return;
        }

        if (!res.ok) {
            errorMsg.textContent = '⚠ Erro ao registrar. Tente novamente.';
            errorMsg.style.display = 'block';
            return;
        }

        // Guardar no localStorage para evitar re-envio neste dispositivo
        localStorage.setItem('evt_' + (window.eventoToken || '') + '_checked', email);

        document.getElementById('checkin-card').style.display = 'none';
        document.getElementById('success-card').style.display = 'block';

    } catch {
        errorMsg.textContent = '⚠ Sem conexão com o servidor.';
        errorMsg.style.display = 'block';
    } finally {
        if (btn) btn.disabled = false;
    }
}
