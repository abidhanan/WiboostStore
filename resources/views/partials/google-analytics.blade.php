<script>
    (() => {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', 'G-JV1409WNMH');

        let loaded = false;
        const loadGoogleAnalytics = () => {
            if (loaded) return;
            loaded = true;

            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-JV1409WNMH';
            document.head.appendChild(script);
        };

        ['pointerdown', 'touchstart', 'keydown'].forEach((eventName) => {
            window.addEventListener(eventName, loadGoogleAnalytics, { once: true, passive: true });
        });

        window.addEventListener('load', () => {
            window.setTimeout(loadGoogleAnalytics, 12000);
        }, { once: true });
    })();
</script>
