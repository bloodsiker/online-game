import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/homepage.js',
                'resources/js/mini-app/game.js', 'resources/js/mini-app/chat-realtime.js', 'resources/js/mini-app/link-modal.js'],
            refresh: true,
        }),
    ],
});
