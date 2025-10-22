<template>
  <div class="flex items-center gap-3 mb-4 w-full overflow-visible">
    <!-- ช่องค้นหา -->
     <div >
      <p class="text-neutral-700 font-medium text-2xl">Search</p>
    <input
    v-model.trim="searchInput"
    type="text"
    :placeholder="placeholder"
    @keyup.enter="$emit('search', searchInput)"
    class="flex-1 w-[1000px] h-[58px] px-4 rounded-[20px] border border-neutral-200 bg-white
           focus:border-red-300 outline-none
           text-neutral-700 placeholder-red-300 shadow-sm transition-colors duration-200 font-medium text-base"
    /></div>

    <!-- ปุ่มค้นหา -->
    <button
      @click="$emit('search', searchInput)"
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
import { ref } from "vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
    const props = defineProps({
      placeholder: {
        type: String,
        default: "Search...", // ค่าเริ่มต้น
      },
    })
const searchInput = ref("");
defineEmits(["search"]);
</script>
