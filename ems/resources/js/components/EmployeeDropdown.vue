<template>
    <div class="relative inline-block text-left" ref="dropdownRef">
      <button
        type="button"
        @click="toggle"
        class="flex h-[50px] items-center justify-center rounded-[17px] border border-neutral-100 bg-white px-4 py-2 text-[16px] font-medium text-neutral-700 shadow-sm transition hover:bg-neutral-50 focus:outline-none min-w-[140px]"
        :class="hasSelection ? 'border-rose-700 text-rose-700' : ''"
      >
        <span class="mr-1">{{ label }}</span>

        <svg
          :class="[
            'ml-2 h-7 w-7 text-rose-700 transition-transform duration-200',
            show ? 'rotate-180 text-rose-700' : '',
            hasSelection ? 'text-rose-700' : ''
          ]"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M6 9l6 6 6-6" />
        </svg>
      </button>

      <div
        v-if="show"
        class="absolute left-0 z-20 mt-2 w-[200px] rounded-[15px] border border-neutral-200 bg-white p-3 shadow-lg max-h-60 overflow-y-auto"
      >
        <p
          class="mb-2 cursor-pointer text-[14px] font-semibold text-sky-500 hover:underline px-1"
          @click="toggleAll"
        >
          Select All
        </p>

        <div v-for="opt in options" :key="opt.value" class="mb-1 flex items-center gap-2 hover:bg-rose-50 p-1 rounded-lg cursor-pointer" @click="toggleItem(opt.value)">
          <input
            type="checkbox"
            :id="`${label}-${opt.value}`"
            :checked="modelValue.includes(opt.value)"
            class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-rose-600 pointer-events-none"
          />
          <label :for="`${label}-${opt.value}`" class="cursor-pointer text-[14px] text-neutral-700 pointer-events-none select-none flex-1">
            {{ opt.label }}
          </label>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

  const props = defineProps({
    label: { type: String, default: 'Select' },
    options: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    // [NEW] รับค่าสถานะเปิด/ปิดจาก Parent (ถ้าไม่ส่งมาจะเป็น undefined)
    isOpen: { type: Boolean, default: undefined }
  })

  // [NEW] เพิ่ม 'toggle' เพื่อแจ้ง Parent
  const emit = defineEmits(['update:modelValue', 'toggle'])

  const internalOpen = ref(false)
  const dropdownRef = ref(null)

  // [NEW] Computed เพื่อเลือกใช้ state จาก Parent (ถ้ามี) หรือ Internal
  const show = computed(() => {
    return props.isOpen !== undefined ? props.isOpen : internalOpen.value
  })

  const hasSelection = computed(() => props.modelValue && props.modelValue.length > 0)

  // ฟังก์ชัน Toggle
  function toggle() {
    if (props.isOpen !== undefined) {
      // ถ้า Parent คุม -> แจ้ง Parent ให้สลับค่า
      emit('toggle')
    } else {
      // ถ้าไม่มีใครคุม -> ทำงานด้วยตัวเอง
      internalOpen.value = !internalOpen.value
    }
  }

  // Logic การเลือก Item (ปรับให้คลิกที่แถวได้เลย ไม่ต้องเล็ง Checkbox)
  function toggleItem(val) {
    const newVal = [...props.modelValue]
    const idx = newVal.indexOf(val)
    if (idx > -1) {
      newVal.splice(idx, 1)
    } else {
      newVal.push(val)
    }
    emit('update:modelValue', newVal)
  }

  function toggleAll() {
    if (props.modelValue.length === props.options.length) {
      emit('update:modelValue', [])
    } else {
      emit('update:modelValue', props.options.map((o) => o.value))
    }
  }

  // Click Outside logic (สำหรับกรณี Standalone ที่ไม่มี Parent คุม)
  function handleClickOutside(event) {
    // ถ้า Parent คุมอยู่ (isOpen !== undefined) ให้ Parent จัดการเรื่องปิดเอง (ผ่าน activeDropdown = null)
    if (props.isOpen !== undefined) return

    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
      internalOpen.value = false
    }
  }

  onMounted(() => {
    document.addEventListener('click', handleClickOutside)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
  })
  </script>

  <style scoped>
  /* Custom Scrollbar */
  .overflow-y-auto::-webkit-scrollbar {
      width: 6px;
  }
  .overflow-y-auto::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
  }
  .overflow-y-auto::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
  }
  .overflow-y-auto::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
  }
  </style>