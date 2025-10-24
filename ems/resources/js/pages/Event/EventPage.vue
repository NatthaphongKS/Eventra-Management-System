<!-- pages/event_page.vue -->
<template>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
            <div class="font-[Poppins]"> -->
    <!-- ===== Toolbar (pill) ===== -->
    <div class="mt-3 mb-1 flex items-center gap-4">
        <!-- search + ปุ่มค้นหาแดง -->
        <div class="flex items-center gap-3 flex-1">
            <input v-model.trim="searchInput" placeholder="Search" @keyup.enter="applySearch"
                class="h-11 w-[750px] rounded-full border border-slate-200 bg-white px-3 outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200" />
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full
         bg-[#b91c1c] text-white hover:bg-[#991b1b]
         focus:outline-none focus:ring-2 focus:ring-red-300 cursor-default select-none." @click="applySearch"
                aria-label="Search" title="ค้นหา (คลิกหรือกด Enter)">
                <MagnifyingGlassIcon class="h-5 w-5" />
            </button>
        </div>

        <!-- ปุ่ม Filter/Sort (ตอนนี้ยัง UI) -->
        <!-- Toolbar -->
        <!-- ปุ่ม Filter กลาง (schema-driven) -->
        <Filter v-model="filters" :filter-fields="filterFields" button-label="Filter" @apply="page = 1" />


        <EventSort v-model="selectedSort" :options="sortOptions" />

        <!-- Summary + Add -->

        <router-link to="/add-event" class="ml-auto inline-flex h-11 items-center rounded-full cursor-default select-none
         bg-[#b91c1c] px-4 font-semibold text-white
         hover:bg-[#991b1b] focus:outline-none
         focus:ring-2 focus:ring-red-300">
            + Add
        </router-link>
        <!-- ==== Sort Panel ==== -->
    </div>

    <!-- ===== Table ===== -->
    <EventTable :rows="sorted" v-model:page="page" v-model:pageSize="pageSize" :pageSizeOptions="[10, 20, 50, 100]"
        @edit="editEvent" @delete="deleteEvent" />


</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import 'sweetalert2/dist/sweetalert2.min.css';
import EventTable from '@/components/IndexEvent/EventTable.vue'   // ← คอมโพเนนต์ตารางใหม่
import Filter from '@/components/Button/Filter.vue'
import EventSort from "@/components/IndexEvent/EventSort.vue";
// components



import {
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';


axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true


export default {
    components: {
        MagnifyingGlassIcon, PencilIcon, TrashIcon,
        EventTable, Filter, EventSort
    },

    filters: { category: [], status: [] },
    data() {
        return {
            event: [],
            categories: [],
            catMap: {},

            searchInput: "",
            search: "",


            showSort: false,

            sortBy: "evn_date",
            sortOrder: "asc",
            selectedSort: { id: 'date_desc', key: 'evn_date', order: 'desc', type: 'date' },

            // เพิ่มค่าเริ่มต้นให้ EventSort (เลือก “วันที่จัดงานใหม่สุด”)
            selectedSort: { id: 'date_desc', key: 'evn_date', order: 'desc', type: 'date' },

            // ถ้าอยาก override รายการในคอมโพเนนต์ก็ทำที่นี่ (หรือจะไม่ใส่ก็ได้ จะใช้ default ในคอมโพเนนต์)
            sortOptions: [
                { id: 'title_asc', label: 'ชื่องาน A–Z', key: 'evn_title', order: 'asc', type: 'text' },
                { id: 'title_desc', label: 'ชื่องาน Z–A', key: 'evn_title', order: 'desc', type: 'text' },
                { id: 'invited_desc', label: 'จำนวนคนเชิญมากสุด', key: 'evn_num_guest', order: 'desc', type: 'number' },
                { id: 'invited_asc', label: 'จำนวนคนเชิญน้อยสุด', key: 'evn_num_guest', order: 'asc', type: 'number' },
                { id: 'accepted_desc', label: 'จำนวนคนเข้าร่วมมากสุด', key: 'evn_sum_accept', order: 'desc', type: 'number' },
                { id: 'accepted_asc', label: 'จำนวนคนเข้าร่วมน้อยสุด', key: 'evn_sum_accept', order: 'asc', type: 'number' },
                { id: 'date_desc', label: 'วันที่จัดงานใหม่สุด', key: 'evn_date', order: 'desc', type: 'date' },
                { id: 'date_asc', label: 'วันที่จัดงานเก่าสุด', key: 'evn_date', order: 'asc', type: 'date' },
            ],


            page: 1,
            pageSize: 10,

            // <<< เพิ่มให้มาอยู่ใน data()
            filters: { category: [], status: [] },

        }
    },

    async created() {
        await Promise.all([this.fetchEvent(), this.fetchCategories()]);
    },

    watch: {
        search() { this.page = 1; },
        pageSize() { this.page = 1; },
        event() { this.page = 1; },
    },
    computed: {
        filterFields() {
            // ตัวเลือก Category มาจาก API
            const categoryOptions = this.categories.map(c => ({
                label: c.cat_name,
                value: String(c.id),
            }))

            // ตัวเลือก Status แบบ checkbox
            const statusOptions = [
                { label: 'Done', value: 'done' },
                { label: 'Ongoing', value: 'ongoing' },
                { label: 'Upcoming', value: 'upcoming' },
            ]

            return [
                {
                    fieldKey: 'category',
                    label: 'Category',
                    fieldType: 'checkbox',
                    sectionTitle: 'Category',
                    fieldOptions: categoryOptions,
                },
                {
                    fieldKey: 'status',
                    label: 'Status',
                    fieldType: 'checkbox',
                    sectionTitle: 'Status',
                    fieldOptions: statusOptions,
                },
            ]
        },
        hasActiveFilters() {
            const f = this._appliedFlt || this.flt
            return f.category !== 'all' || f.status !== 'all' || !!(f.dateFrom || f.dateTo)
        },

        // normalize เหมือนเดิม
        normalized() {
            return this.event.map(e => ({
                ...e,
                evn_title: e.evn_title ?? e.evn_name ?? "",
                evn_cat_id: e.evn_cat_id ?? e.evn_category_id ?? "",
                cat_name: e.cat_name ?? e.category_name ?? this.catMap[String(e.evn_cat_id)] ?? "",
                evn_date: e.evn_date ?? "",
                evn_timestart: e.evn_timestart ?? "",
                evn_timeend: e.evn_timeend ?? "",
                evn_num_guest: Number(e.evn_num_guest ?? 0),
                evn_sum_accept: Number(e.evn_sum_accept ?? 0),
                evn_status: e.evn_status ?? "",
            }))
        },

        // 1) กรองด้วย EventFilter ก่อน
        byFilter() {
            const catSet = new Set(this.filters.category.map(String))
            const stSet = new Set(this.filters.status.map(s => String(s).toLowerCase()))
            return this.normalized.filter(e => {
                const byCat = catSet.size === 0 || catSet.has(String(e.evn_cat_id))
                const bySt = stSet.size === 0 || stSet.has(String((e.evn_status || '').toLowerCase()))
                return byCat && bySt
            })
        },

        // 2) ค้นหาด้วย search
        filtered() {
            const q = this.search.toLowerCase().trim()
            if (!q) return this.byFilter
            return this.byFilter.filter(e =>
                `${e.evn_title} ${e.cat_name} ${e.evn_date} ${e.evn_timestart} ${e.evn_timeend} ${e.evn_status}`
                    .toLowerCase()
                    .includes(q)
            )
        },

        // 3) sort
        sorted() {
            const key = this.sortBy
            const order = this.sortOrder
            const arr = [...this.filtered]
            const parseVal = v => {
                if (key.includes('date')) return new Date(v || 0).getTime()
                if (['evn_num_guest', 'evn_sum_accept'].includes(key)) return Number(v) || 0
                return (v ?? '').toString().toLowerCase()
            }
            arr.sort((a, b) => {
                const va = parseVal(a[key]), vb = parseVal(b[key])
                if (va < vb) return order === "asc" ? -1 : 1
                if (va > vb) return order === "asc" ? 1 : -1
                return 0
            })
            return arr
        },

        // 4) paginate (ถ้าต้องใช้ที่หน้า page ต่อ)
        totalPages() { return Math.ceil(this.sorted.length / this.pageSize) },
        paged() {
            const start = (this.page - 1) * this.pageSize
            return this.sorted.slice(start, start + this.pageSize)
        },

        pageItems() {
            const total = this.totalPages || 1
            const cur = this.page
            const items = []
            if (total <= 7) { for (let i = 1; i <= total; i++) items.push({ type: 'page', value: i }); return items }
            const addPage = p => items.push({ type: 'page', value: p })
            const addDots = () => items.push({ type: 'dots' })
            addPage(1)
            if (cur > 3) addDots()
            const s = Math.max(2, cur - 1), e = Math.min(total - 1, cur + 1)
            for (let p = s; p <= e; p++) addPage(p)
            if (cur < total - 2) addDots()
            addPage(total)
            return items
        },
    },
    watch: {
        search() { this.page = 1 },
        pageSize() { this.page = 1 },
        event() { this.page = 1 },

        // ⬇️ ตัวนี้สำคัญ — ไหลค่าจาก EventSort -> state ที่ sorted() ใช้
        selectedSort: {
            handler(v) {
                if (!v) return
                this.sortBy = v.key
                this.sortOrder = v.order
                this.page = 1
                // debug ให้เห็นในคอนโซลว่ามีการเปลี่ยนจริง
                // console.log('[sort]', v.id, '->', this.sortBy, this.sortOrder)
            },
            immediate: true,
            deep: true,
        },
    },

    methods: {
        async fetchEvent() {
            try {
                const res = await axios.get("/get-event");
                this.event = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            } catch (err) {
                console.error("fetchEvent error", err);
                this.event = [];
            }
        },
        async fetchCategories() {
            try {
                const res = await axios.get("/event-info");
                const cats = res.data?.categories || [];
                this.categories = cats;
                this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
            } catch (err) {
                console.error("fetchCategories error", err);
                this.categories = [];
                this.catMap = {};
            }
        },

        applySearch() { this.search = this.searchInput; this.page = 1; },

        editEvent(id) { //ส่วนส่ง id ไปให้หน้า edit_event
            console.log("Edit event ID:", id);
            this.$router.push(`/EditEvent/${id}`)
        },
        // ไว้ให้ปุ่มไม่ error (ต่อยอดภายหลังได้)

        toggleSort() { this.showSort = !this.showSort; },
        goToPage(p) { if (p < 1) p = 1; if (p > this.totalPages) p = this.totalPages || 1; this.page = p; },
        // editEvent(id) { console.log("Edit event", id); },

        async deleteEvent(id) {
            const ev = this.normalized.find(e => e.id === id);
            const title = ev?.evn_title || 'this event';

            const { isConfirmed } = await Swal.fire({
                title: 'ARE YOU SURE TO DELETE?',
                html: `This will be deleted permanently.<br>Are you sure?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                confirmButtonColor: '#42BB4C',
                cancelButtonColor: '#BA0C16',
                customClass: {
                    confirmButton: 'equal-btn', cancelButton: 'equal-btn'
                },
                customClass: {
                    confirmButton: 'w-28 text-center', // ปุ่ม OK
                    cancelButton: 'w-28 text-center',   // ปุ่ม Cancel
                    actions: 'space-x-8', // ช่องว่างระหว่างปุ่ม
                }
            });
            if (!isConfirmed) return;

            try {
                await axios.patch(`/event/${id}/deleted`);
                await Swal.fire({
                    title: 'DELETE SUCCESS! ',
                    text: 'We have deleted the new event.',
                    icon: 'success',
                    // timer: 2000, ปิดอัตโนมัติหลัง 2 วินาที
                    // เปิดใช้ปุ่ม OK
                    showConfirmButton: true, // เปลี่ยนเป็น true เพื่อแสดงปุ่ม
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#42BB4C',
                    customClass: {
                        confirmButton: 'w-15 text-center text-lg' // กำหนดขนาดปุ่มเอง
                    }
                });
                this.fetchEvent();
            } catch (err) {
                console.error("Error deleting event", err);
                await Swal.fire({
                    title: 'ERROR!',
                    // =========== ใช้ตอนทดสอบ ===========
                    text: err?.response?.data?.message || 'เกิดข้อผิดพลาดในการลบข้อมูล',
                    // =========== ใช้จริง ===========
                    // text: "Sorry, Please try again later.",
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#42BB4C',
                    customClass: {
                        confirmButton: 'w-15 text-center text-lg' // กำหนดขนาดปุ่มเอง
                    }
                });
            }
        },

        formatDate(val) {
            if (!val) return 'N/A';
            const d = new Date(val);
            if (isNaN(d)) return val;
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            return `${dd}/${mm}/${yyyy}`;
        },

        badgeClass(status) {
            // สร้าง badge แบบยูทิลิตี้ล้วน
            const base = 'inline-block min-w-[70px] rounded-full px-2.5 py-1 text-xs font-bold capitalize';
            switch ((status || '').toLowerCase()) {
                // case 'deleted':  return `${base} bg-rose-100 text-rose-800`; ยกเลิกใช้ แต่เก็บไว้ก่อน
                case 'done': return `${base} bg-emerald-100 text-emerald-700`;
                case 'upcoming': return `${base} bg-amber-200 text-amber-900`;
                default: return `${base} bg-slate-200 text-slate-700`;
            }
        },
    }
};
</script>
