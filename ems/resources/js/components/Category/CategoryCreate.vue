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
            :aria-invalid="invalid"
            class="w-[653px] h-[58px] rounded-2xl border px-4 py-3 text-xl font-semibold outline-none
                   placeholder-red-300 focus:ring-2 focus:ring-red-200"
            :class="invalid ? 'border-red-500 focus:border-red-700' : 'border-neutral-200 focus:border-red-400'"
            @keyup.enter="submit"
            autofocus
          />

          <!-- ข้อความเตือน: โชว์ทันทีถ้าไม่กรอก -->
          <p v-if="isEmpty" class="mt-1 text-sm text-red-700">Required field</p>
          <p v-else-if="showDup" class="mt-1 text-sm text-red-700">This name is already in use!</p>
        </div>
      </div>

      <div class="mt-6 flex justify-between">
        <div><Button variant="cancel"
          @click="close" 
          class="inline-flex items-center gap-2">Cancel</Button>
        </div>
        <div>
        <Button variant="create"
          @click="submit"
          :disabled="invalid"
          class="inline-flex items-center gap-2"
        >
          Create
        </Button></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Button from "../Button.vue";

const props = defineProps<{
  open: boolean;
  userName?: string;
  /** ฟังก์ชันตรวจชื่อซ้ำ (รับชื่อที่ trim แล้ว) */
  duplicate?: (name: string) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { name: string }): void;
}>();

const name = ref("");

watch(() => props.open, (v) => {
  if (v) {
    name.value = ""; // เปิดใหม่ รีเซ็ตฟิลด์
  }
});

const trimmed = computed(() => name.value.trim());
const isEmpty = computed(() => trimmed.value.length === 0);
const showDup = computed(() => {
  if (isEmpty.value) return false;        // ว่างให้ขึ้น Required แทน
  return props.duplicate ? props.duplicate(trimmed.value) : false;
});
const invalid = computed(() => isEmpty.value || showDup.value);

function close() {
  emit("update:open", false);
}

function submit() {
  if (invalid.value) return;              // กันกดซ้ำ
  emit("submit", { name: trimmed.value });
}
</script>
