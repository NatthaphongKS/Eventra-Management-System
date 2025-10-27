<template>
  <EventCheckInEvent :rows="rows" v-model="selectedIds" :page="page" :page-size="pageSize"
    @update:page="(p) => page = p" @update:page-size="(s) => pageSize = s" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import EventCheckInEvent from '@/components/IndexEvent/EventCheckInEvent.vue'
//ข้อมูล mock 

const route = useRoute()
const eveId = route.params.id // ปรับตามที่เก็บ eveId
console.log('Event ID:', eveId)
const rows = ref([])

async function fetchEmployeeAttendancers() {
  try {
    const res = await axios.get(`/api/getEmployeeAttendancers/eveId/${eveId}`)
    // คืนเป็น array โดยดูจากโครงสร้างจริงของ response
    console.log('Fetched employee attendances:', res.data)
    return res.data.data ?? res.data ?? []
  } catch (err) {
    console.error('Failed to fetch employee attendances:', err)
  }
}

const selectedIds = ref([])
const page = ref(1)
const pageSize = ref(10)

// เรียก API เมื่อเข้าหน้า
onMounted(async () => {
  rows.value = await fetchEmployeeAttendancers()
})
</script>
