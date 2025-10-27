<template>
  <div class="relative inline-block text-left">
    <!-- ปุ่มหลัก -->
    <button
      @click="toggle"
      class="flex h-[50px] items-center justify-center rounded-[25px] border border-neutral-200 bg-white px-4 py-2 text-[16px] font-medium text-neutral-700 shadow-sm transition hover:bg-neutral-50 focus:outline-none"
    >
      {{ label }}
      <svg
        :class="[
          'ml-2 h-4 w-4 text-red-600 transition-transform duration-200',
          open ? 'rotate-180' : ''
        ]"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <path d="M6 9l6 6 6-6" />
      </svg>
    </button>

    <!-- เมนู dropdown -->
    <div
      v-if="open"
      class="absolute left-0 z-20 mt-2 w-[200px] rounded-[15px] border border-neutral-200 bg-white p-3 shadow-lg"
    >
      <p
        class="mb-2 cursor-pointer text-[14px] font-semibold text-blue-500 hover:underline"
        @click="toggleAll"
      >
        Select All
      </p>

      <div v-for="opt in options" :key="opt.value" class="mb-1 flex items-center gap-2">
        <input
          type="checkbox"
          :id="opt.value"
          v-model="selectedValues"
          :value="opt.value"
          class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-red-600"
        />
        <label :for="opt.value" class="cursor-pointer text-[14px] text-neutral-700">
          {{ opt.label }}
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  label: { type: String, default: 'Department' },
  options: { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:modelValue'])
const open = ref(false)
const selectedValues = ref([...props.modelValue])

function toggle() {
  open.value = !open.value
}
function toggleAll() {
  if (selectedValues.value.length === props.options.length) {
    selectedValues.value = []
  } else {
    selectedValues.value = props.options.map((o) => o.value)
  }
}
watch(selectedValues, (v) => emit('update:modelValue', v))

// ปิด dropdown เมื่อคลิกรอบนอก
function handleClickOutside(e) {
  if (!e.target.closest('.relative')) open.value = false
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>
