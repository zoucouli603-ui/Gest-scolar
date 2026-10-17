import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const brand = Object.fromEntries(shades.map((s) => [s, `rgb(var(--brand-${s}) / <alpha-value>)`]));

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Support/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                blue: brand,
                gold: {
                    50: '#fffbeb', 100: '#fff3c4', 200: '#ffe58a', 300: '#ffd84d',
                    400: '#f9c520', 500: '#f5b800', 600: '#d99a00', 700: '#b07600',
                },
            },
            fontFamily: {
                sans: ['var(--font-sans)', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', 'Poppins', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
