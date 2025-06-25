import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  base: '/',
  plugins: [vue(), vueDevTools(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      // ✅ Proxy para las imágenes y API de Laravel
      '/storage': 'http://localhost:8000',
      '/api': 'http://localhost:8000',
    },
    allowedHosts: ['m-grey-receives-barrier.trycloudflare.com'], // 👈 Aquí
  },
})
