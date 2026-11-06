import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/homepage.js', 'resources/js/telegram-chat-realtime.js'],
            refresh: true,
        }),
    ],
});
