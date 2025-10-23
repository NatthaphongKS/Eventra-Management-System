<template>
  <div class="overflow-hidden rounded-2xl border border-neutral-200 stroke-neutral-200">
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-slate-50 text-neutral-800 bg-neutral-100">
          <th class="w-10 py-3 text-center">
            <input type="checkbox" :checked="allSelected" @change="toggleAll($event)" />
          </th>
          <th class="w-12 py-3 text-center text-neutral-800 font-semibold font-[Poppins]">#</th>
          <th class="w-28 py-3 text-left pl-3 text-neutral-800 font-semibold font-[Poppins]">ID</th>
          <th class="py-3 text-left text-neutral-800 font-semibold font-[Poppins]" >Name</th>
          <th class="w-28 py-3 text-left text-neutral-800 font-semibold font-[Poppins]">Nickname</th>
          <th class="w-40 py-3 text-left text-neutral-800 font-semibold font-[Poppins]">Department</th>
          <th class="w-40 py-3 text-left text-neutral-800 font-semibold font-[Poppins]">Team</th>
          <th class="w-44 py-3 text-left text-neutral-800 font-semibold font-[Poppins]">Position</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(row, rowIndex) in pagedRows" :key="row._key" :class="[
          'border-t',
          isSelected(row.id) ? 'bg-red-100' : 'bg-white',
          'hover:bg-slate-100'
        ]">
          <td class="px-2 py-2 text-center">
            <input type="checkbox" :value="row.id" :disabled="isDisabled(row.id)"
              :checked="row.con_checkin_status === 1" @change="haddleCheckIn(row.con_id)" />
          </td>

          <!-- ลำดับที่ -->
          <td class="px-2 py-2 text-center text-sm text-neutral-800 font-medium font-[Poppins]">
            {{ rowStartIndex + employeeIndex + 1 }}
          </td>

          <!-- ข้อมูลแต่ละคอลัมน์ -->
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.emp_id || '-' }}</td>
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.full_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.emp_nickname || '-' }}</td>
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.department_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.team_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-neutral-800 font-medium font-[Poppins]">{{ employee.position_name || '-' }}</td>
        </tr>

        <tr v-if="pagedRows.length === 0">
          <td :colspan="8" class="px-3 py-6 text-center text-neutral-800 font-medium font-[Poppins]">No data found</td>
        </tr>
      </tbody>

    </table>
  </div>

    <!-- Footer (limit/page + pagination + ช่วงข้อมูล) -->
    <div class="flex flex-col gap-3 bg-white px-3 py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- ซ้าย: limit/page -->
            <div class="flex items-center gap-2 font-regular font-[Poppins]">
                <span>แสดง</span>

        <!-- ⬇️ กล่อง select + ลูกศรแดง -->
        <div class="relative inline-block">
          <select class="appearance-none rounded-[20px] border border-red-700 bg-white px-2 py-1 pr-8
                 focus:outline-none focus:ring-2 focus:ring-rose-200 outline-red-700" :value="innerPageSize"
                        @change="onChangePageSize">
                        <option v-for="opt in pageSizeOptions" :key="opt" :value="opt" class="px-2 py-1 pr-8">{{ opt }}</option>
                    </select>
                    <!-- ลูกศรลงสีแดง -->
                    <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </div>

                <span>{{ rowStartIndex + 1 }}-{{ pagedRows.length + rowStartIndex }} </span>
                <div class="font-regular font-[Poppins]">
                    {{ displayFrom }} จาก {{ totalItems }} รายการ
                </div>
            </div>
        </div>

        <span>{{ rowStartIndex + 1 }}-{{ pagedRows.length + rowStartIndex }} </span>
        <div class="font-regular font-poppins">
          {{ displayFrom }} จาก {{ totalItems }} รายการ
        </div>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="mt-4 flex items-center justify-center gap-3">
    <button class="pg-arrow" :disabled="page === 1" @click="goToPage(page - 1)">
      <svg viewBox="0 0 24 24">
        <path d="M6 12 L18 4 L18 20 Z" />
      </svg>
    </button>

    <template v-for="(it, idx) in pageItems" :key="idx">
      <button v-if="it.type === 'page'" class="pg-num" :class="{ 'pg-active': it.value === page }"
        :aria-current="it.value === page ? 'page' : null" @click="goToPage(it.value)">
        {{ it.value }}
      </button>
      <span v-else class="pg-ellipsis"><i class="dot"></i><i class="dot"></i><i class="dot"></i></span>
    </template>

    <button class="pg-arrow" :disabled="page === totalPages || totalPages === 0" @click="goToPage(page + 1)">
      <svg viewBox="0 0 24 24" style="transform: scaleX(-1)">
        <path d="M6 12 L18 4 L18 20 Z" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

defineOptions({ name: 'EventCheckInEvent' })

/* ===== Props ===== */
const props = defineProps({
  // ข้อมูลที่ filter/sort แล้ว
  rows: { type: Array, default: () => [] },

  // controlled-mode (ถ้าแม่อยากคุมเอง)
  page: { type: Number, default: undefined },
  pageSize: { type: Number, default: undefined },

  // options
  pageSizeOptions: { type: Array, default: () => [10, 20, 50, 100] },

  // selection
  modelValue: { type: Array, default: () => [] },   // v-model: selectedIds
  disabledIds: { type: Array, default: () => [] },  // id ที่ห้ามเลือก
})

const emit = defineEmits([
  'update:modelValue', 'change',
  'update:page', 'update:pageSize'
])

// ช่วยแปลง props.rows ให้เป็น "อาเรย์เสมอ"
const rowsArray = computed(() => {
  const r = props.rows
  if (Array.isArray(r)) return r
  if (r == null) return []

  // เผื่อ response ทั่วไป
  if (Array.isArray(r.data)) return r.data
  if (Array.isArray(r.items)) return r.items
  if (Array.isArray(r.results)) return r.results
  // เผื่อ API อื่น ๆ ที่ชอบตั้งชื่อแบบนี้
  if (Array.isArray(r.payload)) return r.payload
  if (Array.isArray(r.list)) return r.list
  if (Array.isArray(r.rows)) return r.rows

  // ถ้าเป็น object ตัวเดียว ก็ห่อให้เป็นอาเรย์ 1 ตัว
  if (typeof r === 'object') return [r]
  return []
})

/* ===== Normalize rows: รองรับ JSON ใหม่ (employee.{department,team,position}) ===== */
const normalizedRows = computed(() => {
  return rowsArray.value.map((r, idx) => {
    // record หลัก (confirmation)
    const confirm = r || {}

    // employee / event อาจไม่มี ให้ fallback เป็น {} กัน error
    const emp = (confirm.employee ?? r ?? {}) || {}
    const ev = (confirm.event ?? {}) || {}

    // ชื่อเต็ม: prefix + firstname + lastname
    const fullName =
      [emp.emp_prefix, emp.emp_firstname, emp.emp_lastname].filter(Boolean).join(' ').trim()
      || `${emp.emp_firstname ?? ''} ${emp.emp_lastname ?? ''}`.trim()

    // ==== ชื่อจากโครงสร้างใหม่ ====
    // ถ้ามี object ย่อย ให้ดึงชื่อจากในนั้นก่อน แล้วค่อย fallback ไป alias เดิม ๆ
    const positionName = emp.position?.pst_name ?? r.position_name ?? r.pst_name ?? emp.position_name
    const departmentName = emp.department?.dpm_name ?? r.department_name ?? r.dpm_name ?? emp.department_name
    const teamName = emp.team?.tm_name ?? r.team_name ?? r.tm_name ?? emp.team_name

    return {
      // ใช้ emp.id เป็นหลัก (ไม่มีค่อย fallback con_employee_id > index)
      id: emp.id ?? confirm.con_employee_id ?? idx,

      // key สำหรับ v-for
      _key: `${emp.id ?? 'emp-null'}-${confirm.id ?? 'conf-null'}-${idx}`,

      // ===== คอลัมน์ที่จะโชว์ =====
      emp_id: emp.emp_id ?? r.emp_id ?? '-',
      full_name: fullName || '-',
      emp_nickname: emp.emp_nickname ?? r.emp_nickname ?? '-',
      department_name: departmentName ?? '-',
      team_name: teamName ?? '-',
      position_name: positionName ?? '-',

      // ===== เก็บค่าจาก confirmation (เผื่อใช้ต่อ) =====
      con_id: confirm.id ?? null,
      con_event_id: confirm.con_event_id ?? null,
      con_employee_id: confirm.con_employee_id ?? null,
      con_checkin_status: confirm.con_checkin_status ?? null,
      con_answer: confirm.con_answer ?? null,
      con_reason: confirm.con_reason ?? null,
      con_delete_status: confirm.con_delete_status ?? null,

      // ===== เก็บของ event เผื่อใช้อื่น ๆ =====
      event_title: ev.evn_title ?? null,
      event_date: ev.evn_date ?? null,
      event_time_start: ev.evn_timestart ?? null,
      event_time_end: ev.evn_timeend ?? null,
      event_status: ev.evn_status ?? null,
    }
  })
})




/* ===== Pagination state ===== */
const innerPage = ref(props.page ?? 1)
const innerPageSize = ref(props.pageSize ?? 10)

watch(() => props.page, (v) => { if (typeof v === 'number') innerPage.value = v })
watch(() => props.pageSize, (v) => { if (typeof v === 'number') innerPageSize.value = v })

/* ===== Derived ===== */
const totalItems = computed(() => normalizedRows.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / innerPageSize.value) || 0)

const safePage = computed(() => {
  const t = totalPages.value
  let p = innerPage.value
  if (t === 0) return 1
  if (p < 1) p = 1
  if (p > t) p = t
  return p
})

const rowStartIndex = computed(() => (safePage.value - 1) * innerPageSize.value)

const pagedRows = computed(() => {
  const start = rowStartIndex.value
  return normalizedRows.value.slice(start, start + innerPageSize.value)
})

const displayFrom = computed(() => (totalItems.value === 0 ? 0 : rowStartIndex.value + 1))
const displayTo = computed(() =>
  totalItems.value === 0 ? 0 : Math.min(rowStartIndex.value + innerPageSize.value, totalItems.value)
)

const page = computed(() => safePage.value)

const pageItems = computed(() => {
  const total = totalPages.value || 1
  const cur = safePage.value
  const items = []
  if (total <= 7) { for (let i = 1; i <= total; i++) items.push({ type: 'page', value: i }); return items }
  const addPage = p => items.push({ type: 'page', value: p })
  const addDots = () => items.push({ type: 'dots' })
  addPage(1)
  if (cur > 3) addDots()
  const s = Math.max(2, cur - 1), e = Math.min(total - 1, cur + 1)
  for (let p = s; p <= e; p++) addPage(p)
  if (cur < total - 2) addDots()
  addPage(total)
  return items
})

/* ===== Pagination handlers ===== */
function onChangePageSize(e) {
  const next = Number(e.target.value) || 10
  innerPageSize.value = next
  goToPage(1)
  emit('update:pageSize', next)
}
function goToPage(p) {
  const t = totalPages.value
  let next = Number(p)
  if (Number.isNaN(next) || t === 0) next = 1
  if (next < 1) next = 1
  if (next > t) next = t
  innerPage.value = next
  emit('update:page', next)
}

/* ===== Selection (ทำงานกับ "เฉพาะแถวในหน้าปัจจุบัน") ===== */
const selectedSet = computed(() => new Set(props.modelValue))
const pageIds = computed(() =>
  pagedRows.value.map(r => r.id).filter(id => id !== null && !props.disabledIds.includes(id))
)

const allSelected = computed(() =>
  pageIds.value.length > 0 && pageIds.value.every(id => selectedSet.value.has(id))
)
const isIndeterminate = computed(() => {
  const picked = pageIds.value.filter(id => selectedSet.value.has(id)).length
  return picked > 0 && picked < pageIds.value.length
})
function isSelected(id) { return selectedSet.value.has(id) }
function isDisabled(id) { return props.disabledIds.includes(id) }

function toggleOne(id, e) {
  const next = new Set(selectedSet.value)
  if (e.target.checked) next.add(id); else next.delete(id)
  const out = Array.from(next)
  emit('update:modelValue', out)
  emit('change', out)
}
function toggleAll(e) {
  const out = new Set(selectedSet.value)
  if (e.target.checked) pageIds.value.forEach(id => out.add(id))
  else pageIds.value.forEach(id => out.delete(id))
  const arr = Array.from(out)
  emit('update:modelValue', arr)
  emit('change', arr)
}

function haddleCheckIn(attId) {
  try {
    const res = axios.put(`/updateEmployeeAttendance/attId/${attId}`)
    console.log('Check-in successful:', res.data);
  } catch (error) {
    console.error('Error during check-in:', error);
  }
}
</script>

<style scoped></style>
