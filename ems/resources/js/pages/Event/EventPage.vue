<template>
    <section class="p-0">

        <div class="mt-3 mb-1 flex items-center gap-4">
            <div class="flex items-center gap-3 flex-1">
                <input v-model.trim="searchInput" placeholder="Search" @keyup.enter="applySearch"
                    class="h-11 w-[750px] rounded-full border border-slate-200 bg-white px-3 outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200" />
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full
       bg-[#b91c1c] text-white hover:bg-[#991b1b]
       focus:outline-none focus:ring-2 focus:ring-red-300"
                    @click="applySearch"
                    aria-label="Search" title="ค้นหา (คลิกหรือกด Enter)">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </button>
            </div>

            <Filter v-model="filters" :filter-fields="filterFields" button-label="Filter" @apply="page = 1" />
            <EventSort v-model="selectedSort" :options="sortOptions" />

            <router-link to="/add-event" class="ml-auto inline-flex h-11 items-center rounded-full
       bg-[#b91c1c] px-4 font-semibold text-white
       hover:bg-[#991b1b] focus:outline-none
       focus:ring-2 focus:ring-red-300">
                + Add
            </router-link>
        </div>

        <div v-show="showFilter" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
        </div>

        <DataTable :rows="paged" :columns="eventTableColumns" :loading="false" :total-items="sorted.length"
            :page-size-options="[10, 20, 50, 100]" :page="page" :pageSize="pageSize" :sortKey="sortBy"
            :sortOrder="sortOrder" @update:page="page = $event" @update:pageSize="pageSize = $event; page = 1"
            @sort="handleClientSort" row-key="id" :show-row-number="true" class="mt-4">

            <!-- [แก้โดยเอิร์ธ 2025-10-25]: กดได้ทั้งบรรทัด — ครอบทุกเซลล์ (ยกเว้น actions) ให้คลิก/Enter/Space แล้วไป goDetails(row.id) -->

            <template #cell-evn_title="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full pl-3 py-2 text-slate-800 font-medium truncate hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)"
                      title="ดูรายละเอียด">
                    {{ value }}
                </span>
            </template>

            <template #cell-cat_name="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full pl-3 py-2 hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_date="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_timestart="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_num_guest="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_sum_accept="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_status="{ row, value }">
                <span role="button" tabindex="0"
                      class="block w-full h-full py-1 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                      @click="goDetails(row.id)"
                      @keydown.enter.prevent="goDetails(row.id)"
                      @keydown.space.prevent="goDetails(row.id)">
                    <span :class="badgeClass(value)">
                        {{ value || 'N/A' }}
                    </span>
                </span>
            </template>
            <!-- ปุ่ม Filter/Sort (ตอนนี้ยัง UI) -->
            <!-- Toolbar -->
            <!-- ปุ่ม Filter กลาง (schema-driven) -->
            <Filter v-model="filters" :filter-fields="filterFields" button-label="Filter" @apply="page = 1" />


            <template #actions="{ row }">
                <button @click="editEvent(row.id)" class="rounded-lg p-1.5 hover:bg-slate-100" title="Edit">
                    <PencilIcon class="h-5 w-5 text-neutral-800" />
                </button>
                <button @click="openDelete(row.id)" class="rounded-lg p-1.5 hover:bg-slate-100" title="Delete">

                    <TrashIcon class="h-5 w-5 text-neutral-800" />
                </button>
                <router-link :to="`/EventCheckIn/eveId/${row.id}`" class="rounded-lg p-1.5 hover:bg-slate-100"
                    title="Check-in">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px"
                        fill="currentColor" class="h-5 w-5 text-neutral-800">
                        <path
                            d="M160-120q-33 0-56.5-23.5T80-200v-560q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v560q0 33-23.5 56.5T800-120H160Zm0-80h640v-560H160v560Zm40-80h200v-80H200v80Zm382-80 198-198-57-57-141 142-57-57-56 57 113 113Zm-382-80h200v-80H200v80Zm0-160h200v-80H200v80Zm-40 400v-560 560Z" />
                    </svg>
                </router-link>
            </template>


        </DataTable>
        <ModalAlert :open="showModalAsk" type="confirm" title='ARE YOU SURE TO DELETE'
            message="This wil by deleted permanently  Are you sure?" :show-cancel="true" okText="OK"
            cancelText="Calcel" @confirm="onConfirmDelete" @cancel="onCancelDelete" />
        <ModalAlert :open="showModalSuccess" type="success" title='DELETE SUCCESS!'
            message="We have already deleted event." :show-cancel="false" okText="OK"
            @confirm="onConfirmSuccess" />
        <ModalAlert :open="showModalFail" type="error" title='ERROR!'
            message="Sorry, Please try again later." :show-cancel="false" okText="OK"
            @confirm="onConfirmFail" />

    </section>
</template>

<script>
import ModalAlert from "../../components/Alert/ModalAlert.vue";
import axios from "axios";
import 'sweetalert2/dist/sweetalert2.min.css';
// (เปลี่ยน) Import DataTable แทน EventTable
import DataTable from '@/components/DataTable.vue';
import Filter from '@/components/Button/Filter.vue'
import EventSort from "@/components/IndexEvent/EventSort.vue";
import {
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.withCredentials = true

export default {
    // (เปลี่ยน) ลงทะเบียน DataTable
    components: {
        MagnifyingGlassIcon, PencilIcon, TrashIcon,
        Filter, EventSort, DataTable, ModalAlert
    },

    filters: { category: [], status: [] },
    data() {
        return {
            event: [],
            categories: [],
            catMap: {},

            searchInput: "",
            search: "",

            showFilter: false,
            showSort: false, // ยังคงเก็บไว้ แต่ DataTable ไม่ได้ใช้โดยตรง

            // (สำคัญ) State สำหรับ Sort ยังคงต้องมี เพราะ Logic อยู่ที่นี่
            sortBy: "evn_date",
            sortOrder: "desc", // (ใช้ desc ตาม selectedSort เริ่มต้น)
            selectedSort: { id: 'date_desc', key: 'evn_date', order: 'desc', type: 'date' },
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

            // (สำคัญ) State สำหรับ Paging ยังคงต้องมี
            page: 1,
            pageSize: 10,

            // Filter state (เหมือนเดิม)
            filters: { category: [], status: [] },
            flt: {
                category: 'all',
                status: 'all',
                dateFrom: '',
                dateTo: ''
            },
            _appliedFlt: null,

            // (เพิ่ม) นิยาม Columns สำหรับ DataTable (เพิ่ม sortable)
            eventTableColumns: [
                { key: 'evn_title', label: 'Event', class: 'text-left', headerClass: 'w-[450px]', cellClass: 'pl-3 text-slate-800 font-medium truncate', sortable: true },
                { key: 'cat_name', label: 'Category', class: 'text-left', headerClass: 'pl-2', cellClass: 'pl-3', sortable: true },
                { key: 'evn_date', label: 'Date (D/M/Y)', class: 'w-[120px] text-center whitespace-nowrap', format: this.formatDate, sortable: true },
                { key: 'evn_timestart', label: 'Time', class: 'w-[110px] text-center whitespace-nowrap justify-center', cellClass: 'justify-center', format: (v, r) => this.timeText(v, r.evn_timeend) },
                { key: 'evn_num_guest', label: 'Invited', class: 'w-20 text-center', sortable: true },
                { key: 'evn_sum_accept', label: 'Accepted', class: 'w-20 text-center', sortable: true },
                { key: 'evn_status', label: 'Status', class: '', headerClass: 'content-center', cellClass: 'text-center', sortable: true },
            ],
            showModalAsk: false,
            showModalSuccess: false,
            showModalFail: false,
        }
    },

    // (เหมือนเดิม)
    async created() {
        await Promise.all([this.fetchEvent(), this.fetchCategories()]);
    },

    // (เหมือนเดิม - Client-Side Logic ทั้งหมด)
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
        normalized() {
            return this.event.map(e => ({
                id: e.id, // (ต้องมี id สำหรับ rowKey)
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
        byFilter() {
            const catSet = new Set(this.filters.category.map(String))
            const stSet = new Set(this.filters.status.map(s => String(s).toLowerCase()))
            return this.normalized.filter(e => {
                const byCat = catSet.size === 0 || catSet.has(String(e.evn_cat_id))
                const bySt = stSet.size === 0 || stSet.has(String((e.evn_status || '').toLowerCase()))
                return byCat && bySt
            })
        },
        filtered() {
            const q = this.search.toLowerCase().trim()
            if (!q) return this.byFilter
            return this.byFilter.filter(e =>
                `${e.evn_title} ${e.cat_name} ${e.evn_date} ${e.evn_timestart} ${e.evn_timeend} ${e.evn_status}`
                    .toLowerCase()
                    .includes(q)
            )
        },
        sorted() {
            // (สำคัญ) ใช้ sortBy และ sortOrder จาก data()
            const key = this.sortBy
            const order = this.sortOrder
            const arr = [...this.filtered]
            const parseVal = v => {
                if (key === 'evn_date') return new Date(v || 0).getTime() // (แก้) ใช้ key ตรงๆ
                if (['evn_num_guest', 'evn_sum_accept'].includes(key)) return Number(v) || 0
                return (v ?? '').toString().toLowerCase()
            }
            arr.sort((a, b) => {
                const va = parseVal(a[key]), vb = parseVal(b[key])
                if (va < vb) return order === "asc" ? -1 : 1
                if (va > vb) return order === "asc" ? 1 : -1
                // (เพิ่ม) Fallback sort by ID ถ้าค่าเท่ากัน
                return (a.id || 0) - (b.id || 0);
            })
            return arr
        },
        // (สำคัญ) totalPages และ paged ยังต้องมี เพราะ DataTable รับ :rows="paged"
        totalPages() { return Math.ceil(this.sorted.length / this.pageSize) || 1 },
        paged() {
            const start = (this.page - 1) * this.pageSize
            // (สำคัญ) ต้อง slice จาก sorted
            return this.sorted.slice(start, start + this.pageSize)
        },

    },

    // (เหมือนเดิม)
    watch: {
        search() { this.page = 1; },
        pageSize() { this.page = 1; },
        // (สำคัญ) Watcher นี้ต้องเปลี่ยน -> ให้เปลี่ยน sortBy/sortOrder แต่ *ไม่* ต้องโหลดใหม่
        selectedSort: {
            handler(v) {
                if (!v) return;
                // แค่เปลี่ยนค่า sortBy/sortOrder -> computed `sorted()` จะทำงานใหม่เอง
                this.sortBy = v.key;
                this.sortOrder = v.order;
                this.page = 1; // กลับไปหน้า 1 เมื่อ sort
            },
            immediate: true, // ยังคง immediate เพื่อให้ค่าเริ่มต้นถูกต้อง
            deep: true,
        },
        // (เพิ่ม) Watch page และ pageSize เพื่อเช็คขอบเขต
        page(newPage) {
            const total = this.totalPages;
            if (newPage < 1) this.page = 1;
            else if (newPage > total) this.page = total;
        },
        pageSize() {
            // เมื่อ pageSize เปลี่ยน, computed totalPages จะเปลี่ยน
            // ถ้าหน้าปัจจุบันเกิน ให้กลับไปหน้าสุดท้าย
            if (this.page > this.totalPages) {
                this.page = this.totalPages;
            }
        }

    },

    // (เหมือนเดิมส่วนใหญ่)
    methods: {
        // (fetchEvent ดึงข้อมูล *ทั้งหมด* เหมือนเดิม)
        async fetchEvent() {
            try {
                // (สำคัญ) API เดิม /get-event ไม่ต้องส่ง params paging/sort
                const res = await axios.get("/get-event");
                this.event = Array.isArray(res.data) ? res.data : (res.data?.data || []);
                // (คำนวณ totalPages ใหม่หลังจากได้ข้อมูล)
                // ไม่ต้องทำ computed จะทำเอง
                // (เช็ค page ปัจจุบันอีกครั้ง เผื่อข้อมูลลดลง)
                if (this.page > this.totalPages) {
                    this.page = this.totalPages;
                }
            } catch (err) {
                console.error("fetchEvent error", err);
                this.event = [];
            }
        },
        // (fetchCategories เหมือนเดิม)
        async fetchCategories() {
            try {
                const res = await axios.get("/event-info");
                const cats = res.data?.categories || [];
                this.categories = cats.map(c => ({ id: c.id, name: c.cat_name }));
                this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
            } catch (err) {
                console.error("fetchCategories error", err);
                this.categories = [];
                this.catMap = {};
            }
        },

        applySearch() { this.search = this.searchInput; this.page = 1; },

        editEvent(id) {
            this.$router.push(`/EditEvent/${id}`)
        },
        toggleFilter() { this.showFilter = !this.showFilter; },
        toggleSort() { this.showSort = !this.showSort; },
        // (ลบ) goToPage (ให้ DataTable จัดการผ่าน @update:page)

        // methods:
        openDelete(id) {
            this.deleteId = id
            this.showModalAsk = true
        },
        async onConfirmDelete() {
            const id = this.deleteId
            this.showModal = false
            if (!id) return
            try {
                await axios.patch(`/event/${id}/deleted`)
                this.showModalSuccess = true
                this.fetchEvent()
            } catch {
                this.showModalFail = true
            }
        },
        onCancelDelete() {
            this.showModal = false
            this.deleteId = null
        },
        onConfirmSuccess(){
            this.showModalSuccess = false
        },
        onConfirmFail(){
            this.showModalFail = false
        },


        // (formatDate, timeText, badgeClass เหมือนเดิม)
        formatDate(val) {
            if (!val) return 'N/A';
            try {
                const d = new Date(val);
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yyyy = d.getFullYear();
                return `${dd}/${mm}/${yyyy}`;
            } catch (e) { return 'Invalid Date'; }
        },
        timeText(startTime, endTime) {
            const format = (t) => t ? String(t).slice(0, 5) : '??:??';
            return `${format(startTime)}-${format(endTime)}`;
        },
        badgeClass(status) {
            const base = 'inline-block min-w-[110px] rounded-md border px-2.5 py-1 text-xs capitalize';
            switch ((status || '').toLowerCase()) {
                case 'done': return `${base} bg-[#DCFCE7] text-[#00A73D]`;
                case 'upcoming': return `${base} bg-[#FFF9C2] text-[#FDC800]`; // (แก้สี)
                case 'ongoing': return `${base} bg-[#DFF3FE] text-[#0084D1]`;     // (แก้สี)
                default: return `${base} bg-slate-100 text-slate-700`;     // (แก้สี)
            }
        },

        // (เพิ่ม) Method รับ Event @sort จาก DataTable (เพื่ออัพเดท sortBy/Order)
        handleClientSort({ key, order }) {
            this.sortBy = key;
            this.sortOrder = order;
            this.page = 1;// กลับไปหน้า 1 เมื่อ sort
            // (อัพเดท selectedSort ด้วย เพื่อให้ UI ของ EventSort ตรงกัน)
            this.selectedSort = this.sortOptions.find(opt => opt.key === key && opt.order === order) || this.selectedSort;
        },

            // earth: go to event details (Options API)
        goDetails(id) {
            // แบบใช้ชื่อ route (ถ้าตั้งชื่อไว้ว่า 'events.show')
            try {
            this.$router.push({ name: 'events.show', params: { id: String(id) } })
            } catch (_) {
            // fallback: ใช้ path ตรง
            this.$router.push({ path: `/events/${id}` })
            }
        },
    }
};
</script>
