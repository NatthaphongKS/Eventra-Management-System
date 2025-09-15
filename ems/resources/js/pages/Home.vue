<!-- pages/event_page.vue -->
<template>

<div class="dashboard-grid">
  <!-- Event Table Section -->
  <div class="card event-card">
    <h2 class="table-title event-title">Event</h2>
    <div class="toolbar toolbar--pill">
      <!-- กลุ่ม search + ปุ่มค้นหาแดง -->
      <div class="search-group">
        <input
          v-model.trim="search"
          placeholder="Search"
          class="pill-input"
        />
        <button
          type="button"
          class="icon-btn icon-btn--solid"
          aria-label="Search"
          title="ค้นหา"
          disabled
          style="opacity:0.5;pointer-events:none;"
        >
          <MagnifyingGlassIcon class="icon" />
        </button>
      </div>

      <!-- ปุ่ม Filter (ตอนนี้ยัง UI เฉยๆ) -->
      <button type="button" class="text-btn" @click="toggleFilter">
        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="4" y1="7" x2="20" y2="7" />
          <line x1="6" y1="12" x2="16" y2="12" />
          <line x1="8" y1="17" x2="12" y2="17" />
        </svg>
        <span>Filter</span>
      </button>

      <!-- ปุ่ม Sort พร้อม dropdown สำหรับ employee table -->
      <div style="position:relative;">
        <button type="button" class="text-btn" @click="showEmpSort = !showEmpSort">
          <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
          </svg>
          <span>Sort</span>
        </button>
        <div v-if="showEmpSort" class="sort-dropdown">
          <div class="sort-title">Sort</div>
          <button v-for="opt in empSortOptions" :key="opt.value" :class="['sort-item', empSort.value===opt.value ? 'active' : '']" @click="setEmpSort(opt.value)">
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- สรุปรายการ + ปุ่มเพิ่ม (เรียงแบบในภาพ) -->
      <span class="summary">ทั้งหมด {{ filtered.length }} รายการ</span>
      <button type="button" class="custom-btn report-btn" @click="onViewReport">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
          <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
        </svg>
        <span>View Report</span>
      </button>
      <button type="button" class="custom-btn export-btn" @click="onExport">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
          <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
        </svg>
        <span>Export</span>
      </button>
    </div>
    <div class="table-wrap">
      <table class="table compact">
        <thead>
          <tr>
            <th class="th col-idx">#</th>
            <th class="th col-title">Event</th>
            <th class="th col-cat">Category</th>
            <th class="th col-date">Date (D/M/Y)</th>
            <th class="th col-time">Time</th>
            <th class="th col-num">Invited</th>
            <th class="th col-num">Accepted</th>
            <th class="th col-status">Status</th>
            <!-- ลบหัวข้อ Action -->
          </tr>
        </thead>

        <tbody>
          <tr v-for="(ev, i) in paged" :key="ev.id">
            <td class="col-idx">{{ (page-1)*pageSize + i + 1 }}</td>
            <td class="col-title"><span class="truncate">{{ ev.evn_title || 'N/A' }}</span></td>
            <td class="col-cat"><span class="truncate">{{ ev.cat_name || 'N/A' }}</span></td>
          <td class="col-date">{{ formatDate(ev.evn_date) }}</td>
          <td class="col-time">
            {{ ev.evn_timestart ? ev.evn_timestart.slice(0,5) : '??:??' }} -
            {{ ev.evn_timeend ? ev.evn_timeend.slice(0,5) : '??:??' }}
          </td>
          <td class="col-num">{{ ev.evn_num_guest ?? '0' }}</td>
          <td class="col-num">{{ ev.evn_sum_accept ?? 'N/A' }}</td>
          <td class="col-status">
            <span :class="['badge', ev.evn_status]">{{ ev.evn_status || 'N/A' }}</span>
            </td>

            <!-- ลบเซลล์ Action -->
        </tr>

        <tr v-if="paged.length === 0">
            <td :colspan="9" style="text-align:center">No data found</td>
        </tr>
        </tbody>
    </table>
    </div>
    <div class="pager2">
      <button class="arrow-btn" :disabled="page===1" @click="goToPage(page-1)">‹</button>
      <template v-for="(it, idx) in pageItems" :key="idx">
        <button v-if="it.type==='page'" class="page-btn" :class="{ active: it.value===page }" @click="goToPage(it.value)">
          {{ it.value }}
        </button>
        <span v-else class="dots">…</span>
      </template>
      <button class="arrow-btn" :disabled="page===totalPages || totalPages===0" @click="goToPage(page+1)">›</button>
    </div>
  </div>

  <!-- Summary/Graph Section -->
  <div class="card summary-card">
    <div class="summary-grid">
      <div class="summary-item">
        <div class="summary-title">Participation Overview</div>
        <div class="summary-placeholder">กราฟ/สรุป</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">Event Participation Graph</div>
        <div class="summary-placeholder">กราฟ/สรุป</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">Attending</div>
        <div class="summary-placeholder">กราฟ/สรุป</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">Not Attending</div>
        <div class="summary-placeholder">กราฟ/สรุป</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">Pending</div>
        <div class="summary-placeholder">กราฟ/สรุป</div>
      </div>
    </div>
  </div>

  <!-- Employee Table Section -->
  <div class="card employee-card">
    <h2 class="table-title employee-title">Employee</h2>
    <div class="table-wrap">
      <table class="table compact employee-table">
        <thead>
          <tr>
            <th class="th col-idx">#</th>
            <th class="th col-id">รหัส</th>
            <th class="th col-prefix">คำนำหน้า</th>
            <th class="th col-name">ชื่อ</th>
            <th class="th col-last">นามสกุล</th>
            <th class="th col-nickname">ชื่อเล่น</th>
            <th class="th col-email">อีเมล</th>
            <th class="th col-phone">เบอร์โทร</th>
            <th class="th col-position">ตำแหน่ง</th>
            <th class="th col-department">แผนก</th>
            <th class="th col-team">ทีม</th>
            <!-- <th class="th col-status">สถานะ</th> -->
          </tr>
        </thead>
        <tbody>
          <tr v-for="(emp, i) in empPaged" :key="emp.id">
            <td class="col-idx">{{ (empPage-1)*empPageSize + i + 1 }}</td>
            <td class="col-id">{{ emp.emp_id }}</td>
            <td class="col-prefix">{{ emp.emp_prefix }}</td>
            <td class="col-name">{{ emp.emp_firstname }}</td>
            <td class="col-last">{{ emp.emp_lastname }}</td>
            <td class="col-nickname">{{ emp.emp_nickname }}</td>
            <td class="col-email"><span class="truncate">{{ emp.emp_email }}</span></td>
            <td class="col-phone">{{ emp.emp_phone }}</td>
            <td class="col-position"><span class="truncate">{{ emp.position }}</span></td>
            <td class="col-department"><span class="truncate">{{ emp.department }}</span></td>
            <td class="col-team"><span class="truncate">{{ emp.team }}</span></td>
          </tr>
          <tr v-if="employees.length === 0">
            <td :colspan="12" style="text-align:center">No data found</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="pager2">
      <button class="arrow-btn" :disabled="empPage===1" @click="goToEmpPage(empPage-1)">‹</button>
      <button class="page-btn active">{{ empPage }}</button>
      <button class="arrow-btn" :disabled="empPage===empTotalPages || empTotalPages===0" @click="goToEmpPage(empPage+1)">›</button>
    </div>
  </div>
</div>
</template>

<script>
import axios from "axios";
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  components: { MagnifyingGlassIcon },
  data() {
    return {
      event: [],
      categories: [],
      catMap: {},
      search: "",
      showFilter: false,
      showSort: false,
      sortBy: "evn_date",
      sortOrder: "asc",
      page: 1,
      pageSize: 10,
      employees: [],
      empPage: 1,
      empPageSize: 10,
      showEmpSort: false,
      empSort: { value: 'name_az' },
      empSortOptions: [
        { value: 'name_az', label: 'ชื่อพนักงาน A–Z' },
        { value: 'name_za', label: 'ชื่อพนักงาน Z–A' },
        { value: 'department_az', label: 'ชื่อแผนก A–Z' },
        { value: 'department_za', label: 'ชื่อแผนก Z–A' },
        { value: 'team_az', label: 'ชื่อทีม A–Z' },
        { value: 'team_za', label: 'ชื่อทีม Z–A' },
        { value: 'position_az', label: 'ชื่อตำแหน่ง A–Z' },
        { value: 'position_za', label: 'ชื่อตำแหน่ง Z–A' },
        { value: 'id_asc', label: 'รหัสพนักงาน น้อย–มาก' },
        { value: 'id_desc', label: 'รหัสพนักงาน มาก–น้อย' },
      ],
    };
  },
  async created() {
  await Promise.all([this.fetchEvent(), this.fetchCategories(), this.fetchEmployees()]);
  },
  watch: {
    search()   { this.page = 1; },
    pageSize() { this.page = 1; },
    event()    { this.page = 1; },
  },
  computed: {
    normalized() {
      return this.event.map(e => ({
        ...e,
        evn_title: e.evn_title ?? e.evn_name ?? "",
        evn_cat_id: e.evn_cat_id ?? e.evn_category_id ?? "",
        cat_name: e.cat_name ?? e.category_name ?? this.catMap[String(e.evn_cat_id)] ?? "",
        evn_date: e.evn_date ?? "",
        evn_timestart: e.evn_timestart ?? "",
        evn_timeend: e.evn_timeend ?? "",
        evn_num_guest: Number(e.evn_num_guest ?? 0),
        evn_sum_accept: Number(e.evn_sum_accept ?? 0),
        evn_status: e.evn_status ?? "",
      }));
    },
    filtered() {
  const q = this.search.toLowerCase().trim();
  return this.normalized.filter(e => {
    const status = String(e.evn_status || '').toLowerCase();
    const matchStatus = status === 'upcoming' || status === 'done';
    // ค้นหาจากหลายฟิลด์
    const match =
      e.evn_title.toLowerCase().includes(q) ||
      (e.evn_name && e.evn_name.toLowerCase().includes(q)) ||
      (e.cat_name && e.cat_name.toLowerCase().includes(q)) ||
      (e.evn_date && e.evn_date.toLowerCase().includes(q));
    return matchStatus && (!q || match);
  });
},
    sorted() {
      const key = this.sortBy;
      const order = this.sortOrder;
      const arr = [...this.filtered];
      const parseVal = v => {
        if (key.includes('date')) return new Date(v || 0).getTime();
        if (['evn_num_guest','evn_sum_accept'].includes(key)) return Number(v) || 0;
        return (v ?? '').toString().toLowerCase();
      };
      arr.sort((a,b) => {
        const va = parseVal(a[key]), vb = parseVal(b[key]);
        if (va < vb) return order === "asc" ? -1 : 1;
        if (va > vb) return order === "asc" ?  1 : -1;
        return 0;
      });
      return arr;
    },
    totalPages() { return Math.ceil(this.sorted.length / this.pageSize); },
    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    },
    pageItems() {
      const total = this.totalPages || 1;
      const cur = this.page;
      const items = [];
      if (total <= 7) { for (let i=1;i<=total;i++) items.push({ type:'page', value:i }); return items; }
      const addPage = p => items.push({ type:'page', value:p });
      const addDots = () => items.push({ type:'dots' });
      addPage(1);
      if (cur > 3) addDots();
      const s = Math.max(2, cur-1), e = Math.min(total-1, cur+1);
      for (let p=s; p<=e; p++) addPage(p);
      if (cur < total-2) addDots();
      addPage(total);
      return items;
    },
    empPaged() {
      let arr = [...this.employees];
      switch (this.empSort.value) {
        case 'name_az':
          arr.sort((a,b) => a.emp_firstname.localeCompare(b.emp_firstname)); break;
        case 'name_za':
          arr.sort((a,b) => b.emp_firstname.localeCompare(a.emp_firstname)); break;
        case 'department_az':
          arr.sort((a,b) => (a.department||'').localeCompare(b.department||'')); break;
        case 'department_za':
          arr.sort((a,b) => (b.department||'').localeCompare(a.department||'')); break;
        case 'team_az':
          arr.sort((a,b) => (a.team||'').localeCompare(b.team||'')); break;
        case 'team_za':
          arr.sort((a,b) => (b.team||'').localeCompare(a.team||'')); break;
        case 'position_az':
          arr.sort((a,b) => (a.position||'').localeCompare(b.position||'')); break;
        case 'position_za':
          arr.sort((a,b) => (b.position||'').localeCompare(a.position||'')); break;
        case 'id_asc':
          arr.sort((a,b) => (a.emp_id||'').localeCompare(b.emp_id||'')); break;
        case 'id_desc':
          arr.sort((a,b) => (b.emp_id||'').localeCompare(a.emp_id||'')); break;
      }
      const start = (this.empPage - 1) * this.empPageSize;
      return arr.slice(start, start + this.empPageSize);
    },
    empTotalPages() {
      return Math.ceil(this.employees.length / this.empPageSize);
    },
  },
  methods: {
    async fetchEmployees() {
      try {
        const res = await axios.get("/get-employees", {
          params: {
            page: this.empPage,
            per_page: this.empPageSize
          }
        });
        if (res.data && res.data.data) {
          this.employees = res.data.data.map(e => ({
            id: e.id,
            emp_id: e.emp_id,
            emp_prefix: e.emp_prefix,
            emp_firstname: e.emp_firstname,
            emp_lastname: e.emp_lastname,
            emp_nickname: e.emp_nickname,
            emp_email: e.emp_email,
            emp_phone: e.emp_phone,
            position: e.position_name,
            department: e.department_name,
            team: e.team_name,
            emp_delete_status: e.emp_delete_status,
          }));
          this.empTotal = res.data.total || this.employees.length;
        } else {
          this.employees = (res.data || []).map(e => ({
            id: e.id,
            emp_id: e.emp_id,
            emp_prefix: e.emp_prefix,
            emp_firstname: e.emp_firstname,
            emp_lastname: e.emp_lastname,
            emp_nickname: e.emp_nickname,
            emp_email: e.emp_email,
            emp_phone: e.emp_phone,
            position: e.position_name,
            department: e.department_name,
            team: e.team_name,
            emp_delete_status: e.emp_delete_status,
          }));
          this.empTotal = this.employees.length;
        }
      } catch (err) {
        console.error("fetchEmployees error", err);
        this.employees = [];
        this.empTotal = 0;
      }
    },
    async fetchEvent() {
      try {
        const res = await axios.get("/get-event", {
          params: {
            page: this.page,
            per_page: this.pageSize,
            search: this.search
          }
        });
        let allEvents = Array.isArray(res.data) ? res.data : (res.data?.data || []);
        this.event = allEvents.filter(ev => {
          const status = String(ev.evn_status || ev.status || '').toLowerCase();
          return status === 'upcoming' || status === 'done';
        });
        this.eventTotal = res.data.total || this.event.length;
      } catch (err) { 
        console.error("fetchEvent error", err); 
        this.event = []; 
        this.eventTotal = 0;
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get("/event-info");
        const cats = res.data?.categories || [];
        this.categories = cats;
        this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
      } catch (err) { console.error("fetchCategories error", err); this.categories = []; this.catMap = {}; }
    },

    goToPage(p) {
      if (p < 1) p = 1;
      if (p > this.totalPages) p = this.totalPages || 1;
      this.page = p;
    },

    editEvent(id) { //ส่วนส่ง id ไปให้หน้า edit_event
      console.log("Edit event ID:", id);
      this.$router.push(`/edit-event/${id}`)
    },
    // ไว้ให้ปุ่มไม่ error (ต่อยอดภายหลังได้)
    toggleFilter() { this.showFilter = !this.showFilter; },
    toggleSort() { this.showSort = !this.showSort; },

    goToPage(p) { if (p < 1) p = 1; if (p > this.totalPages) p = this.totalPages || 1; this.page = p; },
    async deleteEvent(id) {
      if (confirm("Delete?")) {
        try { await axios.delete(`/event/${id}`); this.fetchEvent(); }
        catch (err) { console.error("Error deleting event", err); }
      }
    }
    ,
    formatDate(val) {
      if (!val) return 'N/A';
      const d = new Date(val); if (isNaN(d)) return val;
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
    goToEmpPage(p) {
      if (p < 1) p = 1;
      if (p > this.empTotalPages) p = this.empTotalPages || 1;
      this.empPage = p;
    },
    setEmpSort(value) {
      this.showEmpSort = false;
      const order = value.startsWith('-') ? 'desc' : 'asc';
      const key = value.replace(/^-/, '');
      this.empSort = { value: key, order };
      this.empPage = 1;
    },
  }
};
</script>

<style>
/* Employee Table Styling */
.employee-table {
  margin-top: 0.5rem;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 8px rgba(34,197,94,0.06);
}
.employee-table th {
  background: none;
  color: #000000ff;
  font-weight: 700;
  font-size: 15px;
  border-bottom: 2px solid #b9b9b98e;
  padding: 10px 8px;
}
.employee-table td {
  padding: 8px 8px;
  font-size: 14px;
  background: #fff;
  border-bottom: 1px solid #f3f4f6;
}
.employee-table tr:nth-child(even) td {
  background: #f6fef9;
}
.employee-table .truncate {
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: inline-block;
}
.employee-table .badge {
  min-width: 70px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: lowercase;
  background: #e5e7eb;
  color: #374151;
}
.employee-table .badge.enabled {
  background: #dcfce7;
  color: #166534;
}
.employee-table .badge.deleted {
  background: #fee2e2;
  color: #991b1b;
}
/* Layout grid สำหรับ dashboard */
.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  width: 100%;
}
/* Card สำหรับ Event */
.event-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
/* Card สำหรับ Summary/Graph */
.summary-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}
.summary-item {
  background: #f9fafb;
  border-radius: 14px;
  padding: 1.2rem 1rem;
  box-shadow: 0 1px 6px rgba(0,0,0,0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 120px;
}
.summary-title {
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 0.7rem;
  color: #e11d48;
}
.summary-placeholder {
  color: #bbb;
  font-size: 1.2rem;
  font-style: italic;
}
.employee-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
/* Divider ระหว่าง Card */
.card-divider {
  width: 100%;
  height: 48px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e0e7ef 100%);
  border-radius: 18px;
  margin: 2.5rem 0 2.5rem 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
/* Card สำหรับ Employee */
.employee-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
  margin-top: 2.5rem;
}
.employee-title {
  font-size: 2rem;
  font-weight: 700;
  color: #22c55e;
  margin-bottom: 1rem;
  letter-spacing: 0.03em;
  text-align: left;
  background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
/* สไตล์หัวข้อ Event ด้านบนตาราง */
.event-title {
  font-size: 2rem;
  font-weight: 700;
  color: #f43f5e;
  margin-bottom: 1rem;
  letter-spacing: 0.03em;
  text-align: left;
  background: linear-gradient(90deg, #f43f5e 0%, #f87171 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
/* ปุ่มใหม่สำหรับ View Report และ Export */
.custom-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  padding: 0.5em 1.2em;
  border-radius: 999px;
  font-weight: 500;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.custom-btn .icon {
  width: 1.2em;
  height: 1.2em;
  vertical-align: middle;
}
.report-btn {
  background: linear-gradient(90deg, #f43f5e 0%, #f87171 100%);
  color: #fff;
  margin-right: 0.5em;
}
.report-btn:hover {
  background: linear-gradient(90deg, #be185d 0%, #f43f5e 100%);
  box-shadow: 0 4px 16px rgba(244,63,94,0.12);
  transform: translateY(-2px) scale(1.03);
}
.export-btn {
  background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
  color: #fff;
}
.export-btn:hover {
  background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
  box-shadow: 0 4px 16px rgba(34,197,94,0.12);
  transform: translateY(-2px) scale(1.03);
}
/* ตัวช่วย layout */
.toolbar--pill { display:flex; align-items:center; gap:16px; margin-top:12px; }
.search-group { display:flex; align-items:center; gap:12px; flex:1; }

/* ช่องค้นหาแบบ pill */
.pill-input {
  height: 44px;
  width: 100%;
  padding: 0 16px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  outline: none;
  background: #fff;
}

/* ปุ่มไอคอน */
.icon-btn {
  width: 44px; height: 44px; border-radius: 999px;
  border: 1px solid #e5e7eb; background: #fff;
  display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.icon-btn:hover { background:#f3f4f6; }
.icon-btn--solid { background:#e11d48; color:#fff; border:none; }
.icon-btn--solid:hover { background:#be123c; }

.icon { width:20px; height:20px; display:block; }
.icon-sm { width:18px; height:18px; }

/* ปุ่มตัวอักษร Filter/Sort */
.text-btn {
  height:44px; padding:0 8px; border:none; background:transparent;
  color:#334155; font-weight:500; display:inline-flex; align-items:center; gap:8px; cursor:pointer;
}
.text-btn:hover { color:#0f172a; }

/* ปุ่ม + Add New เป็น pill และชิดขวา */
.pill-add {
  margin-left:auto; height:44px; padding:0 18px; border-radius:999px;
  background:#e11d48; color:#fff; text-decoration:none; display:inline-flex; align-items:center; font-weight:600;
}
.pill-add:hover { background:#be123c; }

/* Summary */
.summary { font-size:12px; color:#666; margin-left:16px; }

/* Table */
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; margin-top:10px; }
thead th { position:sticky; top:0; z-index:1; }
.th { cursor:default; user-select:none; background:#f9fafb; font-weight:600; border-bottom:1px solid #e5e7eb; }
th, td { vertical-align:middle; padding:7px 10px; border-top:1px solid #eee; font-size:14px; }

/* Column widths */
.col-idx{ width:48px; text-align:center; }
.col-title{ width:26%; text-align:center; }
.col-cat{ width:14%; text-align:center; }
.col-date{ width:110px; text-align:center; white-space:nowrap; }
.col-time{ width:92px; text-align:center; white-space:nowrap; }
.col-num{ width:80px; text-align:center; }
.col-status{ width:110px; text-align:center; }
.col-action{ width:120px; text-align:center; }

/* Rows */
tbody tr:nth-child(odd){ background:#fff; }
tbody tr:nth-child(even){ background:#fafafa; }
tbody tr:hover{ background:#f3f4f6; }

/* Text overflow */
.truncate{ display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Action buttons */
.btn-link{ background:transparent; border:none; color:#0ea5e9; font-weight:600; cursor:pointer; padding:2px 4px; font-size:13px; }
.btn-link:hover{ text-decoration:underline; }
.btn-link.danger{ color:#ef4444; }

/* Badge */
.badge { display:inline-block; min-width:70px; padding:3px 8px; border-radius:999px; font-size:12px; font-weight:700; text-transform:lowercase; background:#e5e7eb; color:#374151; }
.badge.deleted{ background:#fee2e2; color:#991b1b; }
.badge.done{ background:#dcfce7; color:#166534; }
.badge.upcoming{ background:#f4ce99; color:#714601; }

/* Pager */
.pager2 { display:flex; gap:.5rem; align-items:center; justify-content:center; margin-top:14px; }
.page-btn { min-width:36px; height:36px; padding:0 10px; border-radius:10px; border:2px solid #b71c1c; background:transparent; color:#b71c1c; font-weight:700; line-height:1; }
.page-btn.active { background:#b71c1c; color:#fff; border-color:#b71c1c; }
.page-btn:hover:not(.active){ background:#fff5f5; }
.arrow-btn { width:36px; height:36px; border-radius:10px; border:none; background:#b71c1c; color:#fff; font-weight:700; }
.arrow-btn:disabled{ opacity:.5; cursor:not-allowed; }
.dots{ padding:0 6px; color:#b71c1c; font-weight:700; }

.employee-table th.col-idx,
.employee-table th.col-id,
.employee-table th.col-phone {
  text-align: center;
}
.employee-table th.col-prefix,
.employee-table th.col-name,
.employee-table th.col-last,
.employee-table th.col-nickname,
.employee-table th.col-email,
.employee-table th.col-position,
.employee-table th.col-department,
.employee-table th.col-team {
  text-align: left;
}
.employee-table td.col-idx,
.employee-table td.col-id,
.employee-table td.col-phone {
  text-align: center;
}
.employee-table td.col-prefix,
.employee-table td.col-name,
.employee-table td.col-last,
.employee-table td.col-nickname,
.employee-table td.col-email,
.employee-table td.col-position,
.employee-table td.col-department,
.employee-table td.col-team {
  text-align: left;
}

/* Sort dropdown */
.sort-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 100;
  width: 200px;
  margin-top: 4px;
}
.sort-title {
  font-weight: 600;
  padding: 10px;
  font-size: 14px;
  color: #111;
  border-bottom: 1px solid #f3f4f6;
}
.sort-item {
  display: block;
  padding: 10px;
  font-size: 14px;
  color: #333;
  cursor: pointer;
  transition: background 0.2s;
}
.sort-item:hover {
  background: #f3f4f6;
}
.sort-item.active {
  background: #e0f2fe;
  color: #0c4a6e;
  font-weight: 500;
}

/* Sort dropdown for employee table */
.sort-dropdown {
  position: absolute;
  top: 44px;
  left: 0;
  min-width: 220px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.10);
  padding: 8px 0;
  z-index: 10;
}
.sort-title {
  font-weight: 700;
  color: #b71c1c;
  padding: 8px 16px 4px 16px;
  font-size: 15px;
}
.sort-item {
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  padding: 8px 16px;
  font-size: 15px;
  color: #222;
  cursor: pointer;
}
.sort-item.active {
  background: #fee2e2;
  color: #b71c1c;
  font-weight: 700;
}
.sort-item:hover:not(.active) {
  background: #f9fafb;
}
</style>
