// resources/js/app.js
import { createApp } from 'vue'
import '../css/app.css'
import axios from './plugin/axios'
import App from './App.vue'
import ReplyForm from './pages/ReplyForm.vue'
import router from './router'


// ยิงผ่าน proxy ของ Vite

axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
// บรรทัดที่ตั้งค่า X-CSRF-TOKEN ด้วยตัวเองเอาออก เพราะมี axios.defaults.withCredentials = true แล้ว
// axios.defaults.headers.common['X-CSRF-TOKEN'] =
//   document.querySelector('meta[name="csrf-token"]')?.content
axios.defaults.withCredentials = true // ถ้าใช้ cookie-based auth

if (document.getElementById('reply-app')) {
 createApp(ReplyForm).mount('#reply-app')
}
if (document.getElementById('app')) {
 const app = createApp(App)
 app.use(router) // ← สำคัญมาก ก่อนหน้านี้ไม่มี
 app.mount('#app')
}
