document.addEventListener('DOMContentLoaded', () => {
    // Theme Toggle Logic
    const themeBtn = document.getElementById('theme-toggle');
    if(themeBtn) {
        const iconMoon = document.getElementById('theme-icon-moon');
        const iconSun = document.getElementById('theme-icon-sun');
        const iconContainer = document.getElementById('theme-icon-container');
        const html = document.documentElement;

        function updateIcon() {
            if (html.classList.contains('light-mode')) {
                iconMoon.style.opacity = '1';
                iconSun.style.opacity = '0';
                iconContainer.style.transform = 'rotate(360deg)';
                themeBtn.style.background = '#ffffff';
                themeBtn.style.color = '#111111';
                themeBtn.style.borderColor = 'rgba(0,0,0,0.1)';
            } else {
                iconMoon.style.opacity = '0';
                iconSun.style.opacity = '1';
                iconContainer.style.transform = 'rotate(0deg)';
                themeBtn.style.background = '#111111';
                themeBtn.style.color = '#efefef';
                themeBtn.style.borderColor = 'rgba(255,255,255,0.1)';
            }
        }

        updateIcon();

        themeBtn.addEventListener('click', () => {
            html.classList.toggle('light-mode');
            if (html.classList.contains('light-mode')) {
                localStorage.setItem('pal_theme', 'light');
            } else {
                localStorage.setItem('pal_theme', 'dark');
            }
            updateIcon();
        });
    }

    // Cursor glow
    document.addEventListener('mousemove', (e) => {
        const g = document.getElementById('pal-cursor-glow');
        if (g) {
            g.style.left = (e.clientX - 200) + 'px';
            g.style.top  = (e.clientY - 200) + 'px';
            g.style.opacity = '1';
        }
    });

});
