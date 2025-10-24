// resources/js/plugins/axios.js
import axios from 'axios'

axios.defaults.baseURL = '/api'             // ใช้ proxy จาก vite.config.js
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true       // เผื่อใช้ cookie auth หรือ Sanctum

export default axios
