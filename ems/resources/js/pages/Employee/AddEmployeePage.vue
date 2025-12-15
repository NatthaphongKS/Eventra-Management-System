<template>
    <div class="font-[Poppins] text-neutral-800 min-h-screen bg-white">
        <header class="max-w-[1160px] mx-auto px-6 pt-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="text-3xl font-semibold text-neutral-800">
                    Add New Employee
                </div>

                <div class="relative">
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".csv"
                        class="hidden"
                        @change="onImport"
                    />
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 transition"
                        @click="goImport"
                    >
                        <span class="material-symbols-outlined text-[20px]"
                            >download</span
                        >
                        <span>Import</span>
                    </button>
                </div>
            </div>

            <EmployeeCreateSuccess
                :open="showCreateSuccess"
                @close="handleSuccessClose"
            />
        </header>

        <div class="max-w-[1160px] mx-auto px-6 pb-20">
            <form @submit.prevent="handleSubmit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-8">
                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Prefix
                                <span class="text-red-600">*</span></label
                            >
                            <DropdownPill
                                v-model="form.prefix"
                                :options="prefixes"
                                placeholder="Select prefix"
                                :error="errors.prefix"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >First Name
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.firstName"
                                placeholder="Ex. Perapat"
                                :error="errors.firstName"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Last Name
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.lastName"
                                placeholder="Ex. Saimai"
                                :error="errors.lastName"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Nickname</label
                            >
                            <InputPill
                                v-model="form.nickname"
                                placeholder="Ex. Beam"
                                :error="errors.nickname"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Phone
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.phone"
                                placeholder="Ex. 0988900988"
                                inputmode="numeric"
                                :error="errors.phone"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Employee ID
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.employeeId"
                                placeholder="Ex. CN707008"
                                :error="errors.employeeId"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div class="pt-6 hidden md:block">
                            <button
                                type="button"
                                @click="onCancel"
                                class="h-[48px] px-8 rounded-[20px] border border-neutral-300 text-neutral-600 font-semibold hover:bg-neutral-50 hover:text-neutral-800 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Department
                                <span class="text-red-600">*</span></label
                            >
                            <DropdownPill
                                v-model="form.department"
                                :options="departments"
                                placeholder="Select Department"
                                :error="errors.department"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Team <span class="text-red-600">*</span></label
                            >
                            <DropdownPill
                                v-model="form.team"
                                :options="teamOptions"
                                placeholder="Select Team"
                                :error="errors.team"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Position
                                <span class="text-red-600">*</span></label
                            >
                            <DropdownPill
                                v-model="form.position"
                                :options="positions"
                                placeholder="Select Position"
                                :error="errors.position"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Email
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.email"
                                type="email"
                                placeholder="Ex. 66160106@go.buu.ac.th"
                                :error="errors.email"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Password
                                <span class="text-red-600">*</span></label
                            >
                            <InputPill
                                v-model="form.password"
                                type="password"
                                placeholder="Ex. Ssaw.1234"
                                :error="errors.password"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-neutral-800 font-semibold text-[15px] mb-3"
                                >Permission
                                <span class="text-red-600">*</span></label
                            >
                            <DropdownPill
                                v-model="form.permission"
                                :options="permissions"
                                placeholder="Select Permission"
                                :error="errors.permission"
                                class="w-full h-[52px] border border-neutral-200 rounded-[20px] px-5 text-[15px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>

                        <div
                            class="pt-6 flex flex-col-reverse md:flex-row justify-end gap-4"
                        >
                            <button
                                type="button"
                                @click="onCancel"
                                class="md:hidden h-[48px] px-8 rounded-[20px] border border-neutral-300 text-neutral-600 font-semibold hover:bg-neutral-50 transition"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center justify-center gap-2 h-[48px] px-8 rounded-[20px] bg-green-600 text-white font-semibold hover:bg-green-700 transition disabled:opacity-60 disabled:cursor-not-allowed shadow-sm hover:shadow-md"
                            >
                                <span
                                    class="material-symbols-outlined text-[20px]"
                                    >add</span
                                >
                                <span>Create</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

// components (ใช้ InputPill และ DropdownPill เหมือนเดิม)
import InputPill from "../../components/Input/InputPill.vue";
import DropdownPill from "../../components/Input/DropdownPill.vue";
import EmployeeCreateSuccess from "../../components/Alert/Employee/EmployeeCreateSuccess.vue";
import ImportButton from "@/components/Button/ImportButton.vue";
import CreateButton from "@/components/Button/CreateButton.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "../../components/Alert/ModalAlert.vue";

const router = useRouter();
const goImport = () => router.push({ name: "upload-file" });

/* ------- options ------- */
const prefixes = [
    { label: "นาย", value: 1 },
    { label: "นาง", value: 2 },
    { label: "นางสาว", value: 3 },
];
const permissions = [
    { label: "Administrator", value: 1 },
    { label: "Human Resources", value: 2 },
    { label: "Position1", value: 3 },
];

const departments = ref([]);
const teams = ref([]);
const positions = ref([]);
const loadingMeta = ref(true);

/* ------- form ------- */
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
});

const errors = reactive({});
const submitting = ref(false);
const showCreateSuccess = ref(false);

/* ------- โหลด meta จาก backend ------- */
onMounted(async () => {
    try {
        const { data } = await axios.get("/meta");

        // Department = แค่ชื่อกับ id พอ
        departments.value = (data.departments || []).map((d) => ({
            label: d.dpm_name,
            value: d.id,
        }));

        // Team = ต้องมี department_id ไว้ filter
        teams.value = (data.teams || []).map((t) => ({
            label: t.tm_name,
            value: t.id,
            department_id: t.tm_department_id ?? null,
        }));

        // Position = ต้องมี team_id ไว้ filter
        positions.value = (data.positions || []).map((p) => ({
            label: p.pst_name,
            value: p.id,
            team_id: p.pst_team_id ?? null,
        }));
    } catch (e) {
        // Error handling แบบเดิม
        console.error(e);
    } finally {
        loadingMeta.value = false;
    }
});

const teamOptions = computed(() => {
    if (!form.department) return [];
    const depId = Number(form.department);
    return teams.value.filter((t) => t.department_id === depId);
});

const positionOptions = computed(() => {
    if (!form.team) return [];
    const teamId = Number(form.team);
    return positions.value.filter((p) => p.team_id === teamId);
});

/* ====== Validation logic (คงเดิม) ====== */
const MSG = {
    requiredSelect: "Required Select",
    requiredText: "Required field only text",
    requiredNumber: "Required field only number",
    requiredEmail: "Required email, should have @ and .",
    requiredField: "Required field",
};
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
    employeeId: ["requiredField"],
};

function validateField(key, value) {
    const rules = fieldRules[key] || [];
    for (const r of rules) {
        if (r === "requiredSelect") {
            if (!value) return MSG.requiredSelect;
        } else if (r === "requiredText") {
            if (!value) return MSG.requiredText;
            // Regex เดิมของคุณ
            const re = /^[A-Za-zก-๙ .'-]+$/u;
            if (!re.test(value)) return MSG.requiredText;
        } else if (r === "requiredNumber") {
            if (!value) return MSG.requiredNumber;
            if (!/^\d{10}$/.test(value)) return MSG.requiredNumber;
        } else if (r === "requiredEmail") {
            if (!value) return MSG.requiredEmail;
            if (!(value.includes("@") && value.includes(".")))
                return MSG.requiredEmail;
        } else if (r === "requiredField") {
            if (!value) return MSG.requiredField;
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
Object.keys(fieldRules).forEach((k) => {
    watch(
        () => form[k],
        (v) => {
            if (errors[k]) {
                const msg = validateField(k, v);
                if (msg) {
                    errors[k] = msg;
                } else {
                    delete errors[k];
                }
            }
        }
    );
});

/* ------- submit ------- */
watch(
    () => form.department,
    () => {
        form.team = "";
        form.position = "";
        delete errors.team;
        delete errors.position;
    }
);

watch(
    () => form.team,
    () => {
        form.position = "";
        delete errors.position;
    }
);

/* ------- submit -> บันทึกลง DB ------- */
async function handleSubmit() {
    showCreateSuccess.value = false;
    if (!validate()) return;
    submitting.value = true;
    try {
        const payload = {
            emp_id: (form.employeeId || "").trim(),
            emp_prefix: Number(form.prefix),
            emp_nickname: form.nickname || null,
            emp_firstname: form.firstName,
            emp_lastname: form.lastName,
            emp_email: form.email,
            emp_phone: String(form.phone || ""),
            emp_position_id: Number(form.position),
            emp_department_id: Number(form.department),
            emp_team_id: Number(form.team),
            emp_password: form.password,
            emp_status: Number(form.permission),
        };

        await axios.post("/save-employee", payload);

        Object.keys(form).forEach((k) => (form[k] = ""));
        Object.keys(errors).forEach((k) => delete errors[k]);

        showCreateSuccess.value = true;
    } catch (err) {
        showCreateSuccess.value = false;
        if (err.response?.status === 422) {
            const e = err.response.data.errors || {};
            // Map errors...
            if (e.emp_id) errors.employeeId = e.emp_id[0];
            if (e.emp_email) errors.email = e.emp_email[0];
            if (e.emp_phone) errors.phone = e.emp_phone[0];

            const msg = Object.values(e).flat().join("<br>");
            await Swal.fire({
                icon: "warning",
                title: "Invalid data",
                html: msg || "Please check your input.",
                confirmButtonText: "OK",
                buttonsStyling: false,
                customClass: {
                    popup: "rounded-2xl",
                    confirmButton:
                        "rounded-full px-5 py-2.5 bg-rose-600 text-white font-semibold hover:bg-rose-700",
                },
            });
        } else {
            const msg = err.response?.data?.message || "Server error";
            await Swal.fire({
                icon: "error",
                title: "Create failed",
                text: msg,
                confirmButtonText: "OK",
                buttonsStyling: false,
                customClass: {
                    popup: "rounded-2xl",
                    confirmButton:
                        "rounded-full px-5 py-2.5 bg-gray-800 text-white font-semibold hover:bg-gray-900",
                },
            });
        }
    } finally {
        submitting.value = false;
    }
}

function onCancel() {
    Object.keys(form).forEach((k) => (form[k] = ""));
    Object.keys(errors).forEach((k) => delete errors[k]);
    router.push("/employee");
}

function handleSuccessClose() {
    showCreateSuccess.value = false;
    router.push("/employee");
}
</script>

<style></style>
