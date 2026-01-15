// vite.config.js
// --------------------------------------------------------------------
// Local
// --------------------------------------------------------------------

import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/js/app.js'], refresh: true }),
    vue(),
  ],
  server: {
    host: '0.0.0.0',          // ให้ container เปิดพอร์ตออกมาได้
    port: 5173,
    strictPort: true,
    hmr: { host: 'localhost', port: 5173 }, // ให้เบราว์เซอร์ยิงกลับ localhost
  },
})
