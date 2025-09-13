<template>
    <div class="min-h-screen">
        <header class="max-w-6xl mx-auto px-6 pt-6">
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
                                    <span class="material-symbols-outlined text-[16px] leading-none" aria-hidden="true">
                                        download
                                    </span>
                                    <b>Import</b>
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
                                <span class="material-symbols-outlined text-[18px] leading-none">close</span>
                                Cancel
                            </button>



                            <button type="submit" :disabled="submitting"
                                class="inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50">
                                <span class="material-symbols-outlined text-[18px] leading-none"
                                    aria-hidden="true">add</span>
                                Create
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
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

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
const teams = ref([])        // [{label, value, department_id}]
const positions = ref([])    // [{label, value}]
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
        const { data } = await axios.get('/meta')
        departments.value = (data.departments || []).map(d => ({ label: d.dpm_name, value: d.id }))
        positions.value = (data.positions || []).map(p => ({ label: p.pst_name, value: p.id }))
        teams.value = (data.teams || []).map(t => ({
            label: t.tm_name, value: t.id, department_id: t.tm_department_id ?? null
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
                confirmButton: 'rounded-full px-5 py-2.5 bg-gray-800 text-white font-semibold hover:bg-gray-900'
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
            if (!/^\d{10}$/.test(value)) return MSG.requiredNumber // เบอร์ 10 หลัก
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

        // เคลียร์ฟอร์มและ error
        Object.keys(form).forEach(k => (form[k] = ''))
        Object.keys(errors).forEach(k => delete errors[k])

        await Swal.fire({
            // ใช้ไอคอนแบบ custom ให้เหมือนภาพ
            icon: undefined,
            iconHtml: `
    <div class="okmark">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path fill="currentColor" d="M9.6 16.2 5.8 12.4l1.4-1.4 2.4 2.4 6.2-6.2 1.4 1.4-7.6 7.6z"/>
      </svg>
    </div>
  `,
            title: 'CREATE SUCCESS!',
            html: 'We have created a new employee.',
            width: 340,
            padding: '24px 24px 28px',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            buttonsStyling: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'swal-compact',
                icon: 'swal-icon',
                title: 'swal-title',
                htmlContainer: 'swal-text',
                confirmButton: 'swal-confirm'
            }
        })
        router.push('/employee')


    } catch (err) {
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

            // แจ้งเตือน validate ผิด
            const msg = Object.values(e).flat().join('<br>')
            await Swal.fire({
                icon: 'warning',
                title: 'Invalid data',
                html: msg || 'Please check your input.',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton:
                        'rounded-full px-5 py-2.5 bg-rose-600 text-white font-semibold hover:bg-rose-700'
                }
            })
        } else {
            // แจ้ง server error
            const msg = err.response?.data?.message || err.response?.data?.error || err.message || 'Server error'
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
/* กล่องป็อปอัป */
.swal-compact {
    border-radius: 20px;
}

/* พื้นที่ไอคอน + วงกลมเขียว */
.swal-icon {
    margin-top: 6px !important;
}

.swal-icon .okmark {
    width: 80px;
    /* ขนาดใกล้เคียงรูป */
    height: 80px;
    border-radius: 9999px;
    background: #22c55e;
    /* เขียวทรงเดียวกับภาพ */
    color: #fff;
    /* สีไอคอนไว้ที่ white */
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.swal-icon .okmark svg {
    width: 36px;
    height: 36px;
}

/* หัวเรื่องตัวหนา พิมพ์ใหญ่ */
.swal-title {
    margin-top: 14px !important;
    font-size: 20px !important;
    font-weight: 800 !important;
    letter-spacing: .02em;
    text-transform: uppercase;
    color: #111827;
    /* gray-900 */
}

/* ข้อความด้านล่าง */
.swal-text {
    margin-top: 6px !important;
    font-size: 14px !important;
    color: #4b5563;
    /* gray-600 */
}

/* ปุ่ม OK สีเขียวกลมมน */
.swal-confirm {
    margin-top: 16px;
    border-radius: 9999px !important;
    background: #22c55e !important;
    /* emerald-500 */
    color: #fff !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    padding: 10px 22px !important;
    line-height: 1.1;
    box-shadow: none !important;
}

.swal-confirm:hover {
    background: #16a34a !important;
}

/* emerald-600 */
</style>
