<!-- src/views/ReplyForm.vue -->
<template>
    <div class="page">
        <section class="card head">
            <h1>แบบฟอร์มเชิญเข้าร่วมกิจกรรม</h1>
            <p><strong>หัวข้อ:</strong> {{ title || "-" }}</p>
            <p><strong>วันที่:</strong> {{ formattedDateTime || "-" }}</p>
            <p><strong>สถานที่:</strong> {{ location || "-" }}</p>
        </section>

        <section v-if="show" class="card form">
            <form @submit.prevent="onSubmit">
                <div class="field">
                    <label>ชื่อ–นามสกุล</label>
                    <input type="text" :placeholder="empName || '—'" readonly />
                    <!-- ไม่ต้องมี error เพราะล็อกไม่ให้แก้ -->
                </div>

                <div class="field">
                    <label>Email</label>
                    <input
                        type="email"
                        :placeholder="empEmail || '—'"
                        readonly
                    />
                </div>

                <div class="field">
                    <label>เบอร์โทร</label>
                    <input type="tel" :placeholder="empPhone || '—'" readonly />
                </div>

                <div class="field">
                    <label>เข้าร่วมหรือไม่</label>
                    <div class="radio-row">
                        <label class="radio">
                            <input
                                type="radio"
                                value="accept"
                                v-model="form.attend"
                            />
                            เข้าร่วม
                        </label>
                        <label class="radio">
                            <input
                                type="radio"
                                value="denied"
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
                        :disabled="form.attend !== 'denied'"
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

        <section v-else class="label card">
            คุณได้ตอบคำถามแบบฟอร์มนี้แล้ว
        </section>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "ReplyForm",
    data() {
        return {
            // จาก URL
            evnID: null,
            empID: null,

            // จาก API (event + employee)
            title: "",
            date: "", // "2025-09-30T00:00:00.000000Z"
            timeStart: "", // "13:00:00"
            timeEnd: "", // "14:00:00"
            location: "",
            empName: "",
            empEmail: "",
            empPhone: "",

            replyStatus: "",

            // ฟอร์มที่ให้ผู้ใช้กรอกจริง ๆ
            form: {
                attend: "", // 'yes' | 'no'
                reason: "", // กรอกได้เฉพาะตอน 'no'
            },

            // state อื่น ๆ
            errors: { attend: "", reason: "" },
            loading: false,
            submitting: false,
            error: "",
        };
    },

    created() {
        const { evnID, empID } = this.resolveIds();
        this.fetchFromApi(evnID, empID);
    },

    computed: {
        show() {
            // แก้ไข: จะแสดงฟอร์มก็ต่อเมื่อ 'replyStatus' ไม่ใช่ 'accept' และไม่ใช่ 'denied'
            // (คือยังอยู่ในสถานะที่ต้องตอบ เช่น 'invalid' หรือเป็นค่าว่างระหว่างโหลด)
            const status = this.replyStatus;
            return status !== "accept" && status !== "denied";
        },
        // แสดง "30 กันยายน 2025 เวลา 13.00 - 14.00 น."
        formattedDateTime() {
            if (!this.date) return "";
            const d = new Date(this.date);
            const dateStr = new Intl.DateTimeFormat(
                "th-TH-u-nu-latn-ca-gregory",
                {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                    timeZone: "Asia/Bangkok",
                }
            ).format(d);

            const hhmmDot = (t) => (t ? t.slice(0, 5).replace(":", ".") : "");
            const s = hhmmDot(this.timeStart);
            const e = hhmmDot(this.timeEnd);

            if (s && e) return `${dateStr} เวลา ${s} - ${e} น.`;
            if (s) return `${dateStr} เวลา ${s} น.`;
            return dateStr;
        },
    },

    watch: {
        // ถ้าเลือก "เข้าร่วม" ให้ล้างเหตุผลทันที
        "form.attend"(val) {
            if (val === "accept") this.form.reason = "";
        },
    },

    methods: {
        // รองรับทั้ง /reply/1/2 และกรณีมี path นำหน้า
        resolveIds() {
            const m = window.location.pathname.match(
                /\/reply\/(\d+)\/(\d+)(?:\/|$)/
            );
            if (!m) throw new Error("ไม่พบ evnID/empID ใน URL");
            return { evnID: m[1], empID: m[2] }; // d เล็กทั้งคู่
        },

        async fetchFromApi(evnID, empID) {
            try {
                console.log("Fetching data for evnID:", evnID, "empID:", empID),
                    (this.loading = true);
                const { data } = await axios.get(
                    `/reply/${evnID}/${empID}`,
                    {
                        headers: { Accept: "application/json" },
                    }
                );

                // เก็บ id ไว้ใช้ตอนส่ง
                this.evnID = evnID;
                this.empID = empID;
                this.replyStatus = data.connect.con_answer;
                console.log("data.connect:", data.connect.con_answer);

                // event
                this.title = data.event?.evn_title || "";
                this.date = data.event?.evn_date || "";
                this.timeStart = data.event?.evn_timestart || "";
                this.timeEnd = data.event?.evn_timeend || "";
                this.location = data.event?.evn_location || "";

                // employee
                this.empName = `${data.employee?.emp_firstname || ""} ${
                    data.employee?.emp_lastname || ""
                }`.trim();
                this.empEmail = data.employee?.emp_email || "";
                this.empPhone = data.employee?.emp_phone || "";

                this.conne;
            } catch (e) {
                this.error =
                    e.response?.data?.message ??
                    e.message ??
                    "โหลดข้อมูลไม่สำเร็จ";
            } finally {
                this.loading = false;
            }
        },

        validate() {
            this.errors.attend = this.form.attend
                ? ""
                : "กรุณาเลือกว่าจะเข้าร่วมหรือไม่";
            this.errors.reason =
                this.form.attend === "denied" && !this.form.reason
                    ? "กรุณาระบุเหตุผลสั้น ๆ"
                    : "";
            return !Object.values(this.errors).some(Boolean);
        },

        async onSubmit() {
            if (!this.validate()) return;
            this.submitting = true;
            try {
                const payload = {
                    evnID: this.evnID,
                    empID: this.empID,
                    // คำตอบฟอร์มจริง
                    attend: this.form.attend,
                    reason: this.form.reason,
                };
                console.log("payload:", payload);
                console.log(
                    "evnID:",
                    this.evnID,
                    "empID:",
                    this.empID,
                    this.form.attend,
                    this.form.reason
                );
                console.log("POST /store", payload); // ดูใน Console ให้มีค่าจริง
                await axios.post("/store", payload, {
                    headers: { Accept: "application/json" },
                });
                //alert('บันทึกคำตอบเรียบร้อย ขอบคุณค่ะ')
                //รีเฟรชหน้าใหม่
                window.location.reload();

                // รีเซ็ตเฉพาะส่วนที่ผู้ใช้กรอก
                this.form = { attend: "", reason: "" };
            } catch (e) {
                console.error(e);
                alert("ส่งไม่สำเร็จ กรุณาลองใหม่อีกครั้งนะคะ");
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>

<style scoped>
/* โทนอ่อนแบบภาพตัวอย่าง */
.page {
    min-height: 100vh;
    background: #fdeceb;
    /* ชมพูอ่อน */
    padding: 32px 16px 64px;
    /* display: grid; */
    gap: 24px;
    place-items: start center;
}

.card {
    margin-top: 10px;
    width: min(820px, 94vw);
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.06), 0 1px 0 rgba(0, 0, 0, 0.04) inset;
    padding: 20px 24px;
}

.head h1 {
    margin: 0 0 8px;
    color: #e21c23;
    /* แดงหัวเรื่อง */
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

/* input:focus,
textarea:focus {
    border-bottom-color: #7ab97a;
} */

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
    accent-color: #bd3017;
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
