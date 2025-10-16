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
    class="inline-flex items-center gap-2.5 rounded-[17px] border border-neutral-200 bg-red-700 text-white
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
      <span class="material-symbols-outlined text-[18px] leading-none">add</span>
    </span>

    <span class="inline-block">
      <slot>Add</slot>
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






















<!--
<template>
     <header class="max-w-6xl mx-auto px-6 pt-6">
<link rel="stylesheet"
  href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    </header>

  <button type="button" class="btn-capsule">
    <span class="btn-capsule__icon material-symbols-outlined text-[16px] leading-none" aria-hidden="true">
    <span class="material-symbols-outlined">add</span>
    </span>
    <span class="btn-capsule__label"><slot>Add</slot></span>
  </button>
</template>

<style scoped>
.btn-capsule{
  display:inline-flex; align-items:center; gap:10px;
  padding:10px 24.5px;
  background:#C91818; color:#ffffff; /* gray-800 */
  border:1px solid rgba(0,0,0,.12);
  border-radius:17px;
  box-shadow:0 1px 1px rgba(0,0,0,.05), 0 2px 6px rgba(0,0,0,.06);
  font-size:15px; font-weight:700; line-height:1;
  cursor:pointer; text-decoration:none;
  transition:border-color .2s, box-shadow .2s, transform .02s, background .2s;
  font-family: "Poppins", "Noto Sans Thai", system-ui, -apple-system, "Segoe UI",
               Roboto, "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
}
.btn-capsule:hover{ background:#B31212; border-color:rgba(0,0,0,.18); box-shadow:0 2px 10px rgba(0,0,0,.08); }
.btn-capsule:active{ transform:translateY(.5px); box-shadow:inset 0 1px 2px rgba(0,0,0,.08); }
.btn-capsule:focus-visible{ outline:none; box-shadow:0 1px 1px rgba(0,0,0,.05), 0 2px 6px rgba(0,0,0,.06), 0 0 0 3px rgba(59,130,246,.35); }

.btn-capsule__icon{ display:inline-flex; }
.icon{ width:18px; height:18px; display:block; fill:currentColor; }
.btn-capsule__label{ display:inline-block; }

/* size mods (ถ้าต้องการ) */
.btn-capsule.is-sm{ padding:8px 14px; font-size:13px; }
.btn-capsule.is-lg{ padding:12px 20px; font-size:15px; }
.btn-capsule.is-block{ display:flex; width:100%; justify-content:center; }

</style> -->
