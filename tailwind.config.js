import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'celeste': '#BFDBF7',
                'amarillo': '#F2E29F',
                'azul-noche': '#011627',
                'naranja': '#FE7F2D',
                'verde': '#297373',
            },
        },
    },

    plugins: [forms],
};
