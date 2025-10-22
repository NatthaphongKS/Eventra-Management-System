<template>
  <div class="flex items-center gap-3 mb-4 w-full overflow-visible">
    <!-- ช่องค้นหา -->
    <input
      v-model.trim="searchInput"
      type="text"
      placeholder="Search..."
      @keyup.enter="$emit('search', searchInput)"
      class="flex-1 h-10 w-full px-4 rounded-[20px] border border-neutral-300 bg-neutral-200
              focus:border-red-300 outline-none
             text-gray-700 placeholder-gray-500 shadow-sm transition-colors duration-200"
    />

    <!-- ปุ่มค้นหา -->
    <button
      @click="$emit('search', searchInput)"
      class="w-10 h-10 flex items-center justify-center rounded-full bg-red-700 text-white
              transition-all duration-200 shadow-sm"
      aria-label="Search"
      title="ค้นหา"
    >
      <MagnifyingGlassIcon class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

const searchInput = ref("");
defineEmits(["search"]);
</script>
