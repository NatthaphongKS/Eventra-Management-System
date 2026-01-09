<template>
    <div class="mt-4">

        <!-- ▼ Drop zone: ลดขนาดกรอบเมื่อมีไฟล์ -->
        <div class="mt-2 rounded-2xl border-2 border-rose-300 border-dashed bg-rose-50 relative transition-all">
            <label :for="inputId" class="block">
                <div class="relative cursor-pointer rounded-2xl transition-all" :class="[
                    dragOver ? 'ring-2 ring-rose-300' : '',
                    model ? 'min-h-[60px] p-2' : 'min-h-[300px] pt-20 pb-6 px-4'
                ]" @dragover.stop.prevent="onDragOver" @dragleave.prevent="dragOver = false" @drop.stop.prevent="onDrop">
                    <!-- แสดงชื่อไฟล์ -->
                    <template v-if="model">
                        <div class="px-2">
                            <div
                                class="flex items-center justify-between rounded-xl bg-white border border-nutral-200 px-4 py-3 shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                        <svg class="w-10 h-10 text-red-600" viewBox="0 0 24 24" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M14 2.25a.25.25 0 0 1 .25.25v5.647c0 .414.336.75.75.75h4.5a.25.25 0 0 1 .25.25V19A2.75 2.75 0 0 1 17 21.75H7A2.75 2.75 0 0 1 4.25 19V5A2.75 2.75 0 0 1 7 2.25z" />
                                            <path d="M16.086 2.638c-.143-.115-.336.002-.336.186v4.323c0 .138.112.25.25.25h3.298c.118 0 .192-.124.124-.22L16.408 2.98a1.8 1.8 0 0 0-.322-.342" />
                                        </svg>
                                    <span class="text-md font-medium text-neutral-700 truncate">{{ model.name }}</span>
                                </div>
                                <button type="button"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                    @click.stop="clearFile" aria-label="Remove file" title="Remove">
                                    ✕
                                </button>
                            </div>

                            <!-- error -->
                            <p v-if="error" class="mt-2 text-xs text-red-600">{{ error }}</p>
                        </div>
                    </template>

                    <!-- เนื้อหาเมื่อยังไม่มีไฟล์ -->
                    <template v-else>
                        <div class="flex flex-col items-center justify-center text-center">
                            <svg class="w-18 h-16 mb-3" viewBox="0 0 24 24" aria-hidden="true">
                                <path class="text-rose-400" fill="currentColor"
                                    d="M6 19h12a5 5 0 0 0 1.02-9.9A7 7 0 0 0 6.06 9.6 4 4 0 0 0 6 19Z" />
                                <path fill="#ffffff" d="M12 8l-3.5 3.5h2.5V17h2v-5.5H15.5L12 8Z" />
                            </svg>
                            <p class="text-gray-600">Choose a file or drag &amp; drop it here</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Excel/CSV (.xls, .xlsx, .csv) · Up to {{ maxSizeMB }}MB
                            </p>
                            <button type="button"
                                class="mt-4 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                @click="openPicker">
                                Browse files
                            </button>

                            <!-- error -->
                            <p v-if="error" class="mt-2 text-xs text-red-600">{{ error }}</p>
                        </div>
                    </template>

                    <!-- input file -->
                    <input :id="inputId" ref="fileInput" type="file" :accept="acceptString" class="hidden"
                        @change="onPick" />
                </div>
            </label>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

/**
 * v-model:file
 * - ใช้แบบ <ExcelUpload v-model:file="file" />
 */
const model = defineModel('file', { default: null })

const props = defineProps({
    accept: {
        type: Array,
        default: () => ['.xls', '.xlsx', '.csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv']
    },
    maxSizeMB: { type: Number, default: 50 },
    inputId: { type: String, default: 'excel-file-input' }
})

const emit = defineEmits([
    'invalid',     // (message: string)
    'picked',      // (file: File)
    'cleared'      // ()
])

const fileInput = ref(null)
const dragOver = ref(false)
const error = ref('')

const acceptString = computed(() => props.accept.join(','))

function openPicker() {
    fileInput.value?.click()
}
function clearFile() {
    model.value = null
    error.value = ''
    emit('cleared')
}
function onDragOver() {
    dragOver.value = true
}
function onDrop(e) {
    dragOver.value = false
    const f = e.dataTransfer?.files?.[0]
    if (f) handleFile(f)
}
function onPick(e) {
    const f = e.target.files?.[0]
    if (f) handleFile(f)
    // reset ให้เลือกไฟล์เดิมซ้ำได้
    e.target.value = ''
}

function handleFile(f) {
    const valid = validate(f)
    if (!valid.ok) {
        error.value = valid.message
        emit('invalid', valid.message)
        return
    }
    error.value = ''
    model.value = f
    emit('picked', f)
}

function validate(f) {
    const sizeOk = f.size <= props.maxSizeMB * 1024 * 1024
    if (!sizeOk) {
        return { ok: false, message: `ไฟล์ใหญ่เกิน ${props.maxSizeMB}MB` }
    }

    // ตรวจนามสกุล file
    const lower = f.name.toLowerCase()
    const extOk = ['.xls', '.xlsx', '.csv'].some(ext => lower.endsWith(ext))
    if (!extOk) {
        return { ok: false, message: 'รองรับเฉพาะ .xls, .xlsx หรือ .csv' }
    }

    return { ok: true, message: '' }
}
</script>
