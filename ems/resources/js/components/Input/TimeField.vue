<script setup lang="ts">
import { computed, ref } from "vue";

const props = defineProps<{
    start: string | null
    end: string | null
    error?: string | null
    disabled?: boolean
}>();

const emit = defineEmits<{
    "update:start": [string | null]
    "update:end": [string | null]
}>();

const startRef = ref<HTMLInputElement | null>(null);
const endRef = ref<HTMLInputElement | null>(null);

function openPicker(which: "start" | "end") {
    const el = which === "start" ? startRef.value : endRef.value;
    if (!el) return;
    // @ts-ignore
    if (el.showPicker) el.showPicker();
    else el.focus();
}

const wrapCls = computed(() =>
    [
        "flex items-center gap-2 h-11 w-full rounded-2xl px-2",
        "border bg-white/90 shadow-sm transition-colors min-w-0",
        props.error
            ? "border-red-500 focus-within:ring-2 focus-within:ring-red-200"
            : "border-red-400 focus-within:ring-2 focus-within:ring-red-100",
        props.disabled ? "opacity-60 pointer-events-none" : "",
    ].join(" ")
);
</script>

<template>
    <div :class="wrapCls">
        <!-- START -->
        <div class="relative w-24 sm:w-28 shrink-0 cursor-text" role="button" tabindex="0" @click="openPicker('start')"
            @keydown.enter.prevent="openPicker('start')" @keydown.space.prevent="openPicker('start')">
            <input ref="startRef" type="time"
                class="time-input h-9 w-full rounded-xl border border-transparent bg-transparent px-2 outline-none text-sm"
                :class="{ 'is-empty': !start }" :value="start || ''"
                @input="emit('update:start', ($event.target as HTMLInputElement).value)" />
            <!-- placeholder ที่คลิกได้ (ปล่อยคลิกทะลุไป div ด้วย pointer-events-none) -->
            <span v-if="!start" class="pointer-events-none absolute inset-0 flex items-center px-2 text-rose-300">
                Start
            </span>
        </div>

        <span class="text-rose-300 select-none mx-1">:</span>

        <!-- END -->
        <div class="relative w-24 sm:w-28 shrink-0 cursor-text" role="button" tabindex="0" @click="openPicker('end')"
            @keydown.enter.prevent="openPicker('end')" @keydown.space.prevent="openPicker('end')">
            <input ref="endRef" type="time"
                class="time-input h-9 w-full rounded-xl border border-transparent bg-transparent px-2 outline-none text-sm"
                :class="{ 'is-empty': !end }" :value="end || ''"
                @input="emit('update:end', ($event.target as HTMLInputElement).value)" />
            <span v-if="!end" class="pointer-events-none absolute inset-0 flex items-center px-2 text-rose-300">
                End
            </span>
        </div>

        <!-- ปุ่มไอคอน -->
        <button type="button" class="ml-auto grid place-items-center h-9 w-9 text-red-600 bg-transparent p-0
         focus:outline-none cursor-default pointer-events-none" disabled aria-disabled="true" tabindex="-1"
            title="Pick start time">
            <span class="material-symbols-outlined ms-icon">schedule</span>
        </button>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
</template>

<style scoped>
/* ทำให้ input type="time" เรียบ */
input[type="time"]::-webkit-calendar-picker-indicator {
    display: none !important;
}

input[type="time"]::-webkit-clear-button,
input[type="time"]::-webkit-inner-spin-button,
input[type="time"]::-webkit-outer-spin-button {
    display: none !important;
}

input[type="time"] {
    appearance: none;
    -webkit-appearance: none;
}

input::-webkit-datetime-edit-fields-wrapper {
    padding: 0;
}

.time-input.is-empty {
    color: transparent;
    caret-color: transparent;
}

.time-input.is-empty::-webkit-datetime-edit {
    color: transparent;
}

.time-input.is-empty::-webkit-datetime-edit-text,
.time-input.is-empty::-webkit-datetime-edit-hour-field,
.time-input.is-empty::-webkit-datetime-edit-minute-field,
.time-input.is-empty::-webkit-datetime-edit-second-field,
.time-input.is-empty::-webkit-datetime-edit-ampm-field {
    color: transparent;
}

/* ขนาด/น้ำหนักของ Material Symbols (schedule) */
.ms-icon {
    font-size: 22px;
    /* ปรับได้ 18–24 ตามชอบ */
    line-height: 1;
    font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}
</style>
