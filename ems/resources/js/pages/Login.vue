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
        <h2 class="pl-10 mt-16 text-4xl font-medium text-neutral-900 mb-5">Sign In</h2>
        
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
             <router-link to="/forgot-password" class="text-rose-300 text-lg underline font-medium hover:text-rose-500 transition-colors">
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

export default {
  data() {
    return {
      email: "",
      password: "",
      loading: false,
      message: "",
      error: "",
      errors: {},
    };
  },
  methods: {
    validateInputs() {
      const errs = {};
      if (!this.email?.trim()) {
        errs.email = ["Email is required"];
      } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(this.email.trim())) {
        errs.email = ["Invalid email"];
      }
      if (!this.password?.trim()) {
        errs.password = ["Password is required"];
      }
      this.errors = errs;
      return Object.keys(errs).length === 0;
    },
    async login() {
      this.message = "";
      this.error = "";
      this.errors = {};

      if (!this.validateInputs()) {
        return;
      }

      this.loading = true;

      try {
        const res = await axios.post('/logined', {
          email: this.email,
          password: this.password
        }, { baseURL: '' });

        this.message = res.data.message;

        if (res.data.redirect) {
          window.location.href = res.data.redirect;
        } else {
          this.$router.push('/'); 
        }
      } catch (err) {
        if (err.response) {
          if (err.response.status === 422) {
            this.errors = err.response.data.errors;
          } else if (err.response.data && err.response.data.message) {
            this.message = err.response.data.message;
          } else {
            this.message = "ไม่สามารถเข้าสู่ระบบได้ กรุณาลองอีกครั้ง";
          }
        } else {
          this.message = "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้";
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
