<template>
  <div class="min-h-screen flex items-center justify-end pr-[8vw] md:pr-[12vw] px-6 bg-red-700">
    <div class="rounded-[28px] bg-white shadow-lg p-8 md:p-10 w-[484px] h-[592px]">
      <div class="text-center text-3xl font-semibold mb-6 text-red-700">Eventra</div>


    <div class="left">
      <h2 class="text-3xl font-semibold text-gray-900 mb-6">Sign In</h2>
      <form @submit.prevent="login">
        <div class="p-2">
          <label></label>
          <input type="email" v-model="email" class=" w-full border border-red-700 rounded-3xl p-3 px-5 placeholder:text-gray-800 placeholder:font-semibold" placeholder="Email" />

        </div>
        <div class="p-2">
          <label></label>
          <input type="password" v-model="password" class=" w-full border border-red-700 rounded-3xl p-3 px-5 placeholder:text-gray-800 placeholder:font-semibold" placeholder="Password" />
        </div>
        <div class="flex justify-center">
          <button type="submit" class="w-2/4 bg-red-700 text-white rounded-3xl p-3 mt-4 font-semibold">Login</button>
        </div>
      </form>
      <p v-if="message">{{ message }}</p>
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
