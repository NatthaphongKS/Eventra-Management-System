<template>
  <teleport to="body">
    <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" @click="$emit('cancel')"></div>
      <div class="relative bg-white rounded-2xl shadow-xl text-center w-[360px] max-w-[90vw] p-6">
        <div class="mx-auto w-14 h-14 rounded-full bg-yellow-400 grid place-items-center shadow-inner">
          <QuestionMarkCircleIcon class="w-10 h-10 text-white" />
        </div>
        <h3 class="mt-4 font-semibold">ARE YOU SURE TO DELETE?</h3>
        <p class="mt-2 text-xs leading-5 text-gray-500">
          This will be deleted permanently.<br />Are you sure?
        </p>
        <div class="mt-5 flex items-center justify-center gap-3">
          <button type="button" @click="$emit('cancel')" class="w-28 h-10 rounded-lg bg-rose-600 text-white hover:bg-rose-700">
            Cancel
          </button>
          <button type="button" @click="$emit('confirm')" class="w-28 h-10 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" autofocus>
            OK
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/solid';

export default {
  name: 'ConfirmDelete',
  components: { QuestionMarkCircleIcon },
  props: { open: { type: Boolean, default: false } },
  emits: ['confirm', 'cancel'],
};
</script>
