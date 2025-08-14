<template>
    <div>
        <button @click="$router.back()" class="btn-back">← กลับ</button>

        <h2>เพิ่มพนักงานใหม่</h2>

        <form @submit.prevent="submitEmployee">
            <div>
                <label>id พนักงาน : </label>
                <input v-model.trim="form.emp_id" required />
            </div>
            <div>
                <label>คำนำหน้า : </label>
                <select id="emp_prefix" v-model="form.emp_prefix" required>
                    <option disabled value="">-- เลือกคำนำหน้า --</option>
                    <option v-for="p in prefixes" :key="p" :value="p">
                        {{ p }}
                    </option>
                </select>
            </div>

            <div>
                <label>ชื่อ</label>
                <input v-model.trim="form.emp_firstname" required />
            </div>

            <div>
                <label>นามสกุล</label>
                <input v-model.trim="form.emp_lastname" required />
            </div>

            <div>
                <label>Email</label>
                <input
                    type="email"
                    v-model.trim="form.emp_email"
                    required
                    autocomplete="email"
                />
            </div>

            <div>
                <label>Phone</label>
                <input
                    v-model="form.emp_phone"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    @input="
                        form.emp_phone = form.emp_phone.replace(/[^0-9]/g, '')
                    "
                />
            </div>

            <div>
                <label>ตำแหน่ง</label>
                <select v-model.number="form.emp_position_id" required>
                    <option disabled value="">-- เลือกตำแหน่ง --</option>
                    <option
                        v-for="p in positions"
                        :value="p.id"
                        :key="'p-' + p.id"
                    >
                        {{ p.pst_name }}
                    </option>
                </select>
            </div>

            <div>
                <label>แผนก</label>
                <select v-model.number="form.emp_department_id" required>
                    <option disabled value="">-- เลือกแผนก --</option>
                    <option
                        v-for="d in departments"
                        :value="d.id"
                        :key="'d-' + d.id"
                    >
                        {{ d.dpm_name }}
                    </option>
                </select>
            </div>

            <div>
                <label>ทีม</label>
                <select v-model.number="form.emp_team_id">
                    <option disabled value="">-- เลือกทีม --</option>
                    <option v-for="t in teams" :value="t.id" :key="'t-' + t.id">
                        {{ t.tm_name }}
                    </option>
                </select>
            </div>

            <div>
                <label>Password</label>
                <input
                    type="password"
                    v-model="form.emp_password"
                    required
                    autocomplete="new-password"
                />
            </div>
            <div>
                <select id="emp_status" v-model="form.emp_status" required>
                    <option disabled value="">-- เลือกการอนุญาติ --</option>
                    <option v-for="p in status" :key="p" :value="p">
                        {{ p }}
                    </option>
                </select>
            </div>

            <button type="submit" :disabled="loading">
                {{ loading ? "กำลังบันทึก..." : "บันทึก" }}
            </button>
        </form>

        <p v-if="message">{{ message }}</p>
    </div>
</template>

<script>
import axios from "axios";
axios.defaults.baseURL = "/api";

// ตั้งค่าพื้นฐานให้ axios (ถ้าโปรเจ็กต์อยู่ root ของโดเมน ใช้แบบนี้โอเค)

axios.defaults.headers.common["Accept"] = "application/json";

export default {
    data() {
        return {
            prefixes: ["นาย", "นาง", "นางสาว"],
            status: ["enabled", "disabled"],
            form: {
                emp_id: "",
                emp_prefix: "นาย",
                emp_firstname: "",
                emp_lastname: "",
                emp_email: "",
                emp_phone: "",
                emp_position_id: "",
                emp_department_id: "",
                emp_team_id: "",
                emp_password: "", // ← ใช้ชื่อนี้ให้ตรงกับ Laravel
                emp_status: "",
            },
            positions: [],
            departments: [],
            teams: [],
            message: "",
            loading: false,
        };
    },
    async created() {
        try {
            const res = await axios.get("/meta");
            this.positions = res.data.positions || [];
            this.departments = res.data.departments || [];
            this.teams = res.data.teams || [];
        } catch (err) {
            this.message =
                err.response?.data?.message || "โหลดข้อมูลอ้างอิงไม่สำเร็จ";
        }
    },
    methods: {
        async submitEmployee() {
            this.loading = true;
            this.message = "";
            try {
                // แคสต์ค่า id เป็นตัวเลข เผื่อกรณีไม่ได้ใช้ v-model.number
                const payload = {
                    ...this.form,
                    emp_position_id: Number(this.form.emp_position_id) || null,
                    emp_department_id:
                        Number(this.form.emp_department_id) || null,
                    emp_team_id: Number(this.form.emp_team_id) || null,
                };

                const res = await axios.post("/save-employee", payload);
                this.message = res.data.message || "บันทึกสำเร็จ";

                // เคลียร์ฟอร์ม
                this.form = {
                    emp_id: " ",
                    emp_prefix: "",
                    emp_firstname: "",
                    emp_lastname: "",
                    emp_email: "",
                    emp_phone: "",
                    emp_position_id: "",
                    emp_department_id: "",
                    emp_team_id: "",
                    emp_password: "",
                    emp_status: "",
                };
            } catch (err) {
                const data = err.response?.data;
                this.message = data?.message || "เกิดข้อผิดพลาด";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
