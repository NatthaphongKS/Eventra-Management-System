<template>
    <div class="relative z-50">
        <!-- ปุ่ม Sort -->
        <button
            type="button"
            @click="$emit('toggle')"
            aria-label="Sort"
            :aria-expanded="isOpen"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800 transition-colors "
            :class="{ 'bg-gray-100': isOpen, 'hover:bg-gray-50': !isOpen }"
        >
            <!-- ไอคอน Sort (inline SVG) -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
            >
                <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m3 16l4 4l4-4m-4 4V4m4 0h10M11 8h7m-7 4h4"
                />
            </svg>

            <span class="hidden sm:inline">Sort</span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-neutral-200 z-50 overflow-hidden"
                @click.stop
            >
                <div class="p-2">
                    <button
                        v-for="option in options"
                        :key="option.key + '-' + option.order"
                        @click="$emit('choose', option)"
                        class="w-full flex items-center gap-3 px-2 py-2 text-sm text-left hover:bg-rose-50 rounded transition-colors"
                        :class="{ 'bg-rose-50 text-rose-700 font-medium': isActive(option) }"
                    >
                        <span class="w-4 h-4 flex items-center justify-center flex-shrink-0">
                            <svg 
                                v-if="isActive(option)"
                                xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 24 24" 
                                fill="none" 
                                stroke="currentColor" 
                                stroke-width="3" 
                                stroke-linecap="round" 
                                stroke-linejoin="round"
                                class="w-3.5 h-3.5 text-rose-600"
                            >
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <span>{{ option.label }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    name: "SortMenu",
    props: {
        isOpen: { type: Boolean, default: false },
        options: { type: Array, default: () => [] },
        sortBy: { type: String, default: null },
        sortOrder: { type: String, default: null },
    },
    emits: ["toggle", "choose"],
    methods: {
        isActive(option) {
            // ตรวจสอบทั้ง key และ order
            return (
                this.sortBy === option.key && this.sortOrder === option.order
            );
        },
    },
};
</script>