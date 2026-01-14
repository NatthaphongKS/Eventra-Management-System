<template>
  <div class="card-stat" :class="{ clickable: isClickable }" @click="handleClick">
    <!-- Header -->
    <div class="card-header">
      <div class="card-icon">
        <svg class="icon" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 2a8 8 0 100 16 8 8 0 000-16zm0 2a6 6 0 110 12 6 6 0 010-12zm0 2a4 4 0 100 8 4 4 0 000-8z"/>
          <path d="M14.5 8L16 9.5 13.5 12 16 14.5 14.5 16 12 13.5 9.5 16 8 14.5 10.5 12 8 9.5 9.5 8 12 10.5z"/>
        </svg>
      </div>
      <span class="card-title">NOT ATTENDING</span>
    </div>

    <!-- Content -->
    <div class="card-content">
      <div class="percentage">{{ getPercentage() }}%</div>
      <div class="mini-donut">
        <svg class="mini-donut-svg" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="30" stroke="#fee2e2" stroke-width="8" fill="none"/>
          <circle
            cx="40"
            cy="40"
            r="30"
            stroke="url(#redGradient)"
            stroke-width="8"
            fill="none"
            stroke-linecap="round"
            :stroke-dasharray="miniCircumference"
            :stroke-dashoffset="miniStrokeDashoffset"
            class="mini-progress"
            transform="rotate(0 40 40)"
          />
          <defs>
            <linearGradient id="redGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#ef4444"/>
              <stop offset="100%" style="stop-color:#dc2626"/>
            </linearGradient>
          </defs>
          <text x="40" y="45" text-anchor="middle" class="mini-donut-number">{{ notAttending }}</text>
        </svg>
      </div>
    </div>

    <!-- Footer -->
    <div class="card-footer">
      <div class="count">{{ notAttending }} person{{ notAttending > 1 ? 's' : '' }}</div>
      <div class="view-link">View Attendance →</div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
  </div>
</template>

<script>
export default {
  name: 'NotAttendingCard',
  props: {
    eventId: {
      type: [Number, String],
      required: true
    },
    notAttending: {
      type: [Number, String],
      default: 0
    },
    total: {
      type: [Number, String],
      default: 0
    },
    loading: {
      type: Boolean,
      default: false
    },
    isClickable: {
      type: Boolean,
      default: false
    }
  },
  emits: ['showNotAttendingEmployees'],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 30;
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference;
      const percentage = (this.notAttending / this.total) * 100;
      return this.miniCircumference - (percentage / 100) * this.miniCircumference;
    }
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0;
      return Math.round((this.notAttending / this.total) * 100);
    },
    handleClick() {
      if (this.isClickable) {
        this.$emit('showNotAttendingEmployees');
      }
    }
  }
}
</script>

<style scoped>
.card-stat {
  background: linear-gradient(135deg, #fdecea 0%, #fef2f2 100%);
  border-radius: 20px;
  padding: 24px;
  text-align: left;
  margin-bottom: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-left: 4px solid #d32f2f;
  font-family: 'Inter', 'Poppins', sans-serif;
}

.clickable {
  cursor: pointer;
}

.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(211, 47, 47, 0.1), 0 4px 6px -2px rgba(211, 47, 47, 0.05);
}

/* Header */
.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.card-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #d32f2f;
}

.icon {
  width: 24px;
  height: 24px;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #d32f2f;
}

/* Content */
.card-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.percentage {
  font-size: 48px;
  font-weight: 800;
  color: #b71c1c;
  line-height: 1;
}

/* Mini donut chart */
.mini-donut {
  position: relative;
}

.mini-donut-svg {
  width: 65px;
  height: 65px;
}

.mini-progress {
  transition: stroke-dasharray 0.8s ease-in-out, stroke-dashoffset 0.8s ease-in-out;
}

.mini-donut-number {
  font-size: 14px;
  font-weight: 700;
  fill: #d32f2f;
  font-family: 'Inter', 'Poppins', sans-serif;
}

/* Footer */
.card-footer {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.count {
  font-size: 14px;
  color: #d32f2f;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.count::before {
  content: '';
  width: 8px;
  height: 8px;
  background: #d32f2f;
  border-radius: 50%;
}

.view-link {
  color: #2563eb;
  font-weight: 500;
  font-size: 14px;
  transition: color 0.2s;
}

.view-link:hover {
  color: #1d4ed8;
}

.loading {
  font-size: 12px;
  color: #6b7280;
  margin-top: 8px;
  text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
  .card-stat {
    padding: 20px;
  }

  .percentage {
    font-size: 36px;
  }

  .mini-donut-svg {
    width: 55px;
    height: 55px;
  }
}
</style>
