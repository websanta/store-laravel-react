import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
	plugins: [
		laravel({
			input: ['resources/css/app.css', 'resources/js/app.tsx'],
			refresh: true,
		}),
		react(),
	],
	server: {
		host: '0.0.0.0',
		port: 5173,
		strictPort: true,
		hmr: {
			host: 'vmmint22.local',
			protocol: 'wss',
		},
		watch: {
			usePolling: true,
			interval: 1000,
		},
	},
	resolve: {
		alias: {
			'@': path.resolve(__dirname, './resources/js'),
			'@components': path.resolve(__dirname, './resources/js/Components'),
			'@pages': path.resolve(__dirname, './resources/js/Pages'),
			'@layouts': path.resolve(__dirname, './resources/js/Layouts'),
			'@types': path.resolve(__dirname, './resources/js/types'),
		},
	},
	build: {
		manifest: true,
		outDir: 'public/build',
		rollupOptions: {
			output: {
				manualChunks: {
					vendor: ['react', 'react-dom'],
				},
			},
		},
	},
});