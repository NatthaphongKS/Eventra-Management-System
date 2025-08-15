<template>
    <h1>สร้างกิจกรรม</h1>

    <!-- Title -->
    <div style="margin: 20px">
        <label>Event Title</label><br />
        <input v-model.trim="form.event_title" type="text" placeholder="ชื่อกิจกรรม" required />
    </div>

    <!-- Details -->
    <div style="margin: 20px">
        <label>Event Details</label><br />
        <textarea v-model.trim="form.event_description" required></textarea>
    </div>

    <!-- Category -->
    <div style="margin: 20px">
        <label>Category</label><br />
        <select v-model="form.event_category_id" required>
            <option disabled value="">-- Select Category --</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.cat_name }}
            </option>
        </select>
    </div>

    <!-- Date/Time -->
    <div style="margin: 20px">
        <label>Date</label><br />
        <input v-model="form.event_date" type="date" required />
    </div>

    <div style="margin: 20px">
        <label>Time</label><br />
        <div style="display: flex; gap: 12px; align-items: center">
            <input v-model="form.event_timestart" type="time" required />
            <span>:</span>
            <input v-model="form.event_timeend" type="time" required />
            <span style="opacity: 0.7">→ {{ durationLabel }}</span>
        </div>
    </div>

    <div style="margin: 20px">
        <label>Duration</label><br />
        <input :value="durationLabel" type="text" readonly placeholder="Auto fill (minutes)" />
    </div>

    <!-- Location -->
    <div style="margin: 20px">
        <label>Location</label><br />
        <input v-model.trim="form.event_location" type="text" required />
    </div>

    <!-- Upload -->
    <div style="margin: 20px">
        <label>Upload attachments</label>
        <div class="dropzone" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop" :class="{ dragging }">
            <p>Choose a file or drag & drop it here</p>
            <p class="muted">pdf, txt, docx, jpeg, xlsx – Up to 50MB</p>
            <button type="button" @click="pickFiles">Browse files</button>
            <input ref="fileInput" type="file" multiple class="hidden-file"
                accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
        </div>
    </div>

    <ul v-if="files.length" class="file-list">
        <li v-for="(f, i) in files" :key="i">
            {{ f.name }} ({{ prettySize(f.size) }})
            <button type="button" @click="removeFile(i)">✕</button>
        </li>
    </ul>

    <!-- ===== Add Guest (table) ===== -->
    <h2 style="margin-top: 28px">Add Guest</h2>

    <div class="guest-toolbar">
        <input v-model.trim="searchDraft" class="search" placeholder="Search..." />
        <div class="filters">
            <select v-model="filtersDraft.department">
                <option value="">Department</option>
                <option v-for="d in departments" :key="d" :value="d">
                    {{ d }}
                </option>
            </select>
            <select v-model="filtersDraft.team">
                <option value="">Team</option>
                <option v-for="t in teams" :key="t" :value="t">{{ t }}</option>
            </select>
            <select v-model="filtersDraft.position">
                <option value="">Position</option>
                <option v-for="p in positions" :key="p" :value="p">
                    {{ p }}
                </option>
            </select>
        </div>

        <!-- ปุ่มกดค่อยกรอง -->
        <button type="button" @click="applySearch" style="margin-left: 8px">
            Search
        </button>
        <button type="button" @click="resetSearch" style="margin-left: 6px">
            Clear
        </button>
    </div>

    <div class="table-wrap">
        <table class="guest-table">
            <thead>
                <tr>
                    <th style="width: 40px">
                        <input type="checkbox" :checked="allCheckedOnPage" @change="toggleAllOnPage($event)" />
                    </th>
                    <th style="width: 60px">#</th>
                    <th style="width: 120px">ID</th>
                    <th>Name</th>
                    <th style="width: 120px">Nickname</th>
                    <th>Department</th>
                    <th>Team</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(e, i) in pagedEmployees" :key="e.id">
                    <td>
                        <input type="checkbox" :value="e.id" :checked="selectedIds.has(e.id)"
                            @change="toggleOne(e.id, $event)" />
                    </td>
                    <td>{{ (page - 1) * perPage + i + 1 }}</td>
                    <td>{{ e.emp_id }}</td>
                    <td>{{ e.emp_firstname }} {{ e.emp_lastname }}</td>
                    <td>{{ e.nickname || "-" }}</td>
                    <td>{{ e.department || "-" }}</td>
                    <td>{{ e.team || "-" }}</td>
                    <td>{{ e.position || "-" }}</td>
                </tr>
                <tr v-if="!loadingEmployees && pagedEmployees.length === 0">
                    <td colspan="8" class="empty">ไม่พบข้อมูล</td>
                </tr>
                <tr v-if="loadingEmployees">
                    <td colspan="8" class="empty">กำลังโหลด...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div>
            แสดง
            <select v-model.number="perPage">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
            </select>
            รายการต่อหน้า
        </div>
        <div class="pager">
            <button :disabled="page === 1" @click="page--">‹</button>
            <span>{{ page }} / {{ totalPages || 1 }}</span>
            <button :disabled="page === totalPages || totalPages === 0" @click="page++">
                ›
            </button>
        </div>
    </div>

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
                event_category_id: "", // ← ใช้ id ให้ตรงกับ option
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

            // table state
            employees: [],
            loadingEmployees: false,
            submittingGuests: false,
            // ใช้งานจริง (ใช้ใน computed filteredEmployees)
            search: "",
            filters: { department: "", team: "", position: "" },

            // ชุด draft (ผูกกับ input UI)
            searchDraft: "",
            filtersDraft: { department: "", team: "", position: "" },

            departments: [],
            teams: [],
            positions: [],
            selectedIds: new Set(),
            page: 1,
            perPage: 10,
        };
    },

    async created() {
        try {
            const res = await axios.get("/event-info"); // baseURL = '/api' แล้ว
            const d = res.data || {};

            // หมวดหมู่
            this.categories = d.categories || [];

            // รายการพนักงาน (ให้คอลัมน์ตรงกับที่ตารางใช้)
            this.employees = (d.employees || []).map((e) => ({
                id: e.id,
                emp_id: e.emp_id || e.code || "",
                emp_firstname: e.emp_firstname || e.first_name || "",
                emp_lastname: e.emp_lastname || e.last_name || "",
                nickname: e.emp_nickname || "",
                department: e.department_name || "", // ← ใช้ชื่อที่ join มา
                team: e.team_name || "",
                position: e.position_name || "",
            }));

            // ตัวเลือกในฟิลเตอร์ (เป็นชื่อ string เพื่อเทียบง่าย)
            this.positions = (d.positions || []).map((x) => x.pst_name);
            this.departments = (d.departments || []).map((x) => x.dpm_name);
            this.teams = (d.teams || []).map((x) => x.tm_name);
        } catch (err) {
            this.message =
                err.response?.data?.message || "โหลดข้อมูลอ้างอิงไม่สำเร็จ";
        }
    },

    computed: {
        durationLabel() {
            const m = this.form.event_duration || 0;
            const h = Math.floor(m / 60),
                mm = m % 60;
            if (!m) return "Auto fill";
            if (h && mm) return `${h}h ${mm}m`;
            if (h) return `${h}h`;
            return `${mm}m`;
        },
        filteredEmployees() {
            const q = this.search.toLowerCase(); // ← ใช้ค่าที่ “ยืนยันแล้ว”
            return this.employees.filter((e) => {
                const matchText =
                    !q ||
                    `${e.emp_id} ${e.emp_firstname} ${e.emp_lastname} ${e.emp_nickname || ""
                        }`
                        .toLowerCase()
                        .includes(q);
                const matchDept =
                    !this.filters.department ||
                    e.department === this.filters.department;
                const matchTeam =
                    !this.filters.team || e.team === this.filters.team;
                const matchPos =
                    !this.filters.position ||
                    e.position === this.filters.position;
                return matchText && matchDept && matchTeam && matchPos;
            });
        },
        totalPages() {
            return Math.ceil(this.filteredEmployees.length / this.perPage);
        },
        pagedEmployees() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredEmployees.slice(start, start + this.perPage);
        },
        allCheckedOnPage() {
            if (this.pagedEmployees.length === 0) return false;
            return this.pagedEmployees.every((e) => this.selectedIds.has(e.id));
        },
    },

    watch: {
        "form.event_timestart": "updateDuration",
        "form.event_timeend": "updateDuration",
        search() {
            this.page = 1;
        },
        filters: {
            deep: true,
            handler() {
                this.page = 1;
            },
        },
        perPage() {
            this.page = 1;
        },
    },

    methods: {
        // Duration auto fill
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
            this.form.event_duration = e >= s ? e - s : 1440 - s + e; // รองรับข้ามเที่ยงคืน
        },
        applySearch() {
            // ยืนยันค่า draft → ค่าใช้งานจริง
            this.search = this.searchDraft;
            this.filters = { ...this.filtersDraft };
            this.page = 1; // รีเซ็ตเป็นหน้าแรกหลังกรอง
        },
        resetSearch() {
            // เคลียร์ทั้ง draft และค่าจริง
            this.searchDraft = "";
            this.filtersDraft = { department: "", team: "", position: "" };
            this.search = "";
            this.filters = { department: "", team: "", position: "" };
            this.page = 1;
        },

        // Upload
        pickFiles() {
            this.$refs.fileInput.click();
        },
        onPick(e) {
            this.addFiles([...e.target.files]);
            e.target.value = "";
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
        prettySize(b) {
            const mb = b / (1024 * 1024);
            return mb >= 1
                ? `${mb.toFixed(2)} MB`
                : `${(b / 1024).toFixed(0)} KB`;
        },

        // Submit Event
        async submitForm() {
            try {
                if (this.selectedIds.size === 0) {
                    this.message = "กรุณาเลือกพนักงานอย่างน้อย 1 คน";
                    return;
                }

                this.loading = true;
                this.message = "";

                const fd = new FormData();
                Object.entries(this.form).forEach(([k, v]) =>
                    fd.append(k, v ?? "")
                );
                this.files.forEach((f) =>
                    fd.append("attachments[]", f, f.name)
                );

                // แนบรายชื่อผู้เข้าร่วม
                Array.from(this.selectedIds).forEach((id) =>
                    fd.append("employee_ids[]", id)
                );

                const res = await axios.post("/event-save", fd, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                this.message = res.data.message || "บันทึกสำเร็จ";
                this.files = [];
                if (res.data.redirect) {
                    this.$router.push(res.data.redirect);
                }
                // ล้างตัวเลือก ถ้าต้องการ
                // this.selectedIds = new Set();


            } catch (err) {
                this.message = err.response?.data?.message || "เกิดข้อผิดพลาด";
            } finally {
                this.loading = false;
            }
        },

        toggleOne(id, ev) {
            if (ev.target.checked) this.selectedIds.add(id);
            else this.selectedIds.delete(id);
            this.selectedIds = new Set(this.selectedIds); // keep reactive
        },
        toggleAllOnPage(ev) {
            const tick = ev.target.checked;
            this.pagedEmployees.forEach((e) => {
                if (tick) this.selectedIds.add(e.id);
                else this.selectedIds.delete(e.id);
            });
            this.selectedIds = new Set(this.selectedIds);
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

.table-wrap {
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}

.guest-table {
    width: 100%;
    border-collapse: collapse;
}

.guest-table th,
.guest-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f1f1;
    text-align: left;
}

.guest-table thead th {
    background: #fafafa;
    font-weight: 600;
    color: #333;
}

.guest-toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin: 14px 0;
}

.search {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #eee;
    border-radius: 999px;
}

.filters select {
    padding: 10px 12px;
    border: 1px solid #eee;
    border-radius: 12px;
    background: #fff;
}

.table-footer {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
}

.pager button {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
}

.actions button {
    padding: 10px 14px;
    border: 0;
    background: #c00;
    color: #fff;
    border-radius: 12px;
    cursor: pointer;
}

.empty {
    text-align: center;
    color: #777;
}
</style>
