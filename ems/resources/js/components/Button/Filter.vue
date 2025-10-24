<template>
  <div class="relative inline-block text-left" ref="dropdownRoot" @keydown.esc="isOpen = false">
    <!-- ปุ่ม Filter (หน้าตาเดิม) -->
    <button
      @click="isOpen = !isOpen"
      class="inline-flex items-center gap-2 px-2 py-1 text-neutral-800 hover:text-slate-900 focus:outline-none rounded-md font-medium font-[Poppins]"
    >
      <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#434343">
        <path d="M400-240v-80h160v80H400ZM240-440v-80h480v80H240ZM120-640v-80h720v80H120Z"/>
      </svg>
      <span class="text-base font-medium">Filter</span>
      <!-- <span v-if="selectedBadgeCount" class="ml-1 min-w-5 text-center rounded-full bg-slate-900 text-white text-xs px-1.5 py-0.5">{{ selectedBadgeCount }}</span> -->
    </button>

    <!-- กล่อง Dropdown -->
    <div
      v-show="isOpen"
      class="absolute left-0 mt-2 w-60 rounded-xl border border-slate-200 bg-white shadow-lg z-20 p-3"
      @click.stop
    >
      <!-- Filter All -->
      <label v-if="hasAnyCheckbox" class="flex items-center gap-2 mb-2">
        <input
          type="checkbox"
          class="accent-[#b91c1c] h-4 w-4 rounded"
          :checked="isAllSelected"
          :indeterminate.prop="isIndeterminate"
          @change="toggleAll"
        />
        <span class="font-regular font-[Poppins] uppercase text-neutral-800">Filter All</span>
      </label>

      <!-- Sections -->
      <div v-for="(section, sectionIndex) in groupedSections" :key="sectionIndex" class="mb-3 last:mb-0">
        <div v-if="section.title" class="font-regular font-[Poppins] text-neutral-800 uppercase mb-1">
          {{ section.title }}
        </div>

        <!-- Fields in section -->
        <div v-for="field in section.fields" :key="field.fieldKey">
          <!-- Checkbox group -->
          <div v-if="field.fieldType === 'checkbox'" class="flex flex-col gap-1">
            <label
              v-for="option in field.fieldOptions"
              :key="String(option.value)"
              class="flex items-center gap-2"
            >
              <input
                type="checkbox"
                class="accent-[#b91c1c] h-4 w-4 rounded"
                :checked="hasValue(field.fieldKey, option.value)"
                @change="toggleValue(field.fieldKey, option.value)"
              />
              <span class="font-regular font-[Poppins] text-neutral-800">{{ option.label }}</span>
            </label>
          </div>

          <!-- Select -->
          <div v-else-if="field.fieldType === 'select'">
            <select
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2"
              v-model="selectedSingleByField[field.fieldKey]"
              @change="syncSelect(field.fieldKey)"
            >
              <option :value="field.allValue ?? 'all'">All</option>
              <option v-for="option in field.fieldOptions" :key="String(option.value)" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, reactive, ref, watch } from 'vue'

/** ===== Types ===== */
type OptionItem = { label: string; value: string | number | boolean }
type FilterField = {
  fieldKey: string
  label: string
  fieldType: 'checkbox' | 'select'
  sectionTitle?: string
  fieldOptions: OptionItem[]
  allValue?: string | number
}

type Filters = { [key: string]: any }
type Category = { id: string | number; cat_name: string }
type StatusOpt = { label: string; value: string }

/** ===== Props ===== */
const props = withDefaults(defineProps<{
  modelValue: Filters
  filterFields?: FilterField[]            // schema mode
  categories?: Category[]                 // legacy mode
  statusOptions?: StatusOpt[]             // legacy mode
}>(), {
  modelValue: () => ({ category: [], status: [] }),
  categories: () => [],
  statusOptions: () => ([
    { label: 'Done', value: 'done' },
    { label: 'Ongoing', value: 'ongoing' },
    { label: 'Upcoming', value: 'upcoming' },
  ]),
})

const emit = defineEmits<{
  (e: 'update:modelValue', v: Filters): void
  (e: 'apply', v: Filters): void
  (e: 'clear'): void
}>()

/** ===== UI state ===== */
const isOpen = ref(false)
const dropdownRoot = ref<HTMLElement | null>(null)

/** ===== Resolve fields (schema or legacy) ===== */
const configuredFields = computed<FilterField[]>(() => {
  if (props.filterFields && props.filterFields.length) return props.filterFields
  // map legacy inputs
  const categoryField: FilterField = {
    fieldKey: 'category',
    label: 'Category',
    fieldType: 'checkbox',
    sectionTitle: 'Category',
    fieldOptions: props.categories.map(category => ({ label: category.cat_name, value: String(category.id) })),
  }
  const statusField: FilterField = {
    fieldKey: 'status',
    label: 'Status',
    fieldType: 'checkbox',
    sectionTitle: 'Status',
    fieldOptions: props.statusOptions.map(status => ({ label: status.label, value: status.value })),
  }
  return [categoryField, statusField]
})

/** ===== Local selections ===== */
const selectedValuesByField = reactive<Record<string, Set<any>>>({})
const selectedSingleByField = reactive<Record<string, any>>({})

function initializeLocalState() {
  for (const filterField of configuredFields.value) {
    if (filterField.fieldType === 'checkbox') {
      const currentValues = Array.isArray(props.modelValue?.[filterField.fieldKey])
        ? props.modelValue[filterField.fieldKey]
        : []
      selectedValuesByField[filterField.fieldKey] = new Set(currentValues)
    } else {
      selectedSingleByField[filterField.fieldKey] =
        props.modelValue?.[filterField.fieldKey] ?? (filterField.allValue ?? 'all')
    }
  }
}
initializeLocalState()
watch(() => props.modelValue, initializeLocalState, { deep: true })

/** ===== Group sections for header rendering ===== */
const groupedSections = computed(() => {
  const sectionsMap: Record<string, FilterField[]> = {}
  for (const filterField of configuredFields.value) {
    const sectionTitle = filterField.sectionTitle ?? ''
    ;(sectionsMap[sectionTitle] ||= []).push(filterField)
  }
  return Object.entries(sectionsMap).map(([title, fields]) => ({ title, fields }))
})

/** ===== Utilities ===== */
function hasValue(fieldKey: string, optionValue: any) {
  return !!selectedValuesByField[fieldKey]?.has(optionValue)
}
function toggleValue(fieldKey: string, optionValue: any) {
  const selectedSet = selectedValuesByField[fieldKey] ?? new Set<any>()
  selectedSet.has(optionValue) ? selectedSet.delete(optionValue) : selectedSet.add(optionValue)
  selectedValuesByField[fieldKey] = selectedSet
  pushModel()
}
function syncSelect(fieldKey: string) {
  pushModel()
}

/** badge count (optional) */
const selectedBadgeCount = computed(() => {
  let count = 0
  for (const filterField of configuredFields.value) {
    if (filterField.fieldType === 'checkbox') {
      count += (selectedValuesByField[filterField.fieldKey]?.size ?? 0)
    } else if (filterField.fieldType === 'select' && selectedSingleByField[filterField.fieldKey] !== (filterField.allValue ?? 'all')) {
      count += 1
    }
  }
  return count
})

/** Filter All indicators */
const totalCheckboxOptionsCount = computed(() =>
  configuredFields.value
    .filter(field => field.fieldType === 'checkbox')
    .reduce((sum, field) => sum + field.fieldOptions.length, 0)
)
const totalCheckboxSelectedCount = computed(() =>
  configuredFields.value
    .filter(field => field.fieldType === 'checkbox')
    .reduce((sum, field) => sum + (selectedValuesByField[field.fieldKey]?.size ?? 0), 0)
)
const hasAnyCheckbox = computed(() =>
  configuredFields.value.some(field => field.fieldType === 'checkbox')
)
const isAllSelected = computed(() =>
  totalCheckboxSelectedCount.value > 0 && totalCheckboxSelectedCount.value === totalCheckboxOptionsCount.value
)
const isIndeterminate = computed(() =>
  totalCheckboxSelectedCount.value > 0 && totalCheckboxSelectedCount.value < totalCheckboxOptionsCount.value
)

function toggleAll(event: Event) {
  const checked = (event.target as HTMLInputElement).checked
  for (const filterField of configuredFields.value.filter(f => f.fieldType === 'checkbox')) {
    selectedValuesByField[filterField.fieldKey] = new Set(
      checked ? filterField.fieldOptions.map(option => option.value) : []
    )
  }
  pushModel()
}

/** v-model */
function pushModel() {
  const output: Record<string, any> = {}
  for (const filterField of configuredFields.value) {
    output[filterField.fieldKey] =
      filterField.fieldType === 'checkbox'
        ? Array.from(selectedValuesByField[filterField.fieldKey] ?? [])
        : selectedSingleByField[filterField.fieldKey]
  }
  emit('update:modelValue', output)
}

/** Clear (สำหรับเรียกผ่าน ref ได้) */
function clear() {
  for (const filterField of configuredFields.value) {
    if (filterField.fieldType === 'checkbox') selectedValuesByField[filterField.fieldKey] = new Set()
    else selectedSingleByField[filterField.fieldKey] = filterField.allValue ?? 'all'
  }
  pushModel()
  emit('clear')
}

/** click-outside */
function onDocumentClick(event: MouseEvent) {
  if (!dropdownRoot.value) return
  if (!dropdownRoot.value.contains(event.target as Node)) isOpen.value = false
}
onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick))
</script>


