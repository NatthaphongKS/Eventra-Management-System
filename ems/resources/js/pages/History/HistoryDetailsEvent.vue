<template>
    <EventDetailCard :id="eventId" @back="goBack" />
</template>

<script>
// 1. นำเข้า Component
import EventDetailCard from '@/components/DetailsEvent/EventDetails.vue';

export default {
    components: { EventDetailCard }, // 2. ลงทะเบียน Component
    data() {
        return {
            // ดึง id จาก URL (Route Params) มาเก็บไว้
            eventId: this.$route.params.id 
        }
    },
    methods: {
        goBack() {
            this.$router.push('/events'); // ตัวอย่างการจัดการปุ่ม Back
        }
    }
}
</script>