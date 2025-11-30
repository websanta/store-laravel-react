import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        hmr: {
            protocol: 'ws',
            host: 'vmmint22.local',  // Используем домен вместо IP
            port: 5174,
            clientPort: 5174,
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
        cors: {
            origin: ['https://vmmint22.local', 'http://vmmint22.local'],
            credentials: true,
        },
    },
});
