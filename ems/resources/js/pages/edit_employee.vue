<template>
  <div class="min-screen">
    <header class="max-w-6xl mx-auto px-6 pt-6"></header>

    <section class="px-6 py-8">
      <div class=" max-w-1xl mx-auto rounded-2xl ">
        <div class="px-6 py-5">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">
              Edit New Employee
            </h2>
          </div>
        </div>

        <!-- Loading / Error -->
        <div v-if="loading" class="py-10 text-center text-gray-500">
          Loading…
        </div>
        <div v-else-if="loadError" class="py-10 text-center text-rose-600">
          {{ loadError }}
        </div>

        <!-- Form -->
        <form v-else class="px-6 pb-6" @submit.prevent="save">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <!-- คำนำหน้า -->
            <FormField label="Prefix" required>
              <DropdownPill
                v-model="form.emp_prefix"
                :options="prefixes"
                placeholder="Select prefix"
              />
            </FormField>

            <!-- แผนก -->
            <FormField label="Department" required>
              <DropdownPill
                v-model="form.emp_department_id"
                :options="departments"
                placeholder="Select department"
              />
            </FormField>

            <!-- ชื่อจริง -->
            <FormField label="First Name" required>
              <InputPill
                v-model="form.emp_firstname"
                placeholder="Ex.John"
              />
            </FormField>

            <!-- ทีม -->
            <FormField label="Team" required>
              <DropdownPill
                v-model="form.emp_team_id"
                :options="teams"
                placeholder="Select team"
              />
            </FormField>

            <!-- นามสกุล -->
            <FormField label="Last Name" required>
              <InputPill
                v-model="form.emp_lastname"
                placeholder="Ex.Doe"
              />
            </FormField>

            <!-- ตำแหน่ง -->
            <FormField label="Position" required>
              <DropdownPill
                v-model="form.emp_position_id"
                :options="positions"
                placeholder="Select position"
              />
            </FormField>

            <!-- ชื่อเล่น -->
            <FormField label="Nickname">
              <InputPill
                v-model="form.emp_nickname"
                placeholder="Ex.Beam"
              />
            </FormField>

            <!-- อีเมล -->
            <FormField label="Email" required>
              <InputPill
                v-model="form.emp_email"
                type="email"
                placeholder="Ex.user@mail.com"
              />
            </FormField>

            <!-- เบอร์โทร -->
            <FormField label="Phone" required>
              <InputPill
                v-model="form.emp_phone"
                inputmode="numeric"
                placeholder="098xxxxxxx"
              />
            </FormField>

            <!-- รหัสพนักงาน -->
            <FormField label="Employee ID" required>
              <InputPill
                v-model="form.emp_id"
                placeholder="Ex.CN707008"
              />
            </FormField>

            <!-- สิทธิ์การใช้งาน -->
            <FormField label="Permission" required>
              <DropdownPill
                v-model="form.emp_permission"
                :options="permissions"
                placeholder="Select permission"
              />
            </FormField>

            <!-- รหัสผ่าน -->
            <FormField label="Password">
              <InputPill
                v-model="form.password"
                type="password"
                placeholder="*******"
              />
            </FormField>
          </div>

          <!-- ปุ่ม -->
          <div class="flex items-center justify-between pt-8">
            <button
              type="button"
              @click="$router.back()"
              class="inline-flex items-center gap-2 rounded-full min-w-[120px] justify-center py-2.5 text-sm font-semibold text-white bg-red-700 border border-transparent hover:bg-red-800 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-300"
            >
              ✕ Cancel
            </button>

            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center gap-2 rounded-full bg-green-600 min-w-[120px] justify-center py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
            >
              {{ saving ? "Saving…" : "Confirm" }}
            </button>
          </div>

          <!-- ข้อความแจ้ง -->
          <p v-if="saveError" class="text-rose-600 text-sm pt-2">
            {{ saveError }}
          </p>
          <p v-if="saveDone" class="text-emerald-600 text-sm pt-2">
            Updated successfully.
          </p>
          <p v-if="noChange" class="text-amber-600 text-sm pt-2">
            Please make some changes.
          </p>
        </form>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import FormField from "../components/FormField.vue";
import InputPill from "../components/InputPill.vue";
import DropdownPill from "../components/DropdownPill.vue";

const props = defineProps({ id: { type: String, required: true } });

const loading = ref(true);
const loadError = ref("");
const saving = ref(false);
const saveError = ref("");
const saveDone = ref(false);
const noChange = ref(false); // กรณีไม่มีการเปลี่ยนแปลง

const prefixes = [
  { label: "นาย", value: "นาย" },
  { label: "นาง", value: "นาง" },
  { label: "นางสาว", value: "นางสาว" },
];
const permissions = [
  { label: "User", value: "user" },
  { label: "Admin", value: "admin" },
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
  password: "*******", // default
});

// เก็บข้อมูลเดิมไว้เปรียบเทียบ
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

    form.value.password = "*******"; // ไม่ให้แสดงรหัสจริง

    // clone เก็บ original ไว้
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

    // ถ้าไม่มีการเปลี่ยนแปลง
    if (JSON.stringify(current) === JSON.stringify(compareOriginal)) {
      noChange.value = true;
      return;
    }

    await axios.put(`/employees/${props.id}`, current);
    saveDone.value = true;

    // อัพเดต original ใหม่หลัง save
    original = JSON.parse(JSON.stringify(form.value));
  } catch (err) {
    console.error(err);
    saveError.value = "Failed to save.";
  } finally {
    saving.value = false;
  }
}
</script>
