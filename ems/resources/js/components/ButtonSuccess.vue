<template>
  <!-- ใช้ <slot> ไว้สำหรับใส่ข้อความปุ่มจากข้างนอก -->
  <button
    class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-blue-700"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<script setup>
// ไม่มี script พิเศษ ใช้ $emit เพื่อส่ง event click ออกไป
</script>
