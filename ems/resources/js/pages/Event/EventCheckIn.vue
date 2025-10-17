<template>
  <EventCheckInEvent
    :rows="employees"
    v-model="selectedIds"
    :page="page"
    :page-size="pageSize"
    @update:page="(p) => page = p"
    @update:page-size="(s) => pageSize = s"
  />
</template>

<script setup>
import { ref } from 'vue'
import EventCheckInEvent from '@/components/IndexEvent/EventCheckInEvent.vue'

const employees = ref([
  { id:1, emp_id:'CH0001', emp_prefix:'นาย', emp_firstname:'พศิน', emp_lastname:'สายชล', emp_nickname:'พีท', department_name:'Engineering', team_name:'CodeCraft', position_name:'Software Engineer' },
  { id:2, emp_id:'CH0002', emp_prefix:'น.ส.', emp_firstname:'อรทัย', emp_lastname:'จงรัก', emp_nickname:'อร', department_name:'Product Development', team_name:'DataDynamics', position_name:'Frontend Developer' },
])

const selectedIds = ref([])
const page = ref(1)
const pageSize = ref(10)
</script>
