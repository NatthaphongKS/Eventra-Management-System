<template>
  <div
    class="w-full h-[516px] rounded-[20px] flex-col bg-white p-8 rounded-[20px] shadow-[0_10px_25px_-12px_rgba(0,0,0,0.25)]"
  >
    <!-- Header -->
   <div class="relative z-50 mb-6 flex items-center justify-between">
      <div>
        <h3 class="mb-4 text-left font-semibold text-neutral-700  text-2xl">
          Event Participation
        </h3>

        <!-- Legend -->
        <div class="mt-2 flex gap-6 text-[14px] text-neutral-400 font-medium">
          <div class="flex items-center gap-2">
            <span class="h-4 w-4 border border-neutral-300 rounded bg-green-600"></span>
            Attending
          </div>
          <div class="flex items-center gap-2">
            <span class="h-4 w-4 border border-neutral-300 rounded bg-red-600"></span>
            Not Attending
          </div>
          <div class="flex items-center gap-2">
            <span class="h-4 w-4 border border-neutral-300 rounded bg-sky-600"></span>
            Pending
          </div>
        </div>
      </div>

      <!-- Filter -->
       <div class="relative w-[200px]">
        <!-- Selected -->
        <button
          @click="isOpen = !isOpen"
          class="flex w-full h-[48px] items-center justify-between rounded-[20px] border border-neutral-300 bg-white px-5 py-3 text-[22px] font-semibold text-neutral-700"
        >
          {{ viewType === 'team' ? 'Team' : 'Department' }}

          <!-- Arrow -->
          <svg
            class="h-6 w-6 text-red-700 transition-transform"
            :class="{ 'rotate-180': isOpen }"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
          </svg>
        </button>

        <!-- Dropdown -->
        <div
          v-if="isOpen"
          class="absolute left-0 mt-2 w-full rounded-[20px] border border-neutral-300 bg-white p-2 shadow-lg"
        >
          <div
            @click="select('team')"
            class="cursor-pointer rounded-xl px-4 py-3 text-[20px] h-[48px] font-medium"
            :class="viewType === 'team'
              ? 'bg-red-100 text-neutral-800'
              : 'text-neutral-500 hover:bg-neutral-100'"
          >
            Team
          </div>

          <div
            @click="select('department')"
            class="mt-1 cursor-pointer rounded-xl px-4 py-3 text-[20px] h-[48px] font-medium"
            :class="viewType === 'department'
              ? 'bg-roredse-100 text-neutral-800'
              : 'text-neutral-500 hover:bg-neutral-100'"
          >
            Department
          </div>
        </div>
      </div>

    </div>


    <!-- Chart Body -->
    <div class="flex items-start">
      <!-- Y Axis -->
      <div
        class="flex h-[310px] min-w-[40px] flex-col justify-between border-r pr-3 text-right"
      >
        <div
          v-for="(tick, index) in yAxisTicks"
          :key="index"
          class="translate-y-[-50%] text-[11px] font-medium text-neutral-400 last:translate-y-[50%] first:translate-y-0"
        >
          {{ tick }}
        </div>
      </div>

      <!-- Scroll Area -->
      <div
        class="relative max-w-full flex-1 overflow-x-auto overflow-y-hidden pl-4"
        :class="{ 'flex-none': displayData.length < 4 }"
      >
        <!-- Content -->
        <div
          class="relative z-10 flex min-w-max items-start justify-start gap-8 pt-2"
        >
        <!-- Grid Lines -->
        <div
          class="pointer-events-none absolute inset-0 z-0 flex h-[310px] flex-col justify-between"
        >
          <div
            v-for="n in 5"
            :key="n"
            class="border-b border-dashed border-slate-200"
          ></div>
        </div>

          <div
            v-for="(item, index) in displayData"
            :key="index"
            class="relative flex min-w-[80px] flex-col items-center gap-2"
          >
            <!-- Bars -->
            <div class="flex h-[300px] items-end gap-2">
              <div
                class="w-10 cursor-pointer rounded-xl transition-all duration-500 hover:-translate-y-0.5"
                :style="{ height: getBarHeight(item.attending) + '%' }"
                title="Attending"
                style="background: linear-gradient(180deg,#dcfce7,#00a73d)"
              ></div>

              <div
                class="w-10 cursor-pointer rounded-xl transition-all duration-500 hover:-translate-y-0.5"
                :style="{ height: getBarHeight(item.notAttending) + '%' }"
                title="Not Attending"
                style="background: linear-gradient(180deg,#ffe2e3,#c10008)"
              ></div>

              <div
                class="w-10 cursor-pointer rounded-xl transition-all duration-500 hover:-translate-y-0.5"
                :style="{ height: getBarHeight(item.pending) + '%' }"
                title="Pending"
                style="background: linear-gradient(180deg,#DFF3FE,#0084D1)"
              ></div>
            </div>

            <!-- Label -->
            <div
              class="max-w-[90px] break-words text-center text-[15px] font-medium text-neutal-700"
            >
              {{ item.name }}
            </div>

            <!-- Divider -->
            <span
              v-if="index !== displayData.length - 1"
              class="absolute right-[-16px] top-0 h-full border-r border-dashed border-slate-200"
            ></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
export default {
  name: "GraphEventParticipation",
  props: {
    eventId: { type: [Number, String], required: true },
    data: { type: Object, default: () => ({ departments: [] }) },
    options: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      viewType: "department",
      originalDepartments: [],
      originalTeams: [],
      filteredData: [],
    };
  },
  computed: {
    displayData() {
      return this.filteredData;
    },
    yAxisTicks() {
      const max = this.getMaxValue();
      return [
        max, // 100%
        Math.round(max * 0.75), // 75%
        Math.round(max * 0.5), // 50%
        Math.round(max * 0.25), // 25%
        0, // 0%
      ];
    },
  },
  watch: {
    data: {
      immediate: true,
      deep: true,
      handler(newData) {
        if (newData) {
          this.originalDepartments = newData.departments ? [...newData.departments] : [];
          this.originalTeams = newData.teams ? [...newData.teams] : [];
          this.updateDisplayData();
        }
      },
    },
  },
  methods: {
    getMaxValue() {
      if (this.filteredData.length === 0) return 10;
      let maxSingleValue = 0;
      this.filteredData.forEach((item) => {
        const maxInItem = Math.max(
          item.attending || 0,
          item.notAttending || 0,
          item.pending || 0
        );
        if (maxInItem > maxSingleValue) {
          maxSingleValue = maxInItem;
        }
      });
      return maxSingleValue || 10;
    },
    getBarHeight(value) {
      if (!value || value === 0) return 0;
      const maxValue = this.getMaxValue();
      return (value / maxValue) * 100;
    },
    onViewTypeChange() {
      this.updateDisplayData();
    },
    updateDisplayData() {
      const sourceData =
        this.viewType === "department" ? this.originalDepartments : this.originalTeams;
      this.filteredData = [...sourceData];
    },
  },
};
</script>
