<template>
  <div class="flex items-end gap-3 w-full" v-bind="$attrs">
    <div class="flex-1 min-w-0">
      <p class="text-neutral-800 font-medium text-2xl mb-1">Search</p>

      <div class="relative">
        <input
          v-model.trim="searchInput"
          type="text"
          :placeholder="placeholder"
          @keyup.enter="emitSearch"
          class="w-full h-[52px] pl-4 pr-10 rounded-[20px] border border-neutral-200 bg-white
                 focus:border-red-300 outline-none
                 text-neutral-700 placeholder-red-300 shadow-sm transition-colors duration-200 font-medium text-base"
        />

        <button
          v-if="searchInput"
          @click="clearSearch"
          type="button"
          class="absolute  right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-neutral-100 "
          title="ล้างคำค้นหา"
        >
          <Icon icon="iconoir:cancel" width="30" height="30" class="text-neutral-400 text-xs"/>
        </button>
      </div>
    </div>

    <button
      @click="emitSearch"
      class="w-[62px] h-[60px] flex items-center justify-center rounded-full bg-red-700 text-white transition-all duration-200 shadow-sm shrink-0 hover:bg-red-800 active:scale-95"
      aria-label="Search"
      title="ค้นหา"
    >
      <Icon icon="material-symbols:search-rounded" width="40" height="40"/>
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { Icon } from "@iconify/vue";

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: String,
  placeholder: { type: String, default: "Search..." },
});

const emit = defineEmits(["update:modelValue", "search"]);
const searchInput = ref(props.modelValue || "");

function emitSearch() {
  emit("update:modelValue", searchInput.value);
  emit("search", searchInput.value);
}

// ฟังก์ชันล้างค่า
function clearSearch() {
  searchInput.value = "";
  emitSearch(); // สั่งค้นหาด้วยค่าว่างทันที (Reset ผลลัพธ์)
}

watch(() => props.modelValue, (v) => {
  if (v !== searchInput.value) searchInput.value = v || "";
});
</script>
