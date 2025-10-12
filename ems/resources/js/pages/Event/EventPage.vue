<template>
  <!-- Toolbar and table (unchanged) -->
  <div class="mt-3 mb-1 flex items-center gap-4">
    <div class="flex items-center gap-3 flex-1">
      <input
        v-model.trim="searchInput"
        placeholder="Search"
        @keyup.enter="applySearch"
        class="h-11 w-[750px] rounded-full border border-slate-200 bg-white px-3 outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200"
      />
      <button
        type="button"
        class="inline-flex h-11 w-11 items-center justify-center rounded-full
         bg-[#b91c1c] text-white hover:bg-[#991b1b]
         focus:outline-none focus:ring-2 focus:ring-red-300 cursor-default cursor-default select-none."
        @click="applySearch"
        aria-label="Search"
        title="ค้นหา (คลิกหรือกด Enter)"
      >
        <MagnifyingGlassIcon class="h-5 w-5" />
      </button>
    </div>

    <button
      type="button"
      class="inline-flex h-11 items-center gap-2 px-2 text-slate-700 font-medium hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-200 rounded-md cursor-default select-none"
      @click="toggleFilter"
    >
      <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="4" y1="7" x2="20" y2="7" />
        <line x1="6" y1="12" x2="16" y2="12" />
        <line x1="8" y1="17" x2="12" y2="17" />
      </svg>
      <span>Filter</span>
    </button>

    <button
      type="button"
      class="inline-flex h-11 items-center gap-2 px-2 text-slate-700 font-medium hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-200 rounded-md cursor-default select-none "
      @click="toggleSort"
    >
      <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"  >
        <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
      </svg>
      <span>Sort</span>
    </button>

    <span class="ml-4 text-xs text-slate-500 cursor-default select-none">ทั้งหมด {{ filtered.length }} รายการ</span>
    <router-link
      to="/add-event"
      class="ml-auto inline-flex h-11 items-center rounded-full cursor-default select-none
         bg-[#b91c1c] px-4 font-semibold text-white
         hover:bg-[#991b1b] focus:outline-none
         focus:ring-2 focus:ring-red-300"
         >
      + Add New
    </router-link>

    <div v-show="showFilter" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
      <div class="grid gap-3 sm:grid-cols-3">
        <label class="text-sm">
          <span class="mb-1 block text-slate-600">Category</span>
          <select v-model="flt.category" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
            <option value="all">All</option>
            <option v-for="c in categories" :key="c.id" :value="String(c.id)">
              {{ c.cat_name }}
            </option>
          </select>
        </label>

        <label class="text-sm">
          <span class="mb-1 block text-slate-600">Status</span>
          <select v-model="flt.status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
            <option value="upcoming">Upcoming</option>
            <option value="done">Done</option>
          </select>
        </label>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <button type="button"
                class="rounded-full bg-[#b91c1c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#991b1b]"
                @click="applyFilters">
          Apply
        </button>
        <button type="button"
                class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-white"
                @click="clearFilters">
          Clear
        </button>

        <template v-if="hasActiveFilters">
          <span class="ml-2 text-xs text-slate-500">Active:</span>
          <span v-if="flt.category!=='all'" class="rounded-full bg-white px-2.5 py-1 text-xs ring-1 ring-slate-200">
            Category: {{ catMap[flt.category] || flt.category }}
          </span>
          <span v-if="flt.status!=='all'" class="rounded-full bg-white px-2.5 py-1 text-xs ring-1 ring-slate-200">
            Status: {{ flt.status }}
          </span>
        </template>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="overflow-hidden rounded-2xl cursor-default select-none border border-slate-200 mb-8">
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-slate-50">
          <th class="table-head cursor-default select-none w-12 text-center py-3">#</th>
          <th class="table-head cursor-default select-none w-[26%] text-center py-3">Event</th>
          <th class="table-head cursor-default select-none w-[14%] text-center py-3">Category</th>
          <th class="table-head cursor-default select-none w-[110px] text-center whitespace-nowrap py-3">Date (D/M/Y)</th>
          <th class="table-head cursor-default select-none w-[92px] text-center whitespace-nowrap py-3">Time</th>
          <th class="table-head cursor-default select-none w-20 text-center py-3">Invited</th>
          <th class="table-head cursor-default select-none w-20 text-center py-3">Accepted</th>
          <th class="table-head cursor-default select-none w-[110px] text-center py-3">Status</th>
          <th class="table-head cursor-default select-none w-28 text-center py-3">Action</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(ev, i) in paged"
          :key="ev.id"
          class="odd:bg-white even:bg-slate-50 hover:bg-slate-100"
        >
          <td class="px-2 py-2 text-center text-sm text-slate-700 cursor-default select-none border-t border-slate-200">
            {{ (page-1)*pageSize + i + 1 }}
          </td>

          <td class="px-3 py-2 text-center border-t cursor-default select-none border-slate-200">
            <span class="block truncate text-sm text-slate-800">{{ ev.evn_title || 'N/A' }}</span>
          </td>

          <td class="px-3 py-2 text-center border-t cursor-default select-none border-slate-200">
            <span class="block truncate text-sm text-slate-800">{{ ev.cat_name || 'N/A' }}</span>
          </td>

          <td class="px-3 py-2 text-center text-sm text-slate-700 cursor-default select-none border-t border-slate-200">
            {{ formatDate(ev.evn_date) }}
          </td>

          <td class="px-3 py-2 text-center text-sm text-slate-700 border-t cursor-default select-none border-slate-200 whitespace-nowrap">
            {{ ev.evn_timestart ? ev.evn_timestart.slice(0,5) : '??:??' }} -
            {{ ev.evn_timeend ? ev.evn_timeend.slice(0,5) : '??:??' }}
          </td>

          <td class="px-3 py-2 text-center text-sm text-slate-700 border-t cursor-default select-none border-slate-200">
            {{ ev.evn_num_guest ?? '0' }}
          </td>

          <td class="px-3 py-2 text-center text-sm text-slate-700 border-t cursor-default select-none border-slate-200">
            {{ ev.evn_sum_accept ?? 'N/A' }}
          </td>

          <td class="px-3 py-2 text-center border-t cursor-default select-none border-slate-200">
            <span :class="badgeClass(ev.evn_status)">
              {{ ev.evn_status || 'N/A' }}
            </span>
          </td>

          <!-- Actions -->
          <td class="px-3 py-2 text-center border-t border-slate-200">
            <div class="flex items-center justify-center gap-1.5">
              <button
                @click.stop="editEvent(ev.id)"
                aria-label="Edit"
                class="rounded-lg p-1.5 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-200"
                title="Edit"
              >
                <PencilIcon class="h-4 w-4 text-slate-600" />
              </button>

              <button
                @click.stop="deleteEvent(ev.id)"
                aria-label="Delete"
                class="rounded-lg p-1.5 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-200"
                title="Delete"
              >
                <TrashIcon class="h-5 w-5 text-slate-600 hover:text-rose-600" />
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="paged.length === 0">
          <td :colspan="9" class="px-3 py-6 text-center text-slate-600">
            No data found
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-4 flex items-center justify-center gap-3">
    <button
      class="pg-arrow"
      :disabled="page===1"
      @click="goToPage(page-1)"
    >
      <svg viewBox="0 0 24 24">
        <path d="M6 12 L18 4 L18 20 Z" />
      </svg>
    </button>

    <template v-for="(it, idx) in pageItems" :key="idx">
      <button
        v-if="it.type==='page'"
        class="pg-num"
        :class="{ 'pg-active': it.value===page }"
        :aria-current="it.value===page ? 'page' : null"
        @click="goToPage(it.value)"
      >
        {{ it.value }}
      </button>

      <span v-else class="pg-ellipsis">
        <i class="dot"></i><i class="dot"></i><i class="dot"></i>
      </span>
    </template>

    <button
      class="pg-arrow"
      :disabled="page===totalPages || totalPages===0"
      @click="goToPage(page+1)"
    >
      <svg viewBox="0 0 24 24" style="transform: scaleX(-1)">
        <path d="M6 12 L18 4 L18 20 Z" />
      </svg>
    </button>
  </div>

  <!-- ConfirmDelete component (teleported to body) -->
  <ConfirmDelete
    :open="confirmOpen"
    :title="confirmTitle"
    @confirm="onConfirmDelete"
    @cancel="onCancelDelete"
  />
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import 'sweetalert2/dist/sweetalert2.min.css';

// components
import ConfirmDelete from '@/components/ConfirmDelete.vue';

import {
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  components: { MagnifyingGlassIcon, PencilIcon, TrashIcon, ConfirmDelete },
  data() {
    return {
      event: [],
      categories: [],
      catMap: {},

      searchInput: "",
      search: "",

      showFilter: false,
      showSort: false,

      sortBy: "evn_date",
      sortOrder: "asc",

      page: 1,
      pageSize: 10,

      flt: {
        category: 'all',
        status: 'all',
        dateFrom: '',
        dateTo: ''
      },
      _appliedFlt: null,

      /* confirmation modal state */
      confirmOpen: false,
      confirmId: null,
      confirmTitle: '',
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
    hasActiveFilters() {
      const f = this._appliedFlt || this.flt;
      return f.category!=='all' || f.status!=='all' || !!(f.dateFrom || f.dateTo);
    },
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
      return this.normalized.filter(e =>
        !q || `${e.evn_title} ${e.cat_name} ${e.evn_date} ${e.evn_timestart} ${e.evn_timeend} ${e.evn_status}`.toLowerCase().includes(q)
      );
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
        this.event = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (err) {
        console.error("fetchEvent error", err);
        this.event = [];
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get("/event-info");
        const cats = res.data?.categories || [];
        this.categories = cats;
        this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
      } catch (err) {
        console.error("fetchCategories error", err);
        this.categories = [];
        this.catMap = {};
      }
    },

    applySearch() { this.search = this.searchInput; this.page = 1; },

    editEvent(id) {
      // ส่วนส่ง id ไปให้หน้า edit_event
      this.$router.push(`/edit-event/${id}`);
    },

    toggleFilter() { this.showFilter = !this.showFilter; },
    toggleSort() { this.showSort = !this.showSort; },
    goToPage(p) { if (p < 1) p = 1; if (p > this.totalPages) p = this.totalPages || 1; this.page = p; },

    /* open confirm modal (แทนการเรียก Swal ตรงนี้) */
    deleteEvent(id) {
      const ev = this.normalized.find(e => e.id === id);
      this.confirmId = id;
      this.confirmTitle = ev?.evn_title || 'this event';
      this.confirmOpen = true;
    },

    /* cancel from modal */
    onCancelDelete() {
      this.confirmOpen = false;
      this.confirmId = null;
      this.confirmTitle = '';
    },

    /* confirmed: call API to delete */
    async onConfirmDelete() {
      const id = this.confirmId;
      this.confirmOpen = false;
      this.confirmId = null;
      this.confirmTitle = '';

      try {
        await axios.patch(`/event/${id}/deleted`);
        await Swal.fire({
          title: 'DELETE SUCCESS!',
          text: 'We have deleted the event.',
          icon: 'success',
          confirmButtonText: 'OK',
          confirmButtonColor: '#42BB4C'
        });
        this.fetchEvent();
      } catch (err) {
        console.error("Error deleting event", err);
        await Swal.fire({
          title: 'ERROR!',
          text: err?.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล',
          icon: 'error',
          confirmButtonText: 'OK',
          confirmButtonColor: '#42BB4C'
        });
      }
    },

    formatDate(val) {
      if (!val) return 'N/A';
      const d = new Date(val);
      if (isNaN(d)) return val;
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },

    badgeClass(status) {
      // สร้าง badge แบบยูทิลิตี้ล้วน
      const base = 'inline-block min-w-[70px] rounded-full px-2.5 py-1 text-xs font-bold capitalize';
      switch ((status || '').toLowerCase()) {
        case 'done':     return `${base} bg-emerald-100 text-emerald-700`;
        case 'upcoming': return `${base} bg-amber-200 text-amber-900`;
        default:         return `${base} bg-slate-200 text-slate-700`;
      }
    },
  }
};
</script>
