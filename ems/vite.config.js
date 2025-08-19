// import { defineConfig } from 'vite';
// import vue from '@vitejs/plugin-vue';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//   plugins: [
//     laravel({
//       input: ['resources/js/app.js'],
//       refresh: true,
//     }),
//     vue(),
//   ],
// });

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    vue(),
    laravel({
      input: ['resources/js/app.js', 'resources/css/app.css'],
      refresh: true,
    }),
  ],
  server: {
    host: true,         // ให้เข้าถึงจากภายนอกคอนเทนเนอร์
    port: 5173,
    hmr: {
      host: 'localhost' // ให้ HMR ชี้กลับมาที่เครื่องเรา
    }
  }
})
