<!-- /**
 * ชื่อไฟล์: DepartmentEdit.vue
 * คำอธิบาย: Pop up สำหรับแก้ไขแผนก
 * Input: Department
 * Output: Update department
 * ชื่อผู้เขียน/แก้ไข: Natthaphong Kongsinl
 * วันที่จัดทำ/แก้ไข: 2026-03-10
 */ -->
<!-- resources/js/components/Department/DepartmentEdit.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-auto p-12 rounded-[20px] bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Edit Department</div>

      <div class="mt-20 space-y-4">
        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Department Name <span class="text-red-700">*</span>
          </label>

          <input
            v-model="name"
            type="text"
            placeholder="Ex. Sales"
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
          <CancelButton @click="close" class="inline-flex items-center gap-2" />
        </div>
        <div>
          <CreateButton @click="submit" class="inline-flex items-center gap-2">
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
  department: {
    id: number;
    dpm_name: string;
    dpm_delete_status: string;
  } | null;
  isDuplicate?: (name: string, currentId: number) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { id: number; name: string; status: string }): void;
}>();

const MAX_LEN = 255;

const name = ref("");
const status = ref("active");
const submitted = ref(false);
const departmentId = ref(null);

watch(() => props.open, (v) => {
  if (v && props.department) {
    departmentId.value = props.department.id;
    name.value = props.department.dpm_name || "";
    status.value = props.department.dpm_delete_status || "active";
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
  if (!props.isDuplicate || !trimmed.value || !departmentId.value) return false;
  return props.isDuplicate(trimmed.value, departmentId.value);
});

const invalid = computed(() => isEmpty.value || tooLong.value || showDup.value);

function close() {
  emit("update:open", false);
}

function submit() {
  submitted.value = true;
  if (invalid.value) return;

  emit("submit", {
    id: departmentId.value,
    name: trimmed.value,
    status: status.value,
  });
}
</script>
