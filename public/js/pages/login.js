document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggles
    const passwordInputs = document.querySelectorAll('.login-input-password');
    const toggleBtns = document.querySelectorAll('.login-password-btn');

    toggleBtns.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            // O campo é o que está no mesmo wrapper do botão (a posição na página é só o plano B)
            const input = btn.closest('.login-password-wrapper')?.querySelector('input') || passwordInputs[index];
            const rotulo = btn.dataset.rotulo || 'senha';
            if (input) {
                const mostrar = input.type === 'password';
                input.type = mostrar ? 'text' : 'password';
                // Leitores de tela anunciam o estado atual do botão
                btn.setAttribute('aria-pressed', String(mostrar));
                btn.setAttribute('aria-label', (mostrar ? 'Ocultar ' : 'Mostrar ') + rotulo);
            }
        });
    });
});
