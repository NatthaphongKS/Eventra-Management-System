<template>
  <div class="donut-content">
    <h3 class="chart-title">Actual Attendance</h3>
    
    <!-- Loading state -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>กำลังโหลดข้อมูล...</p>
    </div>
    
    <!-- Chart content -->
    <div v-else class="donut-container">
      <div class="donut-chart-wrapper">
        <svg class="donut-svg" viewBox="0 0 160 160" width="160" height="160">
          <!-- Background circle -->
          <circle 
            cx="80" 
            cy="80" 
            r="65" 
            fill="transparent" 
            stroke="#f1f5f9" 
            stroke-width="20"
          />
          
          <!-- Progress circle with gradient -->
          <circle 
            cx="80" 
            cy="80" 
            r="65" 
            fill="transparent" 
            stroke="url(#donutGradient)" 
            stroke-width="20"
            stroke-linecap="round"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="strokeDashoffset"
            transform="rotate(0 80 80)"
            class="progress-circle"
          />
          
          <!-- Gradient definition -->
          <defs>
            <linearGradient id="donutGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#4ade80;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#22c55e;stop-opacity:1" />
            </linearGradient>
          </defs>
        </svg>
        
        <div class="donut-center">
          <div class="main-number">{{ displayAttendance }}</div>
          <div class="sub-label">Person</div>
        </div>
      </div>
    </div>
    
    <div v-if="!isLoading" class="attendance-info">
      <div class="attendance-stats">
        <div class="attendance-dot"></div>
        <span class="attendance-text">
          Attended {{ attendancePercentage.toFixed(2) }}% Actual attendance {{ displayAttendance }} Person from {{ displayTotal }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

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
    };
  },
  computed: {
    circumference() {
      return 2 * Math.PI * 65;
    },
    attendancePercentage() {
      if (!this.displayTotal || this.displayTotal === 0) return 0;
      return (this.displayAttendance / this.displayTotal) * 100;
    },
    strokeDashoffset() {
      const percentage = this.attendancePercentage;
      return this.circumference - (percentage / 100) * this.circumference;
    },
    displayAttendance() {
      // Use props data directly
      return this.attendanceData?.attending || 0;
    },
    displayTotal() {
      // Use props data directly
      return this.attendanceData?.total || 0;
    }
  },
  methods: {}
};
</script>

<style scoped>
.donut-content {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  font-family: 'Inter', 'Poppins', sans-serif;
  padding: 12px;
}

.chart-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
  text-align: center;
  flex-shrink: 0;
}

.donut-container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex: 1;
  margin-bottom: 12px;
}

.donut-chart-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  max-width: 240px;
  max-height: 240px;
  aspect-ratio: 1;
}

.donut-svg {
  width: 100%;
  height: 100%;
}

.progress-circle {
  transition: stroke-dashoffset 1s ease-in-out;
}

.donut-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.main-number {
  font-size: 48px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 6px;
}

.sub-label {
  font-size: 16px;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stats-info {
  text-align: center;
}

.stats-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.green-dot {
  width: 8px;
  height: 8px;
  background-color: #22c55e;
  border-radius: 50%;
  flex-shrink: 0;
}

.stats-text {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.4;
}

.attendance-info {
  text-align: center;
  margin-top: 8px;
  flex-shrink: 0;
}

.attendance-stats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 8px;
}

.attendance-dot {
  width: 8px;
  height: 8px;
  background-color: #22c55e;
  border-radius: 50%;
  flex-shrink: 0;
}

.attendance-text {
  font-size: 13px;
  color: #6b7280;
  line-height: 1.3;
  word-wrap: break-word;
}

@media (max-width: 768px) {
  .donut-content {
    padding: 8px;
  }
  
  .donut-chart-wrapper {
    max-width: 180px;
    max-height: 180px;
  }
  
  .main-number {
    font-size: 36px;
  }
  
  .sub-label {
    font-size: 14px;
  }
  
  .chart-title {
    font-size: 16px;
    margin-bottom: 12px;
  }
  
  .attendance-text {
    font-size: 12px;
  }
}

/* Loading state */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  color: #6b7280;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f1f5f9;
  border-top: 3px solid #22c55e;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>