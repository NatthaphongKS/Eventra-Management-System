<!-- resources/js/components/IndexEvent/EventSort.vue -->
<template>
  <div ref="root" class="relative inline-block">
    <button
      type="button"
      class="inline-flex h-11 items-center gap-2 px-3 text-neutral-800 font-medium font-[Poppins]
             hover:text-slate-900 focus:outline-none  rounded-md "
      @click="open = !open"
    >
      <!-- ใช้สีตามตัวอักษร เพื่อมองเห็นชัดขึ้น -->
      <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20"
           viewBox="0 -960 960 960" fill="currentColor" class="opacity-70">
        <path d="M120-240v-80h240v80H120Zm0-200v-80h480v80H120Zm0-200v-80h720v80H120Z"/>
      </svg>
      <span>Sort</span>
    </button>

    <ul
      v-show="open"
      class="absolute left-0 mt-2 w-64 rounded-2xl bg-white shadow-lg ring-1 ring-black/5 p-1 z-50"
      role="menu"
    >
      <li v-for="option in optionsList" :key="option.id">
        <button
          class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-50
                 flex items-center justify-between"
          @click="select(option)"
          role="menuitem"
        >
          <span>{{ option.label }}</span>
          <span v-if="modelValue?.id === option.id" class="text-rose-600">✓</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

defineOptions({ name: 'EventSort' })

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ id: '', key: '', order: 'asc', type: 'text' }),
  },
  options: {
    type: Array,
    default: () => ([
      { id: 'title_asc',     label: 'ชื่องาน A–Z',           key: 'evn_title',      order: 'asc',  type: 'text'   },
      { id: 'title_desc',    label: 'ชื่องาน Z–A',           key: 'evn_title',      order: 'desc', type: 'text'   },
      { id: 'invited_desc',  label: 'จำนวนคนเชิญมากสุด',      key: 'evn_num_guest',  order: 'desc', type: 'number' },
      { id: 'invited_asc',   label: 'จำนวนคนเชิญน้อยสุด',     key: 'evn_num_guest',  order: 'asc',  type: 'number' },
      { id: 'accepted_desc', label: 'จำนวนคนเข้าร่วมมากสุด',   key: 'evn_sum_accept', order: 'desc', type: 'number' },
      { id: 'accepted_asc',  label: 'จำนวนคนเข้าร่วมน้อยสุด',  key: 'evn_sum_accept', order: 'asc',  type: 'number' },
      { id: 'date_desc',     label: 'วันที่จัดงานใหม่สุด',     key: 'evn_date',       order: 'desc', type: 'date'   },
      { id: 'date_asc',      label: 'วันที่จัดงานเก่าสุด',     key: 'evn_date',       order: 'asc',  type: 'date'   },
    ])
  },
  closeOnSelect: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'change'])

const open = ref(false)
const root = ref(null)
const optionsList = computed(() => props.options)

function select(option) {
  const picked = { id: option.id, key: option.key, order: option.order, type: option.type }
  emit('update:modelValue', picked)
  emit('change', picked)
  if (props.closeOnSelect) open.value = false
}

function onDocClick(e) { if (root.value && !root.value.contains(e.target)) open.value = false }
function onKey(e) { if (e.key === 'Escape') open.value = false }

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onKey)
})
onUnmounted(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKey)
})
</script>
