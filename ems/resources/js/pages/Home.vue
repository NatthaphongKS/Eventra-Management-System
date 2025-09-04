<!-- pages/event_page.vue -->
<template>

  <h2 class="table-title event-title">Event</h2>
  <!-- ===== Toolbar  ===== -->
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

       <!-- ปุ่ม Sort (ตอนนี้ยัง UI เฉยๆ) -->
    <button type="button" class="text-btn" @click="toggleSort">
      <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
      </svg>
      <span>Sort</span>
    </button>

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
  <!-- ↑↑ ปิดทูลบาร์ตรงนี้ให้เรียบร้อย ↑↑ -->
  <!-- ===== Table ===== -->
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


  <!-- ===== Pagination ===== -->
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

      // ไว้ให้ปุ่ม Filter/Sort ไม่ error (ยังไม่ทำ logic จริง)
      showFilter: false,
      showSort: false,

      sortBy: "evn_date",
      sortOrder: "asc",

      page: 1,
      pageSize: 10,
    };
  },
  async created() {
    await Promise.all([this.fetchEvent(), this.fetchCategories()]);
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
  },
  methods: {
    async fetchEvent() {
      try {
        const res = await axios.get("/get-event");
        let allEvents = Array.isArray(res.data) ? res.data : (res.data?.data || []);
        // filter เฉพาะสถานะ upcoming หรือ done
        this.event = allEvents.filter(ev => {
          const status = String(ev.evn_status || ev.status || '').toLowerCase();
          return status === 'upcoming' || status === 'done';
        });
      } catch (err) { console.error("fetchEvent error", err); this.event = []; }
    },
    async fetchCategories() {
      try {
        const res = await axios.get("/event-info");
        const cats = res.data?.categories || [];
        this.categories = cats;
        this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
      } catch (err) { console.error("fetchCategories error", err); this.categories = []; this.catMap = {}; }
    },

  // ลบ applySearch เพราะใช้ real-time search แล้ว

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
    },
    formatDate(val) {
      if (!val) return 'N/A';
      const d = new Date(val); if (isNaN(d)) return val;
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
  }
};
</script>

<style>
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
</style>
