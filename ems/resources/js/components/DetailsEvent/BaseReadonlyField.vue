<template>
    <div>
        <label class="mb-1 block text-xl font-medium text-neutral-800">{{ label }}</label>
        <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5 text-md font-medium">
            <input 
                :value="value" 
                disabled 
                class="w-full bg-transparent text-neutral-400 outline-none text-md font-medium" 
            />
            <span v-if="icon" class="text-neutral-400">
                <Icon :icon="icon" class="h-6 w-6" />
            </span>
            <slot name="append"></slot>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";

export default {
    name: "BaseReadonlyField",
    components: { Icon },
    props: {
        label: { type: String, required: true },
        value: { type: [String, Number], default: "" },
        icon: { type: String, default: "" }
    }
};
</script>