<template>
    <div>
        <header class="mx-auto max-w-[960px] px-4 pt-6">
            <link
                rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
            />
        </header>

        <!-- Body -->
        <div class="px-2 py-0">
            <!-- Header -->
            <div class="px-0 md:px-0 pt-1 pb-7">
                <div class="flex items-center justify-between gap-3">
                    <div class="translate-x-0 md:translate-x-20">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Edit Employee
                        </h2>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-[1080px] mx-auto px-4 md:px-8">
                <form @submit.prevent="save">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10 gap-y-5 justify-between"
                    >
                        <!-- ซ้าย -->
                        <div class="flex flex-col gap-5">
                            <FormField label="Prefix" required class="w-full">
                                <DropdownPill
                                    v-model="form.emp_prefix"
                                    :options="prefixes"
                                    placeholder="Select prefix"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="First Name" required>
                                <InputPill
                                    v-model="form.emp_firstname"
                                    placeholder="Ex. John"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Last Name" required>
                                <InputPill
                                    v-model="form.emp_lastname"
                                    placeholder="Ex. Doe"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Nickname">
                                <InputPill
                                    v-model="form.emp_nickname"
                                    placeholder="Ex. Beam"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Phone" required>
                                <InputPill
                                    v-model="form.emp_phone"
                                    inputmode="numeric"
                                    placeholder="098xxxxxxx"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Employee ID" required>
                                <InputPill
                                    v-model="form.emp_id"
                                    placeholder="Ex. CN707008"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <!-- ปุ่ม Cancel -->
                            <div class="pt-2">
                                <button
                                    type="button"
                                    @click="$router.back()"
                                    class="inline-flex items-center gap-2 rounded-full min-w-[120px] justify-center py-2.5 text-sm font-semibold text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300"
                                >
                                    ✕ Cancel
                                </button>
                            </div>
                        </div>

                        <!-- ขวา -->
                        <div class="flex flex-col gap-5">
                            <FormField label="Department" required>
                                <DropdownPill
                                    v-model="form.emp_department_id"
                                    :options="departments"
                                    placeholder="Select department"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Team" required>
                                <DropdownPill
                                    v-model="form.emp_team_id"
                                    :options="teams"
                                    placeholder="Select team"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Position" required>
                                <DropdownPill
                                    v-model="form.emp_position_id"
                                    :options="positions"
                                    placeholder="Select position"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Email" required>
                                <InputPill
                                    v-model="form.emp_email"
                                    type="email"
                                    placeholder="Ex.user@mail.com"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Password">
                                <InputPill
                                    v-model="form.password"
                                    type="password"
                                    placeholder="*******"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <FormField label="Permission" required>
                                <DropdownPill
                                    v-model="form.emp_permission"
                                    :options="permissions"
                                    placeholder="Select permission"
                                    class="h-11 w-full"
                                />
                            </FormField>

                            <!-- ปุ่ม Confirm -->
                            <div class="pt-2 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-2 rounded-full bg-green-600 min-w-[120px] justify-center py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                                >
                                    {{ saving ? "Saving…" : "Confirm" }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- ✅ Alert -->
                <p v-if="saveError" class="text-rose-600 text-sm pt-4">
                    {{ saveError }}
                </p>
                <p v-if="saveDone" class="text-emerald-600 text-sm pt-4">
                    Updated successfully.
                </p>
                <p v-if="noChange" class="text-amber-600 text-sm pt-4">
                    Please make some changes.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import FormField from "../../components/Input/FormField.vue";
import InputPill from "../../components/Input/InputPill.vue";
import DropdownPill from "../../components/Input/DropdownPill.vue";

const props = defineProps({ id: { type: String, required: true } });

const loading = ref(true);
const loadError = ref("");
const saving = ref(false);
const saveError = ref("");
const saveDone = ref(false);
const noChange = ref(false);

const prefixes = [
    { label: "นาย", value: "นาย" },
    { label: "นาง", value: "นาง" },
    { label: "นางสาว", value: "นางสาว" },
];
const permissions = [
  { label: "Administrator", value: "enabled" },
  { label: "Human Resources", value: "disabled" },
  { label: "Position1", value: "disabled" },
];

const departments = ref([]);
const teams = ref([]);
const positions = ref([]);

const form = ref({
    emp_id: "",
    emp_prefix: "",
    emp_firstname: "",
    emp_lastname: "",
    emp_nickname: "",
    emp_email: "",
    emp_phone: "",
    emp_position_id: "",
    emp_department_id: "",
    emp_team_id: "",
    emp_permission: "",
    password: "*******",
});

let original = {};

onMounted(async () => {
    try {
        const meta = await axios.get("/employees-meta");
        departments.value = (meta.data.departments || []).map((d) => ({
            label: d.dpm_name,
            value: d.id,
        }));
        teams.value = (meta.data.teams || []).map((t) => ({
            label: t.tm_name,
            value: t.id,
        }));
        positions.value = (meta.data.positions || []).map((p) => ({
            label: p.pst_name,
            value: p.id,
        }));

        const res = await axios.get(`/employees/${props.id}`);
        Object.assign(form.value, res.data.data || res.data);

        form.value.password = "*******";
        original = JSON.parse(JSON.stringify(form.value));
    } catch (err) {
        console.error(err);
        loadError.value = "Failed to load employee.";
    } finally {
        loading.value = false;
    }
});

async function save() {
    saving.value = true;
    saveError.value = "";
    saveDone.value = false;
    noChange.value = false;

    try {
        const current = { ...form.value };
        if (current.password === "*******") {
            delete current.password;
        }

        const compareOriginal = { ...original };
        if (compareOriginal.password === "*******") {
            delete compareOriginal.password;
        }

        if (JSON.stringify(current) === JSON.stringify(compareOriginal)) {
            noChange.value = true;
            return;
        }

        await axios.put(`/employees/${props.id}`, current);
        saveDone.value = true;
        original = JSON.parse(JSON.stringify(form.value));
    } catch (err) {
        console.error(err);
        saveError.value = "Failed to save.";
    } finally {
        saving.value = false;
    }
}
</script>
