<template>
    <div class="page">
      <!-- ===== Header ===== -->
      <div class="page-head">
        <h1>Add New Event</h1>
      </div>

      <!-- ===== Card: Event form ===== -->
      <div class="card">
        <!-- Row: Title + Category -->
        <div class="grid-2 gap-24">
          <div class="field">
            <label>Event Title <span class="req">*</span></label>
            <div class="input">
              <input v-model.trim="form.event_title" type="text" placeholder="Name this event" required />
            </div>
          </div>

          <div class="field">
            <label>Category <span class="req">*</span></label>
            <div class="input with-caret">
              <select v-model="form.event_category_id" required>
                <option disabled value="">Choose Category</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">
                  {{ c.cat_name }}
                </option>
              </select>
              <svg class="caret" viewBox="0 0 20 20"><path d="M5 7l5 6 5-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            </div>
          </div>
        </div>

        <!-- Row: Details + Upload -->
        <div class="grid-2 gap-24 mt-20">
          <div class="field">
            <label>Event Details <span class="req">*</span></label>
            <div class="input">
              <textarea v-model.trim="form.event_description" rows="5" placeholder="Write some description... (255 words)" required></textarea>
            </div>
          </div>

          <div class="field">
            <label>Upload attachments</label>
            <div
              class="dropzone"
              :class="{ dragging }"
              @dragover.prevent="dragging = true"
              @dragleave.prevent="dragging = false"
              @drop.prevent="onDrop"
            >
              <div class="dz-icon">
                <svg viewBox="0 0 24 24"><path d="M12 16V4m0 0l-4 4m4-4l4 4M4 16a4 4 0 014-4h1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div class="dz-text">
                <div class="dz-title">Choose a file or drag & drop it here</div>
                <div class="dz-sub">pdf, txt, docx, jpeg, xlsx - Up to 50MB</div>
              </div>
              <button type="button" class="btn neutral" @click="pickFiles">Browse files</button>
              <input ref="fileInput" type="file" multiple class="hidden-file"
                     accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
            </div>

            <ul v-if="files.length" class="file-list">
              <li v-for="(f, i) in files" :key="i">
                <span class="file-name">{{ f.name }}</span>
                <span class="file-size">({{ prettySize(f.size) }})</span>
                <button type="button" class="chip x" @click="removeFile(i)">✕</button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Row: Date / Time / Duration -->
        <div class="grid-3 gap-24 mt-20">
          <div class="field">
            <label>Date <span class="req">*</span></label>
            <div class="input with-icon">
              <svg class="icon" viewBox="0 0 24 24"><path d="M7 2v3M17 2v3M3 9h18M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <input v-model="form.event_date" type="date" required />
            </div>
          </div>

          <div class="field">
            <label>Time <span class="req">*</span></label>
            <div class="time-row">
              <div class="input with-icon">
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                <input v-model="form.event_timestart" type="time" required />
              </div>
              <span class="colon">:</span>
              <div class="input with-icon">
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                <input v-model="form.event_timeend" type="time" required />
              </div>
              <span class="hint">→ {{ durationLabel }}</span>
            </div>
          </div>

          <div class="field">
            <label>Duration</label>
            <div class="input">
              <input :value="durationLabel" type="text" readonly placeholder="Auto fill (minutes)" />
            </div>
          </div>
        </div>

        <!-- Row: Location -->
        <div class="grid-1 mt-20">
          <div class="field">
            <label>Location <span class="req">*</span></label>
            <div class="input with-pin">
              <input v-model.trim="form.event_location" type="text" placeholder="Location/Building/Room Name" required />
              <span class="pin">
                <svg viewBox="0 0 24 24"><path d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="10" r="2.7" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Card: Add Guest ===== -->
      <div class="card mt-28">
        <h2 class="card-title">Add Guest</h2>

        <!-- Toolbar -->
        <div class="toolbar">
          <div class="search-wrap">
            <input v-model.trim="searchDraft" class="search" placeholder="Search..." />
            <button class="icon-btn" @click="applySearch" aria-label="search">
              <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.4-3.4" stroke="currentColor" stroke-width="2" /></svg>
            </button>
          </div>

          <div class="chip-select">
            <div class="chip">
              <span class="chip-label">ID</span>
              <select v-model="sortDir">
                <option value="asc">↑</option>
                <option value="desc">↓</option>
              </select>
            </div>

            <div class="chip">
              <span class="chip-label">Department</span>
              <select v-model="filtersDraft.department">
                <option value="">All</option>
                <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>

            <div class="chip">
              <span class="chip-label">Team</span>
              <select v-model="filtersDraft.team">
                <option value="">All</option>
                <option v-for="t in teams" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>

            <div class="chip">
              <span class="chip-label">Position</span>
              <select v-model="filtersDraft.position">
                <option value="">All</option>
                <option v-for="p in positions" :key="p" :value="p">{{ p }}</option>
              </select>
            </div>
          </div>

          <div class="toolbar-actions">
            <button class="btn ghost" @click="resetSearch">Clear</button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th style="width:48px">
                  <input type="checkbox" :checked="allCheckedOnPage" @change="toggleAllOnPage($event)" />
                </th>
                <th style="width:56px">#</th>
                <th style="width:120px">ID</th>
                <th>Name</th>
                <th style="width:140px">Nickname</th>
                <th>Department</th>
                <th>Team</th>
                <th>Position</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e, i) in pagedEmployees" :key="e.id">
                <td><input type="checkbox" :value="e.id" :checked="selectedIds.has(e.id)" @change="toggleOne(e.id,$event)" /></td>
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

        <!-- Footer: per-page + pagination + actions -->
        <div class="table-footer">
          <div class="perpage">
            แสดง
            <select v-model.number="perPage">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
            รายการ
            <span class="muted">— {{ rangeText }}</span>
          </div>

          <div class="pagination">
            <button class="pager" :disabled="page===1" @click="gotoPage(page-1)">‹</button>

            <button v-for="n in pageList" :key="`p-${n.key}`"
                    class="pager"
                    :class="{ active: n.num===page, dots: n.dots }"
                    :disabled="n.dots"
                    @click="!n.dots && gotoPage(n.num)">
              {{ n.label }}
            </button>

            <button class="pager" :disabled="page===totalPages || totalPages===0" @click="gotoPage(page+1)">›</button>
          </div>

          <div class="actions">
            <button class="btn danger" @click="cancel">✕ Cancel</button>
            <button class="btn success" :disabled="loading" @click="submitForm">
              {{ loading ? "กำลังบันทึก..." : "＋ Create" }}
            </button>
          </div>
        </div>

        <p v-if="message" class="status">{{ message }}</p>
      </div>
    </div>
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

        employees: [],
        loadingEmployees: false,

        // filters (draft for UI) + confirmed filters
        search: "",
        filters: { department: "", team: "", position: "" },
        searchDraft: "",
        filtersDraft: { department: "", team: "", position: "" },

        departments: [],
        teams: [],
        positions: [],
        selectedIds: new Set(),

        // sorting & paging
        sortDir: "asc",
        page: 1,
        perPage: 10,
      };
    },

    async created() {
      try {
        const res = await axios.get("/event-info");
        const d = res.data || {};

        this.categories = d.categories || [];

        this.employees = (d.employees || []).map((e) => ({
          id: e.id,
          emp_id: e.emp_id || e.code || "",
          emp_firstname: e.emp_firstname || e.first_name || "",
          emp_lastname: e.emp_lastname || e.last_name || "",
          nickname: e.emp_nickname || "",
          department: e.department_name || "",
          team: e.team_name || "",
          position: e.position_name || "",
        }));

        this.positions = (d.positions || []).map((x) => x.pst_name);
        this.departments = (d.departments || []).map((x) => x.dpm_name);
        this.teams = (d.teams || []).map((x) => x.tm_name);
      } catch (err) {
        this.message = err.response?.data?.message || "โหลดข้อมูลอ้างอิงไม่สำเร็จ";
      }
    },

    computed: {
      durationLabel() {
        const m = this.form.event_duration || 0;
        const h = Math.floor(m / 60), mm = m % 60;
        if (!m) return "Auto fill";
        if (h && mm) return `${h}h ${mm}m`;
        if (h) return `${h}h`;
        return `${mm}m`;
      },

      filteredEmployees() {
        const q = this.search.toLowerCase();
        const rows = this.employees.filter((e) => {
          const matchText =
            !q ||
            `${e.emp_id} ${e.emp_firstname} ${e.emp_lastname} ${e.nickname || ""}`.toLowerCase().includes(q);
          const matchDept = !this.filters.department || e.department === this.filters.department;
          const matchTeam = !this.filters.team || e.team === this.filters.team;
          const matchPos = !this.filters.position || e.position === this.filters.position;
          return matchText && matchDept && matchTeam && matchPos;
        });

        // sort by ID asc/desc (string-friendly)
        const dir = this.sortDir === "asc" ? 1 : -1;
        return rows.sort((a, b) => (a.emp_id > b.emp_id ? dir : a.emp_id < b.emp_id ? -dir : 0));
      },

      totalPages() {
        return Math.ceil(this.filteredEmployees.length / this.perPage) || 1;
      },

      pagedEmployees() {
        const start = (this.page - 1) * this.perPage;
        return this.filteredEmployees.slice(start, start + this.perPage);
      },

      allCheckedOnPage() {
        if (this.pagedEmployees.length === 0) return false;
        return this.pagedEmployees.every((e) => this.selectedIds.has(e.id));
      },

      rangeText() {
        const total = this.filteredEmployees.length;
        if (!total) return "0 รายการ";
        const start = (this.page - 1) * this.perPage + 1;
        const end = Math.min(this.page * this.perPage, total);
        return `${start}-${end} จาก ${total} รายการ`;
      },

      // create pretty page list: [1, 2, ..., 7, 9]
      pageList() {
        const tp = this.totalPages;
        const cur = this.page;
        const nums = new Set([1, tp, cur, cur - 1, cur + 1, 2, tp - 1].filter((n) => n >= 1 && n <= tp));
        const sorted = [...nums].sort((a, b) => a - b);
        const out = [];
        for (let i = 0; i < sorted.length; i++) {
          const n = sorted[i];
          out.push({ key: `n${n}`, num: n, label: n });
          if (i < sorted.length - 1 && sorted[i + 1] - n > 1) {
            out.push({ key: `d${n}`, dots: true, label: "..." });
          }
        }
        return out;
      },
    },

    watch: {
      "form.event_timestart": "updateDuration",
      "form.event_timeend": "updateDuration",
      search() { this.page = 1; },
      filters: {
        deep: true,
        handler() { this.page = 1; },
      },
      perPage() { this.page = 1; },
      sortDir() { this.page = 1; },
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
        if (s == null || e == null) { this.form.event_duration = 0; return; }
        this.form.event_duration = e >= s ? e - s : 1440 - s + e;
      },

      // ===== Search / Filter =====
      applySearch() {
        this.search = this.searchDraft;
        this.filters = { ...this.filtersDraft };
        this.page = 1;
      },
      resetSearch() {
        this.searchDraft = "";
        this.filtersDraft = { department: "", team: "", position: "" };
        this.search = "";
        this.filters = { department: "", team: "", position: "" };
        this.page = 1;
      },

      // ===== Upload =====
      pickFiles() { this.$refs.fileInput.click(); },
      onPick(e) { this.addFiles([...e.target.files]); e.target.value = ""; },
      onDrop(e) { this.dragging = false; this.addFiles([...e.dataTransfer.files]); },
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
      removeFile(i) { this.files.splice(i, 1); },
      prettySize(b) {
        const mb = b / (1024 * 1024);
        return mb >= 1 ? `${mb.toFixed(2)} MB` : `${(b / 1024).toFixed(0)} KB`;
      },

      // ===== Submit =====
      async submitForm() {
        try {
          if (this.selectedIds.size === 0) { this.message = "กรุณาเลือกพนักงานอย่างน้อย 1 คน"; return; }
          this.loading = true; this.message = "";

          const fd = new FormData();
          Object.entries(this.form).forEach(([k, v]) => fd.append(k, v ?? ""));
          this.files.forEach((f) => fd.append("attachments[]", f, f.name));
          Array.from(this.selectedIds).forEach((id) => fd.append("employee_ids[]", id));

          const res = await axios.post("/event-save", fd, { headers: { "Content-Type": "multipart/form-data" } });
          this.message = res.data.message || "บันทึกสำเร็จ";
          this.files = [];
          if (res.data.redirect) this.$router.push(res.data.redirect);
        } catch (err) {
          this.message = err.response?.data?.message || "เกิดข้อผิดพลาด";
        } finally {
          this.loading = false;
        }
      },

      // ===== Table selection & paging =====
      toggleOne(id, ev) {
        if (ev.target.checked) this.selectedIds.add(id);
        else this.selectedIds.delete(id);
        this.selectedIds = new Set(this.selectedIds);
      },
      toggleAllOnPage(ev) {
        const tick = ev.target.checked;
        this.pagedEmployees.forEach((e) => {
          if (tick) this.selectedIds.add(e.id);
          else this.selectedIds.delete(e.id);
        });
        this.selectedIds = new Set(this.selectedIds);
      },
      gotoPage(n) { if (n >= 1 && n <= this.totalPages) this.page = n; },

      cancel() { this.$router?.back?.() || (window.history.length ? window.history.back() : null); },
    },
  };
  </script>

  <style scoped>
  /* ===== Theme ===== */
  :root { --primary:#BA0C16; --primary-200:#ffdcdc; --primary-300:#ffc9c9; --border:#eee; --text:#333; --muted:#8a8a8a; --bg:#fafafa; }
  .page{ padding:24px; }
  .page-head h1{ font-size:22px; font-weight:800; color:#222; margin:0 0 12px; }
  .card{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; }
  .card-title{ margin:0 0 12px; font-size:18px; font-weight:700; }

  /* ===== Layout helpers ===== */
  .grid-1{ display:grid; grid-template-columns:1fr; gap:16px; }
  .grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .grid-3{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
  .gap-24{ gap:24px; }
  .mt-20{ margin-top:20px; }
  .mt-28{ margin-top:28px; }

  /* ===== Fields ===== */
  .field label{ display:block; font-weight:600; color:#222; margin-bottom:8px; }
  .req{ color:var(--primary); }
  .input{ position:relative; }
  .input input,
  .input select,
  .input textarea{
    width:100%; padding:12px 14px; border:1.6px solid var(--primary-300);
    border-radius:12px; outline:none; font-size:14px; transition:box-shadow .15s,border .15s;
    background:#fff;
  }
  .input textarea{ resize:vertical; min-height:120px; }
  .input input:focus, .input select:focus, .input textarea:focus{
    border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-200);
  }

  /* icons inside input */
  .input.with-icon .icon{
    position:absolute; left:12px; top:50%; transform:translateY(-50%); width:18px; height:18px; opacity:.7;
  }
  .input.with-icon input{ padding-left:38px; }
  .with-caret select{ appearance:none; -webkit-appearance:none; }
  .with-caret .caret{ position:absolute; right:12px; top:50%; width:16px; height:16px; transform:translateY(-50%); opacity:.7; pointer-events:none; }

  .input.with-pin .pin{ position:absolute; right:10px; top:50%; transform:translateY(-50%); width:20px; height:20px; opacity:.8; }

  .time-row{ display:flex; gap:10px; align-items:center; }
  .colon{ opacity:.5; }
  .hint{ color:var(--muted); font-size:13px; }

  /* ===== Dropzone ===== */
  .dropzone{
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px;
    border:2px dashed var(--primary-300); border-radius:16px; background:#fff6f6; padding:24px; text-align:center;
  }
  .dropzone.dragging{ background:#ffeeee; }
  .dz-icon svg{ width:44px; height:44px; }
  .dz-title{ font-weight:700; color:#222; }
  .dz-sub{ color:var(--muted); font-size:12px; }
  .hidden-file{ display:none; }
  .file-list{ list-style:none; margin:10px 0 0; padding:0; }
  .file-list li{ display:flex; align-items:center; gap:8px; padding:6px 0; }
  .file-name{ font-weight:600; }
  .file-size{ color:var(--muted); }
  .chip.x{ border:0; background:#eee; padding:4px 8px; border-radius:999px; cursor:pointer; }

  /* ===== Toolbar ===== */
  .toolbar{ display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin:8px 0 14px; }
  .search-wrap{ display:flex; align-items:center; gap:8px; flex:1; }
  .search{ flex:1; padding:12px 14px; border:1px solid var(--border); border-radius:999px; outline:none; }
  .icon-btn{ width:40px; height:40px; border:1px solid var(--border); border-radius:12px; background:#fff; display:grid; place-items:center; cursor:pointer; }
  .icon-btn svg{ width:18px; height:18px; }

  .chip-select{ display:flex; flex-wrap:wrap; gap:8px; }
  .chip{ display:flex; align-items:center; gap:8px; background:#fff; border:1px solid var(--border); border-radius:12px; padding:8px 10px; }
  .chip select{ border:0; background:transparent; outline:none; }
  .chip-label{ color:#444; font-weight:600; }

  .toolbar-actions .btn.ghost{ background:#fff; border:1px solid var(--border); }

  /* ===== Table ===== */
  .table-wrap{ border:1px solid var(--border); border-radius:14px; overflow:hidden; }
  .table{ width:100%; border-collapse:collapse; }
  .table th, .table td{ padding:12px 14px; border-bottom:1px solid #f2f2f2; text-align:left; }
  .table thead th{ background:#fafafa; font-weight:700; color:#333; }

  /* ===== Footer & Pagination ===== */
  .table-footer{
    display:grid; grid-template-columns: 1fr auto auto; gap:12px; align-items:center; padding:14px 0;
  }
  .perpage select{ padding:6px 8px; border:1px solid var(--border); border-radius:8px; background:#fff; }
  .muted{ color:var(--muted); }

  .pagination{ display:flex; gap:6px; align-items:center; }
  .pager{ min-width:36px; height:36px; padding:0 10px; border:1px solid var(--border); border-radius:10px; background:#fff; cursor:pointer; }
  .pager.active{ background:var(--primary); color:#fff; border-color:var(--primary); }
  .pager.dots{ cursor:default; }
  .pager:disabled{ opacity:.5; cursor:not-allowed; }

  .actions{ display:flex; gap:10px; }
  .btn{ border:0; border-radius:12px; padding:10px 16px; font-weight:700; cursor:pointer; }
  .btn.neutral{ background:#fff; border:1px solid var(--border); }
  .btn.success{ background:#00b26a; color:#fff; }
  .btn.danger{ background:#f14f4f; color:#fff; }
  .btn:disabled{ opacity:.7; cursor:not-allowed; }

  .status{ margin-top:10px; color:#444; }

  /* mobile */
  @media (max-width: 980px){
    .grid-2, .grid-3{ grid-template-columns:1fr; }
    .table-footer{ grid-template-columns: 1fr; gap:10px; }
  }
  </style>
