<!-- /**
 * ชื่อไฟล์: EventCheckIn.vue
 * คำอธิบาย: หน้าสำหรับเช็คชื่อผู้เข้าร่วมกิจกรรม
 * Input: ข้อมูลพนักงานและการเช็คชื่อจาก API /getEmployeeForCheckin/eveId/{eveId}
 * Output: หน้าจอสำหรับการเช็คชื่อ และพร้อมสำหรับการเช็คชื่อผู้เข้าร่วม
 * คนแก้ไข: Natthaphong Kongsinl
 * วันที่แก้ไข: 2026-02-27
 */ -->
<!-- resources/js/components/Position/PositionCreate.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-auto p-12 rounded-[20px] bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Create Position</div>

      <div class="mt-20 space-y-4">
        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Department <span class="text-red-700">*</span>
          </label>

          <select
            v-model="departmentId"
            @change="teamId = ''"
            class="w-[653px] h-[58px] rounded-2xl border border-neutral-200 px-4 py-3 text-xl font-semibold outline-none focus:border-red-400 focus:ring-2 focus:ring-red-300"
          >
            <option value="">-- Select Department --</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.dpm_name }}
            </option>
          </select>

          <p v-if="submitted && !departmentId" class="mt-1 text-sm text-red-700">
            Please select a department
          </p>
        </div>

        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Team <span class="text-red-700">*</span>
          </label>

          <select
            v-model="teamId"
            class="w-[653px] h-[58px] rounded-2xl border border-neutral-200 px-4 py-3 text-xl font-semibold outline-none focus:border-red-400 focus:ring-2 focus:ring-red-300"
            :disabled="!departmentId"
          >
            <option value="">-- Select Team --</option>
            <option v-for="team in filteredTeams" :key="team.id" :value="team.id">
              {{ team.tm_name }}
            </option>
          </select>

          <p v-if="submitted && !teamId" class="mt-1 text-sm text-red-700">
            Please select a team
          </p>
        </div>

        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Position Name <span class="text-red-700">*</span>
          </label>

          <input
            v-model="name"
            type="text"
            placeholder="Ex. Senior Developer"
            :aria-invalid="submitted && invalid"
            class="w-[653px] h-[58px] rounded-2xl border px-4 py-3 text-xl font-semibold outline-none
                   placeholder-red-300 focus:ring-2 focus:ring-red-200"
            :class="(submitted && invalid)
              ? 'border-red-500 focus:border-red-700'
              : 'border-neutral-200 focus:border-red-400'"
            @keydown.enter.prevent="submit"
            autofocus
          />

          <div class="mt-1 flex items-center justify-between">
            <p v-if="submitted && isEmpty" class="text-sm text-red-700">Required field</p>

            <p v-else-if="submitted && tooLong" class="text-sm text-red-700">
              Character limit exceeded (maximum 255 characters).
            </p>

            <p v-else-if="submitted && showDup" class="text-sm text-red-700">
              This position name already exists in the selected team!
            </p>
          </div>
        </div>

      </div>

      <div class="mt-6 flex justify-between">
        <div>
          <CancelButton @click="close" />
        </div>
        <div>
          <CreateButton @click="submit" >
          </CreateButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import CancelButton from "../Button/CancelButton.vue";
import CreateButton from "../Button/CreateButton.vue";

const props = defineProps<{
  open: boolean;
  duplicate?: (name: string, teamId: number) => boolean;
  departments?: Array<{ id: number; dpm_name: string }>;
  teams?: Array<{ id: number; tm_name: string; tm_department_id: number; department_name?: string }>;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { name: string; teamId: number; status: string }): void;
}>();

const MAX_LEN = 255;

const name = ref("");
const status = ref("active");
const departmentId = ref("");
const teamId = ref("");
const submitted = ref(false);

watch(() => props.open, (v) => {
  if (v) {
    name.value = "";
    status.value = "active";
    departmentId.value = "";
    teamId.value = "";
    submitted.value = false;
  }
});

watch(name, () => {
  if (submitted.value) submitted.value = false;
});

const filteredTeams = computed(() => {
  if (!departmentId.value) return [];
  return (props.teams || []).filter(t => String(t.tm_department_id) === String(departmentId.value));
});

const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);

const charCount = computed(() => Array.from(trimmed.value).length);
const tooLong = computed(() => charCount.value > MAX_LEN);

const showDup = computed(() => {
  if (!props.duplicate || !trimmed.value || !teamId.value) return false;
  return props.duplicate(trimmed.value, Number(teamId.value));
});

const invalid = computed(() => isEmpty.value || tooLong.value || showDup.value);

function close() {
  emit("update:open", false);
}

function submit() {
  submitted.value = true;
  if (invalid.value || !departmentId.value || !teamId.value) return;

  emit("submit", {
    name: trimmed.value,
    teamId: Number(teamId.value),
    status: status.value,
  });
}
</script>
