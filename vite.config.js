import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
            publicDirectory: 'public',
            buildDirectory: 'build',
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        https: {
            key: fs.readFileSync('./infrastructure/docker/nginx/certs/temp-key.pem'),
            cert: fs.readFileSync('./infrastructure/docker/nginx/certs/temp.pem'),
        },
        hmr: {
            protocol: 'wss',
            host: 'vmmint22.local',
            port: 5174,
            clientPort: 443,
            path: 'vite-hmr',
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
