<template>
  <div class="relative" @keydown.escape="open=false">
    <button
      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      @click="open = !open"
      :title="modelValue === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
      </svg>
      <span>Sort</span>
      <span class="text-gray-400">({{ modelValue === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด' }})</span>
    </button>

    <div
      v-if="open"
      class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg"
    >
      <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="setDir('desc')">
        วันที่ล่าสุด
      </button>
      <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="setDir('asc')">
        วันที่เก่าสุด
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";

type Dir = "asc" | "desc";
const props = defineProps<{ modelValue: Dir }>();
const emit = defineEmits<{ (e:"update:modelValue", v: Dir): void }>();

const open = ref(false);

function setDir(v: Dir){
  emit("update:modelValue", v);
  open.value = false;
}

function onDocClick(e: MouseEvent){
  const el = e.target as HTMLElement;
  if (!el.closest(".relative")) open.value = false;
}

onMounted(() => document.addEventListener("click", onDocClick));
onBeforeUnmount(() => document.removeEventListener("click", onDocClick));
</script>
