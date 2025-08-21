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
          <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
        </svg>
      </button>
    </div>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <!-- Sort: dropdown เลือกได้ -->
      <div class="relative" @keydown.escape="sortOpen=false">
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="sortOpen = !sortOpen"
          :title="sortDir === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'"
        >
          <!-- sort icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
          </svg>
          <span>Sort</span>
          <span class="text-gray-400">({{ sortDir === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด' }})</span>
        </button>

        <!-- เมนูตัวเลือก -->
        <div
          v-if="sortOpen"
          class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg"
        >
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="applySort('desc')">
            วันที่ล่าสุด
          </button>
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="applySort('asc')">
            วันที่เก่าสุด
          </button>
        </div>
      </div>

      <!-- Add New -->
      <button
        class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="openAdd"
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
                <path d="M9 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.278l.84 12.255A2 2 0 008.114 22h7.772a2 2 0 001.996-2.745L18.722 7H19a1 1 0 100-2h-3V4a1 1 0 00-1-1H9zm2 4a1 1 0 112 0v10a1 1 0 11-2 0V7z"/>
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

  <!-- Add Modal -->
  <div v-if="addOpen" class="fixed inset-0 z-20 grid place-items-center bg-black/40 p-4" @click.self="closeAdd">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <h3 class="text-xl font-semibold text-gray-900">Create Category</h3>
      <div class="mt-5">
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Type name <span class="text-red-600">*</span>
        </label>
        <input
          v-model="newName"
          type="text"
          placeholder="Ex. สัมมนา"
          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-200"
        />
        <!-- แสดงผู้ใช้ที่จะถูกบันทึก ตามรูป -->
        <p class="mt-2 text-xs text-gray-500">
          ระบบจะบันทึก Created by เป็นชื่อผู้ใช้:
          <span class="font-medium text-gray-700">{{ userName }}</span>
        </p>
      </div>
      <div class="mt-6 flex justify-between">
        <button
          @click="closeAdd"
          class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white px-5 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
        >
          ✕ Cancel
        </button>
        <button
          :disabled="!newName.trim()"
          @click="createCategory"
          class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          + Create
        </button>
      </div>
    </div>
  </div>

</template>

<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import Swal from "sweetalert2";

type Row = {
  id: number;
  name: string;
  createdBy: string;
  createdAt: string; // ISO string
};

/* ข้อมูลเริ่มต้นว่าง (ต้องเพิ่มเอง) */
const rows = ref<Row[]>([]);

/* ผู้ใช้ปัจจุบัน (ดึงจากระบบจริงได้) */
const userName = ref("สมใจ");

/* state */
const query = ref("");
const page = ref(1);
const pageSize = ref(10);

/* sort: 'desc' = วันที่ล่าสุด, 'asc' = วันที่เก่าสุด */
const sortOpen = ref(false);
const sortDir = ref<'asc'|'desc'>('desc');

/* modal */
const addOpen = ref(false);
const newName = ref("");

function openAdd() { newName.value = ""; addOpen.value = true; }
function closeAdd() { addOpen.value = false; }

function onSearch() { page.value = 1; }
function applySort(dir: 'asc'|'desc') { sortDir.value = dir; sortOpen.value = false; page.value = 1; }

/* filter */
const filtered = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return rows.value.slice();
  return rows.value.filter(r =>
    r.name.toLowerCase().includes(q) || r.createdBy.toLowerCase().includes(q)
  );
});

/* sort by createdAt (จริง/ISO) */
const sorted = computed(() => {
  const copy = filtered.value.slice();
  const dir = sortDir.value === 'asc' ? 1 : -1;
  copy.sort((a, b) => {
    const ta = new Date(a.createdAt).getTime();
    const tb = new Date(b.createdAt).getTime();
    return (ta - tb) * dir;
  });
  return copy;
});

/* pagination */
const total = computed(() => sorted.value.length);
const totalPages = computed(() => Math.ceil(total.value / pageSize.value));
const startIndex = computed(() => (page.value - 1) * pageSize.value);
const endIndex = computed(() => Math.min(startIndex.value + pageSize.value, total.value));
const pagedRows = computed(() => sorted.value.slice(startIndex.value, endIndex.value));
const visibleCountText = computed(() => total.value === 0 ? "0 จาก 0 รายการ" : `${endIndex.value} จาก ${total.value} รายการ`);

/* actions */
function createCategory() {
  const name = newName.value.trim();
  if (!name) return;
  const nextId = Math.max(0, ...rows.value.map(r => r.id)) + 1;
  rows.value.push({
    id: nextId,
    name,
    createdBy: userName.value,
    createdAt: new Date().toISOString(), // วัน-เวลาแบบเรียลไทม์
  });
  closeAdd();

   // แจ้งเตือนหลังเพิ่มสำเร็จ
  Swal.fire({
    title: "เพิ่มหมวดหมู่สำเร็จ!",
    text: `คุณได้เพิ่ม "${name}" แล้ว`,
    icon: "success",
    draggable: true
  });

}
function remove(id: number) { rows.value = rows.value.filter(r => r.id !== id);
Swal.fire({
  title: "Are you sure?",
  text: "You won't be able to revert this!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, delete it!"
}).then((result) => {
  if (result.isConfirmed) {
    Swal.fire({
      title: "Deleted!",
      text: "Your file has been deleted.",
      icon: "success"
    });
  }
});
}

/* display date D/M/Y */
function formatDate(iso: string) {
  const d = new Date(iso);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

/* ปิด dropdown เมื่อคลิกนอก */
onMounted(() => {
  document.addEventListener("click", (e) => {
    const el = e.target as HTMLElement;
    if (!el.closest(".relative")) sortOpen.value = false;
  });
});
</script>
