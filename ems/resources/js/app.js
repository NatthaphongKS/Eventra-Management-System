import { createApp } from 'vue'
import '../css/app.css'
import axios from 'axios'
import App from './App.vue'
import ReplyForm from './pages/Reply_form.vue'


axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content
axios.defaults.withCredentials = true

if (document.getElementById('reply-app')) {
  createApp(ReplyForm).mount('#reply-app')   // สำหรับหน้า reply
} else if (document.getElementById('app')) {
  createApp(App).mount('#app')               // สำหรับหน้าอื่น
}

