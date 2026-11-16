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

<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#0b1020">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="EcoTech">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<script>
    // Service workers need a secure page (HTTPS or localhost); skip quietly otherwise
    if ('serviceWorker' in navigator && window.isSecureContext) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
