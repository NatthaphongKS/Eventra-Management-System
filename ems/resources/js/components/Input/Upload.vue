<template>
    <div class="mt-4">
        <div class="mt-2 rounded-2xl border-2 border-dashed border-rose-400 bg-rose-50 relative transition-all">
            <!-- Drop zone (ไม่ใช้ label แล้ว) -->
            <div class="relative rounded-2xl transition-all" :class="[
                dragOver ? 'ring-2 ring-rose-400 ring-offset-2 ring-offset-rose-50' : '',
                model ? 'min-h-[60px] p-2' : 'min-h-[300px] pt-20 pb-6 px-4 cursor-pointer'
            ]" @click="!model && openPicker()" @dragover.stop.prevent="onDragOver"
                @dragleave.prevent="dragOver = false" @drop.stop.prevent="onDrop">
                <!-- มีไฟล์แล้ว -->
                <template v-if="model">
                    <div class="px-2">
                        <div
                            class="flex items-center justify-between gap-3 rounded-xl bg-white border border-neutral-200 px-4 py-3 shadow-sm min-w-0">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="flex items-center justify-center h-8 w-8 rounded-[8px] bg-white">
                                    <Icon icon="basil:file-solid" class="h-10 w-10 text-red-700" />
                                </div>

                                <span class="text-sm text-gray-700 truncate block">
                                    {{ model.name }}
                                </span>
                            </div>

                            <!-- ✅ ปุ่ม Remove ไม่ trigger file picker แล้ว -->
                            <button type="button"
                                class="flex-shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                @click.stop="clearFile" aria-label="Remove file">
                                ✕
                            </button>
                        </div>

                        <!-- <p v-if="error" class="mt-2 text-xs text-red-600">
                            {{ error }}
                        </p> -->
                    </div>
                </template>

                <!-- ยังไม่มีไฟล์ -->
                <template v-else>
                    <div class="flex flex-col items-center justify-center text-center">
                        <svg class="w-18 h-16 mb-3" viewBox="0 0 24 24">
                            <path class="text-rose-400" fill="currentColor"
                                d="M6 19h12a5 5 0 0 0 1.02-9.9A7 7 0 0 0 6.06 9.6 4 4 0 0 0 6 19Z" />
                            <path fill="#ffffff" d="M12 8l-3.5 3.5h2.5V17h2v-5.5H15.5L12 8Z" />
                        </svg>

                        <p class="text-gray-700 font-medium">
                            Choose a file or drag & drop it here
                        </p>

                        <p class="text-xs text-gray-500 mt-1">
                            Excel/CSV (.xls, .xlsx, .csv) · Up to {{ maxSizeMB }}MB
                        </p>

                        <button type="button"
                            class="mt-5 rounded-[10px] border border-rose-400 bg-white px-4 py-1.5 text-xs font-medium text-neutral-600 hover:bg-neutral-100 transition"
                            @click.stop="openPicker">
                            Browse files
                        </button>

                        <!-- <p v-if="error" class="mt-2 text-xs text-red-600">
                            {{ error }}
                        </p> -->
                    </div>
                </template>

                <!-- input file -->
                <input ref="fileInput" type="file" :accept="acceptString" class="hidden" @change="onPick" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Icon } from "@iconify/vue";

const model = defineModel("file", { default: null });

const props = defineProps({
    accept: {
        type: Array,
        default: () => [
            ".xls",
            ".xlsx",
            ".csv",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "text/csv",
        ],
    },
    maxSizeMB: { type: Number, default: 50 },
});

const emit = defineEmits(["invalid", "picked", "cleared"]);

const fileInput = ref(null);
const dragOver = ref(false);
// const error = ref("");

const acceptString = computed(() => props.accept.join(","));

function openPicker() {
    fileInput.value?.click();
}

function clearFile() {
    model.value = null;
    error.value = "";
    emit("cleared");
}

function onDragOver() {
    dragOver.value = true;
}

function onDrop(e) {
    dragOver.value = false;
    const f = e.dataTransfer?.files?.[0];
    if (f) handleFile(f);
}

function onPick(e) {
    const f = e.target.files?.[0];
    if (f) handleFile(f);
    e.target.value = "";
}

function handleFile(f) {
    const valid = validate(f);
    if (!valid.ok) {
        emit('invalid', valid.message);
        return;
    }
    model.value = f;
    emit('picked', f);
}

function validate(f) {
    if (f.size > props.maxSizeMB * 1024 * 1024) {
        return { ok: false, message: `ไฟล์ใหญ่เกิน ${props.maxSizeMB}MB` };
    }

    const lower = f.name.toLowerCase();
    const extOk = [".xls", ".xlsx", ".csv"].some((ext) =>
        lower.endsWith(ext)
    );

    if (!extOk) {
        return { ok: false, message: "รองรับเฉพาะ .xls, .xlsx หรือ .csv" };
    }

    return { ok: true };
}
</script>
