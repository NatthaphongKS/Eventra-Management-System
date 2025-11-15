// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.js'], refresh: true }),
    vue(),
  ],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: {
      // ถ้าเข้าผ่าน 127.0.0.1 ให้ใช้ 127.0.0.1
      // ถ้าเข้าผ่านชื่อโดเมน/ไอพีเครื่อง ให้ใช้ค่านั้น
      host: 'localhost',
      port: 5173,
    },
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
        // ถ้า Laravel ใช้คุกกี้/เซสชัน ให้เปิด websocket cookie/headers ด้วย
        // ws: true,
      },
      // ถ้าใช้ Sanctum: ต้อง proxy ด้วย เพื่อให้คุกกี้ออกโดเมนเดียวกัน
      '/sanctum': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
