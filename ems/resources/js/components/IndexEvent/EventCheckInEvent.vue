<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-slate-50 text-slate-700">
          <th class="w-10 py-3 text-center">
            <input
              type="checkbox"
              :checked="allSelected"
              @change="toggleAll($event)"
            />
          </th>
          <th class="w-12 py-3 text-center">#</th>
          <th class="w-28 py-3 text-left pl-3">ID</th>
          <th class="py-3 text-left">Name</th>
          <th class="w-28 py-3 text-left">Nickname</th>
          <th class="w-40 py-3 text-left">Department</th>
          <th class="w-40 py-3 text-left">Team</th>
          <th class="w-44 py-3 text-left">Position</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(employee, employeeIndex) in pagedRows"
          :key="employee.id ?? employeeIndex"
          :class="[
            'border-t',
            isSelected(employee.id) ? 'bg-rose-50' : (employeeIndex % 2 ? 'bg-slate-50' : 'bg-white'),
            'hover:bg-slate-100'
          ]"
        >
          <td class="px-2 py-2 text-center">
            <input
              type="checkbox"
              :value="employee.id"
              :checked="isSelected(employee.id)"
              :disabled="isDisabled(employee.id)"
              @change="toggleOne(employee.id, $event)"
            />
          </td>

          <td class="px-2 py-2 text-center text-sm text-slate-700">
            {{ rowStartIndex + employeeIndex + 1 }}
          </td>

          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.emp_id || '-' }}</td>
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.full_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.emp_nickname || '-' }}</td>
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.department_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.team_name || '-' }}</td>
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.position_name || '-' }}</td>
        </tr>

        <tr v-if="pagedRows.length === 0">
          <td :colspan="8" class="px-3 py-6 text-center text-slate-600">No data found</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Footer: limit/page + range -->
  <div class="flex flex-col gap-3 border-t bg-white px-3 py-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2 text-sm text-slate-700">
        <span>แสดง</span>
        <select
          class="rounded-lg border px-2 py-1 bg-white focus:outline-none focus:ring-2 focus:ring-rose-200"
          :value="innerPageSize"
          @change="onChangePageSize"
        >
          <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">{{ opt }}</option>
        </select>
        <span>{{ displayFrom }}–{{ displayTo }} รายการ</span>
        <div class="text-xs text-slate-500">ทั้งหมด {{ totalItems }} รายการ</div>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="mt-4 flex items-center justify-center gap-3">
    <button class="pg-arrow" :disabled="page === 1" @click="goToPage(page - 1)">
      <svg viewBox="0 0 24 24"><path d="M6 12 L18 4 L18 20 Z" /></svg>
    </button>

    <template v-for="(it, idx) in pageItems" :key="idx">
      <button
        v-if="it.type === 'page'"
        class="pg-num"
        :class="{ 'pg-active': it.value === page }"
        :aria-current="it.value === page ? 'page' : null"
        @click="goToPage(it.value)"
      >
        {{ it.value }}
      </button>
      <span v-else class="pg-ellipsis"><i class="dot"></i><i class="dot"></i><i class="dot"></i></span>
    </template>

    <button
      class="pg-arrow"
      :disabled="page === totalPages || totalPages === 0"
      @click="goToPage(page + 1)"
    >
      <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M6 12 L18 4 L18 20 Z" /></svg>
    </button>
  </div>
</template>

<script setup>
import { computed, ref ,watch} from 'vue'

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

/* ===== Normalize rows (เหมือนเดิม) ===== */
const normalizedRows = computed(() => {
  return (props.rows || []).map((e) => ({
    id: e.id ?? e.emp_id_key ?? e.employee_id ?? null,
    emp_id: e.emp_id,
    full_name:
      e.full_name ||
      [e.emp_prefix, e.emp_firstname, e.emp_lastname].filter(Boolean).join(' ') ||
      `${e.emp_firstname ?? ''} ${e.emp_lastname ?? ''}`.trim(),
    emp_nickname: e.emp_nickname,
    department_name: e.department_name ?? e.dpm_name,
    team_name: e.team_name ?? e.tm_name,
    position_name: e.position_name ?? e.pst_name,
  }))
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
const displayTo   = computed(() =>
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
</script>

<style scoped>
</style>
