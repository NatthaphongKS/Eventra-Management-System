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
            <!-- ไฟล์เดิม -->
            <div v-if="filesExisting.length" class="file-list" style="margin-bottom:8px">
                <p style="margin:4px 0 8px; opacity:.8">ไฟล์เดิม</p>
                <ul style="list-style:none; padding:0; margin:0">
                    <li v-for="f in filesExisting" :key="f.id"
                        style="display:flex; gap:10px; align-items:center; padding:6px 0;">
                        <a :href="f.url" target="_blank" rel="noopener">{{ f.file_name }}</a>
                        <span style="opacity:.7; font-size:12px">
                            ({{ (f.file_size / 1024 / 1024).toFixed(2) }} MB)
                        </span>
                        <!-- ปุ่มกากบาท -->
                        <button type="button" @click="removeExisting(f.id)"
                            style="border:0; background:#eee; border-radius:6px; padding:4px 8px; cursor:pointer">
                            ✕
                        </button>
                    </li>
                </ul>
            </div>

            <!-- โซนอัปโหลดไฟล์ใหม่ (เหมือนหน้า create) -->
            <div class="dropzone" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop" :class="{ dragging }">
                <p>Choose a file or drag & drop it here</p>
                <p class="muted">pdf, txt, docx, jpeg, xlsx – Up to 50MB</p>
                <button type="button" @click="pickFiles">Browse files</button>
                <input ref="fileInput" type="file" multiple class="hidden-file"
                    accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
            </div>

            <!-- รายการไฟล์ใหม่ที่จะเพิ่ม -->
            <ul v-if="files.length" class="file-list">
                <li v-for="(f, i) in files" :key="i">
                    {{ f.name }} ({{ prettySize(f.size) }})
                    <button type="button" @click="removeFile(i)">✕</button>
                </li>
            </ul>
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
const filesExisting = ref([])         // ไฟล์เดิมจากเซิร์ฟเวอร์
const files = ref([])                 // ไฟล์ใหม่ (เหมือนหน้า create)
const dragging = ref(false)

const form = ref({
    id,
    evn_title: '',
    evn_category_id: '',
    evn_description: '',
    evn_date: '',
    evn_timestart: '',
    evn_timeend: '',
    evn_duration: 0,
    evn_location: ''
    // evn_file:  << ลบทิ้ง ไม่ให้ผูกกับฟอร์ม
})

const loading = ref(false)
const message = ref('')

onMounted(async () => {
    try {
        // หมวดหมู่ (ถ้า /categories ไม่มี endpoint ให้ใช้ /event-info แล้ว map เอาเฉพาะ categories)
        const catRes = await axios.get('/categories')
        categories.value = catRes.data

        const { data } = await axios.get(`/edit_event/${id}`)
        const e = data?.event ?? {}
        filesExisting.value = data?.files ?? []

        form.value.evn_title = e.evn_title ?? ''
        form.value.evn_category_id = e.evn_category_id ? Number(e.evn_category_id) : ''
        form.value.evn_description = e.evn_description ?? ''
        form.value.evn_date = (e.evn_date ?? '').split('T')[0].split(' ')[0] || ''
        form.value.evn_timestart = e.evn_timestart ?? ''
        form.value.evn_timeend = e.evn_timeend ?? ''
        form.value.evn_duration = e.evn_duration ?? 0
        form.value.evn_location = e.evn_location ?? ''
    } catch {
        message.value = 'โหลดข้อมูลไม่สำเร็จ'
    }
})

function removeExisting(id) {
    filesExisting.value = filesExisting.value.filter(f => f.id !== id)
    // เพิ่ม id ที่ถูกลบใส่ hidden list ส่งกลับไปหลังบ้าน
    deletedIds.value.push(id)
}

const deletedIds = ref([]) // เก็บ id ไฟล์ที่ถูกลบจริง


const MAX_FILE_MB = 50;
const ALLOW_TYPES = [
    "application/pdf",
    "text/plain",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "image/jpeg",
    "image/png",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "application/vnd.ms-excel",
];

function pickFiles() {
    fileInput.value?.click?.()
}
function onPick(e) {
    addFiles([...e.target.files])
    e.target.value = ""
}
function onDrop(e) {
    dragging.value = false
    addFiles([...e.dataTransfer.files])
}
function addFiles(list) {
    const errs = []
    list.forEach(f => {
        const tooBig = f.size > MAX_FILE_MB * 1024 * 1024
        const badType = !ALLOW_TYPES.includes(f.type)
        if (tooBig) errs.push(`${f.name}: ไฟล์เกิน ${MAX_FILE_MB}MB`)
        else if (badType) errs.push(`${f.name}: ประเภทไฟล์ไม่รองรับ`)
        else files.value.push(f)
    })
    if (errs.length) alert(errs.join('\n'))
}
function removeFile(i) { files.value.splice(i, 1) }
function prettySize(b) {
    const mb = b / (1024 * 1024)
    return mb >= 1 ? `${mb.toFixed(2)} MB` : `${(b / 1024).toFixed(0)} KB`
}
const fileInput = ref(null)


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
        const fd = new FormData()
        fd.append('id', form.value.id)
        fd.append('evn_title', form.value.evn_title ?? '')
        fd.append('evn_category_id', form.value.evn_category_id ?? '')
        fd.append('evn_description', form.value.evn_description ?? '')
        fd.append('evn_date', form.value.evn_date ?? '')
        fd.append('evn_timestart', form.value.evn_timestart ?? '')
        fd.append('evn_timeend', form.value.evn_timeend ?? '')
        fd.append('evn_location', form.value.evn_location ?? '')

        files.value.forEach(f => fd.append('attachments[]', f, f.name))

        deletedIds.value.forEach(id => fd.append('delete_file_ids[]', id))

        const { data } = await axios.post('/edit-event', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
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
