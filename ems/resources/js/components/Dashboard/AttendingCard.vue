<template>
  <div
    class="relative w-full rounded-2xl p-5 font-sans transition-all duration-200 shadow-sm mb-4 border-l-[4px] border-l-green-600 hover:-translate-y-0.5 hover:shadow-md"
    :class="[
      'bg-green-100',
    ]"
  >
    <div class="mb-2 flex items-center gap-2 text-gray-700">
      <div class="flex items-center justify-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-7 h-7 sm:w-9 sm:h-9"
          viewBox="0 0 24 24"
        >
          <path
            fill="currentColor"
            d="m21.1 12.5l1.4 1.41l-6.53 6.59L12.5 17l1.4-1.41l2.07 2.08zM10 17l3 3H3v-2c0-2.21 3.58-4 8-4l1.89.11zm1-13a4 4 0 0 1 4 4a4 4 0 0 1-4 4a4 4 0 0 1-4-4a4 4 0 0 1 4-4"
          />
        </svg>
      </div>
      <span class="text-xl sm:text-2xl font-semibold font-[poppins] text-neutral-700"
        >Accepted</span
      >
    </div>

    <div class="mb-2 flex items-center justify-between w-full ml-10">
      <div class="flex items-baseline text-green-600 leading-none">
        <span class="text-4xl sm:text-5xl font-extrabold font-[poppins]">{{ getPercentage() }}</span>
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
              class="stroke-slate-200"
              stroke-width="7"
            />
            <circle
              cx="30"
              cy="30"
              r="25"
              fill="transparent"
              class="stroke-green-600 transition-all duration-700 ease-out"
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
              {{ attending }}
            </text>
          </svg>
        </div>
      </div>
    </div>

    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between sm:gap-0 pt-2 border-t border-green-200/50 sm:border-none"
    >
      <div class="flex items-center gap-2 text-xs text-gray-600 order-2 sm:order-1">
        <span class="h-2.5 w-2.5 rounded-full bg-green-600 flex-shrink-0"></span>
        <span class="whitespace-nowrap text-base font-[poppins]">
          <b class="text-gray-800 ">{{ attending }}</b>
          Person from
          <b class="text-gray-800 ">{{ total }}</b>
        </span>
      </div>

      <button
        class="order-1 sm:order-2 w-full sm:w-auto text-center rounded-full px-4 py-2 text-sm sm:text-base font-basic font-[poppins] shadow-sm transition-all border mr-3.5"
        :class="[
          isSelected
            ? 'bg-green-600 border-green-800 text-white'
            : 'bg-white border-transparent text-gray-500 hover:bg-gray-50 hover:text-gray-700 hover:shadow-md',
            isClickable ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md' : '',
        ]"
        @click="handleClick"
      >
        View Accepted
      </button>
    </div>

    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/70 font-semibold text-green-600"
    >
      Loading...
    </div>
  </div>
</template>

<script>
export default {
  name: "AttendingCard",
  props: {
    attending: {
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
    // เพิ่ม prop นี้
    isSelected: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["showAttendingEmployees"],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 25;
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference;
      const percentage = (this.attending / this.total) * 100;
      return this.miniCircumference - (percentage / 100) * this.miniCircumference;
    },
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0;
      return Math.round((this.attending / this.total) * 100);
    },
    handleClick() {
      if (this.isClickable) {
        this.$emit("showAttendingEmployees");
      }
    },
  },
};
</script>
