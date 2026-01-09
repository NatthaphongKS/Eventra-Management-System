<template>
    <div class="w-full">
        <label
            :for="id"
            class="block text-xl font-medium text-neutral-800 mb-1"
        >
            {{ label }}
            <span v-if="required" class="text-rose-700"> *</span>
        </label>

        <div class="w-full">
            <slot :id="id" />
        </div>

        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    required: Boolean,
    error: String,
    id: String, // เพิ่ม prop id
});
</script>
