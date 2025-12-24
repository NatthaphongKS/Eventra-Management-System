<template>
    <div>
        <!-- Header + ปุ่ม Import -->
        <div class="mx-auto max-w-[1400px] px-6">
            <header class="pt-6 mb-6">
                <link rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

                <div class="flex items-center justify-between gap-3 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 ml-8">
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
                    <EmployeeCannotCreate :open="showCreateError" :message="createErrorMessage"
                        @close="handleErrorClose" />

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
                                    <InputPill v-model="form.nickname" placeholder="Ex.beam"
                                        class="mt-1 block h-11 w-full" :error="errors.nickname" />
                                </FormField>

                                <FormField label="Phone" required>
                                    <InputPill v-model="form.phone" placeholder="Ex.0988900988" inputmode="numeric"
                                        class="mt-1 block h-11 w-full" :error="errors.phone" />
                                </FormField>

                                <FormField label="Employee ID" required>
                                    <div class="grid grid-cols-2 gap-3 mt-1">
                                        <!-- Company -->
                                        <DropdownPill v-model="form.companyId" :options="companies"
                                            placeholder="Company" class="h-11 w-full" :error="errors.companyId" />

                                        <!-- Employee Number -->
                                        <InputPill v-model="form.employeeNumber" placeholder="Ex.0001"
                                            inputmode="numeric" class="h-11 w-full" :error="errors.employeeNumber"
                                            @input="onEmployeeNumberInput" />
                                    </div>
                                </FormField>


                                <!-- ปุ่ม Cancel -->
                                <div class="pt-2">
                                    <button type="button" @click="onCancel" class="inline-flex -ml-11">
                                        <CancelButton />
                                    </button>
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
                                        class="mt-1 block h-11 w-full" :error="errors.team"
                                        :disabled="!form.department" />
                                </FormField>

                                <FormField label="Position" required>
                                    <DropdownPill v-model="form.position" :options="positionOptions"
                                        :placeholder="form.team ? 'Select Position' : 'Please select Team first'"
                                        class="mt-1 block h-11 w-full" :error="errors.position"
                                        :disabled="!form.team" />
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
                                    <button type="submit" :disabled="submitting" class="inline-flex disabled:opacity-50"
                                        style="all: unset; display: inline-flex;">
                                        <CreateButton :disabled="submitting" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/* =========================================================
 * 1. Imports
 * ======================================================= */
import { reactive, computed, watch, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

/* ---------- Components ---------- */
import FormField from '../../components/Input/FormField.vue'
import InputPill from '../../components/Input/InputPill.vue'
import DropdownPill from '../../components/Input/DropdownPill.vue'
import ImportButton from '../../components/Button/ImportButton.vue'
import CreateButton from '@/components/Button/CreateButton.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
import ModalAlert from '../../components/Alert/ModalAlert.vue'
import EmployeeCannotCreate from '../../components/Alert/Employee/EmployeeCannotCreate.vue'


/* =========================================================
 * 2. Router / Navigation
 * ======================================================= */
const router = useRouter()
const goImport = () => router.push({ name: "upload-file" })


/* =========================================================
 * 3. Static Options
 * ======================================================= */
const permissions = [
    { label: 'Administrator', value: 1 },
    { label: 'Human Resources', value: 2 },
    { label: 'Employee', value: 3 },
]


/* =========================================================
 * 4. Reactive State
 * ======================================================= */

/* --- meta data --- */
const prefixes = ref([])
const companies = ref([])
const departments = ref([])
const teams = ref([])
const positions = ref([])
const loadingMeta = ref(true)

/* --- form data --- */
const form = reactive({
    prefix: "",
    firstName: "",
    lastName: "",
    nickname: "",
    phone: "",
    employeeId: "",
    department: "",
    team: "",
    position: "",
    email: "",
    password: "",
    permission: "",
    companyId: "",
    employeeNumber: "",
})

/* --- validation --- */
const errors = reactive({})
const suspendValidation = ref(false)

/* --- ui states --- */
const submitting = ref(false)
const showCreateSuccess = ref(false)
const showCreateError = ref(false)
const createErrorMessage = ref('')
const showLoadMetaError = ref(false)
const loadMetaErrorMessage = ref('')


/* =========================================================
 * 5. Lifecycle
 * ======================================================= */

/**
 * โหลดข้อมูล meta (prefix, department, team, position)
 */
onMounted(async () => {
    try {
        const { data } = await axios.get("/meta")

        prefixes.value = (data.prefixes || []).map(p => ({
            label: p.label,
            value: p.value,
        }))

        companies.value = (data.companies || []).map(c => ({
            label: c.com_name, // CN, CNI, CNT
            value: c.com_name // ใช้ชื่อ company เป็น prefix
        }))

        departments.value = (data.departments || []).map(d => ({
            label: d.dpm_name,
            value: d.id,
        }))

        teams.value = (data.teams || []).map(t => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }))

        positions.value = (data.positions || []).map(p => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }))
    } catch {
        showLoadMetaError.value = true
        loadMetaErrorMessage.value = 'Load failed. Please try again.'
    } finally {
        loadingMeta.value = false
    }
})


/* =========================================================
 * 6. Computed
 * ======================================================= */

/**
 * team options filter ตาม department
 */
const teamOptions = computed(() => {
    if (!form.department) return []
    return teams.value.filter(t => t.department_id === Number(form.department))
})

/**
 * position options filter ตาม team
 */
const positionOptions = computed(() => {
    if (!form.team) return []
    return positions.value.filter(p => p.team_id === Number(form.team))
})

/**
 * รวม companyId + employeeNumber เป็น employeeId
 */
const employeeIdCombined = computed(() => {
    if (!form.companyId || !form.employeeNumber) return ''
    return `${form.companyId}${form.employeeNumber}`
})


/* =========================================================
 * 7. Validation Logic
 * ======================================================= */

const MSG = {
    requiredSelect: "Required Select",
    requiredText: "Required field only text",
    requiredNumber: "Required field only number",
    requiredEmail: "Required email, should have @ and .",
    requiredField: "Required field",
    employeeNumber4: "Employee number must be 4 digits",
}

const fieldRules = {
    prefix: ["requiredSelect"],
    department: ["requiredSelect"],
    team: ["requiredSelect"],
    position: ["requiredSelect"],
    permission: ["requiredSelect"],
    firstName: ["requiredText"],
    lastName: ["requiredText"],
    nickname: ["requiredText"],
    phone: ["requiredNumber"],
    email: ["requiredEmail"],
    password: ["requiredField"],
    companyId: ["requiredSelect"],
    employeeNumber: ["employeeNumber4"],
}

/**
 * validate field เดียว
 */
function validateField(key, value) {
    const rules = fieldRules[key] || []
    for (const r of rules) {
        if (r === 'requiredSelect' && !value) return MSG.requiredSelect
        if (r === 'requiredText') {
            if (!value || !/^[A-Za-zก-๙ .'-]+$/u.test(value)) return MSG.requiredText
        }
        if (r === 'requiredNumber') {
            if (!value) return 'Required phone number'
            if (!/^\d{10}$/.test(value)) return 'Phone number must be 10 digits'
        }
        if (r === 'requiredEmail') {
            if (!value || !(value.includes('@') && value.includes('.'))) return MSG.requiredEmail
        }
        if (r === 'requiredField' && !value) return MSG.requiredField
    }
    if (key === 'employeeNumber') {
        if (value.length !== 4) return MSG.employeeNumber4
    }
    return ""
}

/**
 * validate ทั้งฟอร์ม (ตอน submit)
 */
function validate() {
    Object.keys(errors).forEach(k => delete errors[k])
    Object.keys(fieldRules).forEach(k => {
        const msg = validateField(k, form[k])
        if (msg) errors[k] = msg
    })
    return Object.keys(errors).length === 0
}


/* =========================================================
 * 8. Watchers
 * ======================================================= */

/**
 * live validation (หยุดได้ด้วย suspendValidation)
 */
Object.keys(fieldRules).forEach(k => {
    watch(() => form[k], v => {
        if (suspendValidation.value) return
        const msg = validateField(k, v)
        msg ? errors[k] = msg : delete errors[k]
    })
})

/**
 * reset team / position เมื่อเปลี่ยน department
 */
watch(() => form.department, () => {
    form.team = ""
    form.position = ""
    delete errors.team
    delete errors.position
})

/**
 * reset position เมื่อเปลี่ยน team
 */
watch(() => form.team, () => {
    form.position = ""
    delete errors.position
})


/* =========================================================
 * 9. Submit / Actions
 * ======================================================= */

/**
 * submit create employee
 */
async function handleSubmit() {
    showCreateSuccess.value = false
    if (!validate()) return

    submitting.value = true
    try {
        await axios.post("/save-employee", {
            emp_id: employeeIdCombined.value,
            emp_prefix: Number(form.prefix),
            emp_nickname: form.nickname || null,
            emp_firstname: form.firstName,
            emp_lastname: form.lastName,
            emp_email: form.email,
            emp_phone: String(form.phone),
            emp_position_id: Number(form.position),
            emp_department_id: Number(form.department),
            emp_team_id: Number(form.team),
            emp_password: form.password,
            emp_status: Number(form.permission),
        })

        suspendValidation.value = true
        Object.keys(form).forEach(k => form[k] = "")
        Object.keys(errors).forEach(k => delete errors[k])
        showCreateSuccess.value = true

    } catch (err) {
        createErrorMessage.value =
            err.response?.data?.message ||
            err.message ||
            'Sorry, please try again later.'

        showCreateError.value = true
    } finally {
        submitting.value = false
    }
}

/**
 * cancel form
 */
function onCancel() {
    Object.keys(form).forEach(k => form[k] = "")
    Object.keys(errors).forEach(k => delete errors[k])
    router.push("/employee")
}


/* =========================================================
 * 10. Modal Handlers
 * ======================================================= */

function handleSuccessClose() {
    showCreateSuccess.value = false
    router.push("/employee")
}

function handleErrorClose() {
    showCreateError.value = false
}

function handleLoadMetaErrorClose() {
    showLoadMetaError.value = false
}

/* =========================================================
 * 11.
 * ======================================================= */
function onEmployeeNumberInput(e) {
    form.employeeNumber = e.target.value
        .replace(/\D/g, '')
        .slice(0, 4)
}

</script>


<style></style>
