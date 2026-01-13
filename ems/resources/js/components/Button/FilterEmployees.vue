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
        class="absolute top-full right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 p-4 space-y-4"
        @click.stop
      >
        <h3 class="font-semibold text-neutral-800 mb-3">Filter</h3>

        <div v-for="(opts, key) in options" :key="key" class="mb-4">
          <label class="block text-sm font-medium text-neutral-800 mb-1 capitalize">
            {{ key }}
          </label>

          <div class="relative">
            <div
              @click="toggleDropdown(key)"
              class="w-full rounded-xl border border-red-700 px-3 py-2 text-neutral-800 text-sm flex items-center justify-between cursor-pointer overflow-hidden"
            >
              <span class="truncate pr-2 font-medium">
                {{ getSelectedLabel(key, opts) }}
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
              v-if="openKey === key"
              class="absolute z-50 mt-1 w-full bg-white rounded-xl shadow-md border border-gray-200 max-h-56 overflow-y-auto py-2"
            >
              <div
                class="flex items-center pl-5 py-2 text-[15px] font-bold cursor-pointer hover:bg-gray-50 transition-colors"
                @click="toggleSelectAll(key, opts)"
              >
                <span style="color: #20b5ff">Select All</span>
              </div>

              <div
                v-for="opt in opts"
                :key="opt"
                class="flex items-center gap-3 px-4 py-2 text-sm cursor-pointer hover:bg-gray-50"
                @click="toggleOption(key, opt)"
              >
                <div class="flex items-center">
                  <input
                    type="checkbox"
                    :checked="modelValue[key]?.includes(opt)"
                    readonly
                    class="custom-checkbox"
                  />
                </div>
                <span class="text-neutral-700 font-medium">{{ opt }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Custom Checkbox: พื้นแดง ติ๊กขาว */
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

<script>

export default {
  props: {
    modelValue: { type: Object, required: true },
    options: { type: Object, required: true },
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

    // --- ส่วนแสดงผลชื่อ (Logic: All <-> ชื่อตัวเลือก) ---
    getSelectedLabel(key, opts) {
      // 1. ตรวจสอบและดึงค่า (ป้องกัน null/undefined)
      let selected = Array.isArray(this.modelValue[key]) ? this.modelValue[key] : [];

      // 2. ถ้าไม่ได้เลือกอะไรเลย (length 0) -> แสดง "All"
      if (selected.length === 0) {
        return "All";
      }

      // 3. ถ้าเลือกครบทุกตัว -> แสดง "All" (ถ้าไม่อยากได้ให้ลบ 3 บรรทัดนี้ออก)
      if (opts && selected.length === opts.length) {
        return "All";
      }

      // 4. แสดงชื่อตัวเลือกที่ถูกเลือก
      return selected.join(", ");
    },

    // --- ส่วนกดเลือก (Toggle) ---
    toggleOption(key, value) {
      // 1. ดึง Array ปัจจุบัน (ป้องกัน null/undefined)
      let current = Array.isArray(this.modelValue[key]) ? [...this.modelValue[key]] : [];

      const index = current.indexOf(value);

      if (index > -1) {
        // มีอยู่แล้ว -> เอาออก (ถ้าเหลือ 0 จะกลับไปเป็น All เอง)
        current.splice(index, 1);
      } else {
        // ยังไม่มี -> ใส่เพิ่ม (All จะหายไป)
        current.push(value);
      }

      this.$emit("update:modelValue", {
        ...this.modelValue,
        [key]: current,
      });
    },

    toggleSelectAll(key, options) {
      const isAll = this.isAllSelected(key, options);
      this.$emit("update:modelValue", {
        ...this.modelValue,
        [key]: isAll ? [] : [...options],
      });
    },

    isAllSelected(key, options) {
      const selected = Array.isArray(this.modelValue[key]) ? this.modelValue[key] : [];
      return selected.length === options.length && options.length > 0;
    },

    handleClickOutside(event) {
      if (
        this.$refs.filterBox &&
        !this.$refs.filterBox.contains(event.target)
      ) {
        this.isOpen = false;
        this.openKey = null;
      }
    },
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
  },
};

</script>
