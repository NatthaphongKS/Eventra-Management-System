<!-- src/components/Button/CancelButton.vue -->
<template>
  <header class="max-w-6xl mx-auto px-6 pt-6">
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
  </header>

  <button
    type="button"
    @click="$emit('click')"
    class="inline-flex items-center justify-center gap-2.5 w-[140px] h-[45px] rounded-[20px] border border-neutral-200 bg-red-700 text-white
           font-bold leading-none shadow-sm transition
           hover:border-black/20 hover:shadow-md
           active:translate-y-px active:shadow-inner
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/35"
    :class="[
      size === 'sm' ? 'px-3.5 py-2 text-[13px]' :
      size === 'lg' ? 'px-5 py-3 text-[15px]' :
                       'px-7 py-2.5 text-[15px]',       // default md
      block ? 'w-full justify-center' : ''
    ]"
  >
    <span class="inline-flex" aria-hidden="true">
      <!-- ใช้ Material Symbols ตามที่ให้มา -->
      <span class="material-symbols-outlined text-[18px] leading-none">close</span>
    </span>

    <span class="inline-block">
      <slot>Cancel</slot>
    </span>
  </button>
</template>

<script setup lang="ts">
defineEmits<{ (e: 'click'): void }>()

withDefaults(defineProps<{
  size?: 'sm' | 'md' | 'lg'
  block?: boolean
}>(), {
  size: 'md',
  block: false
})
</script>
