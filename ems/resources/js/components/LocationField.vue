<template>
    <div class="field">
        <label>{{ label }} <span v-if="required" class="req">*</span></label>
        <div class="input with-pin">
            <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" type="text"
                :placeholder="placeholder" :required="required" />
            <span class="pin">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" fill="none" stroke="currentColor"
                        stroke-width="1.6" />
                    <circle cx="12" cy="10" r="2.7" fill="none" stroke="currentColor" stroke-width="1.6" />
                </svg>
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: "LocationField",
    props: {
        modelValue: { type: String, default: "" },     // v-model
        required: { type: Boolean, default: true },
        label: { type: String, default: "Location" },
        placeholder: { type: String, default: "Location/Building/Room Name" },
    },
    emits: ["update:modelValue"],
};
</script>
