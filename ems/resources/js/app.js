import { createApp } from 'vue'
import '../css/app.css'
import axios from 'axios'
import App from './App.vue'
import ReplyForm from './pages/ReplyForm.vue'
import router from './router'


axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content
axios.defaults.withCredentials = true

if (document.getElementById('reply-app')) {
  createApp(ReplyForm).mount('#reply-app')   // สำหรับหน้า reply
} else if (document.getElementById('app')) {
  createApp(App).mount('#app')               // สำหรับหน้าอื่น
}

