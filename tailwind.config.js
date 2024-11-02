import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */

export default {
    content: [
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    safelist: [
        {
            pattern: /w-.+/,
        },
        {
            pattern: /rounded-.+/,
        },
        {
            pattern: /text-.+/,
        },
        {
            pattern: /p.?-.+/,
        },
        {
            pattern: /m.?-.+/,
        },
        {
            pattern: /shadow-.+/,
        },
        {
            pattern: /border-.+/,
        },
        {
            pattern: /ring-.+/,
        },
        
    ],

    plugins: [
        forms,
        
        
    ],
};

