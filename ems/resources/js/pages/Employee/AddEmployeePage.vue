    <template>
        <div>
            <header class="mx-auto max-w-[960px] px-4 pt-6">
                <link rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
            </header>

            <!-- Card -->
            <div class="px-2 py-0">

                <!-- Header -->
                <div class="px-0 md:px-0 pt-1 pb-7">
                    <div class="flex items-center justify-between gap-3">
                        <div class="translate-x-0 md:translate-x-20">
                            <h2 class="text-xl font-semibold text-gray-800">Add New Employee</h2>
                        </div>
                        <div class="relative md:-translate-x-32">
                            <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="onImport" />
                            <ImportButton class="ml-auto" label="Import" icon="download" @click="goImport" />
                        </div>
                    </div>
                    <!-- Success alert -->
                    <EmployeeCreateSuccess :open="showCreateSuccess" @close="showCreateSuccess = false" />
                </div>

                <!-- Body -->
                <div class="max-w-[1160px] px-6 md:px-8 lg:px-20 mr-auto">
                    <form @submit.prevent="handleSubmit">
                        <div class="grid grid-cols-1 md:grid-cols-[520px_64px_520px] items-start gap-y-5">
                            <!-- ซ้าย -->
                            <div class="flex flex-col gap-5">
                                <FormField label="Prefix" required class="max-w-[220px]">
                                    <DropdownPill class="mt-1 block w-full" v-model="form.prefix" :options="prefixes"
                                        placeholder="Select prefix" :error="errors.prefix" />
                                </FormField>

                                <FormField label="First Name" required class="w-[399px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.firstName" placeholder="Ex.Perapat"
                                        :error="errors.firstName" />
                                </FormField>

                                <FormField label="Last Name" required class="w-[399px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.lastName" placeholder="Ex.Saimai"
                                        :error="errors.lastName" />
                                </FormField>

                                <FormField label="Nickname" required class="w-[399px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.nickname" placeholder="Ex.beam"
                                        :error="errors.nickname" />
                                </FormField>

                                <FormField label="Phone" required class="w-[399px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.phone" placeholder="Ex.0988900988"
                                        inputmode="numeric" :error="errors.phone" />
                                </FormField>

                                <FormField label="Employee ID" required class="w-[399px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.employeeId" placeholder="Ex.CN707008"
                                        :error="errors.employeeId" />
                                </FormField>

                                <div @click="onCancel">
                                    <CancelButton />
                                </div>
                            </div>

                            <!-- ช่องกลาง -->
                            <div></div>

                            <!-- ขวา -->
                            <div class="flex flex-col gap-5 md:ml-32 lg:ml-48 xl:ml-64 2xl:ml-80">
                                <FormField label="Department" required class="w-[480px]">
                                    <DropdownPill class="mt-1 block w-full" v-model="form.department" :options="departments"
                                        placeholder="Select Department" :error="errors.department" />
                                </FormField>

                                <FormField label="Team" required class="w-[480px]">
                                    <DropdownPill class="mt-1 block w-full" v-model="form.team" :options="teamOptions"
                                        placeholder="Select Team" :error="errors.team" />
                                </FormField>

                                <FormField label="Position" required class="w-[480px]">
                                    <DropdownPill class="mt-1 block w-full" v-model="form.position" :options="positions"
                                        placeholder="Select Position" :error="errors.position" />
                                </FormField>

                                <FormField label="Email" required class="w-[480px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.email" type="email"
                                        placeholder="Ex.66160106@go.buu.ac.th" :error="errors.email" />
                                </FormField>

                                <FormField label="Password" required class="w-[480px]">
                                    <InputPill class="mt-1 block w-full" v-model="form.password" type="password"
                                        placeholder="Ex.Ssaw.1234" :error="errors.password" />
                                </FormField>

                                <FormField label="Permission" required class="w-[480px]">
                                    <DropdownPill class="mt-1 block w-full" v-model="form.permission" :options="permissions"
                                        placeholder="Select Permission" :error="errors.permission" />
                                </FormField>

                                <!-- ปุ่ม Create -->
                                <div class="-mt-2 ml-80">
                                    <button type="submit" :disabled="submitting"
                                        class="bg-transparent border-0 p-0 disabled:opacity-50"
                                        style="all: unset; display: inline-block;">
                                        <CreateButton :disabled="submitting" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

// components
import FormField from '../../components/Input/FormField.vue'
import InputPill from '../../components/Input/InputPill.vue'
import DropdownPill from '../../components/Input/DropdownPill.vue'
import ImportButton from '@/components/Button/ImportButton.vue'
import CreateButton from '@/components/Button/CreateButton.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
import EmployeeCreateSuccess from '../../components/Alert/Employee/EmployeeCreateSuccess.vue'

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
const teams = ref([])        // [{label, value, department_id}]
const positions = ref([])    // [{label, value}]
const loadingMeta = ref(true)

/* ------- form ------- */
const form = reactive({
    prefix: '', firstName: '', lastName: '', nickname: '',
    phone: '', employeeId: '',
    department: '', team: '', position: '',
    email: '', password: '', permission: '',
})

/* ------- validation state ------- */
const errors = reactive({})

/* ------- success state ------- */
const submitting = ref(false)
const showCreateSuccess = ref(false)

/* ------- โหลด meta จาก backend ------- */
onMounted(async () => {
    try {
        const { data } = await axios.get('/meta')
        departments.value = (data.departments || []).map(d => ({ label: d.dpm_name, value: d.id }))
        positions.value = (data.positions || []).map(p => ({ label: p.pst_name, value: p.id }))
        teams.value = (data.teams || []).map(t => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null
        }))
    } catch (e) {
        await Swal.fire({
            icon: 'error',
            title: 'Load failed',
            text: 'โหลด Department/Team/Position ไม่สำเร็จ',
            confirmButtonText: 'OK',
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton:
                    'rounded-full px-5 py-2.5 bg-gray-800 text-white font-semibold hover:bg-gray-900'
            }
        })
    } finally {
        loadingMeta.value = false
    }
})

const teamOptions = computed(() => teams.value)

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

function validateField(key, value) {
    const rules = fieldRules[key] || []
    for (const r of rules) {
        if (r === 'requiredSelect') {
            if (!value) return MSG.requiredSelect
        } else if (r === 'requiredText') {
            if (!value) return MSG.requiredText
            const re = /^[A-Za-zก-๙ .'-]+$/u
            if (!re.test(value)) return MSG.requiredText
        } else if (r === 'requiredNumber') {
            // 1) ไม่ใส่อะไรเลย
            if (!value) {
                return 'Required phone number'
            }
            // 2) ใส่มาแล้วแต่ไม่ใช่ตัวเลขทั้งหมด หรือไม่ใช่ความยาว 10 หลักพอดี
            if (!/^\d+$/.test(value) || value.length !== 10) {
                return 'Phone number must be 10 digits'
            }
        } else if (r === 'requiredEmail') {
            if (!value) return MSG.requiredEmail
            if (!(value.includes('@') && value.includes('.'))) return MSG.requiredEmail
        } else if (r === 'requiredField') {
            if (!value) return MSG.requiredField
        }
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

// live-validate
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
async function handleSubmit() {
    // ถ้ากดรอบใหม่ ซ่อน success เก่าก่อน
    showCreateSuccess.value = false

    if (!validate()) return
    submitting.value = true
    try {
        const payload = {
            emp_id: (form.employeeId || '').trim(),
            emp_prefix: Number(form.prefix),
            emp_nickname: form.nickname || null,
            emp_firstname: form.firstName,
            emp_lastname: form.lastName,
            emp_email: form.email,
            emp_phone: String(form.phone || ''),
            emp_position_id: Number(form.position),
            emp_department_id: Number(form.department),
            emp_team_id: Number(form.team),
            emp_password: form.password,
            emp_status: Number(form.permission)
        }

        await axios.post('/save-employee', payload)

        // ล้างฟอร์ม + ล้าง error เก่า
        Object.keys(form).forEach(k => (form[k] = ''))
        Object.keys(errors).forEach(k => delete errors[k])

        //แสดงกล่องสำเร็จ
        showCreateSuccess.value = true


    } catch (err) {
        showCreateSuccess.value = false // อย่าโชว์ success ถ้า error

        if (err.response?.status === 422) {
            const e = err.response.data.errors || {}

            function normalizeMsg(fieldMsg, fieldName) {
                if (!fieldMsg) return ''

                // duplicate เคสซ้ำ
                if (
                    /already been taken/i.test(fieldMsg) ||
                    /already exists/i.test(fieldMsg) ||
                    /duplicate/i.test(fieldMsg) ||
                    /ซ้ำ/.test(fieldMsg) ||
                    /มีอยู่แล้ว/.test(fieldMsg)
                ) {
                    switch (fieldName) {
                        case 'emp_email':
                            return 'This email is already in use.'
                        case 'emp_id':
                            return 'This employee ID is already in use.'
                        case 'emp_phone':
                            return 'This phone number is already in use.'
                        default:
                            return 'Already in use.'
                    }
                }

                return fieldMsg
            }

            errors.employeeId = normalizeMsg(e.emp_id?.[0], 'emp_id') || ''
            errors.prefix = e.emp_prefix?.[0] || ''
            errors.firstName = e.emp_firstname?.[0] || ''
            errors.lastName = e.emp_lastname?.[0] || ''
            errors.email = normalizeMsg(e.emp_email?.[0], 'emp_email') || ''
            errors.phone = normalizeMsg(e.emp_phone?.[0], 'emp_phone') || ''
            errors.position = e.emp_position_id?.[0] || ''
            errors.department = e.emp_department_id?.[0] || ''
            errors.team = e.emp_team_id?.[0] || ''
            errors.password = e.emp_password?.[0] || ''
            errors.permission = e.emp_status?.[0] || ''

            const flatMsgsRaw = Object.values(e).flat().filter(Boolean)

            const isDuplicate = flatMsgsRaw.some(msg =>
                /already\s+exists/i.test(msg) ||
                /already been taken/i.test(msg) ||
                /duplicate/i.test(msg) ||
                /ซ้ำ/.test(msg) ||
                /มีอยู่แล้ว/.test(msg)
            )

            // กรณี invalid ปกติ (ไม่ใช่ duplicate) → ยังใช้ Swal เตือน
            if (!isDuplicate) {
                const msgHtml = flatMsgsRaw.join('<br>') || 'Please check your input.'
                await Swal.fire({
                    icon: 'warning',
                    title: 'Invalid data',
                    html: msgHtml,
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton:
                            'rounded-full px-5 py-2.5 bg-rose-600 text-white font-semibold hover:bg-rose-700'
                    }
                })
            }

        } else {
            // server / network error → ยังเตือนด้วย Swal ได้
            const msg = err.response?.data?.message
                || err.response?.data?.error
                || err.message
                || 'Server error'

            await Swal.fire({
                icon: 'error',
                title: 'Create failed',
                text: msg,
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton:
                        'rounded-full px-5 py-2.5 bg-gray-800 text-white font-semibold hover:bg-gray-900'
                }
            })
        }
    } finally {
        submitting.value = false
    }
}

function onCancel() {
    Object.keys(form).forEach(k => (form[k] = ''))
    Object.keys(errors).forEach(k => delete errors[k])
    router.push('/employee')
}
</script>

<style>
/* ยังเก็บ style ของ sweetalert error/warning ได้
   แต่ style ของ success popup (swal-compact / okmark / etc.)
   ตอนนี้ไม่จำเป็นต่อ flow แล้ว
   ถ้าจะล้าง ก็ลบทั้งหมดทิ้งได้ปลอดภัย */
</style>
