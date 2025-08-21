<!-- src/views/ReplyForm.vue -->
<template>
    <div class="page">
        <!-- หัวการ์ด -->
        <section class="card head">
            <h1>ตอบรับเข้าร่วมการประชุม</h1>
            <p><strong>หัวข้อ:</strong> ประชุมประจำปี</p>
            <p><strong>วันที่:</strong> 10 กันยายน 2025 เวลา 13:00–16:00</p>
            <p><strong>สถานที่:</strong> ห้องประชุม A, อาคารกลาง</p>
        </section>

        <!-- ฟอร์ม -->
        <section class="card form">
            <form @submit.prevent="onSubmit">
                <div class="field">
                    <label>ชื่อ–นามสกุล</label>
                    <input
                        v-model.trim="form.fullname"
                        type="text"
                        placeholder="นาย ประชุทร์ อินทร์โสธา"
                    />
                    <small v-if="errors.fullname" class="error">{{
                        errors.fullname
                    }}</small>
                </div>

                <div class="field">
                    <label>Email</label>
                    <input
                        v-model.trim="form.email"
                        type="email"
                        placeholder="payup@gmail.com"
                    />
                    <small v-if="errors.email" class="error">{{
                        errors.email
                    }}</small>
                </div>

                <div class="field">
                    <label>เบอร์โทร</label>
                    <input
                        v-model.trim="form.phone"
                        type="tel"
                        placeholder="0918254678"
                    />
                    <small v-if="errors.phone" class="error">{{
                        errors.phone
                    }}</small>
                </div>

                <div class="field">
                    <label>เข้าร่วมหรือไม่</label>
                    <div class="radio-row">
                        <label class="radio">
                            <input
                                type="radio"
                                value="yes"
                                v-model="form.attend"
                            />
                            เข้าร่วม
                        </label>
                        <label class="radio">
                            <input
                                type="radio"
                                value="no"
                                v-model="form.attend"
                            />
                            ไม่เข้าร่วม
                        </label>
                    </div>
                    <small v-if="errors.attend" class="error">{{
                        errors.attend
                    }}</small>
                </div>

                <div class="field" :class="{ disabled: form.attend !== 'no' }">
                    <label>หมายเหตุ (กรณีไม่เข้าร่วม)</label>
                    <textarea
                        v-model.trim="form.reason"
                        :disabled="form.attend !== 'no'"
                        rows="3"
                        placeholder="ระบุเหตุผลสั้น ๆ ค่ะ"
                    />
                    <small v-if="errors.reason" class="error">{{
                        errors.reason
                    }}</small>
                </div>

                <div class="actions">
                    <button
                        type="submit"
                        class="primary"
                        :disabled="submitting"
                    >
                        {{ submitting ? "กำลังส่ง…" : "ส่งคำตอบ" }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();

onMounted(() => {
    // ตั้ง title จาก meta ถ้ามี
    const title = route.meta?.title || "ตอบรับเข้าร่วมการประชุม";
    document.title = title;
});

const form = reactive({
    fullname: "",
    email: "",
    phone: "",
    attend: "", // 'yes' | 'no'
    reason: "",
});

const errors = reactive({
    fullname: "",
    email: "",
    phone: "",
    attend: "",
    reason: "",
});

const submitting = ref(false);

function validate() {
    errors.fullname = form.fullname ? "" : "กรุณากรอกชื่อ–นามสกุล";
    errors.email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
        ? ""
        : "อีเมลไม่ถูกต้อง";
    errors.phone = form.phone ? "" : "กรุณากรอกเบอร์โทร";
    errors.attend = form.attend ? "" : "กรุณาเลือกว่าจะเข้าร่วมหรือไม่";
    errors.reason =
        form.attend === "no" && !form.reason ? "กรุณาระบุเหตุผลสั้น ๆ" : "";
    return !Object.values(errors).some(Boolean);
}

async function onSubmit() {
    if (!validate()) return;
    submitting.value = true;
    try {
        // ตัวอย่างเรียก API ไป Laravel (ปรับ URL ให้ตรงโปรเจค)
        await axios.post("/api/replies", {
            fullname: form.fullname,
            email: form.email,
            phone: form.phone,
            attend: form.attend === "yes",
            reason: form.attend === "no" ? form.reason : null,
        });
        alert("บันทึกคำตอบเรียบร้อย ขอบคุณค่ะ");
        // เคลียร์ฟอร์ม
        form.fullname = "";
        form.email = "";
        form.phone = "";
        form.attend = "";
        form.reason = "";
    } catch (e) {
        console.error(e);
        alert("ส่งไม่สำเร็จ กรุณาลองใหม่อีกครั้งนะคะ");
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
/* โทนอ่อนแบบภาพตัวอย่าง */
.page {
    min-height: 100vh;
    background: #fdeceb; /* ชมพูอ่อน */
    padding: 32px 16px 64px;
    display: grid;
    gap: 24px;
    place-items: start center;
}
.card {
    width: min(820px, 94vw);
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.06), 0 1px 0 rgba(0, 0, 0, 0.04) inset;
    padding: 20px 24px;
}
.head h1 {
    margin: 0 0 8px;
    color: #e21c23; /* แดงหัวเรื่อง */
    font-size: 32px;
    font-weight: 800;
}
.head p {
    margin: 4px 0;
    color: #333;
}

.form {
    margin-top: 8px;
}
.field {
    margin-bottom: 18px;
}
.field.disabled {
    opacity: 0.75;
}

label {
    display: block;
    font-weight: 700;
    margin-bottom: 8px;
}

input,
textarea {
    width: 100%;
    border: none;
    border-bottom: 2px solid #ddd;
    padding: 10px 2px;
    outline: none;
    font-size: 15px;
    border-radius: 0;
    background: transparent;
}
input:focus,
textarea:focus {
    border-bottom-color: #7ab97a;
}

.radio-row {
    display: flex;
    gap: 28px;
    padding: 6px 0 0;
}
.radio {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #444;
}
.radio input[type="radio"] {
    width: 18px;
    height: 18px;
    accent-color: #7ab97a;
}

.error {
    color: #d12c2c;
    font-size: 12.5px;
    margin-top: 6px;
    display: block;
}

.actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}
button.primary {
    background: #4caf50;
    color: #fff;
    font-weight: 700;
    border: none;
    border-radius: 999px;
    padding: 12px 22px;
    cursor: pointer;
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.08);
}
button.primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
