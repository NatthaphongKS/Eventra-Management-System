<template>
    <div class="relative z-50" ref="filterBox">
        <button
            type="button"
            @click="toggle"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800 hover:bg-gray-50"
            :class="{ 'bg-gray-100': isOpen }"
        >
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
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
                class="absolute top-full right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 p-4 space-y-4"
                @click.stop
            >
                <h3 class="font-semibold text-neutral-800 mb-3">Filter</h3>

                <div v-for="(opts, key) in options" :key="key">
                    <label class="block text-sm font-medium text-neutral-800 mb-1 capitalize">
                        {{ key }}
                    </label>
                    <div class="relative">
                        <select
                            :value="modelValue[key]"
                            @change="updateFilter(key, $event.target.value)"
                            class="w-full appearance-none rounded-xl border border-red-700 px-3 py-2 text-neutral-800 text-sm outline-none focus:ring-2 focus:ring-red-300"
                        >
                            <option value="all">All</option>
                            <option v-for="opt in opts" :key="opt" :value="opt">{{ opt }}</option>
                        </select>

                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    name: "FilterEmployees",
    props: {
        modelValue: {
            type: Object,
            required: true,
            default: () => ({})
        },
        options: {
            type: Object,
            required: true,
            default: () => ({})
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isOpen: false,
        };
    },
    mounted() {
        document.addEventListener("click", this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
        },
        close() {
            this.isOpen = false;
        },
        updateFilter(key, value) {
            const newFilters = { ...this.modelValue, [key]: value };
            this.$emit("update:modelValue", newFilters);
        },
        handleClickOutside(event) {
            if (this.$refs.filterBox && !this.$refs.filterBox.contains(event.target)) {
                this.isOpen = false;
            }
        }
    }
};
</script>
