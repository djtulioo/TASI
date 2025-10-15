import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            width: {
                '30': '100px',
            },
            spacing: {
                '30': '100px',
            },
            colors: {
                indigo: {
                    50:  '#e9f7f6',
                    100: '#c8ebe9',
                    200: '#a3dedb',
                    300: '#7dd1cd',
                    400: '#0e5f5a', // novo tom base
                    500: '#0c544f',
                    600: '#0a4944',
                    700: '#083f39',
                    800: '#06342e',
                    900: '#042923',
                    950: '#021f1a',
                },
            },
        },
    },

    plugins: [forms, typography],
};
