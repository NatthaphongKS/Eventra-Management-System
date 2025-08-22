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
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
              />
            </div>
            <button
              class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
              @click="onSearch"
              aria-label="search"
            >
              <!-- search icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"
                />
              </svg>
            </button>
          </div>

          <!-- Right controls -->
          <div class="flex items-center gap-2">
            <!-- Sort -->
            <button
              class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="toggleSort"
              :title="sortTitle"
            >
              <!-- sort icon -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
              </svg>
              <span>Sort</span>
              <span class="text-gray-400">({{ sortAsc ? 'A→Z' : 'Z→A' }})</span>
            </button>

            <!-- Add New -->
            <button
              class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
              @click="onAdd"
            >
              <span class="text-lg leading-none">＋</span>
              <span>Add New</span>
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50 text-gray-600">
              <tr class="text-left text-sm">
                <th class="w-14 px-6 py-3 font-semibold">#</th>
                <th class="px-6 py-3 font-semibold">Category</th>
                <th class="px-6 py-3 font-semibold">Created by</th>
                <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
                <th class="w-16 px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(row, idx) in pagedRows"
                :key="row.id"
                class="text-sm text-gray-700 hover:bg-gray-50"
              >
                <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>
                <td class="px-6 py-3">{{ row.name }}</td>
                <td class="px-6 py-3">{{ row.createdBy }}</td>
                <td class="px-6 py-3">{{ formatDate(row.createdAt) }}</td>
                <td class="px-6 py-3">
                  <button
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600"
                    @click="remove(row.id)"
                    aria-label="delete"
                  >
                    <!-- trash -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M9 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.278l.84 12.255A2 2 0 008.114 22h7.772a2 2 0 001.996-2.745L18.722 7H19a1 1 0 100-2h-3V4a1 1 0 00-1-1H9zm2 4a1 1 0 112 0v10a1 1 0 11-2 0V7z"
                      />
                    </svg>
                  </button>
                </td>
              </tr>

              <tr v-if="pagedRows.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                  ไม่พบข้อมูลที่ค้นหา
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <span>แสดง</span>
            <select
              v-model.number="pageSize"
              class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none"
            >
              <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
            </select>
            <span>{{ visibleCountText }}</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
              :disabled="page === 1"
              @click="page--"
            >
              ก่อนหน้า
            </button>
            <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
            <button
              class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
              :disabled="page === totalPages || totalPages === 0"
              @click="page++"
            >
              ถัดไป
            </button>
          </div>
        </div>
    
</template>
<script setup lang="ts">
import { computed, ref } from "vue";

type Row = {
  id: number;
  name: string;
  createdBy: string;
  createdAt: string; // ISO string
};

// --- sample data (แทนข้อมูลจริงจาก API) ---
const rows = ref<Row[]>([
  { id: 1, name: "ประชุม", createdBy: "สมปอง", createdAt: "2025-08-20" },
  { id: 2, name: "สัมมนา", createdBy: "สมหมาย", createdAt: "2025-08-20" },
  { id: 3, name: "อบรม", createdBy: "สมปอง", createdAt: "2025-08-20" },
]);

// state
const query = ref("");
const sortAsc = ref(true);
const page = ref(1);
const pageSize = ref(10);

const sortTitle = computed(() => (sortAsc.value ? "เรียง A→Z" : "เรียง Z→A"));

const filtered = computed(() => {
  const q = query.value.trim();
  if (!q) return rows.value.slice();
  return rows.value.filter((r) => r.name.includes(q) || r.createdBy.includes(q));
});

const sorted = computed(() => {
  const copy = filtered.value.slice();
  copy.sort((a, b) => (sortAsc.value ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name)));
  return copy;
});

// pagination
const total = computed(() => sorted.value.length);
const totalPages = computed(() => Math.ceil(total.value / pageSize.value));
const startIndex = computed(() => (page.value - 1) * pageSize.value);
const endIndex = computed(() => Math.min(startIndex.value + pageSize.value, total.value));
const pagedRows = computed(() => sorted.value.slice(startIndex.value, endIndex.value));

const visibleCountText = computed(() => {
  if (total.value === 0) return "0 จาก 0 รายการ";
  return `${endIndex.value} จาก ${total.value} รายการ`;
});

// actions
function onSearch() {
  page.value = 1;
}

function toggleSort() {
  sortAsc.value = !sortAsc.value;
}

function onAdd() {
  // เปลี่ยนเป็น navigation / modal / emit ตามโปรเจกต์จริง
  const id = Math.max(0, ...rows.value.map((r) => r.id)) + 1;
  rows.value.push({
    id,
    name: `หมวดใหม่ #${id}`,
    createdBy: "ผู้ใช้",
    createdAt: new Date().toISOString().slice(0, 10),
  });
}

function remove(id: number) {
  rows.value = rows.value.filter((r) => r.id !== id);
}

function formatDate(iso: string) {
  const d = new Date(iso);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}
</script>