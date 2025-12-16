<template>
    <div class="relative">
        <div v-if="loading" class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/60">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-rose-200 border-t-rose-600"></div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-neutral-200" :class="{ 'opacity-50': loading }">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-neutral-100 text-neutral-800">
                        <th v-if="selectable" class="w-10 py-3 text-center">
                            <input type="checkbox" :checked="allSelectedOnPage" :indeterminate.prop="isIndeterminate"
                                @change="toggleSelectAllOnPage" class="accent-red-600" />
                        </th>

                        <th v-if="showRowNumber" class="w-12 py-3 text-center font-semibold">
                            #
                        </th>

                        <th v-for="col in columns" :key="col.key" class="py-3 px-3 font-semibold" :class="[
                            col.class,
                            col.headerClass,

                        ]">
                            <slot :name="`header-${col.key}`" :label="col.label" :column="col">

                                <span>{{ col.label }}</span>

                            </slot>
                        </th>

                        <th v-if="$slots.actions" class="w-28 py-3 text-center font-semibold">
                            &nbsp;
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <template v-if="!loading && rows.length > 0">
                        <tr v-for="(row, index) in rows" :key="row[rowKey] ?? index" class="border-t hover:bg-neutral-100"
                            :class="[
                                { 'bg-red-100': selectable && selectedSet.has(row[rowKey]) },
                                rowClass(row),
                            ]">
                            <td v-if="selectable" class="px-2 py-2 text-center">
                                <input type="checkbox" :value="row[rowKey]" :checked="selectedSet.has(row[rowKey])"
                                    @change="toggleSelectOne(row[rowKey], $event)" class="accent-red-600" />
                            </td>

                            <td v-if="showRowNumber" class="px-2 py-2 text-center text-sm text-slate-700">
                                {{ rowStartIndex + index + 1 }}
                            </td>

                            <td v-for="col in columns" :key="col.key" class="px-3 py-2 text-sm text-slate-800"
                                :class="[col.class, col.cellClass]">
                                <slot :name="`cell-${col.key}`" :value="getValue(row, col.key)" :row="row">
                                    {{ formatValue(row, col) }}
                                </slot>
                            </td>

                            <td v-if="$slots.actions" class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <slot name="actions" :row="row" :index="rowStartIndex + index"></slot>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr v-if="!loading && rows.length === 0">
                        <td :colspan="totalColspan" class="px-3 py-6 text-center text-neutral-700">
                            <slot name="empty"> No data found </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <div v-if="totalItems > 0" class="mt-4 flex flex-col items-center gap-4 md:flex-row md:items-center">
                <div class="flex items-center gap-2 text-sm text-slate-700">
                    <slot name="footer-info" :from="fromItem" :to="toItem" :total="totalItems">
                        <span>แสดง</span>
                        <div class="relative inline-block">
                            <select
                                class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200"
                                :value="pageSize" @change="onChangePageSize">
                                <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">
                                    {{ opt }}
                                </option>
                            </select>
                            <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </div>
                        <span>
                            {{ fromItem }}-{{ toItem }} จาก {{ totalItems }} รายการ
                        </span>
                    </slot>
                </div>

            </div>
            <div class="flex items-center justify-center gap-3 md:mx-auto">
                <button class="pg-arrow" :disabled="page === 1" @click="goToPage(page - 1)">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 12 L18 4 L18 20 Z" />
                    </svg>
                </button>

                <template v-for="(it, idx) in pageItems">
                    <button v-if="it.type === 'page'" :key="`page-${it.value}`" class="pg-num"
                        :class="{ 'pg-active': it.value === page }" @click="goToPage(it.value)">
                        {{ it.value }}
                    </button>
                    <span v-else :key="`dots-${idx}`" class="pg-ellipsis">
                        <i class="dot"></i><i class="dot"></i><i class="dot"></i>
                    </span>
                </template>

                <button class="pg-arrow" :disabled="page === totalPages || totalPages === 0"
                    @click="goToPage(page + 1)">
                    <svg viewBox="0 0 24 24" style="transform: scaleX(-1)">
                        <path d="M6 12 L18 4 L18 20 Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch, useSlots } from 'vue';

defineOptions({ name: 'DataTable' });

// --- Props ---
const props = defineProps({
    /**
     * (สำคัญ) ข้อมูลที่แสดง *เฉพาะในหน้านี้*
     * @type {Array<Object>}
     */
    rows: { type: Array, default: () => [] },

    /**
     * (สำคัญ) นิยามคอลัมน์
     * @type {Array<{
     * key: String,       // (Required) key ใน object ของ row
     * label: String,     // (Required) ชื่อที่จะแสดงใน <th>
     * class?: String,     // (Optional) class สำหรับ <th> และ <td>
     * headerClass?: String, // (Optional) class สำหรับ <th> เท่านั้น
     * cellClass?: String,   // (Optional) class สำหรับ <td> เท่านั้น
     * format?: Function,  // (Optional) ฟังก์ชันจัดรูปแบบ (value, row) => String
     * sortable?: Boolean  // (Optional) คอลัมน์นี้คลิกเพื่อเรียงลำดับได้
     * }>}
     */
    columns: { type: Array, default: () => [] },

    /**
     * (สำคัญ) สถานะกำลังโหลด
     */
    loading: { type: Boolean, default: false },

    // --- Pagination Props ---
    /**
     * (v-model) หน้าปัจจุบัน (Controlled by parent)
     */
    page: { type: Number, default: 1 },
    /**
     * (v-model) จำนวนรายการต่อหน้า (Controlled by parent)
     */
    pageSize: { type: Number, default: 10 },
    /**
     * (สำคัญ) จำนวนรายการทั้งหมดในฐานข้อมูล
     */
    totalItems: { type: Number, default: 0 },
    /**
     * ตัวเลือกจำนวนรายการต่อหน้า
     */
    pageSizeOptions: { type: Array, default: () => [10, 20, 50, 100] },

    // --- Sorting Props ---
    /**
     * (v-model) คอลัมน์ที่กำลังใช้เรียง
     */
    sortKey: { type: String, default: null },
    /**
     * (v-model) ทิศทางการเรียง ('asc' หรือ 'desc')
     */
    sortOrder: { type: String, default: null },

    // --- Selection Props ---
    /**
     * key ของ row ที่จะใช้เป็น unique identifier
     */
    rowKey: { type: String, default: 'id' },
    /**
     * เปิดใช้งานโหมดเลือก (checkbox)
     */
    selectable: { type: Boolean, default: false },
    /**
     * (v-model) Array ของ key (id) ที่ถูกเลือก
     */
    modelValue: { type: Array, default: () => [] },

    // --- Misc Props ---
    /**
     * แสดงคอลัมน์ลำดับที่ (#) หรือไม่
     */
    showRowNumber: { type: Boolean, default: true },
    /**
     * ฟังก์ชันสำหรับกำหนด class ให้ <tr>
     */
    rowClass: { type: Function, default: () => '' },
});

// --- Emits ---
const emit = defineEmits([
    'update:page',
    'update:pageSize',
    'update:modelValue',
    'update:sortKey',
    'update:sortOrder',
    'sort', // Event ใหม่สำหรับ Server-Side Sorting
    'checkbox-checkin',
     'check-all-page'
]);

const slots = useSlots(); // (ใช้เช็ค $slots.actions)

// --- Computed (Pagination) ---
const totalPages = computed(() =>
    Math.ceil(props.totalItems / props.pageSize) || 1
);
const rowStartIndex = computed(() => (props.page - 1) * props.pageSize);

const fromItem = computed(() =>
    props.totalItems === 0 ? 0 : rowStartIndex.value + 1
);
const toItem = computed(() =>
    Math.min(rowStartIndex.value + props.rows.length, props.totalItems)
);

const pageItems = computed(() => {
    const total = totalPages.value;
    const cur = props.page;
    const items = [];
    if (total <= 7) {
        for (let i = 1; i <= total; i++) items.push({ type: 'page', value: i });
        return items;
    }
    const addPage = (p) => items.push({ type: 'page', value: p });
    const addDots = () => items.push({ type: 'dots' });
    addPage(1);
    if (cur > 3) addDots();
    const s = Math.max(2, cur - 1);
    const e = Math.min(total - 1, cur + 1);
    for (let p = s; p <= e; p++) addPage(p);
    if (cur < total - 2) addDots();
    addPage(total);
    return items;
});

const totalColspan = computed(() => {
    return (
        props.columns.length +
        (props.selectable ? 1 : 0) +
        (props.showRowNumber ? 1 : 0) +
        (slots.actions ? 1 : 0)
    );
});

// --- Methods (Pagination) ---
function goToPage(p) {
    let next = Math.max(1, Math.min(p, totalPages.value));
    emit('update:page', next);
}

function onChangePageSize(e) {
    const nextSize = Number(e.target.value) || 10;
    emit('update:pageSize', nextSize);
    goToPage(1); // Reset to page 1
}

// --- Methods (Sorting) ---
function handleSort(key) {
    let nextOrder = 'asc';
    if (props.sortKey === key) {
        nextOrder = props.sortOrder === 'asc' ? 'desc' : 'asc';
    }

    emit('update:sortKey', key);
    emit('update:sortOrder', nextOrder);
    emit('sort', { key, order: nextOrder });
}

// --- Methods (Data Formatting) ---
function getValue(row, key) {
    if (!key) return '';
    return key
        .split('.')
        .reduce((acc, part) => (acc ? acc[part] : undefined), row);
}

function formatValue(row, col) {
    const value = getValue(row, col.key);
    if (col.format) {
        return col.format(value, row);
    }
    return value ?? 'N/A';
}

// --- Computed (Selection) ---
const selectedSet = computed(() => new Set(props.modelValue));

const pageRowKeys = computed(() =>
    props.rows.map((row) => row[props.rowKey]).filter((k) => k != null)
);

const allSelectedOnPage = computed(
    () =>
        pageRowKeys.value.length > 0 &&
        pageRowKeys.value.every((key) => selectedSet.value.has(key))
);

const isIndeterminate = computed(() => {
    const pickedCount = pageRowKeys.value.filter((key) =>
        selectedSet.value.has(key)
    ).length;
    return pickedCount > 0 && pickedCount < pageRowKeys.value.length;
});

// --- Methods (Selection) ---
function toggleSelectOne(key, event) {
  const next = new Set(selectedSet.value);
  const checked = event.target.checked; // ✅ ประกาศตัวแปร checked

  if (checked) {
    next.add(key);
  } else {
    next.delete(key);
  }

  emit('update:modelValue', Array.from(next));
  emit('checkbox-checkin', { keys: [key], checked }); // ✅ ส่งให้แม่ component
}

function toggleSelectAllOnPage(event) {
  const next = new Set(selectedSet.value);
  const checked = event.target.checked; // ✅

  pageRowKeys.value.forEach((key) => {
    if (checked) {
      next.add(key);
    } else {
      next.delete(key);
    }
  });

  emit('update:modelValue', Array.from(next));
  //emit('checkbox-checkin', { keys: Array.from(pageRowKeys.value), checked }); // ✅ ส่งรวมทั้งหมดในหน้า
  emit('check-all-page', {                           // ✅ เพิ่ม อีเวนต์ให้แม่
    action: checked ? 'check' : 'uncheck',          //    บอกประเภทการกระทำ
    pageKeys: Array.from(pageRowKeys.value),        //    คีย์ทั้งหมดบนหน้า
    rowsOnPage: props.rows,                         //    แถวจริงบนหน้า (มี empCheckinStatus)
    rowKey: props.rowKey,                           //    ชื่อคีย์ (เช่น 'empId')
    checkinField: 'empCheckinStatus',               //    ฟิลด์สถานะที่ใช้กรอง
  });
}

</script>

<style>
/* (Style ของ Pagination สีแดง - เหมือนเดิม) */
.pg-arrow {
    @apply grid h-9 w-9 place-items-center rounded-xl border border-red-700 bg-white text-red-700 transition hover:bg-rose-50 disabled:opacity-40;
}

.pg-arrow svg {
    @apply h-5 w-5 fill-current;
}

.pg-num {
    @apply grid h-9 min-w-[36px] place-items-center rounded-xl border border-red-700 bg-white px-2 text-sm font-medium text-red-700 transition hover:bg-rose-50;
}

.pg-num.pg-active {
    @apply border-red-700 bg-red-700 text-white;
}

.pg-ellipsis {
    @apply flex h-9 w-9 items-end justify-center gap-0.5 pb-2.5 text-slate-400;
}

.pg-ellipsis .dot {
    @apply h-1 w-1 rounded-xl bg-current;
}
</style>
