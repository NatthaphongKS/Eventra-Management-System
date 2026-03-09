<!-- /**
 * ชื่อไฟล์: DropdownPill.vue
 * คำอธิบาย: Component สำหรับแสดง Dropdown แบบ Pill ใช้เลือกข้อมูลจากรายการตัวเลือก
 * Input: รับค่าจากหน้าแม่ผ่าน defineProps เช่น modelValue, options, placeholder, error และ disabled
 * Output: ส่งค่าที่ผู้ใช้เลือกกลับไปยังหน้าแม่ผ่าน emit update:modelValue
 * ชื่อผู้เขียน/แก้ไข: Thanusin leenarat
 * วันที่จัดทำ/แก้ไข: 9 มีนาคม 2569
 */ -->

<template>
    <div class="relative mb-2" ref="dropdownRef">
        <!-- Trigger -->
        <button type="button" :disabled="disabled"
            class="w-full h-[50px] rounded-[15px] border px-4 py-2.5 text-base flex items-center justify-between focus:outline-none focus:ring-2 transition"
            :class="[
                disabled ? 'bg-gray-50 text-neutral-400 cursor-not-allowed' : 'bg-white',
                error ? 'border-red-700 focus:ring-red-300' : 'border-neutral-200 focus:ring-red-300',
            ]" @click="toggleDropdown" @keydown.down.prevent="openDropdownAndMove(1)"
            @keydown.up.prevent="openDropdownAndMove(-1)" @keydown.enter.prevent="commitHighlightedOption"
            @keydown.esc.prevent="isDropdownOpen = false">

            <span :title="selectedLabel" :class="[
                disabled
                    ? 'text-neutral-400'
                    : (selectedLabel ? 'text-neutral-800' : 'text-red-300')
            ]">
                {{ selectedLabel ? truncateText(selectedLabel) : placeholder }}
            </span>

            <!-- ไอคอน -->
            <svg class="h-8 w-8 transition-transform text-red-700" :class="isDropdownOpen ? 'rotate-180' : ''"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown -->
        <transition name="fade" appear>
            <div v-if="isDropdownOpen"
                class="absolute z-20 left-0 right-0 mt-1 rounded-xl border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto outline-none"
                role="listbox" tabindex="0" @keydown.down.prevent="moveHighlight(1)"
                @keydown.up.prevent="moveHighlight(-1)" @keydown.enter.prevent="commitHighlightedOption"
                @keydown.esc.prevent="isDropdownOpen = false">
                <div v-for="(option, index) in normalizedOptions" :key="option.value" role="option"
                    :aria-selected="isOptionSelected(option.value)" class="px-3 py-2 text-sm cursor-pointer" :class="[
                        index === highlightedIndex ? 'bg-red-100 rounded-lg' : 'bg-transparent',
                        'text-neutral-800'
                    ]" @mouseenter="highlightedIndex = index" @mouseleave="highlightedIndex = selectedOptionIndex"
                    @click="selectOption(option.value)">
                    <span :title="option.label">
                        {{ truncateText(option.label) }}
                    </span>
                </div>
            </div>
        </transition>

        <p class="text-xs text-red-500 min-h-[16px]">
            {{ error || '\u00A0' }}
        </p>
    </div>
</template>


<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number, Object], default: '' },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Select' },
    error: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    optionLabel: { type: String, default: '' },
    optionValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const isDropdownOpen = ref(false)
const highlightedIndex = ref(-1)
const dropdownRef = ref(null)

function truncateText(text, maxLength = 40) {
    if (!text) return ''
    return text.length > maxLength ? text.slice(0, maxLength) + '...' : text
}

const normalizedOptions = computed(() => {
    return props.options.map((optionItem) => {
        if (typeof optionItem === 'string' || typeof optionItem === 'number') {
            return { label: String(optionItem), value: optionItem }
        }

        const label = props.optionLabel
            ? optionItem[props.optionLabel]
            : (optionItem.label ?? String(optionItem.value ?? optionItem))

        const value = props.optionValue
            ? optionItem[props.optionValue]
            : (optionItem.value ?? optionItem)

        return { label, value }
    })
})

const selectedOptionIndex = computed(() =>
    normalizedOptions.value.findIndex((option) =>
        JSON.stringify(option.value) === JSON.stringify(props.modelValue)
    )
)

const selectedLabel = computed(() => {
    const index = selectedOptionIndex.value
    return index >= 0 ? normalizedOptions.value[index].label : ''
})

function isOptionSelected(optionValue) {
    return JSON.stringify(optionValue) === JSON.stringify(props.modelValue)
}

function toggleDropdown() {
    if (props.disabled) return

    isDropdownOpen.value = !isDropdownOpen.value

    if (isDropdownOpen.value) {
        highlightedIndex.value = selectedOptionIndex.value >= 0 ? selectedOptionIndex.value : 0
    }
}

function openDropdownAndMove(direction) {
    if (!isDropdownOpen.value) {
        isDropdownOpen.value = true
        highlightedIndex.value = selectedOptionIndex.value >= 0 ? selectedOptionIndex.value : 0
    }
    moveHighlight(direction)
}

function moveHighlight(direction) {
    if (!isDropdownOpen.value) return

    const optionCount = normalizedOptions.value.length
    if (optionCount === 0) return

    highlightedIndex.value =
        (((highlightedIndex.value + direction) % optionCount) + optionCount) % optionCount
}

function commitHighlightedOption() {
    if (!isDropdownOpen.value) return

    if (highlightedIndex.value >= 0) {
        selectOption(normalizedOptions.value[highlightedIndex.value].value)
    }
}

function selectOption(selectedValue) {
    emit('update:modelValue', selectedValue)
    isDropdownOpen.value = false
}

function handleClickOutside(event) {
    if (!dropdownRef.value) return
    if (!dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

watch(() => props.modelValue, () => {
    if (!isDropdownOpen.value) {
        highlightedIndex.value = selectedOptionIndex.value
    }
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .12s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
