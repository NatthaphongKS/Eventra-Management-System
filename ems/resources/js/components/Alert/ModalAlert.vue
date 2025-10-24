<!-- components/Alert/ModalAlert.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="w-[400px] rounded-2xl bg-white p-6 text-center shadow-lg">
      <!-- Icon -->
      <div
        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full"
        :class="{
          'bg-green-100 text-green-600': type === 'success',
          'bg-red-100 text-red-600': type === 'error',
          'bg-yellow-100 text-yellow-600': type === 'warning',
          'bg-blue-100 text-blue-600': type === 'confirm',
        }"
      >
        <component :is="iconComp" class="h-10 w-10" />
      </div>

      <!-- Title + Message -->
      <h2 class="mb-2 text-lg font-bold text-neutral-800">{{ title }}</h2>
      <p class="mb-6 text-sm text-neutral-600">{{ message }}</p>

      <!-- Buttons -->
      <div class="flex justify-center gap-3">
        <button
          v-if="showCancel"
          class="rounded-lg bg-rose-600 px-6 py-2 font-medium text-white hover:bg-rose-700"
          @click="onCancel"
        >
          {{ cancelText }}
        </button>
        <button
          class="rounded-lg bg-green-600 px-6 py-2 font-medium text-white hover:bg-green-700"
          @click="onConfirm"
        >
          {{ okText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
// ✅ ใช้ heroicons (solid)
import {
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/solid'

const open = defineModel('open', { type: Boolean, default: false })

const props = defineProps({
  type: { type: String, default: 'success' }, // success | error | warning | confirm
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  showCancel: { type: Boolean, default: false },
  okText: { type: String, default: 'OK' },
  cancelText: { type: String, default: 'Cancel' },
})

const emit = defineEmits(['confirm', 'cancel'])

const iconComp = computed(() => {
  switch (props.type) {
    case 'success': return CheckCircleIcon
    case 'error':   return XCircleIcon
    case 'warning': return ExclamationTriangleIcon
    case 'confirm': return QuestionMarkCircleIcon
    default:        return CheckCircleIcon
  }
})

function onConfirm() {
  emit('confirm')
  open.value = false
}
function onCancel() {
  emit('cancel')
  open.value = false
}
</script>
