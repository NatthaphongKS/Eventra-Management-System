// import { createApp } from 'vue'
// import App from './App.vue'
// import router from './router'

// createApp(App).use(router).mount('#app')

import axios from 'axios'
import { createApp } from 'vue'
import App from './App.vue'

// 1. Import router ของคุณ
// (เช็ก Path ให้ถูกนะครับ บางทีอาจจะเป็น './router/index.js')
import router from './router' 

const app = createApp(App)

// 2. สั่งให้แอป Vue "ใช้" router (จุดที่มักจะลืม)
// บรรทัดนี้ต้องอยู่ก่อน app.mount()
app.use(router) 
axios.defaults.withCredentials = true // ถ้าใช้ cookie-based auth

// 3. Mount แอป
app.mount('#app')