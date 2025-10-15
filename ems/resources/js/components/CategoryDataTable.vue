<template>
  <table class="min-w-full divide-y divide-gray-100">
    <colgroup>
      <col class="w-14" />
      <col class="w-[48%]" />
      <col class="w-[22%]" />
      <col class="w-[22%]" />
      <col class="w-16" />
    </colgroup>

    <thead class="bg-gray-50 text-gray-600">
      <tr class="text-left text-sm">
        <th class="w-14 px-6 py-3 font-semibold">#</th>
        <th class="px-6 py-3 font-semibold">Category</th>
        <th class="px-6 py-3 font-semibold">Created by</th>
        <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
        <th class="w-16 px-6 py-3"></th>
      </tr>
    </thead>

    <tbody class="divide-y divide-gray-100">
      <tr
        v-for="(row, idx) in pagedRows"
        :key="row.id"
        class="text-sm text-gray-700 hover:bg-gray-50"
      >
        <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>
        <td class="px-6 py-3 max-w-xs relative">
          <div class="truncate-text" :title="row.name">{{ row.name }}</div>
        </td>
        <td class="px-6 py-3">{{ row.createdBy }}</td>
        <td class="px-6 py-3">{{ formatDate(row.createdAt) }}</td>
        <td class="px-6 py-3">
          <button
            class="rounded-lg p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600"
            @click="$emit('delete', row.id)"
            aria-label="delete"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M9 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.278l.84 12.255A2 2 0 008.114 22h7.772a2 2 0 001.996-2.745L18.722 7H19a1 1 0 100-2h-3V4a1 1 0 00-1-1H9zm2 4a1 1 0 112 0v10a1 1 0 11-2 0V7z"/>
            </svg>
          </button>
        </td>
      </tr>

      <tr v-if="pagedRows.length === 0">
        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
          ไม่พบข้อมูลที่ค้นหา
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Footer -->
  <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <span>แสดง</span>
      <select
        :value="pageSize"
        @change="$emit('update:pageSize', Number(($event.target as HTMLSelectElement).value))"
        class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none"
      >
        <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
      </select>
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

type Row = {
  id: number;
  name: string;
  createdBy: string;
  createdAt: string | null;
};

const props = defineProps<{
  rows: Row[];                // รับรายการที่ "จัดเรียง + กรอง" แล้วจากหน้าแม่
  page: number;
  pageSize: number;
  startIndex: number;         // เอามาคำนวณเลขลำดับให้ต่อเนื่อง
  formatDate: (iso?: string | null) => string;
}>();

defineEmits<{
  (e:"update:page", v:number): void;
  (e:"update:pageSize", v:number): void;
  (e:"delete", id:number): void;
}>();

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
.truncate-text {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  max-width: 600px;
  cursor: pointer;
}
</style>
