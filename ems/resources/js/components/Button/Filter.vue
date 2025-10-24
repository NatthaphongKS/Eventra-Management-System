<template>
  <div class="relative inline-block text-left" ref="dropdownRoot" @keydown.esc="isOpen = false">
    <!-- ปุ่ม Filter -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100"
      :class="{ 'bg-gray-100': isOpen }"
      aria-label="Filter"
      :aria-expanded="isOpen"
    >
      <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
        <line x1="4" y1="7" x2="20" y2="7" />
        <line x1="6" y1="12" x2="16" y2="12" />
        <line x1="8" y1="17" x2="12" y2="17" />
      </svg>
      <span class="hidden sm:inline">Filter</span>
    </button>

    <!-- แผง -->
    <div
      v-show="isOpen"
      class="absolute top-full right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
      @click.stop
    >
      <div class="p-4 space-y-3">
        <!-- หัวข้อ -->
        <h3 class="font-semibold text-gray-800">Filter</h3>

        <!-- ✅ Filter All (เฉพาะเมื่อมี checkbox อย่างน้อย 1 ฟิลด์) -->
        <label v-if="hasAnyCheckbox" class="flex items-center gap-2">
          <input
            type="checkbox"
            class="accent-[#b91c1c] h-4 w-4 rounded"
            :checked="isAllSelected"
            :indeterminate.prop="isIndeterminate"
            @change="toggleAll"
          />
          <span class="text-[15px] text-gray-800">Filter All</span>
        </label>

        <!-- ฟิลด์ทั้งหมด -->
        <div v-for="(section, sIdx) in groupedSections" :key="sIdx" class="space-y-2">
          <!-- ชื่อหัวข้อ (เช่น Category / Status) -->
          <div v-if="section.title" class="text-gray-800">{{ section.title }}</div>

          <template v-for="field in section.fields" :key="field.fieldKey">
            <!-- กลุ่ม checkbox -->
            <div v-if="field.fieldType === 'checkbox'" class="max-h-60 overflow-auto space-y-1 pr-1">
              <label v-for="opt in field.fieldOptions" :key="String(opt.value)" class="flex items-center gap-2">
                <input
                  type="checkbox"
                  class="accent-[#b91c1c] h-4 w-4 rounded"
                  :checked="hasValue(field.fieldKey, opt.value)"
                  @change="toggleValue(field.fieldKey, opt.value)"
                />
                <span class="text-sm text-gray-800">{{ opt.label }}</span>
              </label>
            </div>

            <!-- select (สำหรับหน้าแบบดรอปดาวน์) -->
            <div v-else-if="field.fieldType === 'select'">
              <!-- <label class="block text-[14px] text-gray-800 mb-1">{{ field.label }}</label> -->
              <select
                class="w-full rounded-[14px] border border-rose-400 bg-white px-3 py-2 outline-none"
                v-model="selectedSingleByField[field.fieldKey]"
                @change="pushModel()"
              >
                <option :value="field.allValue ?? 'all'">all</option>
                <option v-for="opt in field.fieldOptions" :key="String(opt.value)" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, reactive, ref, watch } from 'vue'

/** Types */
type OptionItem = { label: string; value: string | number | boolean }
type FilterField = {
  fieldKey: string
  label: string
  fieldType: 'select' | 'checkbox'
  sectionTitle?: string
  fieldOptions: OptionItem[]
  allValue?: string | number
}
type Filters = Record<string, any>

/** Props */
const props = withDefaults(defineProps<{
  modelValue: Filters
  filterFields: FilterField[]
}>(), {
  modelValue: () => ({}),
  filterFields: () => [],
})

const emit = defineEmits<{
  (e: 'update:modelValue', v: Filters): void
}>()

/** UI */
const isOpen = ref(false)
const dropdownRoot = ref<HTMLElement | null>(null)

/** Local state */
const selectedValuesByField = reactive<Record<string, Set<any>>>({})
const selectedSingleByField = reactive<Record<string, any>>({})

function initializeLocalState() {
  for (const f of props.filterFields) {
    if (f.fieldType === 'checkbox') {
      const arr = Array.isArray(props.modelValue?.[f.fieldKey]) ? props.modelValue[f.fieldKey] : []
      selectedValuesByField[f.fieldKey] = new Set(arr)
    } else {
      selectedSingleByField[f.fieldKey] = props.modelValue?.[f.fieldKey] ?? (f.allValue ?? 'all')
    }
  }
}
initializeLocalState()
watch(() => props.modelValue, initializeLocalState, { deep: true })

/** Group sections */
const groupedSections = computed(() => {
  const map: Record<string, FilterField[]> = {}
  for (const f of props.filterFields) {
    const sec = f.sectionTitle ?? f.label ?? ''
    ;(map[sec] ||= []).push(f)
  }
  return Object.entries(map).map(([title, fields]) => ({ title, fields }))
})

/** Helpers */
function hasValue(key: string, val: any) {
  return !!selectedValuesByField[key]?.has(val)
}
function toggleValue(key: string, val: any) {
  const set = selectedValuesByField[key] ?? new Set<any>()
  set.has(val) ? set.delete(val) : set.add(val)
  selectedValuesByField[key] = set
  pushModel()
}
function pushModel() {
  const out: Filters = {}
  for (const f of props.filterFields) {
    out[f.fieldKey] = f.fieldType === 'checkbox'
      ? Array.from(selectedValuesByField[f.fieldKey] ?? [])
      : selectedSingleByField[f.fieldKey]
  }
  emit('update:modelValue', out)
}

/** ✅ Filter All logic (รวมทุก checkbox-field) */
const totalCheckboxOptionsCount = computed(() =>
  props.filterFields.filter(f => f.fieldType === 'checkbox')
    .reduce((sum, f) => sum + f.fieldOptions.length, 0)
)
const totalCheckboxSelectedCount = computed(() =>
  props.filterFields.filter(f => f.fieldType === 'checkbox')
    .reduce((sum, f) => sum + (selectedValuesByField[f.fieldKey]?.size ?? 0), 0)
)
const hasAnyCheckbox = computed(() =>
  props.filterFields.some(f => f.fieldType === 'checkbox')
)
const isAllSelected = computed(() =>
  totalCheckboxSelectedCount.value > 0 &&
  totalCheckboxSelectedCount.value === totalCheckboxOptionsCount.value
)
const isIndeterminate = computed(() =>
  totalCheckboxSelectedCount.value > 0 &&
  totalCheckboxSelectedCount.value < totalCheckboxOptionsCount.value
)

function toggleAll(ev: Event) {
  const checked = (ev.target as HTMLInputElement).checked
  for (const f of props.filterFields.filter(x => x.fieldType === 'checkbox')) {
    selectedValuesByField[f.fieldKey] = new Set(checked ? f.fieldOptions.map(o => o.value) : [])
  }
  pushModel()
}

/** click-outside */
function onDocumentClick(e: MouseEvent) {
  if (!dropdownRoot.value) return
  if (!dropdownRoot.value.contains(e.target as Node)) isOpen.value = false
}
onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))
</script>
