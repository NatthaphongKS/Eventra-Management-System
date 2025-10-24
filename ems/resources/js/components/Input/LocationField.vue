<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    modelValue: string | null;
    placeholder?: string;
    error?: string | null;
}>();
const emit = defineEmits<{ "update:modelValue": [string | null] }>();

const cls = computed(() =>
    [
        "flex items-center gap-2 h-12 w-full rounded-2xl px-4",
        "border bg-white/90 shadow-sm",
        props.error
            ? "border-red-500 text-red-600 focus-within:ring-2 focus-within:ring-red-200"
            : "border-red-400 text-gray-700 focus-within:ring-2 focus-within:ring-red-100",
    ].join(" ")
);
</script>

<template>
    <div :class="cls">
        <input class="flex-1 bg-transparent outline-none placeholder:text-red-300/90" type="text"
            :value="modelValue || ''" @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            :placeholder="placeholder || 'Location/Building/Room Name'" />
        <!-- pin icon -->
        <svg class="text-red-600 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" />
        </svg>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
</template>
