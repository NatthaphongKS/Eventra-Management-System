<!-- /**
 * ชื่อไฟล์: TeamFilter.vue
 * คำอธิบาย: Filter component ของ Team (dropdown style ตามแบบ FilterEmployees)
 * Input: modelValue (filters), departments (options)
 * Output: Update filter values
 * ชื่อผู้เขียน/แก้ไข: Natthaphong Kongsinl
 * วันที่จัดทำ/แก้ไข: 2026-03-10
 */ -->

<template>
    <div class="relative z-50" ref="filterBox">
        <button
            type="button"
            @click="toggle"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800 hover:bg-gray-50"
            :class="{ 'bg-gray-100': isOpen }"
        >
            <svg
                class="w-6 h-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <line x1="4" y1="7" x2="20" y2="7" />
                <line x1="6" y1="12" x2="16" y2="12" />
                <line x1="8" y1="17" x2="12" y2="17" />
            </svg>
            <span>Filter</span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute top-full left-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 p-4 space-y-4"
                @click.stop
            >
                <h3 class="font-semibold text-neutral-800 mb-3">Filter</h3>

                <!-- Department Filter -->
                <div v-if="departments && departments.length > 0" class="mb-4">
                    <label class="block text-sm font-medium text-neutral-800 mb-1 capitalize">
                        Department
                    </label>

                    <div class="relative">
                        <div
                            @click="toggleDropdown('department')"
                            class="w-full rounded-xl border border-red-700 px-3 py-2 text-neutral-800 text-sm flex items-center justify-between cursor-pointer overflow-hidden"
                        >
                            <span class="truncate pr-2 font-medium">
                                {{ getSelectedLabel('department') }}
                            </span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-red-700 shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </div>

                        <div
                            v-if="openKey === 'department'"
                            class="absolute z-50 mt-1 w-full bg-white rounded-xl shadow-md border border-gray-200 max-h-56 overflow-y-auto overflow-x-hidden py-2"
                        >
                            <div
                                class="flex items-center pl-5 py-2 text-[15px] font-bold cursor-pointer hover:bg-gray-50 transition-colors"
                                @click="toggleSelectAll('department')"
                            >
                                <span style="color: #20b5ff">Select All</span>
                            </div>

                            <div
                                v-for="dept in departments"
                                :key="dept.id"
                                class="flex items-center gap-3 px-4 py-2 text-sm cursor-pointer hover:bg-gray-50"
                                @click="toggleOption('department', String(dept.id))"
                            >
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="modelValue.department?.includes(String(dept.id))"
                                        readonly
                                        class="custom-checkbox"
                                    />
                                </div>
                                <span class="text-neutral-700 font-medium">{{ dept.dpm_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Object,
            default: () => ({ department: [] }),
        },
        departments: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["update:modelValue"],
    data() {
        return {
            isOpen: false,
            openKey: null,
        };
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
            this.openKey = null;
        },
        toggleDropdown(key) {
            this.openKey = this.openKey === key ? null : key;
        },
        getSelectedLabel(key) {
            let selected = Array.isArray(this.modelValue[key]) ? this.modelValue[key] : [];

            if (selected.length === 0) {
                return "All";
            }

            if (key === "department" && this.departments && selected.length === this.departments.length) {
                return "All";
            }

            const getNames = () => {
                if (key === "department") {
                    return selected.map((id) => {
                        const dept = this.departments.find((d) => String(d.id) === String(id));
                        return dept ? dept.dpm_name : id;
                    });
                }
                return selected;
            };

            return getNames().join(", ");
        },
        toggleOption(key, value) {
            let current = Array.isArray(this.modelValue[key]) ? [...this.modelValue[key]] : [];
            const index = current.indexOf(value);

            if (index > -1) {
                current.splice(index, 1);
            } else {
                current.push(value);
            }

            this.$emit("update:modelValue", {
                ...this.modelValue,
                [key]: current,
            });
        },
        toggleSelectAll(key) {
            let options = [];
            if (key === "department") {
                options = this.departments.map((d) => String(d.id));
            }

            const isAll = this.isAllSelected(key, options);
            this.$emit("update:modelValue", {
                ...this.modelValue,
                [key]: isAll ? [] : options,
            });
        },
        isAllSelected(key, options) {
            const selected = Array.isArray(this.modelValue[key]) ? this.modelValue[key] : [];
            return options && options.length > 0 && selected.length === options.length;
        },
        close() {
            this.isOpen = false;
            this.openKey = null;
        },
    },
};
</script>

<style scoped>
.custom-checkbox {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 1.5px solid #d1d5db;
    border-radius: 4px;
    background-color: #fff;
    display: inline-grid;
    place-content: center;
    cursor: pointer;
    position: relative;
}

.custom-checkbox:checked {
    background-color: #b91c1c;
    border-color: #b91c1c;
}

.custom-checkbox:checked::after {
    content: "";
    width: 10px;
    height: 5px;
    border-left: 2px solid white;
    border-bottom: 2px solid white;
    transform: rotate(-45deg) translate(1px, -1px);
}
</style>
