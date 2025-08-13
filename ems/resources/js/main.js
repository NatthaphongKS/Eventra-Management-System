import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')

import axios from 'axios'
axios.defaults.withCredentials = true // ถ้าใช้ cookie-based auth
