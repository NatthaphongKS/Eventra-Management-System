<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center absolute inset-0 bg-black/50">
    <div class="w-[640px] h-[360px] p-8 rounded-2xl bg-white shadow-xl">
      <div class="text-3xl font-semibold text-neutral-800">Create Category</div>

      <div class="mt-14">
        <label class="mb-2 block text-2xl font-semibold text-neutral-800">
          Type name <span class="text-red-700">*</span>
        </label>
        <input
          v-model.trim="name"
          type="text"
          placeholder="Ex. สัมมนา"
          class="w-full rounded-[20px] border border-neutral-200 px-4 py-3 text-xl focus:border-red-400 focus:ring-1 focus:ring-red-200"
          @keyup.enter="submit"
        />
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
