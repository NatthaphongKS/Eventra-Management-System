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
        <h2 class="text-3xl font-semibold text-gray-900 mb-6">Sign In</h2>
        <form @submit.prevent="login">

          <div class="p-2">
            <label></label>
            <input
              type="text"
              v-model="email"

              class="w-full border rounded-3xl p-3 px-5 placeholder:text-gray-800 placeholder:font-semibold"
              placeholder="Email"
            />
            <!-- ส่วนแสดง error ไม่ใส่ email -->
            <p v-if="errors.email" class="text-red-700 text-m mt-1 text[16px]">
              {{ errors.email[0] }}
            </p>
          </div>

          <div class="p-2">
            <label></label>
            <input
              type="password"
              v-model="password"
              :class="{'border-red-500': errors.password, 'border-red-700': !errors.password}"
              class="w-full border rounded-3xl p-3 px-5 placeholder:text-gray-800 placeholder:font-semibold"
              placeholder="Password"
            />
            <!-- ส่วนแสดง error ไม่ใส่ password -->
            <p v-if="errors.password" class="text-red-700 text-m mt-1 text[16px]">
              {{ errors.password[0] }}
            </p>
          </div>
            <!-- ส่วนแสดง error ตรวจเช็คความถูกไหม email password กับ ดูว่า คนนี้ยังเข้าได้อยู่ไหมเป็น inactive รึเปล่า-->
          <p v-if="message" class="text-red-700 text-m mt-1 text[16px]">{{ message }}</p>

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

export default {
  data() {
    return {
      email: "",
      password: "",
      message: "", //ตัวแปรสำหรับเก็บ error
      errors: {} //ตัวแปรสำหรับเก็บ error ของ field password email
    };
  },
  methods: {
    async login() {
      // เคลียร์ค่า error เก่าก่อนส่ง request ใหม่
      this.message = "";
      this.errors = {};

      try {
        const res = await axios.post('/logined', {
          email: this.email,
          password: this.password
        }, { baseURL: '' }); // ใส่ Base URL ของ API

        this.message = res.data.message;

        if (res.data.redirect) {
          this.$router.push(res.data.redirect);
        }

        //ส่วนจัดการ handle error อื่นๆ
      } catch (err) {
        console.log('Error:', err.response);

        if (err.response) {
          // 2. เช็คว่าเป็น Error 422 (Validation Error) หรือไม่
          if (err.response.status === 422) {
            this.errors = err.response.data.errors;
          }
          // 3. กรณีเป็น Error อื่นๆ เช่น 401, 403, 404 ให้แสดง message รวม
          else if (err.response.data && err.response.data.message) {
            this.message = err.response.data.message;
          } else {
            this.message = "เกิดข้อผิดพลาดในการเชื่อมต่อ";
          }
        }
      }
    }
  }
};
</script>
