<template>
  <div class="flex items-center gap-3 mb-4 w-full overflow-visible">
    <div>
      <p class="text-neutral-700 font-medium text-2xl">Search</p>
      <input
        v-model.trim="searchInput"
        type="text"
        :placeholder="placeholder"
        @input="emitSearch"
        @keyup.enter="emitSearch"
        class="flex-1 w-[1000px] h-[58px] px-4 rounded-[20px] border border-neutral-200 bg-white
               focus:border-red-300 outline-none
               text-neutral-700 placeholder-red-300 shadow-sm transition-colors duration-200 font-medium text-base"
      />
    </div>

    <button
      @click="emitSearch"
      class="w-[70px] h-[70px] flex items-center justify-center rounded-full bg-red-700 text-white
              transition-all duration-200 shadow-sm"
      aria-label="Search"
      title="ค้นหา"
    >
      <MagnifyingGlassIcon class="w-[50px] h-[50px]" />
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: "Search...",
  },
});

const emit = defineEmits(["update:modelValue", "search"]);
const searchInput = ref(props.modelValue || "");

function emitSearch() {
  emit("update:modelValue", searchInput.value);
  emit("search", searchInput.value);
}

// sync ค่าระหว่างภายนอกกับภายใน
watch(() => props.modelValue, (v) => {
  if (v !== searchInput.value) searchInput.value = v || "";
});
</script>
