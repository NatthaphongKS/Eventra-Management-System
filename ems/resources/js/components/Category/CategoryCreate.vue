<!-- resources/js/components/CategoryCreate.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-[412px] p-12 rounded-[20px] bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Create Category</div>

      <div class="mt-20 space-y-4">
        <div class="text-left">
          <label class="mb-2 block text-2xl font-semibold text-neutral-800">
            Type name <span class="text-red-700">*</span>
          </label>

          <input
            v-model.trim="name"
            type="text"
            placeholder="Ex. สัมมนา"
            :aria-invalid="submitted && invalid"
            class="w-[653px] h-[58px] rounded-2xl border px-4 py-3 text-xl font-semibold outline-none
                   placeholder-red-300 focus:ring-2 focus:ring-red-200"
            :class="(submitted && invalid)
              ? 'border-red-500 focus:border-red-700'
              : 'border-neutral-200 focus:border-red-400'"
            @keydown.enter.prevent="submit"
            autofocus
          />

          <!-- ✅ โชว์หลัง “กด Create/Enter” เท่านั้น -->
          <p v-if="submitted && isEmpty" class="mt-1 text-sm text-red-700">Required field</p>
          <p v-else-if="submitted && showDup" class="mt-1 text-sm text-red-700">
            This name is already in use!
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-between">
        <div>
          <CancelButton @click="close" class="inline-flex items-center gap-2" />
        </div>
        <div>
          <!-- ✅ ไม่ disable ตาม invalid เพื่อให้กดแล้วค่อยโชว์ validate -->
          <CreateButton
            @click="submit"
            class="inline-flex items-center gap-2"
          >
            Create
          </CreateButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import CancelButton from "../CancelButton.vue";
import CreateButton from "../CreateButton.vue";

const props = defineProps<{
  open: boolean;
  userName?: string;
  duplicate?: (name: string) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { name: string }): void;
}>();

const name = ref("");
const submitted = ref(false);

// เปิด modal ใหม่ให้รีเซ็ต
watch(() => props.open, (v) => {
  if (v) {
    name.value = "";
    submitted.value = false;
  }
});

// ✅ พอผู้ใช้พิมพ์หลังจากเคยกด submit แล้ว ให้ซ่อน validate ระหว่างพิมพ์
watch(name, () => {
  if (submitted.value) submitted.value = false;
});

const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);

const showDup = computed(() => {
  if (isEmpty.value) return false;
  return props.duplicate ? props.duplicate(trimmed.value) : false;
});

const invalid = computed(() => isEmpty.value || showDup.value);

function close() {
  emit("update:open", false);
  submitted.value = false;
}

function submit() {
  submitted.value = true;        // ✅ เริ่ม validate ตอนกดปุ่ม/Enter
  if (invalid.value) return;

  emit("submit", { name: trimmed.value });
  emit("update:open", false);

  name.value = "";
  submitted.value = false;
}
</script>
