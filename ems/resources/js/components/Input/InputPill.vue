<template>
    <div class="w-full">
        <input v-bind="$attrs" :type="type" :inputmode="inputmode" :placeholder="placeholder" :disabled="disabled"
            class="
                rounded-[15px] h-[50px] border px-4 py-2.5 text-base transition
                focus:outline-none focus:ring-2
                placeholder-red-300
                disabled:placeholder:text-neutral-300
            " :class="[
                disabled
                    ? 'bg-neutral-50 text-neutral-400 cursor-not-allowed'
                    : 'bg-white text-neutral-800',
                error
                    ? 'border-red-700 focus:ring-red-300 focus:border-red-700'
                    : 'border-neutral-200 focus:ring-red-300 focus:border-red-200'
            ]" :value="modelValue" @input="$emit('update:modelValue', $event.target?.value)" />
        <p class="text-xs text-red-500 min-h-[16px]">
            {{ error || '\u00A0' }}
        </p>
    </div>
</template>

<script setup>
defineOptions({ inheritAttrs: false })

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
