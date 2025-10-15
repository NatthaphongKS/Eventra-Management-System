<template>
  <div class="pending-card card-stat">
    <div class="stat-title">Pending</div>
    <div class="stat-value">{{ value }}</div>
    <div class="stat-desc">People who have not responded</div>
  </div>
</template>

<script>
export default {
  name: 'PendingCard',
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
  background: #fffde7;
  border-radius: 12px;
  padding: 20px 16px;
  text-align: center;
  margin-bottom: 16px;
}
.stat-title {
  font-size: 16px;
  color: #fbc02d;
  font-weight: 600;
}
.stat-value {
  font-size: 32px;
  color: #f9a825;
  font-weight: bold;
  margin: 8px 0;
}
.stat-desc {
  font-size: 13px;
  color: #fbc02d;
}
</style>
