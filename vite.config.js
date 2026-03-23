import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
            publicDirectory: 'public',
            buildDirectory: 'build',
            detectTls: false,
            valetTls: false,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        // https: {
        //     key: fs.readFileSync('./infrastructure/docker/nginx/certs/temp-key.pem'),
        //     cert: fs.readFileSync('./infrastructure/docker/nginx/certs/temp.pem'),
        // },
        hmr: {
            protocol: 'wss',
            host: 'store.websanta.ru',
            port: 443,
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
        origin: 'https://store.websanta.ru',
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name]-[hash][extname]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
    },
});
