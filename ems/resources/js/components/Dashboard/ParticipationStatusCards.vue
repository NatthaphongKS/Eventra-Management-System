<template>
  <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <AttendingCard
      :attending="chartData.attending || 0"
      :total="chartData.total_participation || 0"
      :loading="loading"
      :isClickable="isClickable"
      :isSelected="selectedStatus === 'accepted'"
      @showAttendingEmployees="emitStatus('accepted')"
    />

    <NotAttendingCard
      :notAttending="chartData.not_attending || 0"
      :total="chartData.total_participation || 0"
      :loading="loading"
      :isClickable="isClickable"
      :isSelected="selectedStatus === 'declined'"
      @showNotAttendingEmployees="emitStatus('declined')"
    />

    <PendingCard
      :pending="chartData.pending || 0"
      :total="chartData.total_participation || 0"
      :loading="loading"
      :isClickable="isClickable"
      :isSelected="selectedStatus === 'pending'"
      @showPendingEmployees="emitStatus('pending')"
    />
  </div>
</template>

<script>
import AttendingCard from "./AttendingCard.vue";
import NotAttendingCard from "./NotAttendingCard.vue";
import PendingCard from "./PendingCard.vue";

export default {
  name: "ParticipationStatusCards",
  components: {
    AttendingCard,
    NotAttendingCard,
    PendingCard,
  },
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    isClickable: {
      type: Boolean,
      default: true,
    },
    selectedStatus: {
      type: String,
      default: null,
    },
  },
  emits: ["select-status"],
  methods: {
    emitStatus(status) {
      if (!this.isClickable) return;
      this.$emit("select-status", status);
    },
  },
};
</script>
