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
            v-model="name"
            type="text"
            placeholder="Ex. สัมมนา"
            :aria-invalid="submitted && invalid"
            class="w-[653px] h-[58px] rounded-2xl border px-4 py-3 text-xl font-semibold outline-none
                   placeholder-red-300 focus:ring-2 focus:ring-red-200"
            :class="(submitted && invalid) ? 'border-red-500' : 'border-neutral-200'"
            @keydown.enter.prevent="submit"
          />

          <div class="mt-2 flex items-center justify-between">
            <p v-if="submitted && isEmpty" class="text-sm text-red-700">Required field</p>

            <p v-else-if="submitted && tooLong" class="text-sm text-red-700">
              Character limit exceeded (maximum 255 characters).
            </p>

            <p v-else-if="submitted && unchanged" class="text-sm text-red-700">
              The name hasn’t changed
            </p>

            <p v-else-if="submitted && showDup" class="text-sm text-red-700">
              This name is already in use!
            </p>

            <!-- <p class="text-sm text-neutral-500 ml-auto">
              {{ charCount }}/255
            </p> -->
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-between">
        <div>
          <CancelButton @click="close" class="inline-flex items-center gap-2" />
        </div>
        <div>
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

const MAX_LEN = 255;

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

watch(
  () => props.category,
  () => {
    if (props.open) initFromCategory();
  }
);

// พอผู้ใช้พิมพ์หลังจากเคยกด submit แล้ว ให้ซ่อน validate ระหว่างพิมพ์
watch(name, () => {
  if (submitted.value) submitted.value = false;
});

const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);
const unchanged = computed(() => trimmed.value === (originalName.value ?? "").trim());

const charCount = computed(() => Array.from(trimmed.value).length);
const tooLong = computed(() => charCount.value > MAX_LEN);

const showDup = computed(() => {
  if (!trimmed.value || !props.isDuplicate || !props.category) return false;
  return props.isDuplicate(trimmed.value, props.category.id);
});

// ✅ บล็อก submit ถ้าเกิน 255
const invalid = computed(() => isEmpty.value || unchanged.value || showDup.value || tooLong.value);

function close() {
  emit("update:open", false);
  submitted.value = false;
  saving.value = false;
}

function submit() {
  if (!props.category) return;

  submitted.value = true;
  if (invalid.value) return;

  saving.value = true;
  emit("submit", { id: props.category.id, name: trimmed.value });
}
</script>
