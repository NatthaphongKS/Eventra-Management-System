<!-- /**
 * ชื่อไฟล์: ReplyForm.vue
 * คำอธิบาย: หน้าสำหรับตอบกลับคำเชิญเข้าร่วมกิจกรรม (Reply Form)
 * Input: ข้อมูลกิจกรรมจาก API /reply/{encryptURL}
 * Output: แบบฟอร์มสำหรับตอบกลับคำเชิญเข้าร่วมกิจกรรม
 * คนแก้ไข: Natthaphong Kongsinl
 * วันที่แก้ไข: 2026-02-27
 */ -->
<!-- pages/ReplyForm.vue -->
<template>
    <div class="">
        <div v-if="urlStatus">
            <div
                class="min-h-screen bg-red-50 py-10 px-4 flex flex-col items-center gap-5"
            >
                <section
                    class="w-full max-w-[640px] bg-white rounded-xl px-8 py-8 md:px-10"
                >
                    <h1
                        class="mb-4 text-red-700 text-[34px] font-semibold leading-tight"
                    >
                        ตอบรับคำเชิญเข้าร่วมกิจกรรม
                    </h1>
                    <p class="my-1.5 text-gray-800 text-[15px] font-semibold">
                        หัวข้อ: {{ title || "-" }}
                    </p>
                    <p class="my-1.5 text-gray-800 text-[15px] font-semibold">
                        <strong>วันที่:</strong> {{ formattedDate || "-" }}
                    </p>
                    <p class="my-1.5 text-gray-800 text-[15px] font-semibold">
                        <strong>เวลา:</strong> {{ formattedTime || "-" }}
                    </p>
                    <p class="my-1.5 text-gray-800 text-[15px] font-semibold">
                        <strong>สถานที่:</strong> {{ location || "-" }}
                    </p>
                    <p class="my-1.5 text-gray-800 text-[15px] font-semibold">
                        <strong>ชื่อ:</strong> {{ empName || "-" }}
                    </p>
                </section>

                <section
                    v-if="show"
                    class="w-full max-w-[640px] bg-white rounded-xl px-8 py-8 md:px-10"
                >
                    <form @submit.prevent="onSubmit">
                        <div class="mb-6">
                            <label
                                class="block font-semibold text-gray-900 mb-3 text-[15px]"
                                >เข้าร่วมหรือไม่</label
                            >
                            <div class="flex gap-10">
                                <label
                                    class="inline-flex items-center gap-2 font-semibold text-gray-800 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        value="accepted"
                                        v-model="form.attend"
                                        class="w-[18px] h-[18px] cursor-pointer"
                                    />
                                    เข้าร่วม
                                </label>
                                <label
                                    class="inline-flex items-center gap-2 font-semibold text-gray-800 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        value="denied"
                                        v-model="form.attend"
                                        class="w-[18px] h-[18px] cursor-pointer"
                                    />
                                    ไม่เข้าร่วม
                                </label>
                            </div>
                            <small
                                v-if="errors.attend"
                                class="text-red-600 text-[13px] mt-1.5 block"
                            >
                                {{ errors.attend }}
                            </small>
                        </div>

                        <div
                            class="mb-6 transition-opacity duration-200"
                            :class="{ 'opacity-60': form.attend !== 'denied' }"
                        >
                            <label
                                class="block font-semibold text-gray-900 mb-3 text-[15px]"
                                >หมายเหตุ (กรณีไม่เข้าร่วม)</label
                            >
                            <input
                                type="text"
                                v-model.trim="form.reason"
                                :disabled="form.attend !== 'denied'"
                                class="w-full border-0 border-b border-gray-400 py-2 outline-none text-[15px] bg-transparent transition-colors duration-200 focus:border-gray-800 focus:ring-0 disabled:cursor-not-allowed disabled:bg-transparent"
                            />
                            <small
                                v-if="errors.reason"
                                class="text-red-600 text-[13px] mt-1.5 block"
                            >
                                {{ errors.reason }}
                            </small>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button
                                type="submit"
                                class="bg-green-500 text-white font-semibold text-[15px] rounded-full py-2.5 px-8 cursor-pointer transition-colors duration-200 hover:bg-green-600 disabled:opacity-60 disabled:cursor-not-allowed"
                                :disabled="submitting"
                            >
                                {{ submitting ? "กำลังส่ง…" : "ส่งคำตอบ" }}
                            </button>
                        </div>
                    </form>
                </section>

                <section
                    v-else
                    class="w-full max-w-[640px] md:px-10 text-gray-800 font-medium"
                >
                    คุณได้ตอบคำถามแบบฟอร์มนี้แล้ว
                </section>
            </div>
        </div>

        <div v-else>
            <div
                class="min-h-screen bg-red-50 py-10 px-4 flex flex-col items-center gap-5"
            >
                <section
                    class="w-full max-w-[640px] bg-white rounded-xl px-8 py-8 md:px-10"
                >
                    <h1
                        class="mb-4 text-red-700 text-[28px] font-semibold leading-tight"
                    >
                        ตอบรับเข้าร่วมการประชุม
                    </h1>
                    <p class="my-1.5 text-gray-800 text-[15px]">
                        <strong
                            >ลิงก์แบบฟอร์มเชิญเข้าร่วมกิจกรรมไม่ถูกต้อง</strong
                        >
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "ReplyForm",
    data() {
        return {
            // ข้อมูลสำหรับอ้างอิงส่งกลับ API
            evnID: null,
            empID: null,

            // ข้อมูลรายละเอียดการประชุมและพนักงานที่ได้จาก API
            title: "",
            date: "",
            timeStart: "",
            timeEnd: "",
            location: "",
            empName: "",
            empEmail: "",
            empPhone: "",

            // สถานะของ URL และสถานะการตอบรับปัจจุบัน
            urlStatus: true,
            replyStatus: "",

            // ข้อมูลที่ผู้ใช้กรอกในแบบฟอร์ม
            form: {
                attend: "",
                reason: "",
            },

            // ข้อความแจ้งเตือนข้อผิดพลาดในฟอร์ม
            errors: { attend: "", reason: "" },

            // สถานะการโหลดข้อมูลและการส่งข้อมูล
            loading: false,
            submitting: false,
            errorMessage: "", // เก็บข้อความเมื่อเกิดข้อผิดพลาด
        };
    },

    created() {
        this.fetchFromApi();
    },

    computed: {
        /**
         * ตรวจสอบเงื่อนไขในการแสดงแบบฟอร์ม
         * จะแสดงฟอร์มเมื่อผู้ใช้ยังไม่ได้ตอบรับ หรือตอบปฏิเสธ
         */
        show() {
            const status = this.replyStatus;
            return status !== "accepted" && status !== "denied";
        },

        /**
         * จัดรูปแบบวันที่ให้อ่านง่าย (แสดงเป็นรูปแบบภาษาไทย)
         * ตัวอย่าง: 21 สิงหาคม 2569
         */
        formattedDate() {
            if (!this.date) return "";

            const dateObj = new Date(this.date);

            return new Intl.DateTimeFormat("th-TH", {
                day: "numeric",
                month: "long",
                year: "numeric",
                timeZone: "Asia/Bangkok",
            }).format(dateObj);
        },

        /**
         * จัดรูปแบบเวลาเริ่มและเวลาจบการประชุม
         * ตัวอย่าง: 13:00-16:00 น.
         */
        formattedTime() {
            // ฟังก์ชันช่วยสำหรับตัดเอาเฉพาะชั่วโมงและนาที (HH:mm)
            const formatHourMinute = (timeString) => {
                return timeString ? timeString.slice(0, 5) : "";
            };

            const startTime = formatHourMinute(this.timeStart);
            const endTime = formatHourMinute(this.timeEnd);

            if (startTime && endTime) {
                return `${startTime}-${endTime} น.`;
            }
            if (startTime) {
                return `${startTime} น.`;
            }
            return "";
        },
    },

    watch: {
        /**
         * เฝ้าดูการเปลี่ยนแปลงของฟิลด์ attend (การเลือกเข้าร่วม/ไม่เข้าร่วม)
         * หากเลือก "เข้าร่วม" ให้ล้างค่าเหตุผลทิ้ง
         */
        "form.attend"(newValue) {
            if (newValue === "accepted") {
                this.form.reason = "";
                this.errors.reason = "";
            }
            // หากมีการเลือก ให้ล้างข้อความแจ้งเตือนที่ไม่ได้เลือก
            if (newValue) {
                this.errors.attend = "";
            }
        },

        /**
         * เฝ้าดูการเปลี่ยนแปลงของฟิลด์ reason (เหตุผล)
         * หากเริ่มพิมพ์ ให้ล้างข้อความแจ้งเตือน
         */
        "form.reason"(newValue) {
            if (newValue) {
                this.errors.reason = "";
            }
        },
    },

    methods: {
        /**
         * ดึงข้อมูลการประชุมผ่าน API โดยใช้ Token ที่ได้จาก URL
         */
        async fetchFromApi() {
            try {
                this.loading = true;

                // แยก URL เพื่อดึง Token จากส่วนสุดท้ายของ path
                const pathSegments = window.location.pathname.split("/");
                const token = pathSegments[pathSegments.length - 1];

                // เรียก API เพื่อดึงข้อมูล
                const response = await axios.get(`/reply/${token}`, {
                    headers: { Accept: "application/json" },
                });

                const responseData = response.data;

                // ผูกข้อมูลที่ได้จาก API เข้ากับ State ของ Component
                this.urlStatus = responseData.success;
                this.evnID = responseData.event?.id;
                this.empID = responseData.employee?.id;
                this.replyStatus = responseData.connect?.con_answer || "";

                this.title = responseData.event?.evn_title || "";
                this.date = responseData.event?.evn_date || "";
                this.timeStart = responseData.event?.evn_timestart || "";
                this.timeEnd = responseData.event?.evn_timeend || "";
                this.location = responseData.event?.evn_location || "";

                // จัดรูปแบบชื่อพนักงาน
                const firstName = responseData.employee?.emp_firstname || "";
                const lastName = responseData.employee?.emp_lastname || "";
                this.empName = `${firstName} ${lastName}`.trim();

                this.empEmail = responseData.employee?.emp_email || "";
                this.empPhone = responseData.employee?.emp_phone || "";
            } catch (error) {
                // เก็บข้อความ Error กรณีดึงข้อมูลไม่สำเร็จ
                this.errorMessage =
                    error.response?.data?.message ??
                    error.message ??
                    "โหลดข้อมูลไม่สำเร็จ";
            } finally {
                this.loading = false;
            }
        },

        /**
         * ตรวจสอบความถูกต้องของข้อมูลก่อนส่งไปยัง API
         * @returns {boolean} true ถ้าข้อมูลถูกต้อง, false ถ้าข้อมูลไม่ถูกต้อง
         */
        validate() {
            // ตรวจสอบว่าได้เลือกการเข้าร่วมหรือไม่
            this.errors.attend = this.form.attend
                ? ""
                : "กรุณาเลือกว่าจะเข้าร่วมหรือไม่";

            // ตรวจสอบว่าได้ระบุเหตุผลหรือไม่ เมื่อเลือกไม่เข้าร่วม
            this.errors.reason =
                this.form.attend === "denied" && !this.form.reason
                    ? "Required Field"
                    : "";

            // ตรวจสอบว่ามีค่า Error ในออบเจ็กต์ errors หรือไม่
            const hasErrors = Object.values(this.errors).some(Boolean);
            return !hasErrors;
        },

        /**
         * ส่งข้อมูลคำตอบรับกลับไปยัง API (Endpoint /store)
         */
        async onSubmit() {
            if (!this.validate()) return;

            this.submitting = true;
            try {
                // จัดเตรียม Payload สำหรับส่งข้อมูล
                const payload = {
                    evnID: this.evnID,
                    empID: this.empID,
                    attend: this.form.attend,
                    reason: this.form.reason,
                };

                // ส่งข้อมูล
                await axios.post("/store", payload, {
                    headers: { Accept: "application/json" },
                });

                // รีเฟรชหน้าเว็บเมื่อบันทึกข้อมูลสำเร็จ เพื่อแสดงข้อความยืนยัน
                window.location.reload();
            } catch (error) {
                // แจ้งเตือนผู้ใช้เมื่อส่งข้อมูลไม่สำเร็จ
                alert("ส่งไม่สำเร็จ กรุณาลองใหม่อีกครั้งนะคะ");
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>
