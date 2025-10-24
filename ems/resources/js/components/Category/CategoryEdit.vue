<!-- resources/js/components/CategoryEdit.vue -->
<template>
  <div
    v-if="open"
    class="fixed inset-0 z-20 grid place-items-center bg-black/40 p-4"
    @click.self="close"
  >
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <h3 class="text-xl font-semibold text-gray-900">Edit Category</h3>

      <div class="mt-5 space-y-3">
        <div>
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
          <p v-if="showDup" class="mt-2 text-xs text-red-600">
            มีชื่อนี้อยู่แล้วในรายการ
          </p>
          <p v-if="unchanged" class="mt-2 text-xs text-gray-500">
            ชื่อไม่ได้เปลี่ยนจากเดิม
          </p>
        </div>

        <!-- แสดงข้อมูลประกอบ (อ่านอย่างเดียว) -->
         <!--
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="block text-gray-500">Created by</span>
            <span class="font-medium text-gray-800">{{ createdBy || "-" }}</span>
          </div>
          <div>
            <span class="block text-gray-500">Created date</span>
            <span class="font-medium text-gray-800">{{ formatDate(createdAt) }}</span>
          </div>
        </div>
        -->
      </div>


      <div class="mt-6 flex justify-between">
        <button
          @click="close"
          class="inline-flex items-center gap-2 rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700"
          :disabled="saving"
        >
          ✕ Cancel
        </button>

        <button
          @click="submit"
          class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="!name || showDup || saving"
        >
          <svg v-if="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <span>Save</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";

/**
 * Props:
 * - open: ควบคุมการเปิด/ปิด modal ผ่าน v-model:open
 * - category: ออบเจ็กต์แถวที่กำลังแก้ไข (ต้องมี id, name, createdBy, createdAt)
 * - isDuplicate: ฟังก์ชันตรวจชื่อซ้ำ (ยกเว้น id ปัจจุบัน) -> ควรส่งจากหน้าแม่
 * - formatDate: ฟังก์ชันฟอร์แมตวันที่ (ใช้ร่วมกับของหน้าแม่เพื่อ UI ตรงกัน)
 */
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
  formatDate?: (iso?: string | null) => string;
}>();

const emit = defineEmits<{
  (e: "update:open", v: boolean): void;
  (e: "submit", payload: { id: number; name: string }): void;
}>();

const name = ref("");
const originalName = ref("");
const createdBy = ref<string>("-");
const createdAt = ref<string | null>(null);
const saving = ref(false);

watch(
  () => props.open,
  (v) => {
    if (v && props.category) {
      name.value = props.category.name ?? "";
      originalName.value = props.category.name ?? "";
      createdBy.value = props.category.createdBy ?? "-";
      createdAt.value = props.category.createdAt ?? null;
      saving.value = false;
    }
  },
  { immediate: true }
);

const showDup = computed(() => {
  if (!name.value) return false;
  if (!props.isDuplicate) return false;
  return props.isDuplicate(name.value, props.category?.id);
});

const unchanged = computed(() => name.value === originalName.value);

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
  // ไม่เรียก API ที่นี่ — ปล่อยให้หน้าแม่ทำ (สอดคล้องกับ CategoryCreate.vue)
  emit("submit", { id: props.category.id, name: trimmed });
  // ปิดจะถูกจัดการหลังหน้าแม่อัปเดตเสร็จ (หรือจะปิดทันทีตรงนี้ก็ได้)
  // emit("update:open", false);
  saving.value = false;
}

// fallback ถ้าไม่ส่งมา
function defaultFormatDate(iso?: string | null) {
  if (!iso) return "-";
  const d = new Date(iso);
  if (isNaN(d.getTime())) return "-";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

const formatDate = (iso?: string | null) => (props.formatDate ? props.formatDate(iso) : defaultFormatDate(iso));
</script>
