<!-- pages/edit_event.vue -->
<template>
  <div>
    <RouterLink to="/event" class="inline-block px-3 py-2 rounded bg-green-600 text-white">
      ← กลับ
    </RouterLink>
    <h2>Edit event</h2>

    <div style="margin: 20px">
      <label>Event Title</label><br />
      <input
        type="text"
        v-model.trim="form.evn_title"

        required
        style="width:300px;height:30px;border-radius:5px;border:1px solid #ccc;padding:5px;"
      />
    </div>

    <Button id="confirm_edit" @click="confirmEdit" :disabled="loading">
      {{ loading ? 'กำลังบันทึก...' : 'confirm edit' }}
    </Button>

    <p v-if="message" style="margin-top:8px">{{ message }}</p>
  </div>
</template>

<script setup>
import Button from '../components/ButtonSuccess.vue'
import axios from 'axios'
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id) // รับ id จาก URL

const form = ref({ id, evn_title: '' })
const loading = ref(false)
const message = ref('')

onMounted(async () => {
  try {
    const { data } = await axios.get(`/edit_event/${id}`)
    form.value.evn_title = data?.event?.evn_title ?? ''
  } catch {
    message.value = 'โหลดข้อมูลอีเวนต์ไม่สำเร็จ'
  }
})

async function confirmEdit () {
  loading.value = true
  message.value = ''
  try {
    const { data } = await axios.post('/edit-event', {
      id: form.value.id,
      evn_title: form.value.evn_title,
    })
    message.value = data?.message ?? 'บันทึกสำเร็จ'
    // กลับหน้า Event หรือจะอยู่หน้าเดิมก็ได้
    router.push('/event')
  } catch (e) {
    message.value = e.response?.data?.message ?? 'บันทึกไม่สำเร็จ'
  } finally {
    loading.value = false
  }
}
</script>
