document.addEventListener('DOMContentLoaded', () => {
    // Password visibility toggles
    const passwordInputs = document.querySelectorAll('.login-input-password');
    const toggleBtns = document.querySelectorAll('.login-password-btn');

    toggleBtns.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const input = passwordInputs[index];
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    // Optional: change icon to eye-slash
                } else {
                    input.type = 'password';
                    // Optional: change icon to eye
                }
            }
        });
    });
});
