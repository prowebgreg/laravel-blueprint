import preset from './vendor/filament/filament/tailwind.config.preset.js';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'sans-serif'],
                mono: ['Geist Mono', 'monospace'],
            },
        },
    },
};
