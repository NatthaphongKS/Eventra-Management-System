<template>
<div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">{{
            label
        }}</label>
        <div class="relative">
            <button
                type="button"
                class="w-full h-9 px-3 rounded-md border border-rose-300 bg-white text-left flex items-center justify-between"
                @click="$emit('toggle', field)"
            >
                <span class="truncate">{{ modelValue }}</span>
                <span class="ml-2 text-gray-400">▾</span>
            </button>

            <div
                v-if="isOpen"
                class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
            >
                <ul class="max-h-56 overflow-auto py-1">
                    <li
                        v-for="opt in options"
                        :key="field + '-' + opt"
                        @click="$emit('choose', field, opt)"
                        class="px-3 py-2 cursor-pointer hover:bg-rose-50"
                    >
                        {{ opt }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SelectField",
    props: {
        label: { type: String, required: true },
        field: { type: String, required: true },
        modelValue: { type: String, default: "all" },
        options: { type: Array, default: () => [] },
        isOpen: { type: Boolean, default: false },
    },
    emits: ["toggle", "choose"],
};
</script>
