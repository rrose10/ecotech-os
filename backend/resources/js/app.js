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

// Apple-style polish: top bar depth on scroll, count-up numbers, smooth theme switch
(() => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Top bar gets a stronger glass once the page scrolls
    const bar = document.querySelector('.eco-topbar');
    if (bar) {
        const onScroll = () => bar.classList.toggle('is-scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Smooth light/dark switch
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-theme-toggle]')) return;
        const root = document.documentElement;
        root.classList.add('theme-anim');
        setTimeout(() => root.classList.remove('theme-anim'), 600);
    });

    if (reduce) return;

    // Count-up for big numbers like ৳12,450.00 or 24
    const easeOut = (t) => 1 - Math.pow(1 - t, 4);
    const pattern = /^(\D*?)(\d[\d,]*)(\.\d+)?(\D*)$/;

    document.querySelectorAll('main .text-3xl, main .text-2xl, main .text-xl').forEach((el) => {
        if (el.children.length || !el.classList.contains('font-bold') && !el.classList.contains('font-extrabold') && !el.classList.contains('font-semibold')) return;
        const original = el.textContent.trim();
        const m = original.match(pattern);
        if (!m || original.length > 20) return;

        const [, prefix, intPart, decPart = '', suffix] = m;
        const target = parseFloat(intPart.replace(/,/g, '') + decPart);
        if (!isFinite(target) || target === 0) return;

        const decimals = decPart ? decPart.length - 1 : 0;
        const grouped = intPart.includes(',');
        const fmt = (n) => {
            const s = n.toFixed(decimals);
            if (!grouped) return s;
            const [i, d] = s.split('.');
            return i.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (d ? '.' + d : '');
        };

        const start = performance.now() + 150;
        const duration = 1100;
        el.textContent = prefix + fmt(0) + suffix;

        const tick = (now) => {
            const t = Math.min(Math.max((now - start) / duration, 0), 1);
            el.textContent = t >= 1 ? original : prefix + fmt(target * easeOut(t)) + suffix;
            if (t < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });
})();
