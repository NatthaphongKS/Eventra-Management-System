<template>
    <div class="relative" ref="dropdownRef">
        <button
            type="button"
            @click="toggle"
            class="flex w-[160px] flex-none items-center justify-between rounded-2xl border px-3 py-2.5 text-md font-medium shadow-sm transition bg-white"
            :class="modelValue ? 'border-rose-700' : 'border-neutral-400 hover:border-rose-300'"
        >
            <span class="truncate text-neutral-800">
                {{ displayLabel }}
            </span>
            <Icon 
                icon="mdi:chevron-down" 
                class="h-6 w-6 shrink-0 transition-transform text-rose-700" 
                :class="{ 'rotate-180': isOpen }" 
            />
        </button>

        <div 
            v-if="isOpen" 
            class="absolute right-0 z-50 mt-2 w-[160px] rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-lg max-h-72 overflow-auto"
        >
            <button 
                @click="select('')" 
                class="block w-full truncate rounded-xl px-3 py-2 text-left text-sm text-neutral-800 hover:bg-rose-600 hover:text-white"
            >
                All
            </button>
            
            <button
                v-for="(opt, index) in options"
                :key="index"
                @click="select(getValue(opt))"
                class="block w-full truncate rounded-xl px-3 py-2 text-left text-sm text-neutral-800"
                :class="getValue(opt) === modelValue ? 'bg-rose-600 text-white' : 'hover:bg-rose-600 hover:text-white'"
            >
                {{ getLabel(opt) }}
            </button>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";

export default {
    name: "BaseFilterDropdown",
    components: { Icon },
    props: {
        // ข้อความแสดงบนปุ่มเมื่อยังไม่ได้เลือกค่า
        label: { type: String, default: "Select" },
        // ค่าที่ถูกเลือก (รองรับ v-model)
        modelValue: { type: [String, Number], default: "" },
        // รายการตัวเลือก (Array ของ String หรือ Object {label, value})
        options: { type: Array, default: () => [] }, 
    },
    emits: ["update:modelValue"],
    data() {
        return {
            isOpen: false, // สถานะการเปิด/ปิด Dropdown
        };
    },
    computed: {
        /**
         * ชื่อฟังก์ชัน: displayLabel
         * คำอธิบาย: คำนวณข้อความที่จะแสดงบนปุ่ม (แสดง Label ถ้ายังไม่เลือก, แสดงชื่อตัวเลือกถ้าเลือกแล้ว)
         * Input: this.modelValue, this.options
         * Output: String
         */
        displayLabel() {
            if (!this.modelValue) return this.label;
            const found = this.options.find(opt => this.getValue(opt) === this.modelValue);
            return found ? this.getLabel(found) : this.modelValue;
        }
    },
    mounted() {
        // เพิ่ม Event Listener เพื่อตรวจสอบการคลิกนอกพื้นที่
        document.addEventListener("click", this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
        },
        /**
         * ชื่อฟังก์ชัน: select
         * คำอธิบาย: เลือกค่าและส่ง Event กลับไปที่ Parent
         * Input: val (ค่าที่เลือก)
         */
        select(val) {
            // ถ้าคลิกซ้ำค่าเดิม ให้ยกเลิกการเลือก (Toggle off)
            const newValue = this.modelValue === val ? "" : val;
            this.$emit("update:modelValue", newValue);
            this.isOpen = false;
        },
        // Helper: ดึงค่า Value จาก Option
        getValue(opt) {
            return typeof opt === 'object' && opt !== null ? opt.value : opt;
        },
        // Helper: ดึงค่า Label จาก Option
        getLabel(opt) {
            return typeof opt === 'object' && opt !== null ? opt.label : opt;
        },
        // Helper: ปิด Dropdown เมื่อคลิกข้างนอก
        handleClickOutside(e) {
            if (this.$refs.dropdownRef && !this.$refs.dropdownRef.contains(e.target)) {
                this.isOpen = false;
            }
        },
    },
};
</script>