<template>
    <div>
      <h2>Login</h2>
      <form @submit.prevent="login">
        <div>
          <label>Email:</label>
          <input type="email" v-model="email" />
        </div>
        <div>
          <label>Password:</label>
          <input type="password" v-model="password" />
        </div>
        <button type="submit">Login</button>
      </form>
      <p v-if="error">{{ error }}</p>
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
