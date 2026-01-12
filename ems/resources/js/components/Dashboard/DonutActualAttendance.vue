<template>
  <div class="flex h-[516px] w-[604px] flex-col bg-white p-8 rounded-[20px] shadow-[0_10px_25px_-12px_rgba(0,0,0,0.25)]">
    <!-- Title -->
    <h3
      class="mb-4 text-left font-semibold text-neutral-700  text-2xl"
    >
      Actual Attendance
    </h3>

    <!-- Loading -->
    <div
      v-if="isLoading"
      class="flex flex-1 flex-col items-center justify-center text-gray-500"
    >
      <div
        class="mb-3 h-8 w-8 animate-spin rounded-full border-t-green-600"
      ></div>
      <p class="text-sm">กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Chart -->
    <div
      v-else
      class="mb-3 flex flex-1 items-center justify-center"
    >
      <div
        class="relative flex items-center justify-center "
      >
        <svg viewBox="0 0 348 348" class="h-[348px] w-[348px] ">
          <!-- Background -->
          <circle
            cx="174"
            cy="174"
            r="150"
            fill="transparent"
            stroke="#F5F5F5"
            stroke-width="36"
             filter="url(#softDonutShadow)"
          />


          <!-- Progress -->
          <circle
            cx="174"
            cy="174"
            r="150"
            fill="transparent"
            stroke="url(#donutGradient)"
            stroke-width="36"
            stroke-linecap="round"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="strokeDashoffset"
            class="transition-[stroke-dashoffset] duration-1000 ease-in-out "
          />

          <!-- Gradient -->
          <defs>
            <linearGradient id="donutGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#00A73D" />
              <stop offset="100%" stop-color="#00A73D" />
            </linearGradient>
            <defs>
  <filter
    id="softDonutShadow"
    x="-15%"
    y="-15%"
    width="130%"
    height="130%"
  >
    <feDropShadow
      dx="0"
      dy="4"
      stdDeviation="2"
      flood-color="rgba(0,0,0,0.25)"
    />
  </filter>
</defs>

          </defs>
        </svg>
        <span class="absolute flex h-[210px] w-[210px] items-center border-2 border-neutral-200  justify-center rounded-full bg-gradient-to-b from-green-200 to-white"></span>

        <!-- Center text -->
        <div
          class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-center"
        >
          <div
            class="mb-1 text-[64px] font-semibold leading-none text-neutral-800"
          >
            {{ displayAttendance }}
          </div>
          <div
            class="text-base font-medium text-neutral-400 mt-6"
          >
            Person
          </div>
        </div>
      </div>
    </div>

    <!-- Info -->
    <div
      v-if="!isLoading"
      class="flex-shrink-0 text-center"
    >
      <div
        class="flex items-center justify-center gap-2 px-2"
      >
        <span class="h-[18px] w-[18px] flex-shrink-0 rounded-full bg-green-600"></span>
        <span
          class="text-base leading-snug text-neutral-500"
        >
          Attended
          <span class ="text-base font-semibold text-neutral-700 ">{{ attendancePercentage.toFixed(2) }}%</span>
           Actual attendance
          <span class ="text-base font-semibold text-neutral-700 ">{{ displayAttendance }}</span>
           Person from
           <span class ="text-base font-semibold text-neutral-700 ">{{ displayTotal }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DonutActualAttendance',
  props: {
    eventId: {
      type: [Number, String],
      required: true
    },
    attendanceData: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      isLoading: false
    }
  },
  computed: {
    circumference() {
      return 2 * Math.PI * 150
    },
    attendancePercentage() {
      if (!this.displayTotal || this.displayTotal === 0) return 0
      return (this.displayAttendance / this.displayTotal) * 100
    },
    strokeDashoffset() {
      return (
        this.circumference -
        (this.attendancePercentage / 100) * this.circumference
      )
    },
    displayAttendance() {
      // Use props data from parent
      return this.attendanceData?.attending || 0;
    },
    displayTotal() {
      // Use props data from parent
      return this.attendanceData?.total || 0;
    }
  },
  watch: {
    loading: {
      handler(newVal) {
        this.isLoading = newVal;
      },
      immediate: true
    }
  },
  methods: {}
};
</script>

