import { createApp } from 'vue';
import '../css/app.css';
import App from './App.vue';
import router from './router'; // เพิ่มการเชื่อมต่อ Vue Router
import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content;

const app = createApp(App);
app.use(router); // ใช้ Vue Router
app.mount('#app');