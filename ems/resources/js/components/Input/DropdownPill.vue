<template>
    <div class="relative mb-2" ref="root">
        <!-- Trigger -->
        <button type="button" :disabled="disabled"
            class="w-full rounded-[15px] border px-4 py-2.5 text-base flex items-center justify-between focus:outline-none focus:ring-2 transition"
            :class="[
                disabled ? 'bg-gray-50 text-neutral-400 cursor-not-allowed' : 'bg-white',
                error ? 'border-red-700 focus:ring-red-300' : 'neutral-200 focus:ring-red-300',
            ]" @click="toggle" @keydown.down.prevent="openAndMove(1)" @keydown.up.prevent="openAndMove(-1)"
            @keydown.enter.prevent="commitActive" @keydown.esc.prevent="open = false">
            <!-- เปลี่ยนสีข้อความตามสถานะ -->
            <span :class="[displayValue ? 'text-neutral-800' : 'text-red-300']">
                {{ displayValue || placeholder }}
            </span>

            <!-- ไอคอนแดงเข้ม -->
            <svg class="h-8 w-8 transition-transform text-red-700" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20"
                fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Panel -->
        <transition name="fade" appear>
            <div v-if="open"
                class="absolute z-20 left-0 right-0 mt-1 rounded-xl border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto outline-none"
                role="listbox" tabindex="0" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="commitActive" @keydown.esc.prevent="open = false">
                <div v-for="(opt, i) in normalizedOptions" :key="opt.value" role="option"
                    :aria-selected="isSelected(opt.value)" class="px-3 py-2 text-sm cursor-pointer" :class="[
                        i === activeIndex ? 'bg-red-100 rounded-lg' : 'bg-transparent',
                        'text-neutral-800'
                    ]" @mouseenter="activeIndex = i" @mouseleave="activeIndex = selectedIndex" @click="select(opt.value)">
                    {{ opt.label }}
                </div>

            </div>
        </transition>

        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount} from 'vue'

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

const open = ref(false)
const activeIndex = ref(-1)
const root = ref(null)

const normalizedOptions = computed(() => {
    return props.options.map((o) => {
        if (typeof o === 'string' || typeof o === 'number') {
            return { label: String(o), value: o }
        }
        const label = props.optionLabel
            ? o[props.optionLabel]
            : (o.label ?? String(o.value ?? o))
        const value = props.optionValue
            ? o[props.optionValue]
            : (o.value ?? o)
        return { label, value }
    })
})

const selectedIndex = computed(() =>
    normalizedOptions.value.findIndex((o) => isEqual(o.value, props.modelValue))
)
const displayValue = computed(() => {
    const idx = selectedIndex.value
    return idx >= 0 ? normalizedOptions.value[idx].label : ''
})

function isEqual(a, b) {
    try { return JSON.stringify(a) === JSON.stringify(b) }
    catch { return a === b }
}
function isSelected(v) { return isEqual(v, props.modelValue) }

function toggle() {
    if (props.disabled) return
    open.value = !open.value
    if (open.value) {
        activeIndex.value = selectedIndex.value >= 0 ? selectedIndex.value : 0
    }
}
function openAndMove(dir) {
    if (!open.value) {
        open.value = true
        activeIndex.value = selectedIndex.value >= 0 ? selectedIndex.value : 0
    }
    move(dir)
}
function move(dir) {
    if (!open.value) return
    const len = normalizedOptions.value.length
    if (len === 0) return
    activeIndex.value = (((activeIndex.value + dir) % len) + len) % len
}
function commitActive() {
    if (!open.value) return
    if (activeIndex.value >= 0) {
        select(normalizedOptions.value[activeIndex.value].value)
    }
}
function select(v) {
    emit('update:modelValue', v)
    open.value = false
}

function onClickOutside(e) {
    if (!root.value) return
    if (!root.value.contains(e.target)) open.value = false
}

onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))

watch(() => props.modelValue, () => {
    if (!open.value) activeIndex.value = selectedIndex.value
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .12s ease
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0
}
</style>
