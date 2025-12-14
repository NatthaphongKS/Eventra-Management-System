// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.js','resources/js/main.js'], refresh: true }),
    vue(),
  ],
  server: {
  host: '0.0.0.0',
  port: 5173,
  strictPort: true,

  hmr: {
    host: 'localhost',
    port: 5173,
  },

  // ✅ สำคัญมากสำหรับ Mac + Docker (ให้จับการแก้ไฟล์แบบเรียลไทม์)
  watch: {
    usePolling: true,
    interval: 100,
  },

  // ✅ ถ้าจะ proxy /api ไป Laravel ใน Docker
  proxy: {
    '/api': {
      target: 'http://ems_app_dev', // หรือ 'http://app' ถ้าชื่อ service คือ app
      changeOrigin: true,
      secure: false,
    },
    '/sanctum': {
      target: 'http://ems_app_dev',
      changeOrigin: true,
      secure: false,
    },
  },
}
})
