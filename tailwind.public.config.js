import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Konfigurasi CSS publik/user (app.css): tidak memindai view admin sehingga bundle lebih kecil.
 * CSS admin (admin.css) memakai tailwind.config.js yang memindai seluruh view.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
        '!./resources/views/admin/**',
        '!./resources/views/layouts/admin.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
