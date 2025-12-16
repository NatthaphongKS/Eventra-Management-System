<template>
  <div class="card-stat" :class="{ clickable: isClickable }" @click="handleClick">
    <!-- Icon and Title -->
    <div class="card-header">
      <div class="card-icon">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="m22 2-5 5 3 3z"/>
        </svg>
      </div>
      <span class="card-title">Attending</span>
    </div>

    <!-- Main content -->
    <div class="card-content">
      <!-- Large percentage -->
      <div class="percentage">{{ getPercentage() }}%</div>
      
      <!-- Mini donut chart -->
      <div class="mini-donut">
        <svg class="mini-donut-svg" viewBox="0 0 60 60" width="60" height="60">
          <circle 
            cx="30" 
            cy="30" 
            r="25" 
            fill="transparent" 
            stroke="#e5e7eb" 
            stroke-width="6"
          />
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
            transform="rotate(0 30 30)"
            class="mini-progress"
          />
          
          <!-- Center number -->
          <text x="30" y="36" text-anchor="middle" class="mini-donut-number">
            {{ attending }}
          </text>
        </svg>
      </div>
    </div>

    <!-- Footer info -->
    <div class="card-footer">
      <div class="count">{{ attending }} person{{ attending > 1 ? 's' : '' }}</div>
      <div class="view-link">View Attendance →</div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
  </div>
</template>

<script>
export default {
  name: 'AttendingCard',
  props: {
    attending: {
      type: Number,
      default: 0
    },
    total: {
      type: Number,
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
  emits: ['showAttendingEmployees'],
  computed: {
    miniCircumference() {
      return 2 * Math.PI * 25;
    },
    miniStrokeDashoffset() {
      if (this.total === 0) return this.miniCircumference;
      const percentage = (this.attending / this.total) * 100;
      return this.miniCircumference - (percentage / 100) * this.miniCircumference;
    }
  },
  methods: {
    getPercentage() {
      if (this.total === 0) return 0;
      return Math.round((this.attending / this.total) * 100);
    },
    handleClick() {
      if (this.isClickable) {
        console.log('AttendingCard clicked, emitting showAttendingEmployees');
        this.$emit('showAttendingEmployees');
      }
    }
  }
};
</script>

<style scoped>
.card-stat {
  background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
  border-radius: 20px;
  padding: 24px;
  text-align: left;
  margin-bottom: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-left: 4px solid #22c55e;
  font-family: 'Inter', 'Poppins', sans-serif;
}

.clickable {
  cursor: pointer;
}

.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(34, 197, 94, 0.1), 0 4px 6px -2px rgba(34, 197, 94, 0.05);
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
  color: #16a34a;
}

.icon {
  width: 24px;
  height: 24px;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #16a34a;
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
  color: #15803d;
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
  fill: #16a34a;
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
  color: #16a34a;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.count::before {
  content: '';
  width: 8px;
  height: 8px;
  background: #22c55e;
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


