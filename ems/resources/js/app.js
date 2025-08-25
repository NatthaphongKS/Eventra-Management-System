import { createApp } from "vue";
import "../css/app.css";
import App from "./App.vue";
import axios from "axios";
axios.defaults.withCredentials = true;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.headers.common["X-CSRF-TOKEN"] = document.querySelector(
    'meta[name="csrf-token"]'
)?.content;

createApp(App).mount("#app");

const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");
if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
} else {
    console.error('CSRF token not found in <meta name="csrf-token">');
}
