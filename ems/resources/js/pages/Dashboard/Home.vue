<template>
  <div>
    <h2 class="text-neutral-800 font-medium text-2xl">Welcome to Dashboard</h2>
  </div>
  <div> 
    <!-- <button @click="showProfile()">แสดงข้อมูลพนักงานที่เข้าสู่ระบบ</button> -->
  </div>
</template>

<script setup>
import axios from 'axios';

// ประกาศฟังก์ชันเป็น async และใช้ await เพื่อรอผลลัพธ์จาก API
const showProfile = async () => {
  try {
    const response = await axios.get('/showProfile');
    // ผลลัพธ์ที่ได้จะอยู่ใน response.data
    console.log('ข้อมูลพนักงาน:', response.data); 
  } catch (error) {
    console.error('เกิดข้อผิดพลาดในการเรียก API:', error);
  }
};
</script>

<style scoped>
/* เพิ่ม style เฉพาะใน component นี้ ถ้าจำเป็น */
</style>