<template>
  <div class="card-stat" :class="{ clickable: isClickable }" @click="handleClick">
    <!-- Header -->
    <div class="card-header">
      <div class="card-icon">
        <svg class="icon" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16z"/>
          <path d="M13 8h-2v5l4 2.5 1-1.5-3-2V8z"/>
        </svg>
      </div>
      <span class="card-title">PENDING</span>
    </div>

    <!-- Content -->
    <div class="card-content">
      <div class="percentage">{{ getPercentage() }}%</div>
      <div class="mini-donut">
        <svg class="mini-donut-svg" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="30" stroke="#dbeafe" stroke-width="8" fill="none"/>
          <circle
            cx="40"
            cy="40"
            r="30"
            stroke="url(#blueGradient)"
            stroke-width="8"
            fill="none"
            stroke-linecap="round"
            :stroke-dasharray="miniCircumference"
            :stroke-dashoffset="miniStrokeDashoffset"
            class="mini-progress"
            transform="rotate(0 40 40)"
          />
          <defs>
            <linearGradient id="blueGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#3b82f6"/>
              <stop offset="100%" style="stop-color:#1d4ed8"/>
            </linearGradient>
          </defs>
          <text x="40" y="45" text-anchor="middle" class="mini-donut-number">{{ pending }}</text>
        </svg>
      </div>
    </div>

    <!-- Footer -->
    <div class="card-footer">
      <div class="count">{{ pending }} person{{ pending > 1 ? 's' : '' }}</div>
      <div class="view-link">View Attendance →</div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
  </div>
</template>

<script>
export default {
  name: 'PendingCard',
  props: {
    eventId: {
      type: [Number, String],
      required: true
    },
    pending: {
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
  emits: ['showPendingEmployees'],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 30;
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference;
      const percentage = (this.pending / this.total) * 100;
      return this.miniCircumference - (percentage / 100) * this.miniCircumference;
    }
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0;
      return Math.round((this.pending / this.total) * 100);
    },
    handleClick() {
      if (this.isClickable) {
        this.$emit('showPendingEmployees');
      }
    }
  }
}
</script>

<style scoped>
.card-stat {
  background: linear-gradient(135deg, #e8f4fd 0%, #f0f9ff 100%);
  border-radius: 20px;
  padding: 24px;
  text-align: left;
  margin-bottom: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-left: 4px solid #1976d2;
  font-family: 'Inter', 'Poppins', sans-serif;
}

.clickable {
  cursor: pointer;
}

.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(25, 118, 210, 0.1), 0 4px 6px -2px rgba(25, 118, 210, 0.05);
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
  color: #1976d2;
}

.icon {
  width: 24px;
  height: 24px;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #1976d2;
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
  color: #0d47a1;
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
  fill: #1976d2;
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
  color: #1976d2;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.count::before {
  content: '';
  width: 8px;
  height: 8px;
  background: #1976d2;
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
