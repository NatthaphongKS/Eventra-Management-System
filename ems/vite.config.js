// --------------------------------------------------------------------
// server
// --------------------------------------------------------------------

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({ input: ["resources/js/app.js"], refresh: true }),
        vue(),
    ],
    server: {
        host: "0.0.0.0", // ให้ container เปิดพอร์ตออกมาได้
        port: 7222,
        strictPort: true,
        hmr: { host: "10.80.7.17", port: 7222 }, // ให้เบราว์เซอร์ยิงกลับ localhost
        proxy: {
            "/api": {
                target: "http://dekdee2.informatics.buu.ac.th:7111",
                changeOrigin: true,
                secure: false,
            },
            "/sanctum": {
                target: "http://dekdee2.informatics.buu.ac.th:7111",
                changeOrigin: true,
                secure: false,
            },
        },
    },
});

