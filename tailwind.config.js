// path: tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import preset from './vendor/filament/filament/tailwind.config.preset' // <-- Añadido por Filament
/** @type {import('tailwindcss').Config} */
export default {
    // INICIO: Modificación Tarea 3.10
    // Se establece la estrategia 'class' para que el modo claro sea
    // el predeterminado y el modo oscuro se active solo vía JS (Tarea 3.11).
    darkMode: 'class',
    // FIN: Modificación Tarea 3.10
    
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};