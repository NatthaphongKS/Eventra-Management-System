<!-- CapsuleButton.vue -->
<template>
  <header class="max-w-6xl mx-auto px-6 pt-6">
    <!-- ไว้ใช้ material symbols ถ้าต้องการ -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=download,upload"
    />
  </header>

  <!-- ปุ่มสไตล์ capsule (Tailwind ล้วน) -->
  <button
    type="button"
    @click="$emit('click')"
    class="inline-flex items-center gap-2.5 rounded-[17px] border border-neutral-200 bg-white text-neutral-800 font-semibold leading-none
           shadow-sm transition
           hover:border-black/20 hover:shadow-md
           active:translate-y-px active:shadow-inner
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/35"
    :class="[
      size === 'sm' ? 'px-3.5 py-2 text-[13px]' :
      size === 'lg' ? 'px-5 py-3 text-[15px]' :
                       'px-7 py-2.5 text-[15px]',          // default(md)
      block ? 'w-full justify-center' : ''
    ]"
  >
    <!-- ถ้าจะมีไอคอนค่อยเสียบ slot ชื่อ icon -->
    <span v-if="$slots.icon" class="inline-flex">
      <slot name="icon" />
    </span>

    <!-- label -->
    <span class="inline-block text-sky-600">
      <slot>*Click to download template excel file*</slot>
    </span>
  </button>
</template>

<script setup lang="ts">
// รองรับคลิก
defineEmits<{ (e: 'click'): void }>()

// รองรับตัวแปรขนาดและ block display
const props = withDefaults(defineProps<{
  size?: 'sm' | 'md' | 'lg'
  block?: boolean
}>(), {
  size: 'md',
  block: false
})
</script>
