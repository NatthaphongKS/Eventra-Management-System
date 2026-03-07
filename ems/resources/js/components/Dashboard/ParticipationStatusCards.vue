<!-- /**
 * ชื่อไฟล์: ParticipationStatusCards.vue
 * คำอธิบาย: Component สำหรับแสดงการ์ดสถิติสถานะการเข้าร่วมกิจกรรม 3 ประเภท (เข้าร่วม, ไม่เข้าร่วม, รอตอบรับ)
 * Input: ข้อมูลสถิติ (chartData), สถานะโหลด (loading), สถานะคลิกได้ (isClickable), สถานะที่เลือก (selectedStatus)
 * Output: แสดงการ์ด AttendingCard, NotAttendingCard, PendingCard และ emit event select-status เมื่อคลิกเลือก
 * ชื่อผู้เขียน/แก้ไข: Kidrakon Rattanahiran
 * วันที่จัดทำ/แก้ไข: 2026-03-06
 */ -->

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
