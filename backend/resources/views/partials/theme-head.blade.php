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

<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
