<template>
  <div class="attending-card card-stat">
    <div class="stat-title">Attending</div>
    <div class="stat-value">{{ value }}</div>
    <div class="stat-desc">People who will attend</div>
  </div>
</template>

<script>
export default {
  name: 'AttendingCard',
  props: {
    value: {
      type: [Number, String],
      required: true
    }
  }
};
</script>

<style scoped>
.card-stat {
  background: #e0f7fa;
  border-radius: 12px;
  padding: 20px 16px;
  text-align: center;
  margin-bottom: 16px;
}
.stat-title {
  font-size: 16px;
  color: #00796b;
  font-weight: 600;
}
.stat-value {
  font-size: 32px;
  color: #004d40;
  font-weight: bold;
  margin: 8px 0;
}
.stat-desc {
  font-size: 13px;
  color: #009688;
}
</style>
