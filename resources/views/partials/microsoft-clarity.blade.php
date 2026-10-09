<script>
    (() => {
        window.clarity = window.clarity || function () {
            (window.clarity.q = window.clarity.q || []).push(arguments);
        };

        let loaded = false;
        const loadClarity = () => {
            if (loaded) return;
            loaded = true;

            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.clarity.ms/tag/xw4sah6hw9';
            document.head.appendChild(script);
        };

        ['pointerdown', 'touchstart', 'keydown'].forEach((eventName) => {
            window.addEventListener(eventName, loadClarity, { once: true, passive: true });
        });

        window.addEventListener('load', () => {
            window.setTimeout(loadClarity, 15000);
        }, { once: true });
    })();
</script>
