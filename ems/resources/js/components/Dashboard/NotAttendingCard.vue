<template>
  <div
    class="relative w-full rounded-2xl p-5 font-sans transition-all duration-200 shadow-sm mb-4 border-l-[4px] border-l-red-700 hover:-translate-y-0.5 hover:shadow-md"
    :class="[
      'bg-[#FFE2E3]',
    ]"

  >
    <div class="mb-2 flex items-center gap-2 text-gray-700">
      <div class="flex items-center justify-center">
        <svg
          class="w-7 h-7 sm:w-9 sm:h-9"
          xmlns="http://www.w3.org/2000/svg"
          width="30"
          height="30"
          viewBox="0 0 24 24"
        >
          <path
            fill="currentColor"
            d="M12 4a4 4 0 0 1 4 4c0 1.95-1.4 3.58-3.25 3.93L8.07 7.25A4.004 4.004 0 0 1 12 4m.28 10l6 6L20 21.72L18.73 23l-3-3H4v-2c0-1.84 2.5-3.39 5.87-3.86L2.78 7.05l1.27-1.27zM20 18v1.18l-4.86-4.86C18 14.93 20 16.35 20 18"
          />
        </svg>
      </div>
      <span class="text-xl sm:text-2xl font-semibold font-[poppins] text-neutral-700"
        >Declined</span
      >
    </div>

    <div class="mb-2 flex items-center justify-between w-full ml-10">
      <div class="flex items-baseline text-red-700 leading-none">
        <span class="text-4xl sm:text-5xl font-extrabold font-[poppins]">{{
          getPercentage()
        }}</span>
        <span class="ml-1 text-xl sm:text-2xl font-bold">%</span>
      </div>

      <div class="flex items-center justify-center mr-20">
        <div class="relative">
          <svg
            class="w-20 h-20 sm:w-[100px] sm:h-[105px] -rotate-90 transition-all duration-300"
            viewBox="0 0 60 60"
          >
            <circle
              cx="30"
              cy="30"
              r="25"
              fill="transparent"
              class="stroke-white/50"
              stroke-width="7"
            />
            <circle
              cx="30"
              cy="30"
              r="25"
              fill="transparent"
              class="stroke-red-700 transition-all duration-700 ease-out"
              stroke-width="5"
              stroke-linecap="round"
              :stroke-dasharray="miniCircumference"
              :stroke-dashoffset="miniStrokeDashoffset"
            />
            <text
              x="30"
              y="35"
              text-anchor="middle"
              class="fill-gray-700 text-[12px] sm:text-[14px] font-bold"
              transform="rotate(90 30 30)"
            >
              {{ notAttending }}
            </text>
          </svg>
        </div>
      </div>
    </div>

    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between sm:gap-0 pt-2 border-t border-red-200/50 sm:border-none"
    >
      <div class="flex items-center gap-2 text-xs text-gray-600 order-2 sm:order-1">
        <span class="h-2.5 w-2.5 rounded-full bg-red-700 flex-shrink-0"></span>
        <span class="whitespace-nowrap  text-base font-[poppins]">
          <b class="text-gray-800 ">{{
            notAttending
          }}</b>
          Person from
          <b class="text-gray-800 ">{{
            total
          }}</b>
        </span>
      </div>

      <button
        class="order-1 sm:order-2 w-full sm:w-auto text-center rounded-full px-4 py-2 text-sm sm:text-base font-basic font-[poppins] shadow-sm transition-all border mr-3.5"
        :class="[
          isSelected
            ? 'bg-red-600 border-red-800 text-white'
            : 'bg-white border-transparent text-neutral-500 hover:bg-gray-50 hover:text-gray-700 hover:shadow-md',
            isClickable ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : '',
        ]"
        @click="handleClick"
      >
        View Declined
      </button>
    </div>

    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/70 font-semibold text-red-700"
    >
      Loading...
    </div>
  </div>
</template>

<script>
export default {
  name: "NotAttendingCard",
  props: {
    notAttending: {
      type: Number,
      default: 0,
    },
    total: {
      type: Number,
      default: 0,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    isClickable: {
      type: Boolean,
      default: false,
    },
    // เพิ่ม Prop นี้เพื่อรับค่าสถานะการถูกเลือก
    isSelected: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["showNotAttendingEmployees"],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 25;
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference;
      const percentage = (this.notAttending / this.total) * 100;
      return this.miniCircumference - (percentage / 100) * this.miniCircumference;
    },
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0;
      return Math.round((this.notAttending / this.total) * 100);
    },
    handleClick() {
      if (this.isClickable) {
        this.$emit("showNotAttendingEmployees");
      }
    },
  },
};
</script>
