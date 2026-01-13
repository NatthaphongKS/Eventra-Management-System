<template>
  <div
    @click="handleClick"
    :class="[
      'relative rounded-2xl border-l-4 border-green-500 bg-gradient-to-br from-green-100 to-green-50 p-6 font-[Inter,Poppins,sans-serif] shadow-md transition-all',
      isClickable
        ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-lg hover:shadow-green-500/20'
        : ''
    ]"
  >
    <!-- Header -->
    <div class="mb-5 flex items-center gap-3">
      <div class="flex h-8 w-8 items-center justify-center text-green-600">
        <svg
          class="h-6 w-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="m22 2-5 5 3 3z" />
        </svg>
      </div>

      <span class="text-base font-semibold text-green-700">
        Attending
      </span>
    </div>

    <!-- Content -->
    <div class="mb-5 flex items-center justify-between">
      <!-- Percentage -->
      <div class="text-[48px] font-extrabold leading-none text-green-800 md:text-[36px]">
        {{ getPercentage() }}%
      </div>

      <!-- Mini Donut -->
      <svg viewBox="0 0 60 60" class="h-[65px] w-[65px] md:h-[55px] md:w-[55px]">
        <!-- Background -->
        <circle
          cx="30"
          cy="30"
          r="25"
          fill="transparent"
          stroke="#e5e7eb"
          stroke-width="6"
        />

        <!-- Progress -->
        <circle
          cx="30"
          cy="30"
          r="25"
          fill="transparent"
          stroke="#22c55e"
          stroke-width="6"
          stroke-linecap="round"
          :stroke-dasharray="miniCircumference"
          :stroke-dashoffset="miniStrokeDashoffset"
          class="transition-[stroke-dasharray,stroke-dashoffset] duration-700 ease-in-out"
        />

        <!-- Center number -->
        <text
          x="30"
          y="36"
          text-anchor="middle"
          class="fill-green-700 text-sm font-bold"
        >
          {{ attending }}
        </text>
      </svg>
    </div>

    <!-- Footer -->
    <div class="flex flex-col gap-2">
      <div class="flex items-center gap-2 text-sm font-medium text-green-700">
        <span class="h-2 w-2 rounded-full bg-green-500"></span>
        {{ attending }} person{{ attending > 1 ? 's' : '' }}
      </div>

      <div
        class="text-sm font-medium text-blue-600 transition-colors hover:text-blue-700"
      >
        View Attendance →
      </div>
    </div>

    <!-- Loading -->
    <div
      v-if="loading"
      class="mt-2 text-center text-xs text-gray-500"
    >
      Loading...
    </div>
  </div>
</template>

<script>
export default {
  name: 'AttendingCard',
  props: {
    attending: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    loading: { type: Boolean, default: false },
    isClickable: { type: Boolean, default: false }
  },
  emits: ['showAttendingEmployees'],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 25
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference
      const percentage = (this.attending / this.total) * 100
      return this.miniCircumference -
        (percentage / 100) * this.miniCircumference
    }
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0
      return Math.round((this.attending / this.total) * 100)
    },
    handleClick() {
      if (this.isClickable) {
        this.$emit('showAttendingEmployees')
      }
    }
  }
}
</script>
