<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script>
            document.documentElement.classList.remove('dark');
            localStorage.setItem('cv_theme', 'light');
            new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class' && document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                    }
                });
            }).observe(document.documentElement, { attributes: true });
        </script>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        @if(config('app.env') === 'production')
            <!-- Plausible Analytics (Privacy-friendly) -->
            <script defer data-domain="{{ parse_url(config('app.url'), PHP_URL_HOST) }}" src="https://plausible.io/js/script.js"></script>

            <!-- Google Analytics (uncomment and update if needed) -->
            {{--
            <script async src="https://www.googletagmanager.com/gtag/js?id=YOUR_GA_ID"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', 'YOUR_GA_ID');
            </script>
            --}}
        @endif
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
