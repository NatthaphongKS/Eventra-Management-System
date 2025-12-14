// resources/js/plugins/axios.js
import axios from 'axios'

// axios.defaults.baseURL = '/api'             // proxy is handled in vite.config.js if needed
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true       // keep cookies for session auth

const token = document.querySelector('meta[name="csrf-token"]')?.content
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token
}

export default axios
