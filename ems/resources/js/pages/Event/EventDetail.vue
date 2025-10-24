<template>
  <div class="p-6">
    <div class="rounded-[24px] border border-rose-100 bg-white p-6 md:p-8 shadow-[0_6px_24px_rgba(244,63,94,0.08)]">
      <!-- Header -->
      <div class="mb-6 flex items-center gap-3">
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-rose-100 text-rose-700">📅</div>
        <h2 class="text-2xl font-semibold">Details Event</h2>
      </div>

      <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
        <!-- Left -->
        <div class="space-y-6 lg:col-span-2">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm text-slate-600">Event Title</label>
              <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                <span class="text-slate-400">📝</span>
                <input :value="form.title" disabled class="w-full bg-transparent text-slate-800 outline-none" />
              </div>
            </div>
            <div>
              <label class="mb-1 block text-sm text-slate-600">Category</label>
              <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                <span class="text-slate-400">🗂️</span>
                <input :value="categoryDisplay" disabled class="w-full bg-transparent text-slate-800 outline-none" />
                <span class="inline-flex h-7 items-center rounded-xl bg-rose-100 px-2 text-xs text-rose-700">▼</span>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm text-slate-600">Event Details</label>
            <div class="flex w-full items-start gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
              <span class="mt-1 text-slate-400">✍️</span>
              <textarea :value="form.details" rows="4" disabled class="w-full resize-none bg-transparent text-slate-800 outline-none"></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
              <label class="mb-1 block text-sm text-slate-600">Date</label>
              <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                <span class="text-slate-400">📆</span>
                <input :value="form.date" type="date" disabled class="w-full bg-transparent text-slate-800 outline-none" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-sm text-slate-600">Time</label>
                <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                  <span class="text-slate-400">🕘</span>
                  <input :value="form.timeStart" type="time" disabled class="w-full bg-transparent text-slate-800 outline-none" />
                </div>
              </div>
              <div>
                <label class="mb-1 block select-none text-sm opacity-0">Time</label>
                <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                  <span class="text-slate-400">🕛</span>
                  <input :value="form.timeEnd" type="time" disabled class="w-full bg-transparent text-slate-800 outline-none" />
                </div>
              </div>
            </div>
            <div>
              <label class="mb-1 block text-sm text-slate-600">Duration</label>
              <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                <span class="text-slate-400">⏱️</span>
                <input :value="formatDuration(form.duration)" disabled class="w-full bg-transparent text-slate-800 outline-none" />
                <span class="inline-flex h-7 items-center rounded-xl bg-rose-100 px-2 text-xs text-rose-700">⏲</span>
              </div>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm text-slate-600">Location</label>
            <div class="flex w-full items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
              <span class="text-slate-400">📍</span>
              <input :value="form.location" disabled class="w-full bg-transparent text-slate-800 outline-none" />
              <span class="inline-flex h-7 items-center rounded-xl bg-rose-100 px-2 text-xs text-rose-700">🔍</span>
            </div>
          </div>
        </div>

        <!-- Right: Attachments -->
        <div>
          <label class="mb-1 block text-sm text-slate-600">Upload attachments</label>
          <p class="mb-2 text-xs text-slate-500">Drag and drop document to your support task</p>
          <div class="flex min-h-[240px] flex-col gap-3 rounded-3xl border-2 border-dashed border-rose-200 bg-rose-50/40 p-4">
            <div v-if="files.length === 0" class="grid flex-1 place-items-center text-rose-300">ไม่มีไฟล์แนบ</div>
            <div v-else class="flex flex-col gap-3">
              <div v-for="(f, i) in files" :key="i" class="flex items-center justify-between rounded-2xl border border-rose-100 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center gap-3">
                  <div class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 text-rose-600">📄</div>
                  <div class="text-sm">
                    <div class="font-medium text-slate-800">{{ f.name }}</div>
                    <div class="text-rose-500" v-if="f.size">{{ prettySize(f.size) }}</div>
                  </div>
                </div>
                <button class="grid h-8 w-8 place-items-center rounded-full bg-rose-50 text-rose-400" disabled>✕</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Guests -->
      <div class="mt-10">
        <div class="mb-4 flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-2xl bg-rose-100 text-rose-700">👥</div>
          <h3 class="text-xl font-semibold">Add Guest</h3>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-slate-200">
          <table class="w-full min-w-[900px] text-sm">
            <thead>
              <tr class="bg-rose-50/60 text-rose-700">
                <th class="w-10 p-3 text-left"></th>
                <th class="w-12 p-3 text-left">#</th>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Nickname</th>
                <th class="p-3 text-left">Department</th>
                <th class="p-3 text-left">Team</th>
                <th class="p-3 text-left">Position</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in rows" :key="row.id" class="border-t hover:bg-rose-50/40">
                <td class="p-3">
                  <!-- ถ้าอยากให้ดูอย่างเดียวให้ใส่ disabled, ถ้าอยากให้ติ๊กได้ก็เอา disabled ออก -->
                  <input type="checkbox"
                         :checked="selectedIds.has(row.id)"
                         disabled
                         class="accent-rose-600" />
                </td>
                <td class="p-3">{{ i + 1 }}</td>
                <td class="p-3">{{ row.code }}</td>
                <td class="p-3">{{ row.name }}</td>
                <td class="p-3">{{ row.nick }}</td>
                <td class="p-3">{{ row.department }}</td>
                <td class="p-3">{{ row.team }}</td>
                <td class="p-3">{{ row.position }}</td>
              </tr>
              <tr v-if="rows.length === 0">
                <td colspan="8" class="p-6 text-center text-slate-400">No data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="loading" class="mt-4 text-slate-500">กำลังโหลดข้อมูล…</div>
    <div v-if="error" class="mt-4 text-rose-600">{{ error }}</div>
  </div>
</template>

<script>
import axios from "axios";
axios.defaults.baseURL = "/api";
axios.defaults.withCredentials = true;
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

export default {
  name: "EventDetailsPretty",
  props: { id: { type: [String, Number], required: false } },
  data() {
    return {
      loading: false,
      error: "",
      files: [],
      Categories: [],
      rows: [],             // employees
      selectedIds: new Set(), // <-- employee_ids ที่เชื่อมกับ event
      form: {
        title: "", categoryId: "", details: "",
        date: "", timeStart: "", timeEnd: "",
        duration: "", location: "", status: "",
      },
    };
  },
  computed: {
    categoryDisplay() {
      const id = this.form.categoryId;
      if (!id) return "";
      const found = this.Categories.find(c => String(c.id) === String(id));
      return found?.cat_name ?? String(id);
    },
  },
  async created() {
    await this.fetchEvent();
    await this.fetchEmployees();
    await this.fetchGuestSelection(); // <-- โหลด mapping ems_connect
  },
  watch: {
    "$route.params.id"(n, o) {
      if (n !== o) {
        this.fetchEvent();
        this.fetchEmployees();
        this.fetchGuestSelection();
      }
    },
  },
  methods: {
    // -------- Event --------
    async fetchEvent() {
      try {
        this.loading = true;
        const id = this.id ?? this.$route?.params?.id;
        if (!id) { this.error = "ไม่พบรหัสอีเวนต์ในเส้นทาง (route)"; return; }

        const res = await axios.get(`/event/${id}`);
        let item = res.data;
        if (Array.isArray(item)) item = item[0];
        else if (item && typeof item === "object" && "data" in item) item = item.data;
        if (!item) { this.error = "ไม่พบข้อมูลอีเวนต์ใน response"; return; }

        this.form.title      = item.evn_title ?? "";
        this.form.categoryId = item.evn_category_id ?? "";
        this.form.details    = item.evn_description ?? "";
        this.form.date       = (item.evn_date ?? "").slice(0, 10);
        this.form.timeStart  = (item.evn_timestart ?? "").slice(0, 5);
        this.form.timeEnd    = (item.evn_timeend ?? "").slice(0, 5);
        this.form.duration   = item.evn_duration ?? "";
        this.form.location   = item.evn_location ?? "";
        this.form.status     = item.evn_status ?? "";

        this.files = item.evn_file === "have" ? [{ name: "Meeting information.pdf", size: 512000 }] : [];
      } catch (e) {
        console.error(e);
        this.error = `โหลดข้อมูลล้มเหลว${e?.response?.status ? ` (status=${e.response.status})` : ""}`;
      } finally {
        this.loading = false;
      }
    },

    // -------- Employees (ทั้งหมด) --------
    async fetchEmployees() {
      try {
        // NOTE: อย่าใส่ "/" นำหน้า จะได้ต่อ baseURL '/api' อัตโนมัติ
        const r = await axios.get('get-employees');

        const prefixMap = { 1: "นาย", 2: "นาง", 3: "นางสาว" };
        const arr = Array.isArray(r.data) ? r.data : (r.data?.data || []);
        this.rows = arr.map((e, idx) => {
          const prefixText = e.emp_prefix ? (prefixMap[e.emp_prefix] || e.emp_prefix) : "";
          const fullName = [prefixText, e.emp_firstname, e.emp_lastname].filter(Boolean).join(" ").replace(/\s+/g," ").trim();
          return {
            id: e.id ?? idx + 1,
            code: e.emp_id ?? "",
            name: fullName,
            nick: e.emp_nickname ?? "",
            department: e.department_name || e.emp_department_id || "",
            team:       e.team_name       || e.emp_team_id       || "",
            position:   e.position_name   || e.emp_position_id   || "",
          };
        });
      } catch (err) {
        console.error("fetchEmployees error", err);
        this.rows = [];
      }
    },

    // -------- Connect (พนักงานที่ถูกเลือกของ event) --------
    async fetchGuestSelection() {
      try {
        const eventId = this.id ?? this.$route?.params?.id;
        if (!eventId) return;

        const r = await axios.get(`events/${eventId}/connects`);
        const ids = Array.isArray(r.data) ? r.data : (r.data?.employee_ids || []);
        this.selectedIds = new Set(ids.map(n => Number(n)));
      } catch (err) {
        console.error('fetchGuestSelection error', err);
        this.selectedIds = new Set();
      }
    },

    // -------- Utils --------
    formatDuration(v) {
      if (v == null || v === "") return "";
      const n = Number(v); if (!Number.isFinite(n)) return String(v);
      return n === 1 ? "1 Hour" : `${n} Hour`;
    },
    prettySize(bytes) {
      if (!bytes && bytes !== 0) return "";
      const units = ["B","KB","MB","GB"]; let i=0, val=bytes;
      while (val>=1024 && i<units.length-1){ val/=1024; i++; }
      return `${val.toFixed(1)} ${units[i]}`;
    },
  },
};
</script>
