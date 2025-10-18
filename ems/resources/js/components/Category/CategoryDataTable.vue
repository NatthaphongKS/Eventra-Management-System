<template>
  <!-- กล่องตาราง -->
  <div class="overflow-hidden rounded-2xl border border-gray-200/60 bg-white">
    <table class="min-w-full divide-y divide-gray-100">
      <!-- สัดส่วนคอลัมน์ให้ใกล้เคียงภาพ -->
      <colgroup>
        <col class="w-14" />         <!-- # -->
        <col class="w-[48%]" />      <!-- Category -->
        <col class="w-[20%]" />      <!-- Created by -->
        <col class="w-[20%]" />      <!-- Created date -->
        <!-- <col class="w-16" />         Actions -->
      </colgroup>

      <!-- หัวตาราง -->
      <thead class="bg-gray-50 text-gray-600">
        <tr class="text-left text-sm">
          <th class="px-6 py-3 font-semibold">#</th>
          <th class="px-6 py-3 font-semibold">Category</th>
          <th class="px-6 py-3 font-semibold text-center sm:text-left">Created by</th>
          <th class="px-6 py-3 font-semibold text-center sm:text-left">Created date (D/M/Y)</th>
          <th class="px-6 py-3 font-semibold text-center"></th>
        </tr>
      </thead>

      <!-- เนื้อหา -->
      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, idx) in pagedRows"
          :key="row.id"
          class="text-sm text-gray-800 hover:bg-gray-50"
        >
          <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>

          <td class="px-6 py-3">
            <div class="truncate-text" :title="row.name">{{ row.name }}</div>
          </td>

          <td class="px-6 py-3 text-center sm:text-left">
            {{ row.createdBy || '-' }}
          </td>

          <td class="px-6 py-3 text-center sm:text-left">
            {{ formatDate(row.createdAt) }}
          </td>

          <!-- Action (ขวาสุด) -->
          <td class="px-3 py-2">
            <div class="flex items-center justify-center gap-2">
              <!-- Edit -->
              <button
                class="grid h-8 w-8 place-items-center rounded-full text-gray-500 hover:bg-emerald-50 hover:text-emerald-600"
                @click="$emit('edit', row)"
                title="Edit"
                aria-label="edit"
              >
                <span class="material-symbols-outlined text-[20px]">edit</span>
              </button>

              <!-- Delete -->
              <button
                class="grid h-8 w-8 place-items-center rounded-full text-gray-500 hover:bg-red-50 hover:text-red-600"
                @click="$emit('delete', row.id)"
                title="Delete"
                aria-label="delete"
              >
                <span class="material-symbols-outlined text-[20px]">delete</span>
              </button>
            </div>
          </td>
        </tr>

        <!-- ว่าง -->
        <tr v-if="pagedRows.length === 0">
          <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
            ไม่พบข้อมูลที่ค้นหา
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <div class="mt-3 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2 text-sm text-gray-700">
      <span>แสดง</span>

      <!-- select สไตล์พิลขอบแดง -->
      <div class="relative">
        <select
          :value="pageSize"
          @change="$emit('update:pageSize', Number(($event.target as HTMLSelectElement).value))"
          class="appearance-none rounded-full border border-[#C91818]/70 bg-white px-3 pr-8 py-1.5 outline-none focus:ring-2 focus:ring-rose-200"
        >
          <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
        </select>
        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[#C91818]">▾</span>
      </div>

      <span>{{ visibleCountText }}</span>
    </div>

    <div class="flex items-center gap-2">
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === 1"
        @click="$emit('update:page', page - 1)"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === totalPages || totalPages === 0"
        @click="$emit('update:page', page + 1)"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

/** ===== Types ===== */
type Row = {
  id: number;
  name: string;
  createdBy: string;
  createdAt: string | null;
};

/** ===== Props / Emits ===== */
const props = defineProps<{
  rows: Row[];                // ข้อมูลที่ถูกกรอง/เรียงแล้วจากหน้าแม่
  page: number;
  pageSize: number;
  startIndex: number;         // ใช้ทำ running number
  formatDate: (iso?: string | null) => string;
}>();

defineEmits<{
  (e:"update:page", v:number): void;
  (e:"update:pageSize", v:number): void;
  (e:"delete", id:number): void;
  (e:"edit", row: Row): void;
}>();

/** ===== Derived ===== */
const total = computed(() => props.rows.length);
const totalPages = computed(() => Math.ceil(total.value / props.pageSize));
const endIndex = computed(() => Math.min(props.startIndex + props.pageSize, total.value));

const pagedRows = computed(() =>
  props.rows.slice(props.startIndex, props.startIndex + props.pageSize)
);

const visibleCountText = computed(() =>
  total.value === 0 ? "0 จาก 0 รายการ" : `${endIndex.value} จาก ${total.value} รายการ`
);
</script>

<style scoped>
.truncate-text{
  overflow:hidden;
  white-space:nowrap;
  text-overflow:ellipsis;
  max-width: 680px; /* ให้ฟีลเหมือนภาพ */
}
</style>
