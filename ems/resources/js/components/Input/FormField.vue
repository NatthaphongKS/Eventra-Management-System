<!-- FormField.vue -->
<template>
    <div class="w-full">
        <label class="block text-sm font-medium text-neutral-800 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500"> *</span>
        </label>

        <!-- ช่องกรอกให้กินความกว้างเต็มและอยู่บรรทัดล่าง -->
        <div class="w-full">
            <slot />
        </div>

        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    required: Boolean,
    error: String
})
</script>
