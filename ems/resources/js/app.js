import { createApp } from 'vue';
import '../css/app.css'
import App from './App.vue';
import axios from 'axios'
import router from './router'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content

createApp(App).mount('#app');
