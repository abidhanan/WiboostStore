<script>
    (() => {
        if (window.wiboostInputGuardsReady) {
            return;
        }

        window.wiboostInputGuardsReady = true;

        const cleanName = (value) => String(value || '')
            .replace(/[^\p{L}\s]+/gu, '')
            .replace(/\s{2,}/g, ' ');

        const cleanNumber = (value) => String(value || '').replace(/\D+/g, '');

        const guardedSelectors = [
            ['[data-wiboost-name-only]', cleanName],
            ['form[action*="/register"] input[name="name"]', cleanName],
            ['form[action*="/profile"] input[name="name"]', cleanName],
            ['form[action*="/admin/users"] input[name="name"]', cleanName],
            ['input[name="reseller_payout_name"]', cleanName],
            ['[data-wiboost-number-only]', cleanNumber],
            ['input[name="whatsapp"]', cleanNumber],
            ['input[name="reseller_payout_account"]', cleanNumber],
            ['input[name="admin_cs_whatsapp"]', cleanNumber],
            ['input[name="admin_otp_whatsapp"]', cleanNumber],
        ];

        const bindGuard = (input, cleaner) => {
            if (!input || input.dataset.wiboostGuardBound === 'true') {
                return;
            }

            input.dataset.wiboostGuardBound = 'true';
            input.addEventListener('input', () => {
                if (input.dataset.wiboostGuardCleaning === 'true') {
                    return;
                }

                const cleaned = cleaner(input.value);

                if (cleaned !== input.value) {
                    const start = input.selectionStart ?? cleaned.length;
                    input.dataset.wiboostGuardCleaning = 'true';
                    input.value = cleaned;
                    input.setSelectionRange?.(Math.min(start, cleaned.length), Math.min(start, cleaned.length));
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.dataset.wiboostGuardCleaning = 'false';
                }
            });
        };

        const applyGuards = () => {
            guardedSelectors.forEach(([selector, cleaner]) => {
                document.querySelectorAll(selector).forEach((input) => bindGuard(input, cleaner));
            });
        };

        document.addEventListener('DOMContentLoaded', applyGuards);
        window.addEventListener('load', applyGuards, { once: true });
        document.addEventListener('wiboost:content-ready', applyGuards);
    })();
</script>
