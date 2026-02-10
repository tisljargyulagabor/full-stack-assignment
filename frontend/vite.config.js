import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        https: false, // Helyes: a Caddy intézi a HTTPS-t
        hmr: {
            // JAVÍTVA: Az a domain kell ide, amit a böngészőbe írsz!
            host: 'uccproject.localhost',
            protocol: 'wss',
        },
        proxy: {
            '/api': {
                // A docker-compose szervizneve kell ide!
                target: 'http://laravel_app:8000',
                changeOrigin: true,
                secure: false,
            }
        }
    }
})