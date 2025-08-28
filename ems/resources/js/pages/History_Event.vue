<template>
  <!-- Toolbar -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Search -->
    <div class="flex w-full items-center gap-2 sm:max-w-xl">
      <div class="relative flex-1">
        <input
          v-model="query"
          type="text"
          placeholder="Search..."
          class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm outline-none transition focus:border-rose-300 focus:bg-white"
        />
      </div>
      <button
        class="inline-flex items-center justify-center rounded-full bg-rose-600 p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="onSearch"
        aria-label="search"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
        </svg>
      </button>
    </div>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <div class="relative" @keydown.escape="sortOpen = false">
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="sortOpen = !sortOpen"
          :title="sortDir === 'desc' ? 'วันที่ลบล่าสุด' : 'วันที่ลบเก่าสุด'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
          </svg>
          <span>Sort</span>
          <span class="text-gray-400">({{ sortDir === 'desc' ? 'ลบล่าสุด' : 'ลบเก่าสุด' }})</span>
        </button>

        <div v-if="sortOpen" class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg">
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-rose-50" @click="applySort('desc')">วันที่ลบล่าสุด</button>
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-rose-50" @click="applySort('asc')">วันที่ลบเก่าสุด</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-200">
    <table class="min-w-full table-fixed">
      <colgroup>
        <col class="w-14" />
        <col class="w-[28%]" />
        <col class="w-[14%]" />
        <col class="w-[14%]" />
        <col class="w-[14%]" />
        <col class="w-[12%]" />
        <col class="w-[12%]" />
        <col class="w-[14%]" />
      </colgroup>

      <thead class="bg-gray-100 sticky top-0 z-10">
        <tr class="text-gray-700 text-sm font-semibold">
          <th class="px-4 py-3 text-left">#</th>
          <th class="px-4 py-3 text-left">Event</th>
          <th class="px-4 py-3 text-left">Category</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Date (D/M/Y)</th>
          <th class="px-4 py-3 text-left">Time</th>
          <th class="px-4 py-3 text-left">Created by</th>
          <th class="px-4 py-3 text-left">Deleted by</th>
          <th class="px-4 py-3 text-left whitespace-nowrap">Deleted Date</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(ev, idx) in pagedRows"
          :key="ev.id ?? idx"
          class="text-sm text-gray-700 hover:bg-rose-50"
        >
          <td class="px-4 py-3">{{ startIndex + idx + 1 }}</td>
          <td class="px-4 py-3">
            <span class="block truncate">{{ ev.evn_title || 'N/A' }}</span>
          </td>
          <td class="px-4 py-3">{{ ev.category || 'N/A' }}</td>
          <td class="px-4 py-3">{{ formatDate(ev.date) }}</td>
          <td class="px-4 py-3">{{ formatTime(ev.timeStart, ev.timeEnd, ev.time) }}</td>
          <td class="px-4 py-3">{{ ev.created_by || 'N/A' }}</td>
          <td class="px-4 py-3">{{ ev.deleted_by || 'N/A' }}</td>
          <td class="px-4 py-3 whitespace-nowrap">{{ formatDate(ev.deleted_at) }}</td>
        </tr>

        <tr v-if="!loading && pagedRows.length === 0">
          <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">ไม่พบข้อมูลที่ค้นหา</td>
        </tr>
        <tr v-if="loading">
          <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">กำลังโหลดข้อมูล...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <span>แสดง</span>
      <select v-model.number="pageSize" class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none">
        <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
      </select>
      <span>{{ visibleCountText }}</span>
    </div>

    <div class="flex items-center gap-2">
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === 1"
        @click="page--">
        ก่อนหน้า
      </button>
      <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === totalPages || totalPages === 0"
        @click="page++">
        ถัดไป
      </button>
    </div>
  </div>

  <div v-if="sortOpen" class="fixed inset-0 z-0" @click="sortOpen=false"></div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

/* ---------- state ---------- */
const rows = ref([]);
const loading = ref(false);

const query = ref("");
const sortDir = ref("desc"); // 'asc' | 'desc'
const sortOpen = ref(false);

const page = ref(1);
const pageSize = ref(10);

/* ---------- helpers ---------- */
const startIndex = computed(() => (page.value - 1) * pageSize.value);

function formatDate(iso) {
  if (!iso) return "-";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "-";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yy = d.getFullYear();
  return `${dd}/${mm}/${yy}`;
}
function z(n){ return String(n).padStart(2, "0"); }
function formatTime(start, end, single) {
  // รองรับหลายกรณี: start/end เป็น "HH:mm" หรือ ISO; single เป็น string เดียว
  const toHM = (v) => {
    if (!v) return null;
    // ถ้าเป็น "HH:mm" อยู่แล้ว
    if (/^\d{1,2}:\d{2}$/.test(v)) return v;
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return null;
    return `${z(d.getHours())}:${z(d.getMinutes())}`;
  };
  const s = toHM(start), e = toHM(end);
  if (s && e) return `${s}–${e}`;
  if (single) return single;
  if (s) return s;
  return "N/A";
}

const normalize = (s) => String(s ?? "").toLowerCase();

/* ---------- filtering / sorting / paging ---------- */
const filteredRows = computed(() => {
  const q = normalize(query.value);

  // เฉพาะอีเวนต์ที่ถูกลบ: มี deleted_at หรือ status === 'deleted'
  const onlyDeleted = rows.value.filter((r) => {
    const status = normalize(r.status);
    return !!r.deleted_at || status === "deleted";
  });

  if (!q) return onlyDeleted;

  return onlyDeleted.filter((r) =>
    [
      r.evn_title,
      r.category,
      r.date,
      r.created_by,
      r.deleted_by,
      r.deleted_at,
      formatTime(r.timeStart, r.timeEnd, r.time),
    ]
      .join(" ")
      .toLowerCase()
      .includes(q)
  );
});

const sortedRows = computed(() => {
  const arr = [...filteredRows.value];
  const ts = (v) => {
    const t = new Date(v || 0).getTime();
    return Number.isFinite(t) ? t : 0;
  };
  arr.sort((a, b) =>
    sortDir.value === "asc" ? ts(a.deleted_at) - ts(b.deleted_at) : ts(b.deleted_at) - ts(a.deleted_at)
  );
  return arr;
});

const totalPages = computed(() => Math.ceil(sortedRows.value.length / pageSize.value));
const pagedRows = computed(() =>
  sortedRows.value.slice(startIndex.value, startIndex.value + pageSize.value)
);

const visibleCountText = computed(() => {
  const total = sortedRows.value.length;
  const from = total ? startIndex.value + 1 : 0;
  const to = Math.min(startIndex.value + pageSize.value, total);
  return total === 0 ? "0 จาก 0 รายการ" : `${from}-${to} จาก ${total} รายการ`;
});

/* ---------- actions ---------- */
function onSearch() { page.value = 1; }
function applySort(dir) { sortDir.value = dir; sortOpen.value = false; page.value = 1; }

/* ---------- data loading ---------- */
/* ปรับ mapping ให้รองรับชื่อคีย์หลายแบบจาก backend */
async function fetchDeletedEvents() {
  loading.value = true;
  rows.value = [];
  const candidates = [
    { url: "/history/events", params: { deleted: 1 } },
    { url: "/events/deleted", params: {} },
    { url: "/history/events/deleted", params: {} },
  ];

  for (const c of candidates) {
    try {
      const res = await axios.get(c.url, { params: c.params });
      const list = Array.isArray(res.data) ? res.data : (res.data?.data || []);

      rows.value = list.map((e) => {
        const rawStatus = e.status ?? e.evn_status ?? "";
        // หาเวลาจากหลายคีย์ที่พบบ่อย
        const timeStart = e.evn_time_start ?? e.time_start ?? e.start_time ?? e.start ?? null;
        const timeEnd   = e.evn_time_end   ?? e.time_end   ?? e.end_time   ?? e.end   ?? null;
        const timeOne   = e.evn_time       ?? e.time       ?? null;

        return {
          id: e.id ?? e.evn_id,
          evn_title: e.evn_title ?? e.title ?? "",
          category: e.cat_name ?? e.category_name ?? e.category ?? "",
          date: e.evn_date ?? e.date ?? e.evn_date_start ?? e.start_date ?? null,
          timeStart,
          timeEnd,
          time: timeOne,
          created_by: e.created_by_name ?? e.created_by ?? e.creator_name ?? e.creator ?? "",
          deleted_by: e.deleted_by_name ?? e.deleted_by ?? e.deleter_name ?? e.deleter ?? "",
          status: normalize(rawStatus),
          deleted_at: e.deleted_at ?? e.evn_deleted_at ?? null,
        };
      });
      break; // ได้ข้อมูลแล้วหยุด
    } catch (e) {
      // ลองตัวถัดไป
    }
  }
  loading.value = false;
}

onMounted(fetchDeletedEvents);

/* ---------- watchers ---------- */
watch(pageSize, () => (page.value = 1));
watch(query, () => (page.value = 1));
</script>
