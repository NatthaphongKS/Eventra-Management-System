<template>
    <div>
        <div class="mx-auto max-w-[1400px] px-6">
            <header class="pt-6 mb-6">
                <link rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

                <div class="flex items-center justify-between gap-3 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 ml-8">
                        Edit Employee
                    </h2>
                </div>
            </header>

            <div class="px-2 py-0">
                <div class="max-w-[1400px] mx-auto px-6">
                    <form @submit.prevent="openConfirmSave">

                        <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10 gap-y-5">

                            <FormField label="Prefix" required class="w-full">
                                <DropdownPill v-model="form.emp_prefix" :options="prefixes" placeholder="Select prefix"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_prefix" />
                            </FormField>

                            <FormField label="Department" required>
                                <DropdownPill v-model="form.emp_department_id" :options="departments"
                                    placeholder="Select Department" class="mt-1 block h-11 w-full"
                                    :error="errors.emp_department_id" />
                            </FormField>

                            <FormField label="First Name" required>
                                <InputPill v-model="form.emp_firstname" placeholder="Ex.Perapat"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_firstname" />
                            </FormField>

                            <FormField label="Team" required>
                                <DropdownPill v-model="form.emp_team_id" :options="teamOptions"
                                    :placeholder="form.emp_department_id ? 'Select Team' : 'Please select Department first'"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_team_id"
                                    :disabled="!form.emp_department_id" />
                            </FormField>

                            <FormField label="Last Name" required>
                                <InputPill v-model="form.emp_lastname" placeholder="Ex.Saimai"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_lastname" />
                            </FormField>

                            <FormField label="Position" required>
                                <DropdownPill v-model="form.emp_position_id" :options="positionOptions"
                                    :placeholder="form.emp_team_id ? 'Select Position' : 'Please select Team first'"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_position_id"
                                    :disabled="!form.emp_team_id" />
                            </FormField>

                            <FormField label="Nickname" required>
                                <InputPill v-model="form.emp_nickname" placeholder="Ex.beam"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_nickname" />
                            </FormField>

                            <FormField label="Email" required>
                                <div class="relative">
                                    <InputPill v-model="form.emp_email" type="email" placeholder="Ex.example@gmail.com"
                                        class="mt-1 block h-11 w-full disabled:!bg-neutral-100 disabled:cursor-not-allowed disabled:!text-neutral-400"
                                        :error="errors.emp_email" :disabled="!isAdmin" />
                                    <p v-if="!isAdmin"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none">
                                        *Only Administrator can change email
                                    </p>
                                </div>
                            </FormField>

                            <FormField label="Phone" required>
                                <InputPill v-model="form.emp_phone" placeholder="Ex.0988900988" maxlength="10"
                                    class="mt-1 block h-11 w-full" :error="errors.emp_phone" />
                            </FormField>

                            <FormField label="Password" :required="requirePassword">
                                <div class="relative">
                                    <InputPill v-model="form.password" type="password"
                                        :disabled="(!isAdmin || form.emp_permission === 'employee') && !requirePassword"
                                        :placeholder="requirePassword
                                            ? 'Password is required'
                                            : form.emp_permission === 'employee'
                                                ? 'No password required'
                                                : isAdmin
                                                    ? 'Leave blank to keep current password'
                                                    : 'Admin only'
                                            " class="mt-1 block h-11 w-full
                                            disabled:!bg-neutral-100
                                            disabled:cursor-not-allowed
                                            disabled:!text-neutral-400
                                            disabled:placeholder:!text-neutral-400" :error="errors.password" />
                                    <p v-if="form.emp_permission === 'employee'"
                                        class="absolute -bottom-5 left-0 text-[10px] text-gray-400 pointer-events-none">
                                        *Employee does not require password
                                    </p>
                                    <p v-if="!isAdmin"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none">
                                        *Only Administrator can change password
                                    </p>
                                </div>
                            </FormField>

                            <FormField label="Employee ID" required>
                                <div class="grid grid-cols-2 gap-3 mt-1">
                                    <DropdownPill v-model="form.companyId" :options="companies" placeholder="Company"
                                        class="h-11 w-full" :error="errors.companyId" />
                                    <InputPill v-model="form.employeeNumber" placeholder="Ex.0001" maxlength="4"
                                        class="h-11 w-full" :error="errors.employeeNumber"
                                        @input="onEmployeeNumberInput" />
                                </div>
                            </FormField>

                            <FormField label="Permission" required>
                                <div class="relative">
                                    <DropdownPill v-model="form.emp_permission" :options="permissions"
                                        placeholder="Select Permission" class="mt-1 block h-11 w-full"
                                        :error="errors.emp_permission" :disabled="!isAdmin" />
                                    <p v-if="!isAdmin"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none">
                                        *Only Administrator can change permission
                                    </p>
                                </div>
                            </FormField>

                            <div class="pt-2 flex items-end">
                                <button type="button" @click="onCancel" class="inline-flex -ml-11">
                                    <CancelButton />
                                </button>
                            </div>

                            <div class="mt-auto pt-2 flex justify-end flex-col items-end relative z-10">
                                <button type="submit" :disabled="saving"
                                    class="inline-flex items-center justify-center gap-2 w-[140px] h-[45px] rounded-[20px] bg-green-600 text-white font-bold text-[15px] shadow-sm transition hover:shadow-md disabled:opacity-50">
                                    <span v-if="!saving"
                                        class="material-symbols-outlined text-[20px] leading-none">check</span>
                                    <span>{{ saving ? "Saving…" : "Confirm" }}</span>
                                </button>

                                <p v-if="noChange" class="text-amber-600 text-sm pt-2">
                                    No changes were made.
                                </p>
                                <p v-if="saveError" class="text-rose-600 text-sm pt-2">
                                    {{ saveError }}
                                </p>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
            :showCancel="alert.showCancel" :okText="alert.okText" :cancelText="alert.cancelText"
            @confirm="alert.onConfirm" @cancel="alert.onCancel" />
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";

/* ---------- Components ---------- */
import FormField from "@/components/Input/FormField.vue";
import InputPill from "@/components/Input/InputPill.vue";
import DropdownPill from "@/components/Input/DropdownPill.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";

const router = useRouter();
const route = useRoute();
const employeeId = route.params.id;

/* ------- Options ------- */
const prefixes = [
    { label: "นาย", value: "นาย" },
    { label: "นาง", value: "นาง" },
    { label: "นางสาว", value: "นางสาว" },
];

const permissions = [
    { label: "Administrator", value: "admin" },
    { label: "Human Resources", value: "hr" },
    { label: "Employee", value: "employee" },
];

const companies = ref([]);
const departments = ref([]);
const teams = ref([]);
const positions = ref([]);

/* ------- Form ------- */
const form = reactive({
    emp_prefix: "",
    emp_firstname: "",
    emp_lastname: "",
    emp_nickname: "",
    emp_phone: "",
    companyId: "",
    employeeNumber: "",
    companyCode: "",
    emp_department_id: "",
    emp_team_id: "",
    emp_position_id: "",
    emp_email: "",
    password: "",
    emp_permission: "",
});

let original = {};

/* ------- State ------- */
const errors = reactive({});
const saving = ref(false);
const saveError = ref("");
const noChange = ref(false);

/* ------- Alert State ------- */
const alert = reactive({
    open: false,
    type: "confirm",
    title: "",
    message: "",
    showCancel: false,
    okText: "OK",
    cancelText: "Cancel",
    onConfirm: null,
    onCancel: null,
});

function openAlert(cfg = {}) {
    alert.onConfirm = null;
    alert.onCancel = null;
    Object.assign(
        alert,
        {
            open: true,
            type: "success",
            title: "",
            message: "",
            showCancel: false,
            okText: "OK",
            cancelText: "Cancel",
        },
        cfg
    );
}

/* ------- Check User Role ------- */
const currentUser = computed(() => {
    try {
        const u = localStorage.getItem("userData");
        return u ? JSON.parse(u) : {};
    } catch (e) {
        return {};
    }
});

const isAdmin = computed(() => {
    return currentUser.value.emp_permission === "admin";
});

const isHR = computed(() => {
    return currentUser.value.emp_permission === "hr";
});

const requirePassword = computed(() => {
    return (
        originalPermission.value === "employee" &&
        (form.emp_permission === "admin" || form.emp_permission === "hr")
    );
});


/* ------- Computed Options ------- */
const teamOptions = computed(() => {
    if (!form.emp_department_id) return [];
    const depId = Number(form.emp_department_id);
    return teams.value.filter((t) => t.department_id === depId);
});

const positionOptions = computed(() => {
    if (!form.emp_team_id) return [];
    const teamId = Number(form.emp_team_id);
    return positions.value.filter((p) => p.team_id === teamId);
});

let originalPermission = ref("");
/* ------- Load Data ------- */
onMounted(async () => {
    try {
        const { data: metaData } = await axios.get("/meta");

        companies.value = (metaData.companies || []).map((c) => ({
            label: c.com_name,
            value: c.id,
            code: c.com_code || c.com_name,
        }));

        departments.value = (metaData.departments || []).map((d) => ({
            label: d.dpm_name,
            value: d.id,
        }));
        teams.value = (metaData.teams || []).map((t) => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }));
        positions.value = (metaData.positions || []).map((p) => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }));

        const { data: res } = await axios.get(`/employees/${employeeId}`);
        const userData = res.data || res;

        form.emp_prefix = userData.emp_prefix;
        form.emp_firstname = userData.emp_firstname;
        form.emp_lastname = userData.emp_lastname;
        form.emp_nickname = userData.emp_nickname;
        form.emp_phone = userData.emp_phone;
        form.emp_department_id = userData.emp_department_id;
        form.emp_team_id = userData.emp_team_id;
        form.emp_position_id = userData.emp_position_id;
        form.emp_email = userData.emp_email;
        form.emp_permission = userData.emp_permission;
        originalPermission.value = userData.emp_permission;
        form.password = "";

        const fullId = (userData.emp_id || "").trim();
        const sortedCompanies = [...companies.value].sort(
            (a, b) => b.code.length - a.code.length
        );
        const matchedCompany = sortedCompanies.find((c) =>
            fullId.toUpperCase().startsWith(c.code.toUpperCase())
        );

        if (matchedCompany) {
            form.companyId = matchedCompany.value;
            form.companyCode = matchedCompany.code;
            const regex = new RegExp(`^${matchedCompany.code}`, "i");
            form.employeeNumber = fullId.replace(regex, "");
        } else {
            form.employeeNumber = fullId;
        }

        original = JSON.parse(JSON.stringify(form));
    } catch (e) {
        console.error(e);
        openAlert({
            type: "error",
            title: "Error!",
            message: "Failed to load data.",
            onConfirm: () => router.back(),
        });
    }
});

/* ====== Validation ====== */
const MSG = {
    requiredSelect: "Required Select",
    requiredText: "Required field only text",
    requiredNumber: "Required field only number",
    requiredEmail: "Required email",
    requiredField: "Required field",
    employeeNumber4: "Must be 4 digits",
    passwordMin8: "Please enter a password with at least 8 characters",
};

const fieldRules = {
    emp_prefix: ["requiredSelect"],
    emp_department_id: ["requiredSelect"],
    emp_team_id: ["requiredSelect"],
    emp_position_id: ["requiredSelect"],
    emp_permission: ["requiredSelect"],
    emp_firstname: ["requiredText"],
    emp_lastname: ["requiredText"],
    emp_nickname: ["requiredText"],
    emp_phone: ["requiredNumber"],
    emp_email: ["requiredEmail"],
    companyId: ["requiredSelect"],
    employeeNumber: ["employeeNumber4"],
    password: ["passwordMin8"],
};

function validateField(key, value) {
    // employee → admin/hr ต้องใส่ password
    if (key === "password" && requirePassword.value) {
        if (!value) return "Password is required";
        if (value.length < 8) return MSG.passwordMin8;
        return "";
    }
    // employee ไม่ต้องใส่
    if (form.emp_permission === "employee" && key === "password") {
        return "";
    }

    const rules = fieldRules[key] || [];
    for (const r of rules) {
        if (r === "requiredSelect") {
            if (!value) return MSG.requiredSelect;
        } else if (r === "requiredText") {
            if (!value) return MSG.requiredText;
            const re = /^[A-Za-zก-๙ .'-]+$/u;
            if (!re.test(value)) return MSG.requiredText;
        } else if (r === "requiredNumber") {
            if (!value) return "Required phone number";
            if (!/^\d+$/.test(value) || value.length !== 10)
                return "Phone number must be 10 digits";
        } else if (r === "requiredEmail") {
            if (!value || !value.includes("@")) return MSG.requiredEmail;
        } else if (r === "requiredField") {
            if (!value) return MSG.requiredField;
        } else if (r === "employeeNumber4") {
            if (!value) return MSG.requiredField;
            if (!/^\d{4}$/.test(value)) return MSG.employeeNumber4;
        } else if (r === "passwordMin8") {
            if (value && value.length < 8) {
                return MSG.passwordMin8;
            }
        }
    }
    return "";
}

function validate() {
    Object.keys(errors).forEach((k) => delete errors[k]);
    Object.keys(fieldRules).forEach((k) => {
        const msg = validateField(k, form[k]);
        if (msg) errors[k] = msg;
    });
    return Object.keys(errors).length === 0;
}

function onEmployeeNumberInput(e) {
    let val = e.target.value.replace(/\D/g, "").slice(0, 4);
    form.employeeNumber = val;
    if (errors.employeeNumber) delete errors.employeeNumber;
}

/* ====== Watchers ====== */
Object.keys(fieldRules).forEach((k) => {
    watch(
        () => form[k],
        (v) => {
            const msg = validateField(k, v);
            if (msg) errors[k] = msg;
            else delete errors[k];
        }
    );
});

watch(
    () => form.emp_department_id,
    (n, o) => {
        if (o && n !== o) {
            form.emp_team_id = "";
            form.emp_position_id = "";
        }
    }
);
watch(
    () => form.emp_team_id,
    (n, o) => {
        if (o && n !== o) {
            form.emp_position_id = "";
        }
    }
);
watch(
    () => form.companyId,
    (newId) => {
        const found = companies.value.find((c) => c.value === newId);
        form.companyCode = found ? found.code : "";
    }
);
watch(
    () => form.emp_permission,
    (newVal, oldVal) => {
        if (
            newVal === "employee" &&
            (oldVal === "admin" || oldVal === "hr")
        ) {
            form.password = null;
            delete errors.password;
        }
    }
);


/* ------- Logic การบันทึก ------- */
function openConfirmSave() {
    noChange.value = false;
    saveError.value = "";

    if (!validate()) return;

    const current = { ...form };
    const prev = { ...original };
    if (!current.password) delete current.password;
    delete prev.password;

    if (JSON.stringify(current) === JSON.stringify(prev) && !form.password) {
        noChange.value = true;
        return;
    }

    openAlert({
        type: "confirm",
        title: "CONFIRM UPDATE",
        message: "Are you sure you want to update this employee?",
        showCancel: true,
        okText: "Yes, Update",
        onConfirm: async () => {
            await confirmSaveProcess();
        },
    });
}

async function confirmSaveProcess() {
    saving.value = true;
    saveError.value = "";
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const payload = { ...form };
        payload.emp_id = `${form.companyCode}${form.employeeNumber}`;

        if (payload.password) {
            payload.emp_password = payload.password;
        }
        delete payload.password;
        delete payload.companyId;
        delete payload.employeeNumber;
        delete payload.companyCode;

        if (!payload.password) delete payload.password;
        if (payload.emp_permission === "employee") payload.password = null;

        await axios.put(`/employees/${employeeId}`, payload);

        original = JSON.parse(JSON.stringify(form));
        original.password = "";

        openAlert({
            type: "success",
            title: "UPDATE SUCCESS!",
            message: "Employee data has been updated.",
            okText: "OK",
            onConfirm: () => {
                router.push("/employee");
            },
        });
    } catch (err) {
        console.error(err);
        let hasValidationError = false;
        const res = err.response;
        const msg = res?.data?.message || err.message || "";

        if (res && res.status === 422 && res.data.errors) {
            const apiErrors = res.data.errors;
            if (apiErrors.emp_phone) {
                errors.emp_phone = "This phone number is already in use.";
                hasValidationError = true;
            }
            if (apiErrors.emp_email) {
                errors.emp_email = "This email is already in use.";
                hasValidationError = true;
            }
            if (apiErrors.emp_id) {
                errors.employeeNumber = "This ID is already in use.";
                hasValidationError = true;
            }
        } else if (msg.includes("Duplicate entry")) {
            hasValidationError = true;
            if (msg.includes("phone"))
                errors.emp_phone = "This phone number is already in use.";
            else if (msg.includes("email"))
                errors.emp_email = "This email is already in use.";
            else if (msg.includes("emp_id"))
                errors.employeeNumber = "This employee ID is already in use.";
            else hasValidationError = false;
        }

        if (hasValidationError) {
            saving.value = false;
            return;
        }

        saveError.value = msg;
        openAlert({
            type: "error",
            title: "UPDATE FAILED!",
            message: msg,
        });
    } finally {
        saving.value = false;
    }
}

function onCancel() {
    if (saving.value) return;
    const current = { ...form };
    const prev = { ...original };
    if (!current.password) delete current.password;
    delete prev.password;

    if (JSON.stringify(current) !== JSON.stringify(prev) || form.password) {
        if (!confirm("Discard changes?")) return;
    }
    router.push("/employee");
}
</script>
