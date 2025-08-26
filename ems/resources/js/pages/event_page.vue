<!-- pages/event_page.vue -->
<template>
  <router-link to="/add-event" class="btn-red"> + Add Event </router-link>

  <!-- Controls -->
  <div class="toolbar">
    <input v-model.trim="search" class="input" placeholder="Search" />
    <div class="page-size">
      <span>แสดง</span>
      <select v-model.number="pageSize" class="select">
        <option v-for="s in [10,25,50,100]" :key="s" :value="s">{{ s }}</option>
      </select>
      <span>แถว</span>
    </div>
    <span class="summary">ทั้งหมด {{ filtered.length }} รายการ</span>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table class="table compact">
      <thead>
        <tr>
          <th class="th col-idx">#</th>
          <th class="th sortable col-title" @click="setSort('evn_title')">
            Event <span v-if="sortBy==='evn_title'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th sortable col-cat" @click="setSort('cat_name')">
            Category <span v-if="sortBy==='cat_name'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th sortable col-date" @click="setSort('evn_date')">
            Date (D/M/Y) <span v-if="sortBy==='evn_date'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th sortable col-time" @click="setSort('evn_timestart')">
            Time
          </th>
          <th class="th sortable col-num" @click="setSort('evn_num_guest')">
            Invited <span v-if="sortBy==='evn_num_guest'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th sortable col-num" @click="setSort('evn_sum_accept')">
            Accepted <span v-if="sortBy==='evn_sum_accept'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th sortable col-status" @click="setSort('evn_status')">
            Status <span v-if="sortBy==='evn_status'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
          </th>
          <th class="th col-action">Action</th>
        </tr>
      </thead>

    <!-- วน loop แสดง event -->
      <tbody>
        <tr v-for="(ev, i) in paged" :key="ev.id">
          <td class="col-idx">{{ (page-1)*pageSize + i + 1 }}</td>
          <td class="col-title"><span class="truncate">{{ ev.evn_title || 'N/A' }}</span></td>
          <td class="col-cat"><span class="truncate">{{ ev.cat_name || 'N/A' }}</span></td>
          <td class="col-date">{{ formatDate(ev.evn_date) }}</td>
          <!-- Show Time start - end -->
          <td class="col-time">
            {{ ev.evn_timestart ? ev.evn_timestart.slice(0,5) : '??:??' }}
            -
            {{ ev.evn_timeend ? ev.evn_timeend.slice(0,5) : '??:??' }}
          </td>
          <!--End Show Time start - end -->
          <td class="col-num">{{ ev.evn_num_guest ?? '0' }}</td>
          <td class="col-num">{{ ev.evn_sum_accept ?? '0' }}</td>
          <td class="col-status">
            <span :class="['badge', ev.evn_status]">{{ ev.evn_status || 'N/A' }}</span>
          </td>
          <td class="col-action">
            <button class="btn-link" @click="editEvent(ev.id)">✏ Edit</button>
            <button class="btn-link danger" @click="deleteEvent(ev.id)">🗑 Delete</button>
          </td>
        </tr>
        <!-- ถ้าไม่มีข้อมูล -->
        <tr v-if="paged.length === 0">
          <td :colspan="9" style="text-align:center">No data found</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination (red, with dots) V.1 -->
  <div class="pager2">
    <button class="arrow-btn" :disabled="page===1" @click="goToPage(page-1)">‹</button>

    <template v-for="(it, idx) in pageItems" :key="idx">
      <button
        v-if="it.type==='page'"
        class="page-btn"
        :class="{ active: it.value===page }"
        @click="goToPage(it.value)"
      >
        {{ it.value }}
      </button>
      <span v-else class="dots">…</span>
    </template>

    <button class="arrow-btn" :disabled="page===totalPages || totalPages===0" @click="goToPage(page+1)">›</button>
  </div>
</template>

<!-- ดึงข้อมูล event จาก API -->
<script>
import axios from "axios";
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  data() {
    return {
      event: [], // เก็บข้อมูล event
      search: "", // ข้อความค้นหา
      catMap: {}, // แมพ id → ชื่อ category
      sortBy: "evn_date",// state การ sort
      sortDir: "asc", // state การ sort
      page: 1, // state การแบ่งหน้า
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
        evn_title: e.evn_title ?? "",
        evn_cat_id: e.evn_cat_id ?? "",
        cat_name: e.cat_name ?? e.category_name ?? this.catMap[e.evn_cat_id] ?? "",
        evn_date: e.evn_date ?? "",
        evn_timestart: e.evn_timestart ?? "",
        evn_timeend: e.evn_timeend ?? "",
        evn_num_guest: Number(e.evn_num_guest ?? 0),
        evn_sum_accept: Number(e.evn_sum_accept ?? 0),
        evn_status: e.evn_status ?? "",
      }));
    },

    filtered() {
      if (!this.search) return this.normalized;
      const q = this.search.toLowerCase();
      return this.normalized.filter(e =>
        `${e.evn_title} ${e.cat_name} ${e.evn_date} ${e.evn_timestart} ${e.evn_timeend} ${e.evn_num_guest} ${e.evn_sum_accept} ${e.evn_status}`
          .toLowerCase()
          .includes(q)
      );
    },

    sorted() {
      const arr = [...this.filtered];
      const key = this.sortBy;
      arr.sort((a, b) => {
        const parseVal = (v) => {
          if (key.includes('date')) return new Date(v || 0).getTime();
          if (['evn_num_guest', 'evn_sum_accept'].includes(key)) return Number(v) || 0;
          return (v ?? '').toString().toLowerCase();
        };
        const va = parseVal(a[key]);
        const vb = parseVal(b[key]);
        if (va < vb) return this.sortDir === "asc" ? -1 : 1;
        if (va > vb) return this.sortDir === "asc" ? 1 : -1;
        return 0;
      });
      return arr;
    },

    totalPages() {
      return Math.ceil(this.sorted.length / this.pageSize);
    },

    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    },

    pageItems() {
      const total = this.totalPages || 1;
      const cur = this.page;
      const items = [];
      if (total <= 7) {
        for (let i = 1; i <= total; i++) items.push({ type: 'page', value: i });
        return items;
      }
      const addPage = (p) => items.push({ type: 'page', value: p });
      const addDots = () => items.push({ type: 'dots' });

      addPage(1);
      if (cur > 3) addDots();

      const start = Math.max(2, cur - 1);
      const end   = Math.min(total - 1, cur + 1);
      for (let p = start; p <= end; p++) addPage(p);

      if (cur < total - 2) addDots();
      addPage(total);
      return items;
    },
  },

  methods: {
    async fetchEvent() {
      try {
        const res = await axios.get("/get-event");
        this.event = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (err) {
        console.error("fetchEvent error", err);
        this.event = [];
      }
    },

    async fetchCategories() {
      try {
        const res = await axios.get('/event-info');
        const cats = res.data?.categories || [];
        this.catMap = Object.fromEntries(cats.map(c => [c.id, c.cat_name]));
      } catch (e) {
        console.error('fetchCategories error', e);
        this.catMap = {};
      }
    },

    setSort(field) {
      if (this.sortBy === field) {
        this.sortDir = this.sortDir === "asc" ? "desc" : "asc";
      } else {
        this.sortBy = field;
        this.sortDir = "asc";
      }
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

    async deleteEvent(id) {
      if (confirm("Are you sure you want to delete this event?")) {
        try {
          await axios.delete(`/event/${id}`);
          this.fetchEvent();
        } catch (err) {
          console.error("Error deleting event", err);
        }
      }
    },

    formatDate(val) {
      if (!val) return 'N/A';
      const d = new Date(val);
      if (isNaN(d)) return val;
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth()+1).padStart(2, '0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
  },
};
</script>

<style>
.btn-red {
  background: red;
  color: white;
  padding: 6px 12px;
  text-decoration: none;
  border-radius: 4px;
}

/* Controls */
.toolbar { display: flex; gap: .75rem; align-items: center; margin-top: 12px; }
.input { border: 1px solid #ddd; padding: 6px 10px; border-radius: 6px; width: 100%; }
.select { border: 1px solid #ddd; padding: 6px 8px; border-radius: 6px; }
.page-size { display: flex; align-items: center; gap: .5rem; }
.summary { margin-left: auto; font-size: 12px; color: #666; }

/* Table */
.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; margin-top: 10px; }
thead th { position: sticky; top: 0; z-index: 1; }
.th { cursor: default; user-select: none; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
.sortable { cursor: pointer; }
th, td { vertical-align: middle; padding: 7px 10px; border-top: 1px solid #eee; font-size: 14px; }

/* Column widths */
.col-idx    { width: 48px;  text-align: center; }
.col-title  { width: 26%; text-align: center;}
.col-cat    { width: 14%; text-align: center;}
.col-date   { width: 110px; text-align: center; white-space: nowrap; }
.col-time   { width: 92px; text-align: center; white-space: nowrap; }
.col-num    { width: 80px; text-align: center; }
.col-status { width: 110px; text-align: center; }
.col-action { width: 120px; text-align: center; }

/* Row striping */
tbody tr:nth-child(odd)  { background: #fff; }
tbody tr:nth-child(even) { background: #fafafa; }
tbody tr:hover { background: #f3f4f6; }

/* Truncate */
.truncate { display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Action buttons */
.btn-link { background: transparent; border: none; color: #0ea5e9; font-weight: 600; cursor: pointer; padding: 2px 4px; font-size: 13px; }
.btn-link:hover { text-decoration: underline; }
.btn-link.danger { color: #ef4444; }

/* Badge */
.badge {
  display: inline-block;
  min-width: 70px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: lowercase;
  background: #e5e7eb;
  color: #374151;
}
/* สีช่อง evn_status */
.badge.scheduled  { background: #e0f2fe; color: #075985; }
.badge.canceled   { background: #fee2e2; color: #991b1b; }
.badge.completed  { background: #dcfce7; color: #166534; }
.badge.ongoing    { background: #f4ce99; color: #714601; }

/* Pager (red + dots) */
.pager2 { display: flex; gap: .5rem; align-items: center; justify-content: center; margin-top: 14px; }
.page-btn { min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; border: 2px solid #b71c1c; background: transparent; color: #b71c1c; font-weight: 700; line-height: 1; }
.page-btn.active { background: #b71c1c; color: #fff; border-color: #b71c1c; }
.page-btn:hover:not(.active) { background: #fff5f5; }
.arrow-btn { width: 36px; height: 36px; border-radius: 10px; border: none; background: #b71c1c; color: #fff; font-weight: 700; }
.arrow-btn:disabled { opacity: .5; cursor: not-allowed; }
.dots { padding: 0 6px; color: #b71c1c; font-weight: 700; }
</style>
