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
          class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
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
    </div>
  </div>

  <!-- Table -->
<div class="mt-4 min-w-full overflow-hidden rounded-2xl border border-gray-200">
  <table class="min-w-full">
    <thead class="bg-gray-100">
      <tr class="bg-gray-100 text-gray-700 text-sm font-semibold">
        <th class="w-14 px-4 py-3 text-left">#</th>
        <th class="w-28 px-4 py-3 text-left">ID</th>
        <th class="w-56 px-4 py-3 text-left">Name</th>
        <th class="w-32 px-4 py-3 text-left">Nickname</th>

        <!-- กำหนดเท่ากัน -->
        <th class="w-56 px-4 py-3 text-left">Department</th>
        <th class="w-56 px-4 py-3 text-left">Team</th>
        <th class="w-56 px-4 py-3 text-left">Position</th>

        <th class="w-40 px-4 py-3 text-left">Deleted By</th>
        <th class="w-40 px-4 py-3 text-left">Deleted Date</th>
      </tr>
    </thead>
  </table>
</div>



<div>
  <table class="min-w-full">
    <tbody class="divide-y divide-gray-100">
        <tr v-for="(row, idx) in pagedRows" :key="row.id" class="text-sm text-gray-700 hover:bg-gray-50">
          <td class="px-4 py-3">{{ startIndex + idx + 1 }}</td>
          <td class="px-4 py-3">{{ row.code || row.employee_code || row.id }}</td>
          <td class="px-4 py-3">{{ row.name }}</td>
          <td class="px-4 py-3">{{ row.nickname }}</td>
          <td class="px-4 py-3">{{ row.department }}</td>
          <td class="px-4 py-3">{{ row.team }}</td>
          <td class="px-4 py-3">{{ row.position }}</td>
          <td class="px-4 py-3">{{ row.deleted_by_name || row.deleted_by || '-' }}</td>
          <td class="px-4 py-3">{{ formatDate(row.deleted_at) }}</td>
        </tr>

        <tr v-if="!loading && pagedRows.length === 0">
          <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
            ไม่พบข้อมูลที่ค้นหา
          </td>
        </tr>

        <tr v-if="loading">
          <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
            กำลังโหลดข้อมูล...
          </td>
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

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

/**
 * ปรับให้ตรงกับ api.php ของคุณ:
 * - ถ้ามี route ชื่ออื่น ให้เปลี่ยนใน ENDPOINTS หรือตัดให้เหลือเส้นทางเดียวก็ได้
 * - ปรับ mapRow() ให้ตรงกับชื่อฟิลด์ที่ backend ส่งมา
 */
const ENDPOINTS = [
  '/api/employees/inactive',
  '/api/employees?status=inactive',
  '/api/history/employees'
]

const rows = ref([])          // raw rows
const loading = ref(false)
const query = ref('')
const sortDir = ref('desc')   // asc | desc (ตาม Deleted Date)
const sortOpen = ref(false)

const page = ref(1)
const pageSize = ref(10)

const startIndex = computed(() => (page.value - 1) * pageSize.value)

const formatDate = (iso) => {
  if (!iso) return '-'
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return iso
  return d.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function normalizeString(s) {
  return String(s ?? '').toLowerCase()
}

const filteredRows = computed(() => {
  const q = normalizeString(query.value)
  if (!q) return rows.value
  return rows.value.filter((r) => {
    return [
      r.code, r.employee_code, r.id,
      r.name, r.nickname, r.department,
      r.team, r.position, r.deleted_by_name,
      r.deleted_by, r.deleted_at
    ].some((v) => normalizeString(v).includes(q))
  })
})

const sortedRows = computed(() => {
  const arr = [...filteredRows.value]
  arr.sort((a, b) => {
    const da = new Date(a.deleted_at || 0).getTime()
    const db = new Date(b.deleted_at || 0).getTime()
    return sortDir.value === 'asc' ? da - db : db - da
  })
  return arr
})

const totalPages = computed(() =>
  Math.ceil(sortedRows.value.length / pageSize.value)
)

const pagedRows = computed(() =>
  sortedRows.value.slice(startIndex.value, startIndex.value + pageSize.value)
)

const visibleCountText = computed(() => {
  const total = sortedRows.value.length
  const from = total ? startIndex.value + 1 : 0
  const to = Math.min(startIndex.value + pageSize.value, total)
  return `${from}-${to} จาก ${total} รายการ`
})

function onSearch() {
  page.value = 1
}

function applySort(dir) {
  sortDir.value = dir
  sortOpen.value = false
}

/** แปลงโครงสร้าง row จาก backend -> โครงสร้างที่ตารางใช้ */
function mapRow(item) {
  // ปรับ field name ตรงนี้ให้ตรงกับ Employee.php / HistoryEmployeeController.php ของคุณ
  return {
    id: item.id,
    code: item.code || item.employee_code || item.emp_code,
    name: item.name || item.full_name || `${item.first_name ?? ''} ${item.last_name ?? ''}`.trim(),
    nickname: item.nickname || item.nick_name,
    department: item.department?.name || item.department_name || item.department,
    team: item.team?.name || item.team_name || item.team,
    position: item.position?.name || item.position_name || item.position,
    deleted_by_name: item.deleted_by_name || item.deletedBy || item.deleted_by,
    deleted_by: item.deleted_by,
    deleted_at: item.deleted_at || item.deletedDate || item.deleted_date
  }
}

/** ลองเรียกหลาย endpoint จนกว่าจะสำเร็จ */
async function fetchWithFallback() {
  let lastErr = null
  for (const url of ENDPOINTS) {
    try {
      const res = await axios.get(url, { withCredentials: true })
      // รองรับทั้ง {data: [...]} หรือเป็น array ตรง ๆ
      const list = Array.isArray(res.data) ? res.data : (res.data?.data ?? res.data)
      if (!Array.isArray(list)) throw new Error('Unexpected payload format')
      return list.map(mapRow)
    } catch (e) {
      lastErr = e
      // ถ้า 404 หรือ Network error ให้ลองตัวต่อไป
      continue
    }
  }
  throw lastErr ?? new Error('Cannot fetch inactive employees')
}

async function load() {
  loading.value = true
  try {
    const data = await fetchWithFallback()
    rows.value = data
  } catch (e) {
    // ถ้าต้องการแจ้ง error UI: ใส่ toast/alert เพิ่มได้
    console.error('Fetch error:', e)
    rows.value = []
  } finally {
    loading.value = false
  }
}

onMounted(load)

// รีเซ็ตหน้าเมื่อ pageSize เปลี่ยน
watch(pageSize, () => (page.value = 1))
</script>