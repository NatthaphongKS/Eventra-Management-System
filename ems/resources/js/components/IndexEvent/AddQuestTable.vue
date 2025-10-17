<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200">
    <table class="w-full table-auto">
      <thead>
        <tr class="bg-slate-50 text-slate-700">
          <th class="w-10 py-3 text-center">
            <!-- Select all -->
            <input
              type="checkbox"
              :checked="allSelected"
              :indeterminate.prop="isIndeterminate"
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
          v-for="(employee, employeeIndex) in normalizedRows"
          :key="employee.id ?? employeeIndex"
          :class="[
            'border-t',
            isSelected(employee.id) ? 'bg-rose-50' : (employeeIndex % 2 ? 'bg-slate-50' : 'bg-white'),
            'hover:bg-slate-100'
          ]"
        >
          <!-- checkbox -->
          <td class="px-2 py-2 text-center">
            <input
              type="checkbox"
              :value="employee.id"
              :checked="isSelected(employee.id)"
              :disabled="isDisabled(employee.id)"
              @change="toggleOne(employee.id, $event)"
            />
          </td>

          <!-- running number -->
          <td class="px-2 py-2 text-center text-sm text-slate-700">
            {{ rowStartIndex + employeeIndex + 1 }}
          </td>

          <!-- columns -->
          <td class="px-3 py-2 text-sm text-slate-800">{{ employee.emp_id || '-' }}</td>

          <td class="px-3 py-2 text-sm text-slate-800">
            {{ employee.full_name || '-' }}
          </td>

          <td class="px-3 py-2 text-sm text-slate-800">
            {{ employee.emp_nickname || '-' }}
          </td>

          <td class="px-3 py-2 text-sm text-slate-800">
            {{ employee.department_name || '-' }}
          </td>

          <td class="px-3 py-2 text-sm text-slate-800">
            {{ employee.team_name || '-' }}
          </td>

          <td class="px-3 py-2 text-sm text-slate-800">
            {{ employee.position_name || '-' }}
          </td>
        </tr>

        <tr v-if="normalizedRows.length === 0">
          <td :colspan="8" class="px-3 py-6 text-center text-slate-600">
            No data found
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed, toRefs } from 'vue'

defineOptions({ name: 'EmployeeTableSelect' })

/**
 * Props:
 * - rows:    รายการพนักงาน (จากหน้าแม่)
 * - modelValue: อาเรย์ของ id พนักงานที่ถูกเลือก (รองรับ v-model)
 * - rowStartIndex: เลขเริ่มต้นของลำดับแถว (เช่น (page-1)*pageSize)
 * - disabledIds:  รายการ id ที่ไม่อนุญาตให้เลือก (ถ้ามี)
 */
const props = defineProps({
  rows: { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] }, // v-model: selectedIds
  rowStartIndex: { type: Number, default: 0 },
  disabledIds: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'change'])

/** จัดรูปข้อมูลให้คอลัมน์อ่านง่าย/ชื่อสม่ำเสมอ */
const normalizedRows = computed(() => {
  return (props.rows || []).map((e) => ({
    // id ที่ใช้เลือก
    id: e.id ?? e.emp_id_key ?? e.employee_id ?? null,

    // ฟิลด์ที่แสดง
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

/** utilities สำหรับ selection */
const selectedSet = computed(() => new Set(props.modelValue))
const allSelectableIds = computed(() =>
  normalizedRows.value
    .map((r) => r.id)
    .filter((id) => !props.disabledIds.includes(id) && id !== null)
)

const allSelected = computed(() =>
  allSelectableIds.value.length > 0 &&
  allSelectableIds.value.every((id) => selectedSet.value.has(id))
)

const isIndeterminate = computed(() => {
  const picked = allSelectableIds.value.filter((id) => selectedSet.value.has(id)).length
  return picked > 0 && picked < allSelectableIds.value.length
})

function isSelected(id) {
  return selectedSet.value.has(id)
}
function isDisabled(id) {
  return props.disabledIds.includes(id)
}

function toggleOne(id, e) {
  let next = new Set(selectedSet.value)
  if (e.target.checked) next.add(id)
  else next.delete(id)

  const out = Array.from(next)
  emit('update:modelValue', out)
  emit('change', out)
}

function toggleAll(e) {
  let next
  if (e.target.checked) next = allSelectableIds.value.slice()
  else next = []
  emit('update:modelValue', next)
  emit('change', next)
}
</script>
