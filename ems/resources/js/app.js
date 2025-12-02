import { createApp } from 'vue'
import '../css/app.css'
import axios from 'axios'  // <--- ใช้แบบมาตรฐาน (ชัวร์กว่า)
import App from './App.vue'
import ReplyForm from './pages/ReplyForm.vue'
import router from './router'

// Config Axios
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// ดึง CSRF Token จาก meta tag (สำคัญมากสำหรับ Laravel)
const token = document.querySelector('meta[name="csrf-token"]')?.content
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token
} else {
    console.error('CSRF token not found')
}

// Logic การ Mount App
if (document.getElementById('reply-app')) {
  createApp(ReplyForm).mount('#reply-app')
}

if (document.getElementById('app')) {
  const app = createApp(App)
  app.use(router)            // <--- บรรทัดสำคัญที่คุณเพิ่มมา
  app.mount('#app')
}