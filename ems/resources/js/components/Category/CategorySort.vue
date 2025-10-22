<template>
  <div class="relative" @keydown.escape="open=false">
    <button
      class="inline-flex items-center gap-2 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-gray-50"
      @click="open = !open"
      :title="modelValue === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'"
    >
      <Icon icon="lucide:sort-desc" width="30" height="20" />
      <p>Sort</p>
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
import { Icon } from '@iconify/vue'

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
