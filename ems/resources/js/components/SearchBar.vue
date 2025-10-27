<template>
  <div class="flex flex-row items-center gap-3 overflow-visible">
    <div>
      <p class="text-neutral-700 font-medium text-2xl">Search</p>
      <input
        v-model.trim="searchInput"
        type="text"
        :placeholder="placeholder"

        @keyup.enter="emitSearch"
        class="px-4 w-[650px] h-[50px] rounded-[20px] border border-neutral-200 bg-white
               focus:border-red-300 outline-none
               text-neutral-700 placeholder-red-300 shadow-sm transition-colors duration-200 font-medium text-base"
      />
    </div>

    <button
      @click="emitSearch"
      class="w-[50px] h-[50px] flex items-center justify-center rounded-full bg-red-700 text-white
              transition-all duration-200 shadow-sm mt-8"
      aria-label="Search"
      title="ค้นหา"
    >
      <MagnifyingGlassIcon class="w-[30px] h-[30px]" />
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
