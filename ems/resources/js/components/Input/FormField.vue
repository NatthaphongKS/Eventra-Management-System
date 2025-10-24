<template>
    <div class="grid grid-cols-1 sm:grid-cols-[184px,1fr] items-center gap-1 sm:gap-3">
        <label class="text-sm font-medium text-gray-700">
            {{ label }}<span v-if="required" class="text-red-500"> *</span>
        </label>
        <div class="w-full">
            <slot />
            <p v-if="hint" class="mt-1 text-xs text-gray-400">{{ hint }}</p>
        </div>
    </div>
</template>
<script setup>
defineProps({
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    hint: { type: String, default: "" },
})
</script>
