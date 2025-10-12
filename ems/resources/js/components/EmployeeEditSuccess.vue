<template>
  <teleport to="body">
    <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-3xl shadow-xl text-center w-[517px] h-[558px] max-w-[90vw] p-6">
        <div class="mx-auto w-[144px] h-[144px] rounded-full bg-white grid place-items-center mt-20">
          <CheckCircleIcon class="text-green-600" />
        </div>
        <h3 class="mt-6 text-3xl font-bold text-neutral-800 text-center">EDIT SUCCESS!</h3>
        <p class="mt-12 text-xl font-semibold leading-6 text-neutral-800">This  employee has been successfully edited.</p>
        <div class="mt-16 flex items-center justify-center gap-20 text-xl font-semibold">
          <button type="button" @click="$emit('close')" class="w-[168px] h-[58px] rounded-[20px] bg-green-600 border border-neutral-200 text-white hover:bg-emerald-700" autofocus>
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
  name: 'EmployeeEditSuccess',
  components: { CheckCircleIcon },
  props: { open: { type: Boolean, default: false } },
  emits: ['close'],
};
</script>
