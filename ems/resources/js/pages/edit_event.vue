<!-- pages/edit_event.vue -->
<template>
    <div>
        Edit Event
    </div>
    <label>Event Title</label><br />
    <input type="text" style="border: 2px solid black;" v-model.trim="evn_title" /> <!--เพิ่ม border แล้วต้องใส่ชนิด border ด้วย (เช่น solid,
      dashed, dotted) -->
      <label>Event Title</label><br />
    <textarea style="border: 2px solid black;" v-model.trim="evn_title" ></textarea>>
</template>

<script>
import axios from 'axios';
export default {
    data() {
        return {
            evn_title: '',
        };
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData() {
            try {
                // ถ้าเส้นทางอยู่ใน routes/api.php → URL จริงคือ /api/edit_event/:id  ${this.$route.params.id}
                const { data } = await axios.get(`/edit-event/${this.$route.params.id}`)// ดึงข้อมูลจาก API get edit_event{id}
                this.evn_title = data?.evn_title ?? ''
                this.evn_description = data?.evn_description ?? ''

                console.log('loaded:', this.evn_title)
            } catch (err) {
                console.error(err)
                this.evn_title = '(โหลดข้อมูลไม่สำเร็จ)'
            }
        },
    },

    mounted() {
        this.fetchData();
    }
}
</script>
