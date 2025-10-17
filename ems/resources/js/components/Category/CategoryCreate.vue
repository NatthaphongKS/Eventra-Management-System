<template>
  <div v-if="open" class="fixed inset-0 z-20 grid place-items-center bg-black/40 p-4" @click.self="close">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <h3 class="text-xl font-semibold text-gray-900">Create Category</h3>

      <div class="mt-5">
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Type name <span class="text-red-600">*</span>
        </label>
        <input
          v-model.trim="name"
          type="text"
          placeholder="Ex. สัมมนา"
          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-200"
          @keyup.enter="submit"
        />
        <p class="mt-2 text-xs text-gray-500">
          ระบบจะบันทึก Created by เป็นชื่อผู้ใช้:
          <span class="font-medium text-gray-700">{{ userName || '-' }}</span>
        </p>
        <p v-if="showDup" class="mt-2 text-xs text-red-600">
          มีชื่อนี้อยู่แล้วในรายการ
        </p>
      </div>

      <div class="mt-6 flex justify-between">
        <button
          @click="close"
          class="inline-flex items-center gap-2 rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700"
        >
          ✕ Cancel
        </button>
        <button
          :disabled="!name"
          @click="submit"
          class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          + Create
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";

const props = defineProps<{
  open: boolean;
  userName?: string;
  /** ให้หน้าแม่ส่งค่าตรวจซ้ำมา (หรือจะส่ง function ก็ได้) */
  duplicate?: boolean;
}>();

const emit = defineEmits<{
  (e:"update:open", v:boolean): void;
  (e:"submit", name: string): void;
}>();

const name = ref("");

watch(() => props.open, (v) => { if (v) name.value = ""; });

const showDup = computed(() => !!props.duplicate && !!name.value);

function close(){ emit("update:open", false); }
function submit(){
  if (!name.value.trim()) return;
  emit("submit", name.value.trim());
}
</script>
