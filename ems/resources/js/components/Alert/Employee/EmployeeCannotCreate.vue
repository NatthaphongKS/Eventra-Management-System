<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/40" @click="close"></div>
    <div class="bg-white rounded-lg shadow-lg max-w-xl w-full mx-4 p-6 z-10">
      <h3 class="text-xl font-semibold mb-2">Cannot create employees</h3>
      <p class="text-sm text-gray-600 mb-4">{{ message }}</p>
      <div class="flex justify-end">
        <button @click="close" class="px-4 py-2 rounded bg-red-600 text-white">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  message: { type: String, default: '' },
})

const emit = defineEmits(['close'])

function close() {
  emit('close')
}
</script>
