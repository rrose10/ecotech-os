// Mobile sidebar drawer
(() => {
    const sidebar = document.getElementById('eco-sidebar');
    const scrim = document.getElementById('eco-scrim');
    const menu = document.getElementById('eco-menu');

    if (!sidebar || !menu) {
        return;
    }

    const open = () => {
        sidebar.classList.add('is-open');
        scrim?.classList.remove('hidden');
    };

    const close = () => {
        sidebar.classList.remove('is-open');
        scrim?.classList.add('hidden');
    };

    menu.addEventListener('click', open);
    scrim?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => e.key === 'Escape' && close());
})();
