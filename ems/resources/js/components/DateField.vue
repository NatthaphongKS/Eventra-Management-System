<template>
    <div class="field">
        <label>{{ label }} <span v-if="required" class="req">*</span></label>
        <div class="input with-icon">
            <svg class="icon" viewBox="0 0 24 24">
                <path d="M7 2v3M17 2v3M3 9h18M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"
                    fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
            <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" type="date"
                :required="required" :placeholder="placeholder" />
        </div>
    </div>
</template>

<script>
export default {
    name: "DateField",
    props: {
        modelValue: { type: String, default: "" },     // v-model
        required: { type: Boolean, default: true },
        label: { type: String, default: "Date" },
        placeholder: { type: String, default: "dd/mm/yy" },
    },
    emits: ["update:modelValue"],
};
</script>
