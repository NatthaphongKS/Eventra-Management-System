<template>
  <teleport to="body">
    <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-2xl shadow-xl text-center w-[440px] max-w-[92vw] p-8">
        <div class="mx-auto w-16 h-16 rounded-full bg-emerald-500 grid place-items-center shadow-inner">
          <CheckCircleIcon class="w-10 h-10 text-white" />
        </div>
        <h3 class="mt-5 text-xl font-extrabold tracking-wide text-gray-800">DELETE SUCCESS!</h3>
        <p class="mt-2 text-sm text-gray-500">We have deleted a new employee.</p>
        <div class="mt-6">
          <button type="button" @click="$emit('close')" class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" autofocus>
            OK
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

export default {
  name: 'DeleteSucces',
  components: { CheckCircleIcon },
  props: { open: { type: Boolean, default: false } },
  emits: ['close'],
};
</script>
