import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */

export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                themeRed: '#ef4444',
                themeNavy: '#2a2aa3'
            }
        },
    },
    

    plugins: [
        forms,
        
        
    ]
};