<template>
    <div class="relative z-50">
        <!-- ปุ่ม Sort -->
        <button
            type="button"
            @click="$emit('toggle')"
            aria-label="Sort"
            :aria-expanded="isOpen"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800"
            :class="{ 'bg-gray-100': isOpen }"
        >
            <!-- ไอคอน Sort (inline SVG) -->
            <svg
                width="24"
                height="24"
                viewBox="0 0 28 28"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M5 20L9.5 25L14 20M9.5 25V5M17 7H26M17 13H26M17 19H26"
                    stroke="#444444"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span class="hidden sm:inline">Sort</span>
        </button>

        <!-- เมนู -->
        <div
            v-if="isOpen"
            class="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
            @click.stop
        >
            <div class="p-2">
                <div class="mb-2 px-2 text-sm font-semibold text-gray-700">
                    Sort
                </div>

                <button
                    v-for="option in options"
                    :key="option.key + '-' + option.order"
                    @click="$emit('choose', option)"
                    class="w-full flex items-center gap-3 px-2 py-2 text-sm text-left hover:bg-rose-50 rounded"
                    :class="{ 'bg-rose-50 text-rose-700': isActive(option) }"
                >
                    <span class="w-4 h-4 flex items-center justify-center">
                        <span
                            v-if="isActive(option)"
                            class="text-rose-600"
                        ></span>
                    </span>
                    <span>{{ option.label }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SortMenu",
    props: {
        isOpen: { type: Boolean, default: false },
        options: { type: Array, default: () => [] }, // [{key,order,label}]
        sortBy: { type: String, default: null },
        sortOrder: { type: String, default: null },
    },
    emits: ["toggle", "choose"],
    methods: {
        isActive(option) {
            return (
                this.sortBy === option.key && this.sortOrder === option.order
            );
        },
    },
};
</script>
