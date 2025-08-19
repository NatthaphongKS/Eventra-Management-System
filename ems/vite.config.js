

import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'


export default defineConfig({
  plugins: [
    vue(),
    laravel({

      input: ['resources/js/app.js'],   // ถ้ามี css แยกก็ใส่เพิ่ม

      refresh: true,
    }),
  ],

})

