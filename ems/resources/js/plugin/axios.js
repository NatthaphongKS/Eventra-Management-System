import axios from 'axios'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// ถ้า FE วิ่งคนละพอร์ต/คนละโดเมนกับ API ต้องเปิดอันนี้
axios.defaults.withCredentials = true

const token = document.querySelector('meta[name="csrf-token"]')?.content
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token
}

export default axios
