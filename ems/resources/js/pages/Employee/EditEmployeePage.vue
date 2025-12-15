<template>
  <div>
    <header class="mx-auto max-w-[1400px] px-6 pt-6">
      <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

      <div class="flex items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800">
          Edit Employee
        </h2>
      </div>
    </header>

    <div class="px-2 py-0">
      <div class="max-w-[1400px] mx-auto px-6">
        <form @submit.prevent="openConfirmSave">
          <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10 gap-y-5 justify-between items-stretch">

            <div class="flex flex-col gap-5 h-full">
              <FormField label="Prefix" required class="w-full">
                <DropdownPill v-model="form.emp_prefix" :options="prefixes" placeholder="Select prefix"
                  class="mt-1 block h-11 w-full" :error="errors.emp_prefix" />
              </FormField>

              <FormField label="First Name" required>
                <InputPill v-model="form.emp_firstname" placeholder="Ex.Perapat" class="mt-1 block h-11 w-full"
                  :error="errors.emp_firstname" />
              </FormField>

              <FormField label="Last Name" required>
                <InputPill v-model="form.emp_lastname" placeholder="Ex.Saimai" class="mt-1 block h-11 w-full"
                  :error="errors.emp_lastname" />
              </FormField>

              <FormField label="Nickname" required>
                <InputPill v-model="form.emp_nickname" placeholder="Ex.beam" class="mt-1 block h-11 w-full"
                  :error="errors.emp_nickname" />
              </FormField>

              <FormField label="Phone" required>
                <InputPill v-model="form.emp_phone" placeholder="Ex.0988900988" inputmode="numeric"
                  class="mt-1 block h-11 w-full" :error="errors.emp_phone" />
              </FormField>

              <FormField label="Employee ID" required>
                <InputPill v-model="form.emp_id" placeholder="Ex.CN707008" class="mt-1 block h-11 w-full"
                  :error="errors.emp_id" />
              </FormField>

              <div class="pt-2 mt-auto">
                  <CancelButton @click="onCancel" :disabled="saving" />
              </div>
            </div>

            <div class="flex flex-col gap-5 h-full">
              <FormField label="Department" required>
                <DropdownPill v-model="form.emp_department_id" :options="departments" placeholder="Select Department"
                  class="mt-1 block h-11 w-full" :error="errors.emp_department_id" />
              </FormField>

              <FormField label="Team" required>
                <DropdownPill v-model="form.emp_team_id" :options="teamOptions"
                  :placeholder="form.emp_department_id ? 'Select Team' : 'Please select Department first'"
                  class="mt-1 block h-11 w-full" :error="errors.emp_team_id" :disabled="!form.emp_department_id" />
              </FormField>

              <FormField label="Position" required>
                <DropdownPill v-model="form.emp_position_id" :options="positionOptions"
                  :placeholder="form.emp_team_id ? 'Select Position' : 'Please select Team first'"
                  class="mt-1 block h-11 w-full" :error="errors.emp_position_id" :disabled="!form.emp_team_id" />
              </FormField>

              <FormField label="Email" required>
                <InputPill v-model="form.emp_email" type="email" placeholder="Ex.66160106@go.buu.ac.th"
                  class="mt-1 block h-11 w-full" :error="errors.emp_email" />
              </FormField>

              <FormField label="Password" >
                <InputPill v-model="form.password" type="password" placeholder="Leave blank to keep current password"
                  class="mt-1 block h-11 w-full" :error="errors.password" />
              </FormField>

              <FormField label="Permission" required>
                <DropdownPill v-model="form.emp_permission" :options="permissions" placeholder="Select Permission"
                  class="mt-1 block h-11 w-full" :error="errors.emp_permission" />
              </FormField>

              <div class="pt-2 mt-auto flex justify-end flex-col items-end">
                <button
                  type="submit"
                  :disabled="saving"
                  class="inline-flex items-center justify-center gap-2 w-[140px] h-[45px] rounded-[20px] bg-green-600 text-white font-bold text-[15px] shadow-sm transition hover:bg-green-700 hover:shadow-md disabled:opacity-50"
                >
                  <span v-if="!saving" class="material-symbols-outlined text-[20px] leading-none">check</span>
                  <span>{{ saving ? "Saving…" : "Confirm" }}</span>
                </button>

                <p v-if="noChange" class="text-amber-600 text-sm pt-2">No changes were made.</p>
                <p v-if="saveError" class="text-rose-600 text-sm pt-2">{{ saveError }}</p>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>

    <ModalAlert
      v-model:open="alert.open"
      :type="alert.type"
      :title="alert.title"
      :message="alert.message"
      :showCancel="alert.showCancel"
      :okText="alert.okText"
      :cancelText="alert.cancelText"
      @confirm="alert.onConfirm"
      @cancel="alert.onCancel"
    />

  </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

/* ---------- Components ---------- */
import FormField from '@/components/Input/FormField.vue'
import InputPill from '@/components/Input/InputPill.vue'
import DropdownPill from '@/components/Input/DropdownPill.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
// นำเข้า ModalAlert แทนตัวเก่า
import ModalAlert from '@/components/Alert/ModalAlert.vue'

const router = useRouter()
const route = useRoute()
const employeeId = route.params.id

/* ------- Options ------- */
const prefixes = [
  { label: 'นาย', value: 'นาย' },
  { label: 'นาง', value: 'นาง' },
  { label: 'นางสาว', value: 'นางสาว' },
]

const permissions = [
  { label: 'Administrator', value: 'enabled' },
  { label: 'Human Resources', value: 'disabled' },
  { label: 'Employee', value: 'disabled' },
]

const departments = ref([])
const teams = ref([])
const positions = ref([])

/* ------- Form ------- */
const form = reactive({
  emp_prefix: '',
  emp_firstname: '',
  emp_lastname: '',
  emp_nickname: '',
  emp_phone: '',
  emp_id: '',
  emp_department_id: '',
  emp_team_id: '',
  emp_position_id: '',
  emp_email: '',
  password: '',
  emp_permission: '',
})

let original = {}

/* ------- State ------- */
const errors = reactive({})
const saving = ref(false)
const saveError = ref("")
const noChange = ref(false)

/* ------- Alert State (เลียนแบบ Edit Event) ------- */
const alert = reactive({
    open: false,
    type: 'confirm', // success | error | warning | confirm
    title: '',
    message: '',
    showCancel: false,
    okText: 'OK',
    cancelText: 'Cancel',
    onConfirm: null,
    onCancel: null,
})

/* ------- Helper Function เปิด Alert ------- */
function openAlert(cfg = {}) {
    // รีเซ็ต handler เก่า
    alert.onConfirm = null
    alert.onCancel = null

    // รวมค่า config
    Object.assign(alert, {
        open: true,
        type: 'success',
        title: '',
        message: '',
        showCancel: false,
        okText: 'OK',
        cancelText: 'Cancel',
    }, cfg)
}

/* ------- Computed Options ------- */
const teamOptions = computed(() => {
    if (!form.emp_department_id) return []
    const depId = Number(form.emp_department_id)
    return teams.value.filter(t => t.department_id === depId)
})

const positionOptions = computed(() => {
    if (!form.emp_team_id) return []
    const teamId = Number(form.emp_team_id)
    return positions.value.filter(p => p.team_id === teamId)
})

/* ------- Load Data ------- */
onMounted(async () => {
  try {
    const { data: metaData } = await axios.get('/meta')
    departments.value = (metaData.departments || []).map(d => ({ label: d.dpm_name, value: d.id }))
    teams.value = (metaData.teams || []).map(t => ({ label: t.tm_name, value: t.id, department_id: t.tm_department_id ?? null }))
    positions.value = (metaData.positions || []).map(p => ({ label: p.pst_name, value: p.id, team_id: p.pst_team_id ?? null }))

    const { data: res } = await axios.get(`/employees/${employeeId}`)
    const userData = res.data || res

    form.emp_prefix = userData.emp_prefix
    form.emp_firstname = userData.emp_firstname
    form.emp_lastname = userData.emp_lastname
    form.emp_nickname = userData.emp_nickname
    form.emp_phone = userData.emp_phone
    form.emp_id = userData.emp_id
    form.emp_department_id = userData.emp_department_id
    form.emp_team_id = userData.emp_team_id
    form.emp_position_id = userData.emp_position_id
    form.emp_email = userData.emp_email
    form.emp_permission = userData.emp_status
    form.password = ''

    original = JSON.parse(JSON.stringify(form))

  } catch (e) {
    console.error(e)
    openAlert({
        type: 'error',
        title: 'Error!',
        message: 'Failed to load data.',
        onConfirm: () => router.back()
    })
  }
})

/* ====== Validation (Logic เดิม) ====== */
const MSG = {
    requiredSelect: 'Required Select',
    requiredText: 'Required field only text',
    requiredNumber: 'Required field only number',
    requiredEmail: 'Required email',
    requiredField: 'Required field',
}

const fieldRules = {
    emp_prefix: ['requiredSelect'],
    emp_department_id: ['requiredSelect'],
    emp_team_id: ['requiredSelect'],
    emp_position_id: ['requiredSelect'],
    emp_permission: ['requiredSelect'],
    emp_firstname: ['requiredText'],
    emp_lastname: ['requiredText'],
    emp_nickname: ['requiredText'],
    emp_phone: ['requiredNumber'],
    emp_email: ['requiredEmail'],
    emp_id: ['requiredField'],
}

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
            if (!value) return 'Required phone number'
            if (!/^\d+$/.test(value) || value.length !== 10) return 'Phone number must be 10 digits'
        }
        else if (r === 'requiredEmail') { if (!value || !value.includes('@')) return MSG.requiredEmail }
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

Object.keys(fieldRules).forEach((k) => {
    watch(() => form[k], (v) => {
        const msg = validateField(k, v)
        if (msg) errors[k] = msg
        else delete errors[k]
    })
})

watch(() => form.emp_department_id, (n, o) => { if(o && n!==o) { form.emp_team_id=''; form.emp_position_id='' } })
watch(() => form.emp_team_id, (n, o) => { if(o && n!==o) { form.emp_position_id='' } })

/* ------- Logic การบันทึก แบบใหม่ (ใช้ OpenAlert) ------- */
function openConfirmSave() {
    noChange.value = false
    saveError.value = ""

    if (!validate()) return

    // Check changes logic
    const current = { ...form }
    const prev = { ...original }
    if (!current.password) delete current.password
    delete prev.password

    if (JSON.stringify(current) === JSON.stringify(prev) && !form.password) {
        noChange.value = true
        return
    }

    // เรียก Popup Confirm แบบเดียวกับ Event
    openAlert({
        type: 'confirm',
        title: 'ARE YOU SURE TO EDIT?',
        message: 'Are you sure you want to change this?',
        showCancel: true,
        okText: 'OK',
        cancelText: 'Cancel',
        onConfirm: async () => {
            await confirmSaveProcess()
        }
    })
}

// ฟังก์ชันบันทึกจริง (ถูกเรียกจาก callback onConfirm)
async function confirmSaveProcess() {
    saving.value = true
    saveError.value = ""

    try {
        const payload = { ...form }
        if (!payload.password) delete payload.password

        await axios.put(`/employees/${employeeId}`, payload)

        // Update original
        original = JSON.parse(JSON.stringify(form))
        original.password = ''

        // Success Alert
        openAlert({
            type: 'success',
            title: 'EDIT SUCCESS!',
            message: 'Employee has been successfully edited.',
            okText: 'OK',
            onConfirm: () => {
                router.push('/employee')
            }
        })

    } catch (err) {
        console.error(err)
        const msg = err.response?.data?.message || 'Failed to update employee'
        saveError.value = msg

        // Error Alert
        openAlert({
            type: 'error',
            title: 'EDIT FAILED!',
            message: msg,
        })
    } finally {
        saving.value = false
    }
}

function onCancel() {
    if (saving.value) return
    // เช็คหน่อยว่ามีการแก้ไขค้างไหม (optional)
    const current = { ...form }
    const prev = { ...original }
    if (!current.password) delete current.password
    delete prev.password

    // ถ้ามีการแก้ไข ให้ถามยืนยันก่อนออก
    if (JSON.stringify(current) !== JSON.stringify(prev) || form.password) {
        if(!confirm('Discard changes?')) return
    }
    router.push('/employee')
}
</script>
