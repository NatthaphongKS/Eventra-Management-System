<template>
  <!-- กล่องหลักของ Search bar -->
  <div class="flex items-center gap-3 mb-4">
    <!-- ==================== ช่องค้นหา ==================== -->

    <input
      v-model.trim="searchInput"
      type="text"
      placeholder="Search..."
      @keyup.enter="$emit('search', searchInput)"
      class="flex-1 h-10 px-4 rounded-full border border-gray-300 bg-white outline-none
             focus:ring-2 focus:ring-rose-300 focus:border-rose-500"
    />

    <!-- ==================== ปุ่มค้นหา ==================== -->
    <button
      @click="$emit('search', searchInput)"
      class="w-10 h-10 flex items-center justify-center rounded-full bg-red-700 text-white
             hover:bg-rose-700 transition"
      aria-label="Search"
      title="ค้นหา"
    >   
      <!-- ไอคอนแว่นขยาย -->
      <MagnifyingGlassIcon class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
/*
  ==============================
  ส่วน Script (Composition API)
  ==============================
*/
import { ref } from "vue";  // ใช้สร้างตัวแปร reactive
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline"; // นำเข้าไอคอนแว่นขยาย

// ตัวแปรเก็บข้อความที่ผู้ใช้พิมพ์ในช่องค้นหา
const searchInput = ref("");

// กำหนด Event ที่ component จะส่งออก (ให้ parent ฟัง)
defineEmits(["search"]);
</script>
