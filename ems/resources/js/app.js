import { createApp } from 'vue';
import '../css/app.css'
import App from './App.vue';
import axios from 'axios'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content

// [เพิ่ม] import router อย่างเดียว
import router from './router'
