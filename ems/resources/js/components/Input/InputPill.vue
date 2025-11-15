<template>
    <div class="w-full">
        <input v-bind="$attrs" :type="type" :inputmode="inputmode" :placeholder="placeholder" :disabled="disabled"
            class="rounded-full border px-4 py-2.5 text-sm transition
           bg-white placeholder-red-300
           focus:outline-none focus:ring-2" :class="[
            disabled
                ? 'bg-gray-50 text-gray-400 cursor-not-allowed'
                : 'text-neutral-800',
            error
                ? 'border-red-700 focus:ring-red-300 focus:border-red-700'
                : 'border-neutral-200 focus:ring-red-300 focus:border-neutral-200'
        ]" :value="modelValue" @input="$emit('update:modelValue', $event.target && $event.target.value)" />
        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>

</template>

<script setup>
defineOptions({ inheritAttrs: false })  // สำคัญ!
defineProps({

    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: '' },
    type: { type: String, default: 'text' },
    inputmode: { type: String, default: undefined },
    error: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>
