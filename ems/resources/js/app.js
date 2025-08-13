import { createApp } from 'vue';
import App from './App.vue';
import axios from 'axios'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content

createApp(App).mount('#app');
