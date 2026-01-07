<template>
    <div>
        <!-- Header + ปุ่ม Import -->
        <div class="mx-auto max-w-[1400px] px-6">
            <header class="pt-6 mb-6">
                <link
                    rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
                />

                <div class="flex items-center justify-between gap-3 mb-6">
                    <h2 class="text-3xl font-semibold text-nutral-800 ml-8">
                        Add New Employee
                    </h2>

                    <div class="flex justify-end">
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".csv"
                            class="hidden"
                            @change="onImport"
                        />
                        <ImportButton
                            class="ml-auto"
                            label="Import"
                            icon="download"
                            @click="goImport"
                        />
                    </div>

                    <!-- Success alert -->
                    <ModalAlert
                        v-model:open="showCreateSuccess"
                        title="Success"
                        message="Create employee success"
                        type="success"
                        @confirm="handleSuccessClose"
                    />

                    <!-- Error alert -->
                    <EmployeeCannotCreate
                        :open="showCreateError"
                        :message="createErrorMessage"
                        @close="handleErrorClose"
                    />

                    <!-- Load meta error alert -->
                    <EmployeeCannotCreate
                        :open="showLoadMetaError"
                        :message="loadMetaErrorMessage"
                        @close="handleLoadMetaErrorClose"
                    />
                </div>
            </header>

            <!-- Body -->
            <div class="px-2 py-0">
                <div class="max-w-[1400px] mx-auto px-6">
                    <form @submit.prevent="handleSubmit">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 md:gap-x-16 gap-y-6 items-start"
                        >
                            <FormField
                                label="Prefix"
                                required
                                :error="errors.prefix"
                            >
                                <DropdownPill
                                    v-model="form.prefix"
                                    :options="prefixes"
                                    placeholder="Select prefix"
                                    class="h-11 w-full"
                                    :has-error="!!errors.prefix"
                                />
                            </FormField>

                            <FormField
                                label="Department"
                                required
                                :error="errors.department"
                            >
                                <DropdownPill
                                    v-model="form.department"
                                    :options="departments"
                                    placeholder="Select Department"
                                    class="h-11 w-full"
                                    :has-error="!!errors.department"
                                />
                            </FormField>

                            <FormField
                                label="First Name"
                                required
                                :error="errors.firstName"
                            >
                                <InputPill
                                    v-model="form.firstName"
                                    placeholder="Ex.Perapat"
                                    class="h-11 w-full"
                                    :has-error="!!errors.firstName"
                                />
                            </FormField>

                            <FormField
                                label="Team"
                                required
                                :error="errors.team"
                            >
                                <DropdownPill
                                    v-model="form.team"
                                    :options="teamOptions"
                                    :placeholder="
                                        form.department
                                            ? 'Select Team'
                                            : 'Please select Department first'
                                    "
                                    class="h-11 w-full"
                                    :disabled="!form.department"
                                    :has-error="!!errors.team"
                                />
                            </FormField>

                            <FormField
                                label="Last Name"
                                required
                                :error="errors.lastName"
                            >
                                <InputPill
                                    v-model="form.lastName"
                                    placeholder="Ex.Saimai"
                                    class="h-11 w-full"
                                    :has-error="!!errors.lastName"
                                />
                            </FormField>

                            <FormField
                                label="Position"
                                required
                                :error="errors.position"
                            >
                                <DropdownPill
                                    v-model="form.position"
                                    :options="positionOptions"
                                    :placeholder="
                                        form.team
                                            ? 'Select Position'
                                            : 'Please select Team first'
                                    "
                                    class="h-11 w-full"
                                    :disabled="!form.team"
                                    :has-error="!!errors.position"
                                />
                            </FormField>

                            <FormField
                                label="Nickname"
                                required
                                :error="errors.nickname"
                            >
                                <InputPill
                                    v-model="form.nickname"
                                    placeholder="Ex.beam"
                                    class="h-11 w-full"
                                    :has-error="!!errors.nickname"
                                />
                            </FormField>

                            <FormField
                                label="Email"
                                required
                                :error="errors.email"
                            >
                                <InputPill
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Ex.example@gmail.com"
                                    class="h-11 w-full"
                                    :has-error="!!errors.email"
                                />
                            </FormField>

                            <FormField
                                label="Phone"
                                required
                                :error="errors.phone"
                            >
                                <InputPill
                                    v-model="form.phone"
                                    placeholder="Ex.0988900988"
                                    maxlength="10"
                                    class="h-11 w-full"
                                    :has-error="!!errors.phone"
                                />
                            </FormField>

                            <FormField
                                label="Password"
                                :required="!isEmployeePermission"
                                :error="errors.password"
                            >
                                <InputPill
                                    v-model="form.password"
                                    type="password"
                                    :placeholder="
                                        isEmployeePermission
                                            ? 'Employee does not require password'
                                            : 'Ex.Ssaw.1234'
                                    "
                                    class="h-11 w-full"
                                    :disabled="isEmployeePermission"
                                    :has-error="!!errors.password"
                                />
                            </FormField>

                            <FormField
                                label="Employee ID"
                                required
                                :error="
                                    errors.companyId || errors.employeeNumber
                                "
                            >
                                <div class="grid grid-cols-2 gap-3">
                                    <DropdownPill
                                        v-model="form.companyId"
                                        :options="companies"
                                        placeholder="Company"
                                        class="h-11 w-full"
                                        :has-error="!!errors.companyId"
                                    />
                                    <InputPill
                                        v-model="form.employeeNumber"
                                        placeholder="Ex.0001"
                                        maxlength="4"
                                        class="h-11 w-full"
                                        :has-error="!!errors.employeeNumber"
                                        @input="onEmployeeNumberInput"
                                    />
                                </div>
                            </FormField>

                            <FormField
                                label="Permission"
                                required
                                :error="errors.permission"
                            >
                                <DropdownPill
                                    v-model="form.permission"
                                    :options="permissions"
                                    placeholder="Select Permission"
                                    class="h-11 w-full"
                                    :has-error="!!errors.permission"
                                />
                            </FormField>

                            <div
                                class="col-span-full flex justify-between items-center pb-6"
                            >
                                <button
                                    type="button"
                                    @click="onCancel"
                                    class="hover:opacity-80 transition-opacity"
                                >
                                    <CancelButton />
                                </button>

                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <CreateButton :disabled="submitting" />
                                </button>
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
import "sweetalert2/dist/sweetalert2.min.css";

/* ---------- Components ---------- */
import FormField from "../../components/Input/FormField.vue";
import InputPill from "../../components/Input/InputPill.vue";
import DropdownPill from "../../components/Input/DropdownPill.vue";
import ImportButton from "../../components/Button/ImportButton.vue";
import CreateButton from "@/components/Button/CreateButton.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "../../components/Alert/ModalAlert.vue";
import EmployeeCannotCreate from "../../components/Alert/Employee/EmployeeCannotCreate.vue";

/* =========================================================
 * 2. Router / Navigation
 * ======================================================= */
const router = useRouter();
const goImport = () => router.push({ name: "upload-file" });

/* =========================================================
 * 3. Static Options
 * ======================================================= */
const permissions = [
    { label: "Administrator", value: "admin" },
    { label: "Human Resources", value: "hr" },
    { label: "Employee", value: "employee" },
];

/* =========================================================
 * 4. Reactive State
 * ======================================================= */

/* --- meta data --- */
const prefixes = ref([]);
const companies = ref([]);
const departments = ref([]);
const teams = ref([]);
const positions = ref([]);
const loadingMeta = ref(true);

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
    companyCode: "",
});

/* --- validation --- */
const errors = reactive({});
const suspendValidation = ref(false);

/* --- ui states --- */
const submitting = ref(false);
const showCreateSuccess = ref(false);
const showCreateError = ref(false);
const createErrorMessage = ref("");
const showLoadMetaError = ref(false);
const loadMetaErrorMessage = ref("");

/* =========================================================
 * 5. Lifecycle
 * ======================================================= */

/**
 * โหลดข้อมูล meta (prefix, department, team, position)
 */
onMounted(async () => {
    try {
        const { data } = await axios.get("/meta");

        prefixes.value = (data.prefixes || []).map((p) => ({
            label: p.label,
            value: p.value,
        }));

        companies.value = data.companies.map((c) => ({
            label: c.com_name,
            value: c.id,
            code: c.com_name,
        }));

        departments.value = (data.departments || []).map((d) => ({
            label: d.dpm_name,
            value: d.id,
        }));

        teams.value = (data.teams || []).map((t) => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }));

        positions.value = (data.positions || []).map((p) => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }));
    } catch {
        showLoadMetaError.value = true;
        loadMetaErrorMessage.value = "Load failed. Please try again.";
    } finally {
        loadingMeta.value = false;
    }
});

/* =========================================================
 * 6. Computed
 * ======================================================= */

/**
 * team options filter ตาม department
 */
const teamOptions = computed(() => {
    if (!form.department) return [];
    return teams.value.filter(
        (t) => t.department_id === Number(form.department)
    );
});

/**
 * position options filter ตาม team
 */
const positionOptions = computed(() => {
    if (!form.team) return [];
    return positions.value.filter((p) => p.team_id === Number(form.team));
});

/**
 * รวม companyId + employeeNumber เป็น employeeId
 */
const employeeIdCombined = computed(() => {
    if (!form.companyCode || !form.employeeNumber) return "";
    return `${form.companyCode}${form.employeeNumber}`;
});

/**
 * ตรวจสอบสิทธิ์เป็น Employee หรือไม่
 */
const isEmployeePermission = computed(() => form.permission === "employee");

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
    passwordMin: "Password must be at least 8 characters",
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
};

/**
 * validate field
 */
function validateField(key, value) {
    if (errors[key] && errors[key].includes('already')) {
        return errors[key]
    }

    // employee ไม่ต้อง validate password
    if (isEmployeePermission.value && key === "password") {
        return "";
    }

    const rules = fieldRules[key] || [];

    for (const r of rules) {
        if (r === "requiredSelect" && !value) {
            return MSG.requiredSelect;
        }

        if (r === "requiredText") {
            if (!value || !/^[A-Za-zก-๙ .'-]+$/u.test(value)) {
                return MSG.requiredText;
            }
        }

        if (r === "requiredNumber") {
            if (!value) return "Required phone number";
            if (!/^\d{10}$/.test(value)) {
                return "Phone number must be 10 digits";
            }
        }

        if (r === "requiredEmail") {
            if (!value || !(value.includes("@") && value.includes("."))) {
                return MSG.requiredEmail;
            }
        }

        if (key === 'password') {
            if (!value) return MSG.requiredField
            if (value.length < 8) return MSG.passwordMin
        }

        if (r === 'requiredField' && !value) {
            return MSG.requiredField
        }
    }

    if (key === "employeeNumber") {
        if (!/^\d*$/.test(value)) {
            return "Only numbers are allowed";
        }

        if (value.length !== 4) {
            return MSG.employeeNumber4;
        }
    }

    return "";
}

/**
 * validate ทั้งฟอร์ม (ตอน submit)
 */
function validate() {
    Object.keys(errors).forEach((k) => delete errors[k]);
    Object.keys(fieldRules).forEach((k) => {
        const msg = validateField(k, form[k]);
        if (msg) errors[k] = msg;
    });
    return Object.keys(errors).length === 0;
}

/* =========================================================
 * 8. Watchers
 * ======================================================= */

/**
 * live validation (หยุดได้ด้วย suspendValidation)
 */
Object.keys(fieldRules).forEach((k) => {
    watch(
        () => form[k],
        () => {
            if (suspendValidation.value) return;
            if (errors[k] && errors[k].includes("already exist")) {
                delete errors[k];
            }

            const msg = validateField(k, form[k]);
            msg ? (errors[k] = msg) : delete errors[k];
        }
    );
});

/**
 * reset team / position เมื่อเปลี่ยน department
 */
watch(
    () => form.department,
    () => {
        form.team = "";
        form.position = "";
        delete errors.team;
        delete errors.position;
    }
);

/**
 * reset position เมื่อเปลี่ยน team
 */
watch(
    () => form.team,
    () => {
        form.position = "";
        delete errors.position;
    }
);

/**
 * ล้างค่า email / password เมื่อเปลี่ยนสิทธิ์เป็น Employee
 */
watch(
    () => form.permission,
    (newVal) => {
        if (newVal === "employee") {
            form.password = "";
            delete errors.password;
        }
    }
);

/**
 * อัพเดท companyCode เมื่อเปลี่ยน companyId
 */
watch(
    () => form.companyId,
    (id) => {
        const company = companies.value.find((c) => c.value === id);
        form.companyCode = company?.code || "";
    }
);

/* =========================================================
 * 9. Submit / Actions
 * ======================================================= */

/**
 * submit create employee
 */
async function handleSubmit() {
    showCreateSuccess.value = false;
    if (!validate()) return;

    submitting.value = true;
    try {
        await axios.post("/save-employee", {
            emp_company_id: Number(form.companyId),
            emp_id: employeeIdCombined.value,
            emp_prefix: Number(form.prefix),
            emp_nickname: form.nickname || null,
            emp_firstname: form.firstName,
            emp_lastname: form.lastName,
            emp_email: form.email,
            emp_password: isEmployeePermission.value ? null : form.password,
            emp_phone: String(form.phone),
            emp_id: employeeIdCombined.value,
            emp_position_id: Number(form.position),
            emp_department_id: Number(form.department),
            emp_team_id: Number(form.team),
            emp_permission: form.permission,
        })


        suspendValidation.value = true
        Object.keys(form).forEach(k => form[k] = "")
        Object.keys(errors).forEach(k => delete errors[k])
        showCreateSuccess.value = true

        suspendValidation.value = true;
        Object.keys(form).forEach((k) => (form[k] = ""));
        Object.keys(errors).forEach((k) => delete errors[k]);
        showCreateSuccess.value = true;
    } catch (err) {
        if (err.response?.status === 422 && err.response.data.errors) {
            const backendErrors = err.response.data.errors;
            const fieldMap = {
                emp_email: "email",
                emp_phone: "phone",
                emp_id: "employeeNumber",
                emp_firstname: "firstName",
                emp_lastname: "lastName",
            };

            Object.keys(backendErrors).forEach((key) => {
                const frontKey = fieldMap[key] || key;
                errors[frontKey] = Array.isArray(backendErrors[key])
                    ? backendErrors[key][0]
                    : backendErrors[key];
            });
        } else {
            createErrorMessage.value =
                err.response?.data?.message ||
                err.message ||
                "Sorry, please try again later.";
            showCreateError.value = true;
        }
    } finally {
        submitting.value = false;
    }
}

/**
 * cancel form
 */
function onCancel() {
    Object.keys(form).forEach((k) => (form[k] = ""));
    Object.keys(errors).forEach((k) => delete errors[k]);
    router.push("/employee");
}

/* =========================================================
 * 10. Modal Handlers
 * ======================================================= */

function handleSuccessClose() {
    showCreateSuccess.value = false;
    router.push("/employee");
}

function handleErrorClose() {
    showCreateError.value = false;
}

function handleLoadMetaErrorClose() {
    showLoadMetaError.value = false;
}

/* =========================================================
 * 11.
 * ======================================================= */
function onEmployeeNumberInput(e) {
    const rawValue = e.target.value;
    if (/[^0-9]/.test(rawValue)) {
        errors.employeeNumber = "Only numbers are allowed";
    } else {
        delete errors.employeeNumber;
    }
    form.employeeNumber = rawValue.replace(/\D/g, "").slice(0, 4);
}
</script>

<style></style>
