<!-- /**
 * ชื่อไฟล์: Login.vue
 * คำอธิบาย: หน้าเข้าสู่ระบบ (Sign In)
 * Input: email, password
 * Output: redirect ไปหน้าหลักหลัง login สำเร็จ
 * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
 * วันที่จัดทำ/แก้ไข: 2026-02-16
 */ -->

<template>
    <div
        class="min-h-screen flex items-center justify-end pr-[8vw] bg-[url('/images/Background.jpg')] bg-cover bg-center md:pr-[12vw] px-6 bg-red-700">
        <div class="rounded-[28px] bg-white shadow-lg p-8 md:p-10 w-[484px] h-[592px]">
            <div v-if="!forgotPassword" class="flex justify-center items-center gap-4 mb-8">
                <img :src="'/images/email/clicknext.jpeg'" alt="Eventra Logo"
                    class="w-20 h-20 object-cover rounded-2xl shadow-sm" loading="lazy">
                <span class="text-5xl font-medium text-red-700 tracking-tight">
                    Eventra
                </span>
            </div>

            <div class="left" :class="forgotPassword ? 'h-full' : ''">
                <!-- Login Form -->
                <template v-if="!forgotPassword">
                    <h2 class="pl-10 mt-16 text-4xl font-medium text-neutral-900 mb-4">Sign In</h2>
                    <form @submit.prevent="login">

                        <div class="mb-5">
                            <input type="text" v-model="email"
                                class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:text-2xl placeholder:font-medium focus:outline-none focus:ring-0"
                                placeholder="Email" />
                            <p v-if="errors.email" class="text-red-700 text-m mt-1 text[16px]">
                                {{ errors.email[0] }}
                            </p>
                        </div>

                        <div class="mb-2">
                            <input type="password" v-model="password"
                                :class="{ 'border-red-500': errors.password, 'border-red-700': !errors.password }"
                                class="w-[403px] h-[70px] border border-red-700 rounded-full p-3 px-5 placeholder:text-neutral-600 placeholder:text-2xl placeholder:font-medium focus:outline-none focus:ring-0"
                                placeholder="Password" />
                            <p v-if="errors.password" class="text-red-700 text-m mt-1 text[16px]">
                                {{ errors.password[0] }}
                            </p>
                        </div>

                        <p v-if="message" class="text-red-700 text-m mt-1 text[16px]">{{ message }}</p>

                        <div class="flex justify-center mt-4 mb-4">
                            <button type="button" @click="toggleForgotPassword"
                                class="text-red-200 text-lg underline font-regular hover:text-red-700 transition-colors">
                                forgot password
                            </button>
                        </div>

                        <div class="flex justify-center">
                            <button type="submit" :disabled="loading"
                                class="w-[181px] h-[57px] bg-red-700 text-white hover:bg-red-800 rounded-3xl p-3 font-medium text-2xl transition-all duration-300 shadow-md disabled:opacity-60 disabled:cursor-not-allowed">
                                {{ loading ? 'Signing in...' : 'Sign In' }}
                            </button>
                        </div>
                    </form>

                    <p v-if="error" class="text-center text-red-600 mt-4 text-lg font-medium">
                        {{ error }}
                    </p>
                </template>

                <!-- ฟอร์มลืมรหัสผ่าน -->
                <template v-else>
                    <div class="h-full flex flex-col items-center text-center">
                        <div class="w-full">
                            <h2 class="mt-3 text-[28px] font-semibold leading-tight text-neutral-800 text-center">Forgot your password?
                            </h2>
                            <p class="mt-8 text-[24px] leading-tight text-neutral-700 text-center">Please tell your admin
                                <br>
                                to reset your password
                            </p>
                        </div>

                        <div class="mt-auto w-full flex justify-center pb-2">
                            <button type="button" @click="toggleForgotPassword" aria-label="Back to sign in"
                                class="text-red-700 text-6xl leading-none hover:text-red-800 transition-colors">
                                ←
                            </button>
                        </div>
                    </div>
                </template>
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
            errors: {},
            forgotPassword: false,
        };
    },
    methods: {
        validateInputs() {
            const errs = {};
            const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

            if (!this.email?.trim()) {
                errs.email = ["Email is required"];
            } else if (!emailRegex.test(this.email.trim())) {
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
            this.errors = {};

            if (!this.validateInputs()) {
                return;
            }

            this.loading = true;

            try {
                const res = await axios.post('/login', {
                    email: this.email,
                    password: this.password
                }, { baseURL: '' });

                this.message = res.data.message;

                if (res.data.user) {
                    localStorage.setItem(
                        "userData",
                        JSON.stringify(res.data.user)
                    );
                }

                if (typeof res.data.redirect === 'string' &&
                    res.data.redirect.startsWith('/') &&
                    !res.data.redirect.startsWith('//')) {
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
        },
        toggleForgotPassword() {
            this.forgotPassword = !this.forgotPassword;
            this.message = "";
            this.error = "";
            this.errors = {};
            if (this.forgotPassword) {
                this.email = "";
                this.password = "";
            }
        }
    }
};
</script>
