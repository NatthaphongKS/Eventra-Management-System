<!-- pages/edit_event.vue -->
<template>
    <div>
        <RouterLink to="/event" class="inline-block px-3 py-2 rounded bg-green-600 text-white">
            ← กลับ
        </RouterLink>
        <h2>Edit event</h2>

        <!-- Event Title -->
        <div style="margin: 20px">
            <label>Event Title</label><br />
            <input type="text" v-model.trim="form.evn_title" required
                style="width:300px;height:30px;border-radius:5px;border:1px solid #ccc;padding:5px;" />

            <!-- category -->
            <label>category</label>
            <select v-model="form.evn_category_id" required
                style="width:300px;height:36px;border-radius:6px;border:1px solid #ccc;padding:6px;">
                <option disabled value="">-- เลือกหมวดหมู่ --</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">
                    {{ c.cat_name }}
                </option>
            </select>
        </div>

        <!-- description -->
        <div style="margin: 20px">
            <label>description</label><br />
            <input type="textarea" v-model.trim="form.evn_description"
                style="width:300px;height:80px;border-radius:5px;border:1px solid #ccc;padding:5px;" />
        </div>
        <!-- Date -->

        <div style="margin: 20px">
            <label>Date</label><br />
            <input type="date" v-model="form.evn_date"
                style="width:150px;height:30px;border-radius:5px;border:1px solid #ccc;padding:5px;" />

            <!-- Time -->
            <div style="display: flex; gap: 12px; align-items: center">
                <label>Time</label>
                <input v-model="form.evn_timestart" type="time" required />
                <span>:</span>
                <input v-model="form.evn_timeend" type="time" required />
                <span style="opacity: 0.7">→ {{ durationLabel }}</span>
            </div>

            <!-- Duration -->
            <div style="margin: 20px">
                <label>Duration</label><br />
                <input :value="durationLabel" type="text" readonly placeholder="Auto fill (minutes)" />
            </div>

            <!-- Location -->
            <div style="margin: 20px">
                <label>location</label><br />
                <input type="text" v-model="form.evn_location" readonly
                    style="width:500px;height:30px;border-radius:5px;border:1px solid #ccc;padding:5px;" />
            </div>
        </div>

        <!-- Upload -->
        <div style="margin: 20px">
            <label>Upload attachments</label>
            <div class="dropzone" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop" :class="{ dragging }">
                <p>Choose a file or drag & drop it here</p>
                <p class="muted">pdf, txt, docx, jpeg, xlsx – Up to 50MB</p>
                <button type="button" @click="pickFiles">Browse files</button>
                <input ref="fileInput" type="file" v-on:change="form.evn_file" multiple class="hidden-file"
                    accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
            </div>
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
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'

const route = useRoute()
const router = useRouter()
const id = Number(route.params.id)

const categories = ref([])
const form = ref({
    id,
    evn_title: '',
    evn_category_id: '',
    evn_description: '',
    evn_date: '',
    evn_timestart: '',
    evn_timeend: '',
    evn_duration: 0,
    evn_location: ''  // กำหนดค่าเริ่มต้น
})
const loading = ref(false)
const message = ref('')

onMounted(async () => {
    try {
        const catRes = await axios.get('/categories')
        categories.value = catRes.data

        const { data } = await axios.get(`/edit_event/${id}`)
        const e = data?.event ?? {}

        form.value.evn_title = e.evn_title ?? ''
        form.value.evn_category_id = e.evn_category_id ? Number(e.evn_category_id) : ''
        form.value.evn_description = e.evn_description ?? ''
        form.value.evn_date = (e.evn_date ?? '').split('T')[0].split(' ')[0] || ''
        form.value.evn_timestart = e.evn_timestart ?? ''
        form.value.evn_timeend = e.evn_timeend ?? ''
        form.value.evn_duration = e.evn_duration ?? 0
        form.value.evn_location = e.evn_location ?? ''
        form.value.evn_file = e.evn_file ?? ''
    } catch {
        message.value = 'โหลดข้อมูลไม่สำเร็จ'
    }
})

/* ---------- helpers ---------- */
function toMinutes(hhmm) {
    if (!hhmm) return null
    const [h, m] = hhmm.split(':').map(Number)
    if (Number.isNaN(h) || Number.isNaN(m)) return null
    return h * 60 + m
}

/* นาทีที่คำนวณได้จาก time start/end (รองรับกรณีข้ามเที่ยงคืน) */
const durationMinutes = computed(() => {
    const s = toMinutes(form.value.evn_timestart)
    const e = toMinutes(form.value.evn_timeend)
    if (s == null || e == null) return 0
    let end = e
    if (end < s) end += 24 * 60 // ถ้าสิ้นสุดน้อยกว่าเริ่ม -> ถือว่าข้ามวัน
    return end - s
})

/* แสดงผลสวยๆ */
const durationLabel = computed(() => {
    const m = durationMinutes.value
    if (!m) return 'Auto fill'
    const h = Math.floor(m / 60)
    const mm = m % 60
    if (h && mm) return `${h}h ${mm}m`
    if (h) return `${h}h`
    return `${mm}m`
})

/* sync ค่า computed -> form.evn_duration */
watch(durationMinutes, (m) => {
    form.value.evn_duration = m
}, { immediate: true })

async function confirmEdit() {
    loading.value = true
    message.value = ''
    try {
        const { data } = await axios.post('/edit-event', {
            id: form.value.id,
            evn_title: form.value.evn_title,
            evn_category_id: form.value.evn_category_id,
            evn_description: form.value.evn_description,
            evn_date: form.value.evn_date,
            evn_timestart: form.value.evn_timestart,
            evn_timeend: form.value.evn_timeend,
            evn_duration: form.value.evn_duration,     // << ส่งนาทีไปด้วย
            evn_location: form.value.evn_location,   // << ส่ง location ไปด้วย
            evn_file: form.value.evn_file  
        })
        message.value = data?.message ?? 'บันทึกสำเร็จ'
        router.push('/event')
    } catch (e) {
        message.value = e.response?.data?.message ?? 'บันทึกไม่สำเร็จ'
    } finally {
        loading.value = false
    }
}
</script>
