<!-- resources/js/components/CategoryCreate.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <div class="relative w-[762px] h-[412px] p-12 rounded-[20px] bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Create Category</div>

      <div class="mt-24 space-y-4">
        <div class="text-left">
        <label class="mb-2 block text-2xl font-semibold text-neutral-800">
          Type name <span class="text-red-600">*</span>
        </label>

        <input
          v-model.trim="name"
          type="text"
          placeholder="Ex. สัมมนา"
          :aria-invalid="(tried && isEmpty) || showDup"
          class="w-[653px] h-[58px] rounded-2xl border border-neutral-200 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-200"
        />


        <!-- ข้อความเตือน -->
        <p v-if="showDup" class="mt-1 text-sm text-red-600">This name is already use!</p>
        <p v-else-if="tried && isEmpty" class="mt-1 text-sm text-red-600">Required field </p>
        </div>
      </div>

      <div class="mt-6 flex justify-between">
        <div>
        <CancelButton
        @click="close"
        class="inline-flex items-center gap-2"
        />
        </div>

        <div>
        <CreateButton 
        @click="submit"
        class="inline-flex items-center gap-2"
        />
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
  // ฟังก์ชันตรวจชื่อซ้ำ
  duplicate?: (name: string) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { name: string }): void;
}>();

const name = ref("");
const tried = ref(false);   // ✅ เคาะปุ่มแล้วแต่ยังไม่กรอก → ใช้ตัวนี้โชว์ error

watch(() => props.open, (v) => {
  if (v) {
    name.value = "";
    tried.value = false;
  }
});

const isEmpty = computed(() => name.value.trim().length === 0);

const showDup = computed(() => {
  const n = name.value.trim();
  if (!n) return false;
  return props.duplicate ? props.duplicate(n) : false;
});

function close() {
  emit("update:open", false);
}

function submit() {
  tried.value = true;                 // ✅ กดปุ่มแล้ว ค่อยเริ่มโชว์ error
  const n = name.value.trim();

  if (!n) return;                     // ว่าง → แสดง “กรุณากรอกชื่อหมวดหมู่”
  if (showDup.value) return;          // ซ้ำ → แสดง “มีชื่อนี้อยู่แล้ว...”
  emit("submit", { name: n });
}
</script>
