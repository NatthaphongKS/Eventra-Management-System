<!-- resources/js/components/CategoryEdit.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-[412px] rounded-[20px] bg-white p-12 text-left shadow-2xl">
      <p class="text-3xl font-semibold text-neutral-800 text-left">Edit Category</p>

      <div class="mt-20 space-y-4">
        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Type name <span class="text-red-700">*</span>
          </label>

          <input
            v-model.trim="name"
            type="text"
            placeholder="Ex. สัมนา"
            :aria-invalid="submitted && invalid"
            class="w-[653px] h-[58px] rounded-2xl border px-4 py-3 text-xl font-semibold outline-none
                   placeholder-red-300 focus:ring-2 focus:ring-red-200"
            :class="(submitted && invalid) ? 'border-red-500' : 'border-neutral-200'"
            @keydown.enter.prevent="submit"
          />

          <!-- ✅ โชว์หลัง “กด Edit/Enter” เท่านั้น -->
          <p v-if="submitted && isEmpty" class="mt-2 text-sm text-red-700">Required field</p>
          <p v-else-if="submitted && unchanged" class="mt-2 text-sm text-red-700">The name hasn’t changed</p>
          <p v-else-if="submitted && showDup" class="mt-2 text-sm text-red-700">This name is already use!</p>
        </div>
      </div>

      <div class="mt-6 flex justify-between">
        <div>
          <CancelButton
            @click="close"
            :disabled="saving"
            class="inline-flex items-center gap-2"
          />
        </div>
        <div>
          <!-- ✅ ไม่ disable ตาม invalid เพื่อให้กดแล้วค่อยโชว์ validate -->
          <CreateButton
            @click="submit"
            :disabled="saving"
            class="inline-flex items-center gap-2"
          >
            Edit
          </CreateButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import CancelButton from "../../components/CancelButton.vue";
import CreateButton from "../CreateButton.vue";

type Row = {
  id: number;
  name: string;
  createdBy?: string;
  createdAt?: string | null;
};

const props = defineProps<{
  open: boolean;
  category?: Row | null;
  isDuplicate?: (name: string, currentId?: number) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { id: number; name: string }): void;
}>();

const name = ref("");
const originalName = ref("");
const saving = ref(false);
const submitted = ref(false);

function initFromCategory() {
  const n = props.category?.name ?? "";
  name.value = n;
  originalName.value = n;
  saving.value = false;
  submitted.value = false;
}

watch(
  () => props.open,
  (v) => {
    if (v) initFromCategory();
    else {
      saving.value = false;
      submitted.value = false;
    }
  },
  { immediate: true }
);

// กันกรณีเปลี่ยน category ตอน modal เปิด
watch(
  () => props.category,
  () => {
    if (props.open) initFromCategory();
  }
);

// ✅ พอผู้ใช้พิมพ์หลังจากเคยกด submit แล้ว ให้ซ่อน validate ระหว่างพิมพ์
watch(name, () => {
  if (submitted.value) submitted.value = false;
});

/* validations */
const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);
const unchanged = computed(() => trimmed.value === (originalName.value ?? "").trim());

const showDup = computed(() => {
  if (!trimmed.value || !props.isDuplicate || !props.category) return false;
  return props.isDuplicate(trimmed.value, props.category.id);
});

const invalid = computed(() => isEmpty.value || unchanged.value || showDup.value);

function close() {
  if (saving.value) return;
  emit("update:open", false);
  submitted.value = false;
}

function submit() {
  if (!props.category) return;

  submitted.value = true;   // ✅ เริ่ม validate ตอนกดปุ่ม/Enter
  if (invalid.value) return;

  saving.value = true;
  emit("submit", { id: props.category.id, name: trimmed.value });
  // ปกติ parent จะปิด modal หลัง update สำเร็จเอง
}
</script>
