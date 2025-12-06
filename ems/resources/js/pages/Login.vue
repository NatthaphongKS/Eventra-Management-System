<template>
  <div class="min-h-screen flex items-center justify-end pr-[8vw] bg-[url('/images/email/ChatGPT_Image_20_.._2568_23_31_01.png')] bg-cover bg-center md:pr-[12vw] px-6 bg-red-700">
    <div class="rounded-[28px] bg-white shadow-lg p-8 md:p-10 w-[484px] h-[592px]">
      <div class="flex justify-center items-center gap-4 mb-8">
          <img 
            src="../../../public/images/email/clicknext.jpeg" 
            alt="Remote" 
            class="w-20 h-20 object-cover rounded-2xl shadow-sm" 
            loading="lazy"
          >
          <span class="text-5xl font-medium text-red-700 tracking-tight">
                Eventra
          </span>
        </div>
      <div class="left">
        <h2 class="mt-16 ml-10 text-4xl font-medium text-gray-900 mb-5">Sign In</h2>
        
        <form @submit.prevent="login">
          <div class="p-2 text-2xl">
            <input 
              type="email" 
              v-model="email" 
              class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:font-medium focus:outline-none focus:ring-0" 
              placeholder="Email" 
              required
            />
          </div>

          <div class="p-2 text-2xl">
            <input 
              type="password" 
              v-model="password" 
              class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:font-medium focus:outline-none focus:ring-0" 
              placeholder="Password" 
              required
            />
          </div>

          <div class="flex justify-center mt-12 mb-4">
             <router-link to="/forgot-password" class="text-red-200 text-lg underline font-regular hover:text-red-700 transition-colors">
               forgot password
             </router-link>
          </div>

          <div class="flex justify-center">
            <button 
              type="submit" 
              class="w-[181px] h-[57px] bg-red-700 text-white hover:bg-red-800 rounded-3xl p-3 font-medium text-2xl transition-all duration-300 shadow-md"
            >
              Sign In
            </button>
          </div>
        </form>

        <p v-if="error" class="text-center text-red-600 mt-4 text-lg font-medium">
          {{ error }}
        </p>

      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

// Setup CSRF Token (ป้องกัน Error 419)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}
axios.defaults.withCredentials = true;

export default {
  data() {
    return {
      email: "",
      password: "",
      error: "" // <--- สำคัญ! ต้องมีตัวแปรนี้ เพื่อให้ตรงกับ HTML ด้านบน
    };
  },
  methods: {
    async login() {
      this.error = ""; // ล้างข้อความเก่า
      
      try {
        // เช็ค URL ให้ชัวร์ (ปกติ Laravel ใช้ /login ถ้าคุณแก้ Route เป็น /logined ก็ใช้ตัวเดิม)
        const res = await axios.post('/logined', {
          email: this.email,
          password: this.password
        });

        if (res.data.redirect) {
          window.location.href = res.data.redirect;
        } else {
          // เผื่อไว้กรณีไม่มี redirect
          this.$router.push('/'); 
        }

      } catch (err) {
        console.error('Login Error:', err);
        // แสดงข้อความเมื่อ Login พลาด
        this.error = err.response?.data?.message || "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
      }
    }
  }
};
</script>