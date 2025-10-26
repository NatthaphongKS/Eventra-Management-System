<template>
    <div class="relative">
        <!-- Filter Button -->
        <button 
            @click="$emit('toggle')"
            class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors"
            :class="[
                hasActiveFilters ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'
            ]"
            title="Filter">
            <span class="material-symbols-outlined text-xl">
                tune
            </span>
        </button>

        <!-- Filter Panel -->
        <div 
            v-if="show"
            class="absolute right-0 top-12 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-[300px] p-4"
        >
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-700">Filters</h3>
                <button 
                    @click="clearAllFilters"
                    class="text-sm text-red-600 hover:text-red-700"
                    v-if="hasActiveFilters"
                >
                    Clear All
                </button>
            </div>

            <!-- Filter Fields -->
            <div class="space-y-4">
                <div 
                    v-for="field in filterFields" 
                    :key="field.fieldKey"
                    class="space-y-2"
                >
                    <label class="block text-sm font-medium text-gray-700">
                        {{ field.label }}
                    </label>
                    
                    <!-- Select Field -->
                    <select 
                        v-if="field.fieldType === 'select'"
                        :value="modelValue[field.fieldKey] || field.allValue"
                        @change="updateFilter(field.fieldKey, $event.target.value)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                        <option :value="field.allValue">All {{ field.label }}</option>
                        <option 
                            v-for="option in field.fieldOptions" 
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 mt-6">
                <button 
                    @click="applyFilters"
                    class="flex-1 px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800 transition-colors"
                >
                    Apply
                </button>
                <button 
                    @click="$emit('toggle')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EmployeeFilter',
    emits: ['update:modelValue', 'filter', 'toggle'],
    props: {
        modelValue: {
            type: Object,
            default: () => ({})
        },
        filterFields: {
            type: Array,
            default: () => []
        },
        show: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localFilters: { ...this.modelValue }
        }
    },
    computed: {
        hasActiveFilters() {
            return this.filterFields.some(field => {
                const value = this.modelValue[field.fieldKey];
                return value && value !== field.allValue;
            });
        }
    },
    watch: {
        modelValue: {
            handler(newVal) {
                this.localFilters = { ...newVal };
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        updateFilter(fieldKey, value) {
            this.localFilters = {
                ...this.localFilters,
                [fieldKey]: value
            };
        },
        applyFilters() {
            this.$emit('update:modelValue', { ...this.localFilters });
            this.$emit('filter', { ...this.localFilters });
            this.$emit('toggle'); // Close panel
        },
        clearAllFilters() {
            const clearedFilters = {};
            this.filterFields.forEach(field => {
                clearedFilters[field.fieldKey] = field.allValue;
            });
            this.localFilters = { ...clearedFilters };
        }
    }
}
</script>