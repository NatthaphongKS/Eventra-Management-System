<template>
  <div class="not-attending-card card-stat">
    <div class="stat-title">Not Attending</div>
    <div class="stat-value">{{ value }}</div>
    <div class="stat-desc">People who declined</div>
  </div>
</template>

<script>
export default {
  name: 'NotAttendingCard',
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
  background: #ffebee;
  border-radius: 12px;
  padding: 20px 16px;
  text-align: center;
  margin-bottom: 16px;
}
.stat-title {
  font-size: 16px;
  color: #c62828;
  font-weight: 600;
}
.stat-value {
  font-size: 32px;
  color: #b71c1c;
  font-weight: bold;
  margin: 8px 0;
}
.stat-desc {
  font-size: 13px;
  color: #c62828;
}
</style>
