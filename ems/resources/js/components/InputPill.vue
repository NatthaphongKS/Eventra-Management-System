<template>
  <div>
    <input :type="type" :inputmode="inputmode" :placeholder="placeholder" :disabled="disabled" class="w-full rounded-full border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 transition
             placeholder-[#E1B4B4]" :class="[
              disabled
                ? 'bg-gray-50 text-gray-400 cursor-not-allowed'
                : 'bg-white text-gray-700',
              error
                ? 'border-red-500 focus:ring-red-300 focus:border-red-500'
                : 'border-rose-300 focus:ring-rose-300 focus:border-rose-400'
            ]" :value="modelValue" @input="$emit('update:modelValue', $event.target && $event.target.value)" />
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
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
