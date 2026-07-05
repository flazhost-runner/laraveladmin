import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // JANGAN pakai fitur fonts (bunny/google) di sini: font di-fetch
            // SAAT BUILD dari fonts.bunny.net — build gagal total saat host
            // tidak bisa mengakses bunny (timeout). Stack --font-sans di
            // app.css sudah ber-fallback system-ui.
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
