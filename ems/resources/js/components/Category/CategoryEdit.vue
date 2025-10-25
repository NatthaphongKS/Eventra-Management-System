<!-- resources/js/components/CategoryEdit.vue -->
<template>
  <div v-if="open" class="fixed inset-0 z-[70] flex items-center justify-center">
    <!-- Dim background -->
    <div class="absolute inset-0 bg-black/50" @click.self="close"></div>

    <!-- Modal -->
    <div class="relative w-[762px] h-[412px] rounded-[20px] bg-white p-12 text-left shadow-2xl">
      <p class="text-3xl font-semibold text-neutral-800 text-left">Edit Category</p>
        <div class="mt-24 space-y-4">
          <div class="text-left">
            <label class="mb-2 block text-2xl font-semibold text-neutral-800">
              Type name <span class="text-red-600">*</span>
            </label>
            <input
              v-model.trim="name"
              type="text"
              placeholder="Ex. ประชุม"
              class="w-[653px] h-[58px] rounded-2xl border border-neutral-200 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-200"
              @keyup.enter="submit"
            />
            <p v-if="showDup" class="mt-2 text-xs text-red-600">มีชื่อนี้อยู่แล้วในรายการ</p>
          </div>
      </div>
      <!-- Actions -->
      <div class="mt-7 flex items-center justify-between">
        <CancelButton 
        @click="close"
        :disabled="saving"
        
        />

        <CreateButton
        @click="submit"
        :disabled="!name || showDup || saving"
      
         />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import CancelButton from '../../components/CancelButton.vue'
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
const saving = ref(false);

watch(
  () => props.open,
  (v) => {
    if (v && props.category) {
      name.value = props.category.name ?? "";
      saving.value = false;
    }
  },
  { immediate: true }
);

const showDup = computed(() => {
  if (!name.value || !props.isDuplicate) return false;
  return props.isDuplicate(name.value, props.category?.id);
});

function close() {
  if (saving.value) return;
  emit("update:open", false);
}

function submit() {
  if (!props.category) return;
  const trimmed = name.value.trim();
  if (!trimmed) return;
  if (props.isDuplicate?.(trimmed, props.category.id)) return;

  saving.value = true;
  emit("submit", { id: props.category.id, name: trimmed });
  saving.value = false;
}
</script>
