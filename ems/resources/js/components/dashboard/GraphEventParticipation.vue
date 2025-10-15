<template>
  <div class="event-participation-graph">
    <canvas ref="participationChart"></canvas>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'GraphEventParticipation',
  props: {
    data: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const participationChart = ref(null);
    onMounted(() => {
      new Chart(participationChart.value, {
        type: 'bar',
        data: props.data,
        options: {
          ...props.options,
          plugins: {
            legend: { display: false },
            title: { display: true, text: 'Event Participation' }
          }
        }
      });
    });
    return { participationChart };
  }
};
</script>

<style scoped>
.event-participation-graph {
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
}
canvas {
  width: 100% !important;
  height: 300px !important;
}
</style>
