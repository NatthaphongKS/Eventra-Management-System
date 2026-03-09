/**
* ชื่อไฟล์: EditEmployee.vue
* คำอธิบาย: Component สำหรับฟอร์มแก้ไขข้อมูลพนักงาน ตรวจสอบ Validation และอัปเดตข้อมูลไปยัง Backend
พร้อมดักจับการเปลี่ยนหน้า
* Input: -
* Output: หน้าฟอร์มแก้ไขพนักงาน
* ชื่อผู้เขียน/แก้ไข: katcharuek sriphirom
* วันที่จัดทำ/แก้ไข: 8 มีนาคม 2569
*/

<template>
    <div>
        <div class="mx-auto max-w-[1400px] px-6">
            <header class="pt-6 mb-6">
                <link
                    rel="stylesheet"
                    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
                />
                <div class="flex items-center justify-between gap-3 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 ml-8">
                        Edit Employee
                    </h2>
                </div>
            </header>

            <div class="px-2 py-0">
                <div class="max-w-[1400px] mx-auto px-6">
                    <form @submit.prevent="openConfirmSaveDialog">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10 gap-y-5"
                        >
                            <FormField label="Prefix" required class="w-full">
                                <DropdownPill
                                    v-model="employeeForm.prefix"
                                    :options="prefixOptions"
                                    placeholder="Select prefix"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.prefix"
                                />
                            </FormField>

                            <FormField label="Department" required>
                                <DropdownPill
                                    v-model="employeeForm.departmentId"
                                    :options="departmentOptions"
                                    placeholder="Select Department"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.departmentId"
                                />
                            </FormField>

                            <FormField label="First Name" required>
                                <InputPill
                                    v-model="employeeForm.firstName"
                                    placeholder="Ex.Perapat"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.firstName"
                                />
                            </FormField>

                            <FormField label="Team" required>
                                <DropdownPill
                                    v-model="employeeForm.teamId"
                                    :options="filteredTeamOptions"
                                    :placeholder="
                                        employeeForm.departmentId
                                            ? 'Select Team'
                                            : 'Please select Department first'
                                    "
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.teamId"
                                    :disabled="!employeeForm.departmentId"
                                />
                            </FormField>

                            <FormField label="Last Name" required>
                                <InputPill
                                    v-model="employeeForm.lastName"
                                    placeholder="Ex.Saimai"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.lastName"
                                />
                            </FormField>

                            <FormField label="Position" required>
                                <DropdownPill
                                    v-model="employeeForm.positionId"
                                    :options="filteredPositionOptions"
                                    :placeholder="
                                        employeeForm.teamId
                                            ? 'Select Position'
                                            : 'Please select Team first'
                                    "
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.positionId"
                                    :disabled="!employeeForm.teamId"
                                />
                            </FormField>

                            <FormField label="Nickname" required>
                                <InputPill
                                    v-model="employeeForm.nickname"
                                    placeholder="Ex.beam"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.nickname"
                                />
                            </FormField>

                            <FormField label="Email" required>
                                <div class="relative">
                                    <InputPill
                                        v-model="employeeForm.email"
                                        type="email"
                                        placeholder="Ex.example@gmail.com"
                                        class="mt-1 block h-11 w-full disabled:!bg-neutral-100 disabled:cursor-not-allowed disabled:!text-neutral-400"
                                        :error="fieldErrors.email"
                                        :disabled="!isAdminUser"
                                    />
                                    <p
                                        v-if="!isAdminUser"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none"
                                    >
                                        *Only Administrator can change email
                                    </p>
                                </div>
                            </FormField>

                            <FormField label="Phone" required>
                                <InputPill
                                    v-model="employeeForm.phone"
                                    placeholder="Ex.0988900988"
                                    maxlength="10"
                                    class="mt-1 block h-11 w-full"
                                    :error="fieldErrors.phone"
                                />
                            </FormField>

                            <FormField
                                label="Password"
                                :required="isPasswordRequired"
                            >
                                <div class="relative">
                                    <InputPill
                                        v-model="employeeForm.password"
                                        type="password"
                                        :disabled="
                                            (!isAdminUser ||
                                                employeeForm.permission ===
                                                    'employee') &&
                                            !isPasswordRequired
                                        "
                                        :placeholder="
                                            isPasswordRequired
                                                ? 'Password is required'
                                                : employeeForm.permission ===
                                                    'employee'
                                                  ? 'No password required'
                                                  : isAdminUser
                                                    ? 'Leave blank to keep current password'
                                                    : 'Admin only'
                                        "
                                        class="mt-1 block h-11 w-full disabled:!bg-neutral-100 disabled:cursor-not-allowed disabled:!text-neutral-400 disabled:placeholder:!text-neutral-400"
                                        :error="fieldErrors.password"
                                    />
                                    <p
                                        v-if="
                                            employeeForm.permission === 'employee'
                                        "
                                        class="absolute -bottom-5 left-0 text-[10px] text-gray-400 pointer-events-none"
                                    >
                                        *Employee does not require password
                                    </p>
                                    <p
                                        v-if="!isAdminUser"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none"
                                    >
                                        *Only Administrator can change password
                                    </p>
                                </div>
                            </FormField>

                            <FormField label="Employee ID" required>
                                <div class="grid grid-cols-2 gap-3 mt-1">
                                    <DropdownPill
                                        v-model="employeeForm.companyId"
                                        :options="companyOptions"
                                        placeholder="Company"
                                        class="h-11 w-full"
                                        :error="fieldErrors.companyId"
                                    />
                                    <InputPill
                                        v-model="employeeForm.employeeNumber"
                                        placeholder="Ex.0001"
                                        maxlength="4"
                                        class="h-11 w-full"
                                        :error="fieldErrors.employeeNumber"
                                        @input="onEmployeeNumberInput"
                                    />
                                </div>
                            </FormField>

                            <FormField label="Permission" required>
                                <div class="relative">
                                    <DropdownPill
                                        v-model="employeeForm.permission"
                                        :options="permissionOptions"
                                        placeholder="Select Permission"
                                        class="mt-1 block h-11 w-full"
                                        :error="fieldErrors.permission"
                                        :disabled="!isAdminUser"
                                    />
                                    <p
                                        v-if="!isAdminUser"
                                        class="absolute -bottom-5 left-0 text-[10px] text-rose-500 pointer-events-none"
                                    >
                                        *Only Administrator can change
                                        permission
                                    </p>
                                </div>
                            </FormField>

                            <div class="pt-2 flex items-end">
                                <button
                                    type="button"
                                    @click="onCancel"
                                    class="inline-flex -ml-11"
                                >
                                    <CancelButton />
                                </button>
                            </div>

                            <div
                                class="mt-auto pt-2 flex justify-end flex-col items-end relative z-10"
                            >
                                <button
                                    type="submit"
                                    :disabled="isSaving"
                                    class="inline-flex items-center justify-center gap-2 w-[140px] h-[45px] rounded-[20px] bg-green-600 text-white font-bold text-[15px] shadow-sm transition hover:shadow-md disabled:opacity-50"
                                >
                                    <span
                                        v-if="!isSaving"
                                        class="material-symbols-outlined text-[20px] leading-none"
                                        >check</span
                                    >
                                    <span>{{
                                        isSaving ? "Saving…" : "Save"
                                    }}</span>
                                </button>

                                <p
                                    v-if="submitErrorMessage"
                                    class="text-rose-600 text-sm pt-2"
                                >
                                    {{ submitErrorMessage }}
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <ModalAlert
            v-model:open="alertDialog.isOpen"
            :type="alertDialog.type"
            :title="alertDialog.title"
            :message="alertDialog.message"
            :showCancel="alertDialog.showCancel"
            :okText="alertDialog.okText"
            :cancelText="alertDialog.cancelText"
            @confirm="alertDialog.onConfirm"
            @cancel="alertDialog.onCancel"
        />
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from "vue";
import { useRouter, useRoute, onBeforeRouteLeave } from "vue-router";
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

/* ------- Dropdown Options ------- */
const prefixOptions = [
    { label: "นาย", value: "นาย" },
    { label: "นาง", value: "นาง" },
    { label: "นางสาว", value: "นางสาว" },
];

const permissionOptions = [
    { label: "Administrator", value: "admin" },
    { label: "Human Resources", value: "hr" },
    { label: "Employee", value: "employee" },
];

const companyOptions = ref([]);
const departmentOptions = ref([]);
const allTeams = ref([]);
const allPositions = ref([]);

/* ------- Form ------- */
const employeeForm = reactive({
    prefix: "",
    firstName: "",
    lastName: "",
    nickname: "",
    phone: "",
    companyId: "",
    employeeNumber: "",
    companyCode: "",
    departmentId: "",
    teamId: "",
    positionId: "",
    email: "",
    password: "",
    permission: "",
});

let originalFormSnapshot = {};

/* ------- State ------- */
const fieldErrors = reactive({});
const isSaving = ref(false);
const submitErrorMessage = ref("");
const hasNoChanges = ref(false);

/* ------- Alert Dialog State ------- */
const alertDialog = reactive({
    isOpen: false,
    type: "confirm",
    title: "",
    message: "",
    showCancel: false,
    okText: "OK",
    cancelText: "Cancel",
    onConfirm: null,
    onCancel: null,
});

function openAlertDialog(config = {}) {
    alertDialog.onConfirm = null;
    alertDialog.onCancel = null;
    Object.assign(
        alertDialog,
        {
            isOpen: true,
            type: "success",
            title: "",
            message: "",
            showCancel: false,
            okText: "OK",
            cancelText: "Cancel",
        },
        config,
    );
}

/* ------- Check User Role ------- */
const loggedInUser = computed(() => {
    try {
        const storedUser = localStorage.getItem("userData");
        return storedUser ? JSON.parse(storedUser) : {};
    } catch (e) {
        return {};
    }
});

const isAdminUser = computed(() => {
    return loggedInUser.value.emp_permission === "admin";
});

const isPasswordRequired = computed(() => {
    return (
        previousPermission.value === "employee" &&
        (employeeForm.permission === "admin" || employeeForm.permission === "hr")
    );
});

/* ------- Filtered Dropdown Options ------- */
const filteredTeamOptions = computed(() => {
    if (!employeeForm.departmentId) return [];
    const selectedDeptId = Number(employeeForm.departmentId);
    return allTeams.value.filter((t) => t.department_id === selectedDeptId);
});

const filteredPositionOptions = computed(() => {
    if (!employeeForm.teamId) return [];
    const selectedTeamId = Number(employeeForm.teamId);
    return allPositions.value.filter((p) => p.team_id === selectedTeamId);
});

let previousPermission = ref("");

/* ------- Load Data on Mount ------- */
onMounted(async () => {
    try {
        const { data: metaData } = await axios.get("/meta");

        companyOptions.value = (metaData.companies || []).map((c) => ({
            label: c.com_name,
            value: c.id,
            code: c.com_code || c.com_name,
        }));

        departmentOptions.value = (metaData.departments || []).map((d) => ({
            label: d.dpm_name,
            value: d.id,
        }));
        allTeams.value = (metaData.teams || []).map((t) => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }));
        allPositions.value = (metaData.positions || []).map((p) => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }));

        const { data: res } = await axios.get(`/employees/${employeeId}`);
        const fetchedEmployee = res.data || res;

        employeeForm.prefix = fetchedEmployee.emp_prefix;
        employeeForm.firstName = fetchedEmployee.emp_firstname;
        employeeForm.lastName = fetchedEmployee.emp_lastname;
        employeeForm.nickname = fetchedEmployee.emp_nickname;
        employeeForm.phone = fetchedEmployee.emp_phone;
        employeeForm.departmentId = fetchedEmployee.emp_department_id;
        employeeForm.teamId = fetchedEmployee.emp_team_id;
        employeeForm.positionId = fetchedEmployee.emp_position_id;
        employeeForm.email = fetchedEmployee.emp_email;
        employeeForm.permission = fetchedEmployee.emp_permission;
        previousPermission.value = fetchedEmployee.emp_permission;
        employeeForm.password = "";

        const fullEmployeeId = (fetchedEmployee.emp_id || "").trim();
        const sortedCompanies = [...companyOptions.value].sort(
            (a, b) => b.code.length - a.code.length,
        );
        const matchedCompany = sortedCompanies.find((c) =>
            fullEmployeeId.toUpperCase().startsWith(c.code.toUpperCase()),
        );

        if (matchedCompany) {
            employeeForm.companyId = matchedCompany.value;
            employeeForm.companyCode = matchedCompany.code;
            const prefixRegex = new RegExp(`^${matchedCompany.code}`, "i");
            employeeForm.employeeNumber = fullEmployeeId.replace(prefixRegex, "");
        } else {
            employeeForm.employeeNumber = fullEmployeeId;
        }

        originalFormSnapshot = JSON.parse(JSON.stringify(employeeForm));
    } catch (e) {
        console.error(e);
        openAlertDialog({
            type: "error",
            title: "Error!",
            message: "Failed to load data.",
            onConfirm: () => router.back(),
        });
    }
});

/* ====== Validation ====== */
const validationMessages = {
    requiredSelect: "Required Select",
    requiredText: "Required field only text",
    requiredNumber: "Required field only number",
    requiredEmail: "Required email",
    requiredField: "Required field",
    employeeNumber4: "Must be 3 digits",
    passwordMin8: "Please enter a password with at least 8 characters",
};

const fieldValidationRules = {
    prefix: ["requiredSelect"],
    departmentId: ["requiredSelect"],
    teamId: ["requiredSelect"],
    positionId: ["requiredSelect"],
    permission: ["requiredSelect"],
    firstName: ["requiredText"],
    lastName: ["requiredText"],
    nickname: ["requiredText"],
    phone: ["requiredNumber"],
    email: ["requiredEmail"],
    companyId: ["requiredSelect"],
    employeeNumber: ["employeeNumber4"],
    password: ["passwordMin8"],
};

function validateSingleField(fieldKey, fieldValue) {
    if (fieldKey === "password" && isPasswordRequired.value) {
        if (!fieldValue) return "Password is required";
        if (fieldValue.length < 8) return validationMessages.passwordMin8;
        return "";
    }
    if (employeeForm.permission === "employee" && fieldKey === "password") {
        return "";
    }

    const rules = fieldValidationRules[fieldKey] || [];
    for (const rule of rules) {
        if (rule === "requiredSelect") {
            if (!fieldValue) return validationMessages.requiredSelect;
        } else if (rule === "requiredText") {
            if (!fieldValue) return validationMessages.requiredText;
            const textOnlyRegex = /^[A-Za-zก-๙ .'-]+$/u;
            if (!textOnlyRegex.test(fieldValue)) return validationMessages.requiredText;
        } else if (rule === "requiredNumber") {
            if (!fieldValue) return "Required phone number";
            if (!/^\d+$/.test(fieldValue) || fieldValue.length !== 10)
                return "Phone number must be 10 digits";
        } else if (rule === "requiredEmail") {
            if (!fieldValue || !fieldValue.includes("@"))
                return validationMessages.requiredEmail;
        } else if (rule === "requiredField") {
            if (!fieldValue) return validationMessages.requiredField;
        } else if (rule === "employeeNumber4") {
            if (!fieldValue) return validationMessages.requiredField;
            if (!/^\d{3}$/.test(fieldValue))
                return validationMessages.employeeNumber4;
        } else if (rule === "passwordMin8") {
            if (fieldValue && fieldValue.length < 8) {
                return validationMessages.passwordMin8;
            }
        }
    }
    return "";
}

function validateAllFields() {
    Object.keys(fieldErrors).forEach((k) => delete fieldErrors[k]);
    Object.keys(fieldValidationRules).forEach((fieldKey) => {
        const errorMsg = validateSingleField(fieldKey, employeeForm[fieldKey]);
        if (errorMsg) fieldErrors[fieldKey] = errorMsg;
    });
    return Object.keys(fieldErrors).length === 0;
}

function onEmployeeNumberInput(e) {
    let sanitizedValue = e.target.value.replace(/\D/g, "").slice(0, 4);
    employeeForm.employeeNumber = sanitizedValue;
    if (fieldErrors.employeeNumber) delete fieldErrors.employeeNumber;
}

/* ====== Watchers ====== */
Object.keys(fieldValidationRules).forEach((fieldKey) => {
    watch(
        () => employeeForm[fieldKey],
        (newValue) => {
            const errorMsg = validateSingleField(fieldKey, newValue);
            if (errorMsg) fieldErrors[fieldKey] = errorMsg;
            else delete fieldErrors[fieldKey];
        },
    );
});

watch(
    () => employeeForm.departmentId,
    (newDeptId, oldDeptId) => {
        if (oldDeptId && newDeptId !== oldDeptId) {
            employeeForm.teamId = "";
            employeeForm.positionId = "";
        }
    },
);

watch(
    () => employeeForm.teamId,
    (newTeamId, oldTeamId) => {
        if (oldTeamId && newTeamId !== oldTeamId) {
            employeeForm.positionId = "";
        }
    },
);

watch(
    () => employeeForm.companyId,
    (newCompanyId) => {
        const matchedCompany = companyOptions.value.find((c) => c.value === newCompanyId);
        employeeForm.companyCode = matchedCompany ? matchedCompany.code : "";
    },
);

watch(
    () => employeeForm.permission,
    (newPermission, oldPermission) => {
        if (newPermission === "employee" && (oldPermission === "admin" || oldPermission === "hr")) {
            employeeForm.password = null;
            delete fieldErrors.password;
        }
    },
);

/* ------- Save Logic ------- */
function openConfirmSaveDialog() {
    hasNoChanges.value = false;
    submitErrorMessage.value = "";

    if (!validateAllFields()) return;

    const currentFormData = { ...employeeForm };
    const originalFormData = { ...originalFormSnapshot };
    if (!currentFormData.password) delete currentFormData.password;
    delete originalFormData.password;

    if (JSON.stringify(currentFormData) === JSON.stringify(originalFormData) && !employeeForm.password) {
        hasNoChanges.value = true;
        openAlertDialog({
            type: "error",
            title: "NO CHANGES MADE",
            message: "No changes detected in the form.",
            showCancel: true,
            cancelText: "No, Go Back",
        });
        return;
    }

    openAlertDialog({
        type: "confirm",
        title: "CONFIRM UPDATE",
        message: "Are you sure you want to update this employee?",
        showCancel: true,
        okText: "Yes, Update",
        onConfirm: async () => {
            await processSave();
        },
    });
}

let hasUserConfirmedLeave = false;

async function processSave() {
    isSaving.value = true;
    submitErrorMessage.value = "";
    Object.keys(fieldErrors).forEach((k) => delete fieldErrors[k]);

    try {
        // ---- ✅ Check for duplicate name before saving ----
        const { data: allEmployeesRes } = await axios.get("/employees");
        const employeeList = allEmployeesRes.data || allEmployeesRes;
        const normalizeString = (s) => (s || "").trim().toLowerCase();

        const newFirstName = normalizeString(employeeForm.firstName);
        const newLastName = normalizeString(employeeForm.lastName);

        const duplicateEmployee = employeeList.find((emp) => {
            if (String(emp.emp_id) === String(employeeId)) return false;
            return (
                normalizeString(emp.emp_firstname) === newFirstName &&
                normalizeString(emp.emp_lastname) === newLastName
            );
        });

        if (duplicateEmployee) {
            fieldErrors.firstName = "This first name is already in use.";
            fieldErrors.lastName = "This last name is already in use.";
            isSaving.value = false;
            return;
        }
        // ---- End duplicate name check ----

        const requestPayload = { ...employeeForm };
        requestPayload.emp_id = `${employeeForm.companyCode}${employeeForm.employeeNumber}`;

        if (requestPayload.password) {
            requestPayload.emp_password = requestPayload.password;
        }
        delete requestPayload.password;
        delete requestPayload.employeeNumber;
        delete requestPayload.companyCode;

        if (!requestPayload.password) delete requestPayload.password;
        if (requestPayload.emp_permission === "employee") requestPayload.password = null;

        await axios.put(`/employees/${employeeId}`, requestPayload);

        originalFormSnapshot = JSON.parse(JSON.stringify(employeeForm));
        originalFormSnapshot.password = "";

        openAlertDialog({
            type: "success",
            title: "UPDATE SUCCESS!",
            message: "Employee data has been updated.",
            okText: "OK",
            onCancel: () => {
                hasUserConfirmedLeave = true;
                router.push("/employee");
            },
            onConfirm: () => {
                hasUserConfirmedLeave = true;
                router.push("/employee");
            },
        });
    } catch (err) {
        console.error(err);
        let hasDuplicateFieldError = false;
        const apiResponse = err.response;
        const errorMessage = apiResponse?.data?.message || err.message || "";

        if (apiResponse && apiResponse.status === 422 && apiResponse.data.errors) {
            const apiFieldErrors = apiResponse.data.errors;
            if (apiFieldErrors.emp_phone) {
                fieldErrors.phone = "This phone number is already in use.";
                hasDuplicateFieldError = true;
            }
            if (apiFieldErrors.emp_email) {
                fieldErrors.email = "This email is already in use.";
                hasDuplicateFieldError = true;
            }
            if (apiFieldErrors.emp_id) {
                fieldErrors.employeeNumber = "This ID is already in use.";
                hasDuplicateFieldError = true;
            }
        } else if (errorMessage.includes("Duplicate entry")) {
            hasDuplicateFieldError = true;
            if (errorMessage.includes("phone"))
                fieldErrors.phone = "This phone number is already in use.";
            else if (errorMessage.includes("email"))
                fieldErrors.email = "This email is already in use.";
            else if (errorMessage.includes("emp_id"))
                fieldErrors.employeeNumber = "This employee ID is already in use.";
            else hasDuplicateFieldError = false;
        }

        if (hasDuplicateFieldError) {
            isSaving.value = false;
            return;
        }

        submitErrorMessage.value = errorMessage;
        openAlertDialog({
            type: "error",
            title: "UPDATE FAILED!",
            message: errorMessage,
        });
    } finally {
        isSaving.value = false;
    }
}

function onCancel() {
    if (isSaving.value) return;
    router.push("/employee");
}

/* ====== Navigation Guard ====== */
onBeforeRouteLeave((to, from, next) => {
    if (hasUserConfirmedLeave || isSaving.value) {
        next();
        return;
    }

    const currentFormData = { ...employeeForm };
    const originalFormData = { ...originalFormSnapshot };
    if (!currentFormData.password) delete currentFormData.password;
    delete originalFormData.password;

    const hasUnsavedChanges =
        JSON.stringify(currentFormData) !== JSON.stringify(originalFormData) || !!employeeForm.password;

    if (hasUnsavedChanges) {
        openAlertDialog({
            type: "confirm",
            title: "DO YOU WANT TO LEAVE THIS CHANGE?",
            message: "Your changes will be lost.",
            showCancel: true,
            okText: "OK",
            cancelText: "Cancel",
            onConfirm: () => {
                alertDialog.isOpen = false;
                hasUserConfirmedLeave = true;
                next();
            },
            onCancel: () => {
                alertDialog.isOpen = false;
                next(false);
            },
        });
    } else {
        next();
    }
});
</script>
