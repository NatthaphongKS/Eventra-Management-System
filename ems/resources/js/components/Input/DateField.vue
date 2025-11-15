<script setup lang="ts">
import { ref, computed } from "vue";

const props = defineProps<{
    modelValue: string | null;
    placeholder?: string;
    error?: string | null;
}>();
const emit = defineEmits<{ "update:modelValue": [string | null] }>();

const inputEl = ref<HTMLInputElement | null>(null);

function openPicker() {
    const el = inputEl.value;
    if (!el) return;
    // @ts-ignore
    if (el.showPicker) el.showPicker(); else el.focus();
}

const wrapCls = computed(() =>
    [
        "relative flex items-center gap-2 h-12 w-full rounded-2xl px-4",
        "border bg-white/90 shadow-sm",
        props.error
            ? "border-red-500 text-red-600 focus-within:ring-2 focus-within:ring-red-200"
            : "border-red-400 text-gray-700 focus-within:ring-2 focus-within:ring-red-100",
    ].join(" ")
);
</script>

<template>
    <div :class="wrapCls">
        <input ref="inputEl" class="date-input flex-1 bg-transparent outline-none pr-10 caret-rose-500"
            :class="{ 'is-empty': !modelValue }" type="date" :value="modelValue || ''"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)" />

        <!-- placeholder ซ้อน (แสดงเมื่อค่าว่าง) -->
        <span v-if="!modelValue"
            class="pointer-events-none select-none absolute left-4 top-1/2 -translate-y-1/2 text-rose-300">
            dd/mm/yyyy
        </span>

        <!-- ไอคอน Material Symbols สีแดง -->
        <button type="button" class="absolute right-3 grid place-items-center text-red-600" @click="openPicker"
            aria-label="Open calendar">
            <span class="material-symbols-outlined ms-icon">calendar_month</span>
        </button>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
</template>

<style scoped>
/* ซ่อนไอคอน native ของ input[type=date] ให้เหลือเฉพาะไอคอนแดง */
.date-input::-webkit-calendar-picker-indicator {
    opacity: 0;
    display: none;
}

/* ปิดสปิน/ปุ่มเคลียร์ของบางเบราว์เซอร์ */
.date-input::-webkit-inner-spin-button,
.date-input::-webkit-clear-button {
    display: none;
}

/* เอา native appearance ออกให้สไตล์นิ่ง */
.date-input {
    appearance: none;
    -webkit-appearance: none;
}

/* ✅ ซ่อน placeholder/ฟิลด์ native “วว/ดด/ปปปป” ตอนค่าว่าง */
.date-input.is-empty::-webkit-datetime-edit {
    color: transparent;
}

.date-input.is-empty:focus::-webkit-datetime-edit {
    color: transparent;
}

.date-input.is-empty::-webkit-datetime-edit-text,
.date-input.is-empty::-webkit-datetime-edit-year-field,
.date-input.is-empty::-webkit-datetime-edit-month-field,
.date-input.is-empty::-webkit-datetime-edit-day-field {
    color: transparent;
}

/* ไซซ์/น้ำหนักของ Material Symbols */
.ms-icon {
    font-size: 22px;
    line-height: 1;
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

/* กัน autofill เปลี่ยนพื้นหลัง */
input:-webkit-autofill {
    transition: background-color 9999s;
}
</style>
