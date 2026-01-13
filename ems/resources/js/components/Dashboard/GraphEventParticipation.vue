<template>
  <div class="event-participation-graph">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Event Participation</h3>
        <div class="chart-legend">
          <div class="legend-item">
            <div class="legend-indicator attending"></div>
            <span class="legend-text">Attending</span>
          </div>
          <div class="legend-item">
            <div class="legend-indicator not-attending"></div>
            <span class="legend-text">Not Attending</span>
          </div>
          <div class="legend-item">
            <div class="legend-indicator pending"></div>
            <span class="legend-text">Pending</span>
          </div>
        </div>
      </div>

      <div class="filter-dropdown">
        <select v-model="viewType" @change="onViewTypeChange" class="department-select">
          <option value="department">Department</option>
          <option value="team">Team</option>
        </select>
      </div>
    </div>

    <div class="chart-body">
      <div class="y-axis">
        <div class="y-label" v-for="(tick, index) in yAxisTicks" :key="index">
          {{ tick }}
        </div>
      </div>

      <div
        class="bar-chart-scroll-area"
        :class="{ 'is-compact': displayData.length < 4 }"
      >
        <div class="bar-chart-content">
          <div class="grid-lines">
            <div class="grid-line" v-for="n in 5" :key="n"></div>
          </div>
          <div class="bar-group" v-for="(item, index) in displayData" :key="index">
            <div class="bars">
              <div
                class="bar attending"
                :style="{ height: getBarHeight(item.attending) + '%' }"
                :title="`Attending: ${item.attending}`"
              ></div>

              <div
                class="bar not-attending"
                :style="{ height: getBarHeight(item.notAttending) + '%' }"
                :title="`Not Attending: ${item.notAttending}`"
              ></div>

              <div
                class="bar pending"
                :style="{ height: getBarHeight(item.pending) + '%' }"
                :title="`Pending: ${item.pending}`"
              ></div>
            </div>

            <div class="bar-label">{{ item.name }}</div>
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

<style scoped>
/* พื้นหลังและกรอบหลัก */
.event-participation-graph {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  font-family: "Inter", "Poppins", sans-serif;

  /*  บังคับไม่ให้ Component ดันตัวเองจนเกินพื้นที่พ่อแม่ */
  max-width: 100%;
  box-sizing: border-box;
  /* เพื่อให้ padding ไม่ดัน size */
}

/* Header Styles */
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
}

.chart-title {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.department-select {
  background: #ffffff;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  outline: none;
  cursor: pointer;
}

/* --- Layout ระบบกราฟ --- */
.chart-body {
  display: flex;
  align-items: flex-start;
  height: 250px;
  margin-bottom: 20px;
  /* จำเป็นสำหรับ Flexbox เพื่อให้ส่วนขวาจัดการพื้นที่ถูกต้อง */
  min-width: 0;
}

/* 1. แกน Y ด้านซ้าย */
.y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 200px;
  padding-right: 12px;
  border-right: 1px solid #e2e8f0;
  min-width: 40px;
  text-align: right;
  flex-shrink: 0;
}

.y-label {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
  line-height: 1;
  transform: translateY(-50%);
}

.y-label:last-child {
  transform: translateY(50%);
}

.y-label:first-child {
  transform: translateY(0);
}

/* 2. พื้นที่แสดงผลกราฟด้านขวา */
.bar-chart-scroll-area {
  /* กรณีข้อมูลเยอะ (>= 4): ขยายเต็มพื้นที่ (flex: 1) แล้ว Scroll */
  flex: 1;
  overflow-x: auto;
  overflow-y: hidden;
  position: relative;
  padding-left: 16px;

  /* [เจอสักที omg ส่วนนี้ถ้าไม่กำหนด แล้วใช้ flex กล่องก็ขยายไปเรื่อย scroll bar ไม่ขึ้นสักที ] */
  max-width: 100%;

  /* Webkit Scrollbar Logic */
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

/* กรณีข้อมูลน้อย */
.bar-chart-scroll-area.is-compact {
  flex: 0 0 auto;
  max-width: 100%;
}

/* เส้นปะ  Background */
.grid-lines {
  position: absolute;
  top: 0;
  left: 0; /* เปลี่ยนจาก 16px เป็น 0 เพราะตอนนี้มันเทียบกับ bar-chart-content แล้ว */

  /* สั่งให้กว้างเต็มพื้นที่ของ content (ที่ยาวๆ) */
  width: 100%;
  height: 200px; /* สูงเท่าความสูงกราฟ */

  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
  z-index: 0; /* ให้อยู่หลังกราฟ */
}

.grid-line {
  width: 100%;
  border-bottom: 1px dashed #e2e8f0;
  height: 0;
}

/* Content */
.bar-chart-content {
  display: flex;

  /* 1. เปลี่ยนจาก flex-end เป็น flex-start เพื่อยึดด้านบนเป็นหลัก */
  align-items: flex-start;

  min-width: max-content;

  /* 2. เปลี่ยน height เป็น min-height หรือเพิ่มความสูง
       เพื่อให้มีที่ว่างพอสำหรับข้อความ 2-3 บรรทัดโดยไม่ตกขอบ */
  height: auto;
  /* ให้ยืดหดตามเนื้อหา */
  min-height: 250px;
  /* ความสูงขั้นต่ำ (200px กราฟ + gap + ข้อความ) */

  position: relative;
  z-index: 1;
  justify-content: flex-start;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  margin-right: 32px;
  flex-shrink: 0;
  min-width: 80px;
  position: relative;
}
/* สร้างเส้นแบ่ง */
.bar-group::after {
  content: "";
  position: absolute;
  right: -16px; /* ขยับไปอยู่ตรงกลางช่องว่างระหว่างกราฟ (Gap เดิมคือ 32px) */
  top: 0;
  width: 1px;
  height: 100%; /* สูงเท่ากราฟ */
  background-color: #e2e8f0; /* สีเส้น */
  border-right: 1px dashed #e2e8f0; /* หรือใช้ border dashed ก็ได้ */
}
/* ลบเส้นของตัวสุดท้ายออก เพื่อไม่ให้มีเส้นปิดท้าย */
.bar-group:last-child::after {
  display: none;
}

.bars {
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 6px;
  height: 200px;
}

.bar {
  width: 40px;
  border-radius: 4px 4px 0 0;
  transition: height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  cursor: pointer;
}

.bar:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.bar.attending {
  background: linear-gradient(180deg, #dcfce7 0%, #00a73d 100%);
}

.bar.not-attending {
  background: linear-gradient(180deg, #ffe2e3 0%, #c10008 100%);
}

.bar.pending {
  background: linear-gradient(180deg, #dff3fe 0%, #0084d1 100%);
}

.bar-label {
  font-size: 11px;
  color: #6b7280;
  font-weight: 600;
  text-align: center;
  max-width: 90px;
  word-wrap: break-word;
}

/* Legend */
.chart-legend {
  display: flex;
  gap: 24px;
  margin-top: 8px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #4b5563;
}

.legend-indicator {
  width: 12px;
  height: 12px;
  border-radius: 3px;
}

.legend-indicator.attending {
  background: #22c55e;
}

.legend-indicator.not-attending {
  background: #ef4444;
}

.legend-indicator.pending {
  background: #3b82f6;
}

/* Custom Webkit Scrollbar */
.bar-chart-scroll-area::-webkit-scrollbar {
  height: 8px;
}

.bar-chart-scroll-area::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.bar-chart-scroll-area::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
  border: 2px solid #f1f5f9;
}

.bar-chart-scroll-area::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
