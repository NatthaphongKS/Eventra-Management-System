<template>
    <h1>สร้างกิจกรรม</h1>
    <div style="margin: 20px">
        <label>Event Title</label>
        <input
            v-model.trim="form.event_title"
            type="text"
            placeholder="ชื่อกิจกรรม"
            required
        />
    </div>

    <div style="margin: 20px">
        <label>Event Details</label>
        <textarea v-model.trim="form.event_description" required></textarea>
    </div>
    <div style="margin: 20px">
        <label>Category</label>
        <select v-model="form.event_category" required>
            <option disabled value="">-- Select Category --</option>
            <option
                v-for="category in categories"
                :key="category"
                :value="category"
            >
                {{ category.cat_name }}
            </option>
        </select>
    </div>

    <div style="margin: 20px">
        <label>Date</label>
        <input v-model="form.event_date" type="date" required />
    </div>
    <div style="margin: 20px">
        <label>Time</label>
        <div style="display: flex; gap: 12px; align-items: center">
            <input v-model="form.event_timestart" type="time" required />
            <span>:</span>
            <input v-model="form.event_timeend" type="time" required />
            <span style="opacity: 0.7">→ {{ durationLabel }}</span>
        </div>
    </div>

    <div style="margin: 20px">
        <label>Duration</label>
        <input
            :value="durationLabel"
            type="text"
            readonly
            placeholder="Auto fill (minutes)"
            title="คำนวณอัตโนมัติจากเวลาเริ่ม-สิ้นสุด"
        />
    </div>

    <div style="margin: 20px">
        <label>Location</label>
        <input v-model.trim="form.event_location" type="text" required />
    </div>
    <div style="margin: 20px">
        <label>Upload attachments</label>
        <div
            class="dropzone"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
            :class="{ dragging }"
        >
            <p>Choose a file or drag & drop it here</p>
            <p class="muted">pdf, txt, docx, jpeg, xlsx – Up to 50MB</p>
            <button type="button" @click="pickFiles">Browse files</button>
            <input
                ref="fileInput"
                type="file"
                multiple
                class="hidden-file"
                accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                @change="onPick"
            />
        </div>
    </div>

    <!-- รายการไฟล์ที่เลือก -->
    <ul v-if="files.length" class="file-list">
        <li v-for="(f, i) in files" :key="i">
            {{ f.name }} ({{ prettySize(f.size) }})
            <button type="button" @click="removeFile(i)">✕</button>
        </li>
    </ul>

    <button :disabled="loading" @click="submitForm">
        {{ loading ? "กำลังบันทึก..." : "บันทึก Event" }}
    </button>
    <p v-if="message">{{ message }}</p>
</template>

<script>
import axios from "axios";
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

const MAX_FILE_MB = 50;
const ALLOW_TYPES = [
    "application/pdf",
    "text/plain",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "image/jpeg",
    "image/png",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "application/vnd.ms-excel",
];

export default {
    data() {
        return {
            form: {
                event_title: "",
                event_description: "",
                event_category_id: "",
                event_date: "",
                event_timestart: "",
                event_timeend: "",
                event_duration: 0,
                event_location: "",
            },
            categories: [],
            files: [],
            dragging: false,
            loading: false,
            message: "",
        };
    },
    async created() {
        try {
            const res = await axios.get("/event-info");
            this.categories = res.data.categories || [];
        } catch (err) {
            this.message =
                err.response?.data?.message || "โหลดข้อมูลอ้างอิงไม่สำเร็จ";
        }
    },
    computed: {
        // แสดงเป็นชม.อ่านง่าย เช่น "1h 30m"
        durationLabel() {
            const m = this.form.event_duration || 0;
            const h = Math.floor(m / 60);
            const mm = m % 60;
            if (!m) return "Auto fill";
            if (h && mm) return `${h}h ${mm}m`;
            if (h) return `${h}h`;
            return `${mm}m`;
        },
    },

    watch: {
        "form.event_timestart": "updateDuration",
        "form.event_timeend": "updateDuration",
    },

    methods: {
        // ===== Duration auto fill =====
        updateDuration() {
            const toMin = (t) => {
                if (!t) return null;
                const [h, m] = t.split(":").map(Number);
                return h * 60 + m;
            };
            const s = toMin(this.form.event_timestart);
            const e = toMin(this.form.event_timeend);
            if (s == null || e == null) {
                this.form.event_duration = 0;
                return;
            }
            this.form.event_duration = e >= s ? e - s : 1440 - s + e;
        },

        // ===== Upload helpers =====
        pickFiles() {
            this.$refs.fileInput.click();
        },
        onPick(e) {
            this.addFiles([...e.target.files]);
            e.target.value = ""; // reset input
        },
        onDrop(e) {
            this.dragging = false;
            this.addFiles([...e.dataTransfer.files]);
        },
        addFiles(list) {
            const errs = [];
            list.forEach((f) => {
                const tooBig = f.size > MAX_FILE_MB * 1024 * 1024;
                const badType = !ALLOW_TYPES.includes(f.type);
                if (tooBig) errs.push(`${f.name}: ไฟล์เกิน ${MAX_FILE_MB}MB`);
                else if (badType) errs.push(`${f.name}: ประเภทไฟล์ไม่รองรับ`);
                else this.files.push(f);
            });
            if (errs.length) alert(errs.join("\n"));
        },
        removeFile(i) {
            this.files.splice(i, 1);
        },
        prettySize(bytes) {
            const mb = bytes / (1024 * 1024);
            return mb >= 1
                ? `${mb.toFixed(2)} MB`
                : `${(bytes / 1024).toFixed(0)} KB`;
        },

        // ===== Submit =====
        async submitForm() {
            try {
                this.loading = true;
                this.message = "";

                // สร้าง FormData สำหรับ multipart
                const fd = new FormData();
                for (const [k, v] of Object.entries(this.form))
                    fd.append(k, v ?? "");
                // แนบไฟล์ (รองรับหลายไฟล์)
                this.files.forEach((f, i) =>
                    fd.append("attachments[]", f, f.name)
                );

                const res = await axios.post("/events", fd, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                this.message = res.data.message || "บันทึกสำเร็จ";
                // reset
                this.files = [];
                // ...รีเซ็ตฟอร์มถ้าต้องการ
            } catch (err) {
                this.message = err.response?.data?.message || "เกิดข้อผิดพลาด";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.dropzone {
    border: 2px dashed #e7a2a2;
    border-radius: 14px;
    padding: 24px;
    text-align: center;
    background: #fff6f6;
}

.dropzone.dragging {
    background: #ffeeee;
}

.hidden-file {
    display: none;
}

.file-list {
    margin: 10px 0 20px;
    padding: 0;
    list-style: none;
}

.file-list li {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 6px 0;
}

.file-list li button {
    border: 0;
    background: #eee;
    border-radius: 6px;
    padding: 4px 8px;
    cursor: pointer;
}

.muted {
    color: #888;
    font-size: 12px;
}
</style>
