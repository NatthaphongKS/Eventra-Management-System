<template>
  <div class="min-h-screen flex items-center justify-end pr-[8vw]  bg-[url('/images/email/ChatGPT_Image_20_.._2568_23_31_01.png')] bg-cover bg-center md:pr-[12vw] px-6 bg-red-700">
    <div class="rounded-[28px] bg-white shadow-lg p-8 md:p-10 w-[484px] h-[592px]">
      <div class="text-center text-5xl font-semibold mb-6 text-red-700">Eventra</div>


    <div class="left">
      <h2 class="mt-16 text-4xl font-semibold text-gray-900 mb-5">Sign In</h2>
      <form @submit.prevent="login">
        <div class="p-2 text-2xl">
          <label></label>
          <input type="email" v-model="email" class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:font-semibold focus:outline-none focus:ring-0" placeholder="Email" />

        </div>
        <div class="p-2 text-2xl">
          <label></label>
          <input type="password" v-model="password" class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:font-semibold focus:outline-none focus:ring-0" placeholder="Password" />
        </div>
        <div class="flex justify-center mt-16">
           <a href="#" class="text-rose-200 text-sm underline text-base font-medium">forgot password</a>
        </div>
        <div class="flex justify-center">
          <button type="submit" class="w-[181px] h-[57px] bg-red-700 text-white hover:bg-red-800 rounded-3xl p-3 mt-4 font-semibold text-2xl ">Sign In</button>
        </div>
      </form>
      <p v-if="error">{{ error }}</p>
    </div>
    </div>
  </div>
  </template>

<script>
import axios from "axios";

axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]')?.content
axios.defaults.withCredentials = true

export default {
  data() {
    return {
      email: "",
      password: "",
      message: ""
    };
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('/logined', {
          email: this.email,
          password: this.password
        }, { baseURL: '' });

        this.message = res.data.message;

        // ถ้า login ผ่าน → ไปหน้า employee
        if (res.data.redirect) {
          this.$router.push(res.data.redirect);
        }

      } catch (err) {
        console.log('data', err.response?.data)
        this.message = err.response?.data?.message || "เกิดข้อผิดพลาด";
      }
    }
  }
};
</script>