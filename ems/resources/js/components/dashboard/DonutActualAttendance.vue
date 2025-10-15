<template>
  <div class="donut-actual-attendance">
    <canvas ref="donutChart"></canvas>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'DonutActualAttendance',
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const donutChart = ref(null);
    onMounted(() => {
      new Chart(donutChart.value, {
        type: 'doughnut',
        data: props.data,
        options: {
          plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Actual Attendance' }
          },
          cutout: '70%'
        }
      });
    });
    return { donutChart };
  }
};
</script>

<style scoped>
.donut-actual-attendance {
  width: 100%;
  max-width: 320px;
  margin: 0 auto;
}
canvas {
  width: 100% !important;
  height: 260px !important;
}
</style>
