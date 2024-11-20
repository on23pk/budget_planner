import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueDevTools from 'vite-plugin-vue-devtools';

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/api': { // Proxy für API-Endpunkte
        target: 'http://localhost/budget_planner/backend', // Backend-Server
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''), // Entfernt /api aus der URL
      },
    },
  },
});

