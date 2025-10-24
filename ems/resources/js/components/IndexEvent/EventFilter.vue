<template>
    <div class="relative inline-block text-left" ref="root">
        <!-- ปุ่ม Filter -->
        <button @click="isOpen = !isOpen"
            class="inline-flex items-center gap-2 px-2 py-1 text-neutral-800 hover:text-slate-900 focus:outline-none rounded-md
            font-medium font-[Poppins]">
            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#434343">
                <path d="M400-240v-80h160v80H400ZM240-440v-80h480v80H240ZM120-640v-80h720v80H120Z" />
            </svg>
            <span class="text-base font-medium">Filter</span>
            <!-- badge นับจำนวนเงื่อนไขที่เลือก -->
            <!-- <span v-if="badgeCount"
                class="ml-1 min-w-5 text-center rounded-full bg-slate-900 text-white text-xs px-1.5 py-0.5">
                {{ badgeCount }}
            </span> -->
        </button>

        <!-- กล่อง Dropdown -->
        <div v-show="isOpen"
            class="absolute left-0 mt-2 w-60 rounded-xl border border-slate-200 bg-white shadow-lg z-20 p-3">
            <!-- Filter All -->
            <label class="flex items-center gap-2 mb-2 ">
                <input type="checkbox" class="accent-[#b91c1c] h-4 w-4 rounded" :checked="isAllSelected"
                    @change="toggleAll" />
                <span class="font-regular font-[Poppins] uppercase text-neutral-800">Filter All</span>
            </label>

            <!-- Category -->
            <div class="mb-3">
                <div class="font-regular font-[Poppins] text-neutral-800 uppercase mb-1">Category</div>
                <div class="flex flex-col gap-1">
                    <label v-for="c in categories" :key="c.id" class="flex items-center gap-2">
                        <input type="checkbox" class="accent-[#b91c1c] h-4 w-4 rounded"
                            :checked="local.category.has(String(c.id))" @change="toggle('category', String(c.id))" />
                        <span class="font-regular font-[Poppins] text-neutral-800">{{ c.cat_name }}</span>
                    </label>
                </div>
            </div>

            <!-- Status -->
            <div>
                <div class="font-regular font-[Poppins] uppercase text-neutral-800 mb-1">Status</div>
                <div class="flex flex-col gap-1">
                    <label v-for="s in statusOptions" :key="s.value" class="flex items-center gap-2">
                        <input type="checkbox" class="accent-[#b91c1c] h-4 w-4 rounded"
                            :checked="local.status.has(s.value)" @change="toggle('status', s.value)" />
                        <span class="font-regular font-[Poppins] text-neutral-800">{{ s.label }}</span>
                    </label>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue'

/** Props:
 * - v-model: modelValue = { category: string[], status: string[] }
 * - categories: [{id:number|string, cat_name:string}]
 * - statusOptions: [{label:string, value:string}]  (ค่าเริ่มต้นให้มา 3 ตัว)
 */
type Filters = { category: Array<string | number>; status: string[] }
type Category = { id: string | number; cat_name: string }
type StatusOpt = { label: string; value: string }

const props = withDefaults(defineProps<{
    modelValue?: Filters
    categories?: Category[]
    statusOptions?: StatusOpt[]
}>(), {
    modelValue: () => ({ category: [], status: [] }),
    categories: () => [],
    statusOptions: () => ([
        { label: 'Done', value: 'done' },
        { label: 'Ongoing', value: 'ongoing' },
        { label: 'Upcoming', value: 'upcoming' },
    ]),
})
const emit = defineEmits<{
    (e: 'update:modelValue', v: Filters): void
    (e: 'apply', v: Filters): void
    (e: 'clear'): void
}>()

const isOpen = ref(false)
const root = ref<HTMLElement | null>(null)

/* state ภายในเป็น Set เพื่อสลับ on/off ง่าย */
const local = ref<{ category: Set<string | number>, status: Set<string> }>({
    category: new Set(props.modelValue.category ?? []),
    status: new Set(props.modelValue.status ?? [])
})

/* sync เมื่อ parent เปลี่ยนค่าจากภายนอก */
watch(() => props.modelValue, (v) => {
    local.value.category = new Set(v?.category ?? [])
    local.value.status = new Set(v?.status ?? [])
}, { deep: true })

/* นับ badge */
const badgeCount = computed(() =>
    local.value.category.size + local.value.status.size
)

/* Filter All */
const isAllSelected = computed(() => {
    const catAll =
        props.categories.length > 0 &&
        props.categories.every(c => local.value.category.has(String(c.id)))

    const stAll =
        props.statusOptions.length > 0 &&
        props.statusOptions.every(s => local.value.status.has(s.value))

    return catAll && stAll
})
function toggleAll() {
    const all = isAllSelected.value
    if (all) {
        local.value.category.clear()
        local.value.status.clear()
    } else {
        local.value.category = new Set(props.categories.map(c => String(c.id)))
        local.value.status = new Set(props.statusOptions.map(s => s.value))
    }
    push()
}

/* เปลี่ยนค่าเดี่ยว */
function toggle(key: 'category' | 'status', value: string | number) {
    const set = local.value[key]
    // @ts-ignore
    set.has(value) ? set.delete(value) : set.add(value)
    push()
}

/* ส่งค่าออกไปทุกครั้งที่ติ๊ก (หรือจะส่งเฉพาะตอนกด Apply ก็ได้) */
function push() {
    emit('update:modelValue', {
        category: Array.from(local.value.category),
        status: Array.from(local.value.status),
    })
}

/* ปุ่ม Clear/Apply (optional) */
function clear() {
    local.value.category.clear()
    local.value.status.clear()
    push()
    emit('clear')
}

/* click-outside เพื่อปิดกล่อง */
function onDocClick(e: MouseEvent) {
    if (!root.value) return
    if (!root.value.contains(e.target as Node)) isOpen.value = false
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>
