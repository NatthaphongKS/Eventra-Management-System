<template>
  <div class="event-participation-graph">
    <!-- Header with dropdown -->
    <div class="chart-header">
      <h3 class="chart-title">Event Participation</h3>
      <div class="filter-dropdown">
        <!-- View Type Selector -->
        <select v-model="viewType" @change="onViewTypeChange" class="department-select">
          <option value="department">Department</option>
          <option value="team">Team</option>
        </select>
      </div>
    </div>
    
    <!-- Bar chart -->
    <div class="bar-chart-container">
      <div class="bar-chart">
        <div class="bar-group" v-for="(item, index) in displayData" :key="index">
          <div class="bars">
            <div class="bar attending" 
                 :style="{ height: getBarHeight(item.attending) + '%' }"
                 :title="`Attending: ${item.attending}`">
              <span class="bar-value" v-if="item.attending > 0">{{ item.attending }}</span>
            </div>
            <div class="bar not-attending" 
                 :style="{ height: getBarHeight(item.notAttending) + '%' }"
                 :title="`Not Attending: ${item.notAttending}`">
              <span class="bar-value" v-if="item.notAttending > 0">{{ item.notAttending }}</span>
            </div>
            <div class="bar pending" 
                 :style="{ height: getBarHeight(item.pending) + '%' }"
                 :title="`Pending: ${item.pending}`">
              <span class="bar-value" v-if="item.pending > 0">{{ item.pending }}</span>
            </div>
          </div>
          <div class="bar-label">{{ item.name }}</div>
        </div>
      </div>
    </div>
    
    <!-- Modern Legend -->
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
</template>

<script>
import axios from 'axios';

export default {
  name: 'GraphEventParticipation',
  props: {
    eventId: {
      type: [Number, String],
      required: true
    },
    data: {
      type: Object,
      required: false,
      default: () => ({
        departments: []
      })
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      viewType: 'department', // 'department' or 'team'
      originalDepartments: [],
      originalTeams: [],
      filteredData: [],
      isLoading: false
    };
  },
  computed: {
    displayData() {
      return this.filteredData;
    }
  },
  watch: {
    data: {
      immediate: true,
      deep: true,
      handler(newData) {
        if (newData) {
          // Store original data
          this.originalDepartments = newData.departments ? [...newData.departments] : [];
          this.originalTeams = newData.teams ? [...newData.teams] : [];
          
          // Update displayed data based on current view type
          this.updateDisplayData();
        }
      }
    }
  },
  methods: {
    getMaxValue() {
      if (this.filteredData.length === 0) return 1;
      
      let maxValue = 0;
      this.filteredData.forEach(item => {
        const total = (item.attending || 0) + (item.notAttending || 0) + (item.pending || 0);
        if (total > maxValue) {
          maxValue = total;
        }
      });
      
      return maxValue || 1;
    },
    
    getBarHeight(value) {
      if (!value || value === 0) return 0;
      const maxValue = this.getMaxValue();
      return Math.max((value / maxValue) * 100, 100); // Minimum 5% height for visibility
    },

    onViewTypeChange() {
      this.updateDisplayData();
    },

    updateDisplayData() {
      // Get the source data based on view type and show all items
      const sourceData = this.viewType === 'department' ? this.originalDepartments : this.originalTeams;
      this.filteredData = [...sourceData];
    }
  }
};
</script>

<style scoped>
.event-participation-graph {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  font-family: 'Inter', 'Poppins', sans-serif;
}

/* Header */
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #e2e8f0;
}

.chart-title {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.filter-dropdown {
  position: relative;
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
  transition: all 0.2s ease;
  cursor: pointer;
  min-width: 160px;
}

.department-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.department-select:hover {
  border-color: #d1d5db;
}

/* Chart Container */
.bar-chart-container {
  margin-bottom: 24px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 4px;
  -webkit-overflow-scrolling: touch;
}

.bar-chart-container::-webkit-scrollbar {
  height: 8px;
}

.bar-chart-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.bar-chart-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.bar-chart-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.bar-chart {
  display: flex;
  justify-content: flex-start;
  align-items: end;
  height: 280px;
  background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid #e2e8f0;
  min-width: max-content;
  gap: 16px;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 200px;
  min-height: 200px;
  flex: 1;
}

.bar-label {
  font-size: 11px;
  color: #6b7280;
  font-weight: 600;
  text-align: center;
  line-height: 1.2;
  max-width: 90px;
  padding: 0 4px;
  word-wrap: break-word;
  hyphens: auto;
}

.bars {
  display: flex;
  align-items: end;
  gap: 4px;
  height: 200px;
}

.bar {
  width: 18px;
  min-height: 8px;
  border-radius: 4px 4px 0 0;
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  align-items: end;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.bar:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.bar.attending {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.bar.not-attending {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.bar.pending {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.bar-value {
  font-size: 11px;
  font-weight: 700;
  color: white;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  padding: 2px 4px;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.2);
  margin-bottom: 4px;
  min-width: 16px;
  text-align: center;
}

/* Modern Legend */
.chart-legend {
  display: flex;
  justify-content: center;
  gap: 24px;
  flex-wrap: wrap;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #f8fafc;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.legend-item:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.legend-indicator {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.legend-indicator.attending {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.legend-indicator.not-attending {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.legend-indicator.pending {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.legend-text {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

/* Responsive */
@media (max-width: 768px) {
  .chart-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .chart-title {
    text-align: center;
  }
  
  .department-select {
    width: 100%;
  }
  
  .bar-chart {
    padding: 16px;
    height: 240px;
  }
  
  .bars {
    height: 160px;
  }
  
  .bar {
    width: 14px;
  }
  
  .bar-label {
    font-size: 11px;
    max-width: 60px;
  }
  
  .chart-legend {
    gap: 16px;
  }
  
  .legend-item {
    padding: 6px 10px;
  }
  
  .legend-text {
    font-size: 12px;
  }
}

/* Scrollbar styling */
.bar-chart-container::-webkit-scrollbar {
  height: 8px;
}

.bar-chart-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.bar-chart-container::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
  border-radius: 4px;
}

.bar-chart-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
}
</style>
  text-align: center;
  word-break: break-all;
  line-height: 1.2;
}

.bars {
  display: flex;
  align-items: end;
  gap: 2px;
  height: 200px;
}

.bar {
  width: 16px;
  min-height: 4px;
  border-radius: 2px 2px 0 0;
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  align-items: end;
  justify-content: center;
  cursor: pointer;
}

.bar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bar.attending {
  background: linear-gradient(to top, #16a34a 0%, #22c55e 100%);
}

.bar.not-attending {
  background: linear-gradient(to top, #dc2626 0%, #ef4444 100%);
}

.bar.pending {
  background: linear-gradient(to top, #d97706 0%, #f59e0b 100%);
}

.bar-value {
  position: absolute;
  bottom: 100%;
  margin-bottom: 2px;
  font-size: 0.65rem;
  font-weight: 600;
  color: #374151;
  background: rgba(255,255,255,0.9);
  padding: 1px 3px;
  border-radius: 2px;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.bar:hover .bar-value {
  opacity: 1;
}

.chart-legend {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  flex-shrink: 0;
}

.legend-color.attending {
  background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

.legend-color.not-attending {
  background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
}

.legend-color.pending {
  background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
}

.legend-text {
  color: #374151;
  font-weight: 500;
}

@media (max-width: 768px) {
  .bar-chart {
    height: 250px;
    padding: 0.75rem;
  }
  
  .bars {
    height: 150px;
  }
  
  .bar {
    width: 30px;
  }
