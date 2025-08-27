<template>
    <div class="min-h-screen">
        <header class="max-w-6xl mx-auto px-6 pt-6">
        </header>

        <section class="px-6 py-8">
            <div class="bg-white max-w-3xl mx-auto rounded-2xl border">
                <div class="px-6 py-5">
                    <div class="w-full max-w-3xl">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800">Add New Employee</h2>

                            <div class="relative">
                                <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="onImport" />
                                <button type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 text-[#444444]"
                                    @click="goImport">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M5 20h14a1 1 0 0 0 1-1v-7h-2v6H6V6h6V4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1Zm7-9v3h2v-3h3l-4-4-4 4h3Z" />
                                    </svg>
                                    Import
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form class="px-6 pb-6" @submit.prevent="handleSubmit">
                    <div class="w-full max-w-3xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            <FormField label="Prefix" required>
                                <DropdownPill v-model="form.prefix" :options="prefixes" placeholder="Select prefix"
                                    :error="errors.prefix" />
                            </FormField>

                            <FormField label="Department" required>
                                <DropdownPill v-model="form.department" :options="departments"
                                    placeholder="Select Department" :error="errors.department" />
                            </FormField>

                            <FormField label="First Name" required>
                                <InputPill v-model="form.firstName" placeholder="Ex.Perapat"
                                    :error="errors.firstName" />
                            </FormField>

                            <FormField label="Team" required>
                                <DropdownPill v-model="form.team" :options="teamOptions" placeholder="Select Team"
                                    :error="errors.team" />
                            </FormField>

                            <FormField label="Last Name" required>
                                <InputPill v-model="form.lastName" placeholder="Ex.Saimai" :error="errors.lastName" />
                            </FormField>

                            <FormField label="Position" required>
                                <DropdownPill v-model="form.position" :options="positions" placeholder="Select Position"
                                    :error="errors.position" />
                            </FormField>

                            <FormField label="Nickname">
                                <InputPill v-model="form.nickname" placeholder="Ex.beam" :error="errors.nickname" />
                            </FormField>

                            <FormField label="Email" required>
                                <InputPill v-model="form.email" type="email" placeholder="Ex.6610108@ggo.buu.ac.th"
                                    :error="errors.email" />
                            </FormField>

                            <FormField label="Phone" required>
                                <InputPill v-model="form.phone" placeholder="Ex.0988909888" inputmode="numeric"
                                    :error="errors.phone" />
                            </FormField>

                            <FormField label="Password" required>
                                <InputPill v-model="form.password" type="password" placeholder="Ex.Sawa.1234"
                                    :error="errors.password" />
                            </FormField>

                            <FormField label="ID" required>
                                <InputPill v-model="form.employeeId" placeholder="Ex.CN707008"
                                    :error="errors.employeeId" />
                            </FormField>

                            <FormField label="Permission" required>
                                <DropdownPill v-model="form.permission" :options="permissions"
                                    placeholder="Select Permission" :error="errors.permission" />
                            </FormField>
                        </div>


                        <div class="flex items-center justify-between pt-8">
                            <button type="button" @click="onCancel" class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold
         text-white bg-red-700 border border-transparent
         hover:bg-red-800 active:bg-red-900
         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-300">
                                <span class="text-xs mr-1">✕</span>
                                Cancel
                            </button>


                            <button type="submit" :disabled="submitting"
                                class="inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50">
                                <span class="text-base">+</span> Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// components
import FormField from '../components/FormField.vue'
import InputPill from '../components/InputPill.vue'
import DropdownPill from '../components/DropdownPill.vue'

const router = useRouter()
const goImport = () => router.push({ name: 'upload-file' })

/* ------- options ------- */
const prefixes = [
    { label: 'นาย', value: 1 },
    { label: 'นาง', value: 2 },
    { label: 'นางสาว', value: 3 },
]
const permissions = [
    { label: 'Administrator', value: 1 },
    { label: 'Human Resources', value: 2 },
    { label: 'Position1', value: 3 },
]
const PREFIX_MAP = { 'นาย': 1, 'นาง': 2, 'นางสาว': 3 }


const departments = ref([])  // [{label, value}]
const teams = ref([])  // [{label, value, department_id}]
const positions = ref([])  // [{label, value}]
const loadingMeta = ref(true)

/* ------- form ------- */
const form = reactive({
    prefix: '', firstName: '', lastName: '', nickname: '',
    phone: '', employeeId: '',
    department: '', team: '', position: '',   // เก็บเป็น id
    email: '', password: '', permission: '',
})

/* ------- โหลด meta จาก backend ------- */
onMounted(async () => {
    try {
        // ถ้าคุณวาง route นี้ไว้ใน web.php (middleware web,auth) => path คือ /meta
        // ถ้าวางใน api.php และมี prefix /api ให้เปลี่ยนเป็น /api/employees/meta
        const { data } = await axios.get('/meta')
        departments.value = (data.departments || []).map(d => ({ label: d.dpm_name, value: d.id }))
        positions.value = (data.positions || []).map(p => ({ label: p.pst_name, value: p.id }))
        // ต้องแก้ meta() ให้ส่ง tm_department_id มาด้วย (ดูข้อ 2 ด้านล่าง)
        teams.value = (data.teams || []).map(t => ({ label: t.tm_name, value: t.id, department_id: t.tm_department_id ?? null }))
    } catch (e) {
        console.error(e)
        alert('โหลด Department/Team/Position ไม่สำเร็จ')
    } finally {
        loadingMeta.value = false
    }
})

const teamOptions = computed(() => teams.value)

// watch(() => form.department, (depId) => {
//     if (!depId) return
//     const valid = teams.value.some(
//         t => t.value === form.team && t.department_id === depId
//     )
//     if (!valid) form.team = ''
// })

/* ====== Validation ====== */
const MSG = {
    requiredSelect: 'Required Select',
    requiredText: 'Required field only text',
    requiredNumber: 'Required field only number',
    requiredEmail: 'Required email, should have @ and .',
    requiredField: 'Required field',
}
const fieldRules = {
    prefix: ['requiredSelect'],
    department: ['requiredSelect'],
    team: ['requiredSelect'],
    position: ['requiredSelect'],
    permission: ['requiredSelect'],
    firstName: ['requiredText'],
    lastName: ['requiredText'],
    nickname: ['requiredText'],
    phone: ['requiredNumber'],
    email: ['requiredEmail'],
    password: ['requiredField'],
    employeeId: ['requiredField'],
}
const errors = reactive({})

function validateField(key, value) {
    const rules = fieldRules[key] || []
    for (const r of rules) {
        if (r === 'requiredSelect') { if (!value) return MSG.requiredSelect }
        else if (r === 'requiredText') {
            if (!value) return MSG.requiredText
            const re = /^[A-Za-zก-๙ .'-]+$/u
            if (!re.test(value)) return MSG.requiredText
        }
        else if (r === 'requiredNumber') {
            if (!value) return MSG.requiredNumber
            if (!/^\d{10}$/.test(value)) return MSG.requiredNumber  // เบอร์ 10 หลัก
        }
        else if (r === 'requiredEmail') {
            if (!value) return MSG.requiredEmail
            if (!(value.includes('@') && value.includes('.'))) return MSG.requiredEmail
        }
        else if (r === 'requiredField') { if (!value) return MSG.requiredField }
    }
    return ''
}
function validate() {
    Object.keys(errors).forEach(k => delete errors[k])
    Object.keys(fieldRules).forEach(k => {
        const msg = validateField(k, form[k])
        if (msg) errors[k] = msg
    })
    return Object.keys(errors).length === 0
}
Object.keys(fieldRules).forEach(k => {
    watch(() => form[k], (v) => {
        if (errors[k]) {
            const msg = validateField(k, v)
            if (msg) errors[k] = msg
            else delete errors[k]
        }
    })
})

/* ------- submit -> บันทึกลง DB ------- */
const submitting = ref(false)

async function handleSubmit() {
    if (!validate()) return
    submitting.value = true
    try {
        const payload = {
            emp_id: (form.employeeId || '').trim(),
            emp_prefix: Number(form.prefix),
            emp_prefix: form.prefix,
            emp_nickname: form.nickname,
            emp_firstname: form.firstName,
            emp_lastname: form.lastName,
            emp_email: form.email,
            emp_phone: form.phone,
            emp_position_id: Number(form.position),
            emp_department_id: Number(form.department),
            emp_team_id: Number(form.team),
            emp_password: form.password,
            emp_status: Number(form.permission)
        }


        // เส้นทางปัจจุบันของคุณใน web.php คือ /save-employee
        await axios.post('/save-employee', payload)


        alert('Employee created!')
        Object.keys(form).forEach(k => (form[k] = ''))
        Object.keys(errors).forEach(k => delete errors[k])
        router.push({ name: 'employees_page' })
    } catch (err) {
        // รับ validation 422 จาก Laravel ให้อ่าน error ใต้ช่อง
        if (err.response?.status === 422) {
            const e = err.response.data.errors || {}
            errors.employeeId = e.emp_id?.[0] || ''
            errors.prefix = e.emp_prefix?.[0] || ''
            errors.firstName = e.emp_firstname?.[0] || ''
            errors.lastName = e.emp_lastname?.[0] || ''
            errors.email = e.emp_email?.[0] || ''
            errors.phone = e.emp_phone?.[0] || ''
            errors.position = e.emp_position_id?.[0] || ''
            errors.department = e.emp_department_id?.[0] || ''
            errors.team = e.emp_team_id?.[0] || ''
            errors.password = e.emp_password?.[0] || ''
            errors.permission = e.emp_status?.[0] || ''
        } else {
            const msg = err.response?.data?.error || err.message || 'Server error'
            alert(msg) // ช่วยเห็นสาเหตุจริงจาก Controller
        }
    } finally {
        submitting.value = false
    }
}

function onCancel() {
    Object.keys(form).forEach(k => (form[k] = ''))
    Object.keys(errors).forEach(k => delete errors[k])
    router.push('/employee')
    // router.push('/employees')
}
</script>
