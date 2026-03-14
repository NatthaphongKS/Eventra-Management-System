<!-- /**
 * ชื่อไฟล์: DepartmentFilter.vue
 * คำอธิบาย: Filter component ของ Department (dropdown style ตามแบบ FilterEmployees)
 * Input: modelValue (filters)
 * Output: Update filter values
 * ชื่อผู้เขียน/แก้ไข: Natthaphong Kongsinl
 * วันที่จัดทำ/แก้ไข: 2026-03-10
 */ -->

<template>
    <div class="relative z-50" ref="filterBox">
        <button
            type="button"
            @click="toggle"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800 hover:bg-gray-50"
            :class="{ 'bg-gray-100': isOpen }"
        >
            <svg
                class="w-6 h-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <line x1="4" y1="7" x2="20" y2="7" />
                <line x1="6" y1="12" x2="16" y2="12" />
                <line x1="8" y1="17" x2="12" y2="17" />
            </svg>
            <span>Filter</span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute top-full left-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 p-4"
                @click.stop
            >
                <h3 class="font-semibold text-neutral-800 mb-3">Filter</h3>
                <p class="text-sm text-neutral-600">No filters available for departments</p>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:modelValue"],
    data() {
        return {
            isOpen: false,
        };
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
        },
        close() {
            this.isOpen = false;
        },
    },
};
</script>

<style scoped>
.custom-checkbox {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 1.5px solid #d1d5db;
    border-radius: 4px;
    background-color: #fff;
    display: inline-grid;
    place-content: center;
    cursor: pointer;
    position: relative;
}

.custom-checkbox:checked {
    background-color: #b91c1c;
    border-color: #b91c1c;
}

.custom-checkbox:checked::after {
    content: "";
    width: 10px;
    height: 5px;
    border-left: 2px solid white;
    border-bottom: 2px solid white;
    transform: rotate(-45deg) translate(1px, -1px);
}
</style>
