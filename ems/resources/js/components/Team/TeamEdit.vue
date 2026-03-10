<!-- /**
 * ชื่อไฟล์: TeamEdit.vue
 * คำอธิบาย: Pop up สำหรับแก้ไขทีม
 * Input: Team Department
 * Output: Update team
 * ชื่อผู้เขียน/แก้ไข: Natthaphong Kongsinl
 * วันที่จัดทำ/แก้ไข: 2026-03-10
 */ -->
<!-- resources/js/components/Team/TeamEdit.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-auto p-12 rounded-[20px] bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Edit Team</div>

      <div class="mt-20 space-y-4">
        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Department <span class="text-red-700">*</span>
          </label>

          <select
            v-model="departmentId"
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
            Team Name <span class="text-red-700">*</span>
          </label>

          <input
            v-model="name"
            type="text"
            placeholder="Ex. Sales Team"
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
              This name is already in use!
            </p>
          </div>
        </div>


      </div>

      <div class="mt-6 flex justify-between">
        <div>
          <CancelButton @click="close"  />
        </div>
        <div>
          <CreateButton @click="submit" >
            Update
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
  team: {
    id: number;
    tm_name: string;
    tm_department_id: number;
    tm_delete_status: string;
  } | null;
  isDuplicate?: (name: string, currentId: number) => boolean;
  departments?: Array<{ id: number; dpm_name: string }>;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { id: number; name: string; departmentId: number; status: string }): void;
}>();

const MAX_LEN = 255;

const name = ref("");
const status = ref("active");
const departmentId = ref("");
const submitted = ref(false);
const teamId = ref(null);

watch(() => props.open, (v) => {
  if (v && props.team) {
    teamId.value = props.team.id;
    name.value = props.team.tm_name || "";
    status.value = props.team.tm_delete_status || "active";
    departmentId.value = String(props.team.tm_department_id || "");
    submitted.value = false;
  }
});

watch(name, () => {
  if (submitted.value) submitted.value = false;
});

const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);

const charCount = computed(() => Array.from(trimmed.value).length);
const tooLong = computed(() => charCount.value > MAX_LEN);

const showDup = computed(() => {
  if (!props.isDuplicate || !trimmed.value || !teamId.value) return false;
  return props.isDuplicate(trimmed.value, teamId.value);
});

const invalid = computed(() => isEmpty.value || tooLong.value || showDup.value);

function close() {
  emit("update:open", false);
}

function submit() {
  submitted.value = true;
  if (invalid.value || !departmentId.value) return;

  emit("submit", {
    id: teamId.value,
    name: trimmed.value,
    departmentId: Number(departmentId.value),
    status: status.value,
  });
}
</script>
