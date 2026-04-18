import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('hamburgerBtn');
    const links = document.getElementById('navLinks');

    if (btn && links) {
        btn.addEventListener('click', () => {
            const isOpen = links.classList.toggle('open');
            btn.classList.toggle('open', isOpen);
            btn.setAttribute('aria-expanded', isOpen);
        });

        links.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                links.classList.remove('open');
                btn.classList.remove('open');
                btn.setAttribute('aria-expanded', false);
            });
        });
    }
});