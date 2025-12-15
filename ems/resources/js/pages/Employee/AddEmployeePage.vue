<template>
    <div>
        <!-- Header + ปุ่ม Import -->
        <header class="mx-auto max-w-[1400px] px-6 pt-6">
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800">
                    Add New Employee
                </h2>

                <div class="flex justify-end">
                    <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="onImport" />
                    <ImportButton class="ml-auto" label="Import" icon="download" @click="goImport" />
                </div>

                <!-- Success alert -->
                <ModalAlert v-model:open="showCreateSuccess" title="Success" message="Create employee success"
                    type="success" @confirm="handleSuccessClose" />

                <!-- Error alert -->
                <EmployeeCannotCreate :open="showCreateError" :message="createErrorMessage" @close="handleErrorClose" />

                <!-- Load meta error alert -->
                <EmployeeCannotCreate :open="showLoadMetaError" :message="loadMetaErrorMessage"
                    @close="handleLoadMetaErrorClose" />
            </div>
        </header>

        <!-- Body -->
        <div class="px-2 py-0">
            <div class="max-w-[1400px] mx-auto px-6">
                <form @submit.prevent="handleSubmit">
                    <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10 gap-y-5 justify-between">
                        <!-- ซ้าย -->
                        <div class="flex flex-col gap-5">
                            <FormField label="Prefix" required class="w-full">
                                <DropdownPill v-model="form.prefix" :options="prefixes" placeholder="Select prefix"
                                    class="mt-1 block h-11 w-full" :error="errors.prefix" />
                            </FormField>

                            <FormField label="First Name" required>
                                <InputPill v-model="form.firstName" placeholder="Ex.Perapat"
                                    class="mt-1 block h-11 w-full" :error="errors.firstName" />
                            </FormField>

                            <FormField label="Last Name" required>
                                <InputPill v-model="form.lastName" placeholder="Ex.Saimai"
                                    class="mt-1 block h-11 w-full" :error="errors.lastName" />
                            </FormField>

                            <FormField label="Nickname" required>
                                <InputPill v-model="form.nickname" placeholder="Ex.beam" class="mt-1 block h-11 w-full"
                                    :error="errors.nickname" />
                            </FormField>

                            <FormField label="Phone" required>
                                <InputPill v-model="form.phone" placeholder="Ex.0988900988" inputmode="numeric"
                                    class="mt-1 block h-11 w-full" :error="errors.phone" />
                            </FormField>

                            <FormField label="Employee ID" required>
                                <InputPill v-model="form.employeeId" placeholder="Ex.CN707008"
                                    class="mt-1 block h-11 w-full" :error="errors.employeeId" />
                            </FormField>

                            <!-- ปุ่ม Cancel -->
                            <div class="pt-2">
                                <div @click="onCancel">
                                    <CancelButton />
                                </div>
                            </div>
                        </div>

                        <!-- ขวา -->
                        <div class="flex flex-col gap-5">
                            <FormField label="Department" required>
                                <DropdownPill v-model="form.department" :options="departments"
                                    placeholder="Select Department" class="mt-1 block h-11 w-full"
                                    :error="errors.department" />
                            </FormField>

                            <FormField label="Team" required>
                                <DropdownPill v-model="form.team" :options="teamOptions"
                                    :placeholder="form.department ? 'Select Team' : 'Please select Department first'"
                                    class="mt-1 block h-11 w-full" :error="errors.team" :disabled="!form.department" />
                            </FormField>

                            <FormField label="Position" required>
                                <DropdownPill v-model="form.position" :options="positionOptions"
                                    :placeholder="form.team ? 'Select Position' : 'Please select Team first'"
                                    class="mt-1 block h-11 w-full" :error="errors.position" :disabled="!form.team" />
                            </FormField>

                            <FormField label="Email" required>
                                <InputPill v-model="form.email" type="email" placeholder="Ex.example@gmail.com"
                                    class="mt-1 block h-11 w-full" :error="errors.email" />
                            </FormField>

                            <FormField label="Password" required>
                                <InputPill v-model="form.password" type="password" placeholder="Ex.Ssaw.1234"
                                    class="mt-1 block h-11 w-full" :error="errors.password" />
                            </FormField>

                            <FormField label="Permission" required>
                                <DropdownPill v-model="form.permission" :options="permissions"
                                    placeholder="Select Permission" class="mt-1 block h-11 w-full"
                                    :error="errors.permission" />
                            </FormField>

                            <!-- ปุ่ม Create -->
                            <div class="pt-2 flex justify-end">
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

/* ---------- components ---------- */
import FormField from '../../components/Input/FormField.vue'
import InputPill from '../../components/Input/InputPill.vue'
import DropdownPill from '../../components/Input/DropdownPill.vue'
import ImportButton from '@/components/Button/ImportButton.vue'
import CreateButton from '@/components/Button/CreateButton.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
import ModalAlert from '../../components/Alert/ModalAlert.vue'
import EmployeeCannotCreate from '../../components/Alert/Employee/EmployeeCannotCreate.vue'

const router = useRouter()
const goImport = () => router.push({ name: 'upload-file' })

/* ------- options ------- */
const permissions = [
    { label: 'Administrator', value: 1 },
    { label: 'Human Resources', value: 2 },
    { label: 'Employee', value: 3 },
]

const prefixes = ref([])   // [{ label, value }]
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

/* ------- error state ------- */
const showCreateError = ref(false)
const createErrorMessage = ref('')

/* ------- load meta error state ------- */
const showLoadMetaError = ref(false)
const loadMetaErrorMessage = ref('')

/* ------- โหลด meta จาก backend ------- */
onMounted(async () => {
    try {
        const { data } = await axios.get('/meta')

        prefixes.value = (data.prefixes || []).map(p => ({
            label: p.label,
            value: p.value,
        }))

        // Department = แค่ชื่อกับ id พอ
        departments.value = (data.departments || []).map(d => ({
            label: d.dpm_name,
            value: d.id,
        }))

        // Team = ต้องมี department_id ไว้ filter
        teams.value = (data.teams || []).map(t => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }))

        // Position
        positions.value = (data.positions || []).map(p => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }))
    } catch (e) {
        showLoadMetaError.value = true
        loadMetaErrorMessage.value = 'Load failed. Please try again.'
    }

    finally {
        loadingMeta.value = false
    }
})

const teamOptions = computed(() => {
    if (!form.department) return []
    const depId = Number(form.department)
    return teams.value.filter(t => t.department_id === depId)
})

const positionOptions = computed(() => {
    if (!form.team) return []
    const teamId = Number(form.team)
    return positions.value.filter(p => p.team_id === teamId)
})


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
Object.keys(fieldRules).forEach((k) => {
    watch(
        () => form[k],
        (v) => {
            const msg = validateField(k, v)
            if (msg) {
                errors[k] = msg
            } else {
                delete errors[k]
            }
        }
    )
})

watch(
    () => form.department,
    () => {
        form.team = ''
        form.position = ''
        delete errors.team
        delete errors.position
    }
)

watch(
    () => form.team,
    () => {
        form.position = ''
        delete errors.position
    }
)

/* ------- submit -> บันทึกลง DB ------- */
async function handleSubmit() {
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
        showCreateSuccess.value = false

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
            createErrorMessage.value = 'Sorry, please try again later.'
            showCreateError.value = true
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

/* ---------- close modal success ---------- */
function handleSuccessClose() {
    showCreateSuccess.value = false
    router.push('/employee')
}

/* ---------- close modal error ---------- */
function handleErrorClose() {
    showCreateError.value = false
}

/* ---------- close modal load meta error ---------- */
function handleLoadMetaErrorClose() {
    showLoadMetaError.value = false
}

</script>

<style>

</style>
