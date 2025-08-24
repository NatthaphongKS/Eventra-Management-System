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
    host: 'localhost',         // ใช้เครื่องตัวเอง (XAMPP) ไม่ต้อง 0.0.0.0
    port: 5173,
    strictPort: true,
    hmr: { host: 'localhost', port: 5173 }, // HMR ยิงกลับ localhost
  },
})
