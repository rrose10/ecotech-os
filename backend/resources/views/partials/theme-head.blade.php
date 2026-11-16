<script>
    try {
        if (localStorage.getItem('ecoTheme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    } catch (e) {}
    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-theme-toggle]')) return;
        var dark = document.documentElement.classList.toggle('dark');
        try { localStorage.setItem('ecoTheme', dark ? 'dark' : 'light'); } catch (err) {}
    });
</script>
