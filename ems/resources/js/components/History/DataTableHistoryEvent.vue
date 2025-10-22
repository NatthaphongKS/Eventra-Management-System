<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-neutral-200">
                    <th class="w-[5%] py-3 text-center">#</th>
                    <th class="w-[55%] py-3 text-left">Event</th>
                    <th class="w-[10%] py-3 text-center"> Created By </th>
                    <th class="w-[10%] py-3 text-center"> Created Date </th>
                    <th class="w-[10%] py-3 text-center"> Deleted By </th>
                    <th class="w-[10%] py-3 text-center"> Deleted Date </th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(eventItem, eventIndex) in pagedRows" :key="eventItem.id ?? eventIndex"
                    class="odd:bg-white hover:bg-slate-100">
                    <!-- ลำดับ -->
                    <td class="w-[5%] py-2 text-center text-sm text-slate-700 border-t">
                        {{ rowStartIndex + eventIndex + 1 }}
                    </td>

                    <!-- ชื่ออีเวนต์ -->
                    <td class="w-[55%] py-2 text-left border-t">
                        <span class="block truncate text-sm text-slate-800">
                            {{ eventItem.title || 'N/A' }}
                        </span>
                    </td>
                    <!-- ชื่อคนสร้าง -->
                    <td class="w-[10%] py-2 text-center border-t">
                        <span class="block truncate text-sm text-slate-800">
                            {{ eventItem.createdPerson || 'N/A' }}
                        </span>
                    </td>
                    <!-- ชื่อเวลาสร้าง -->
                    <td class="w-[10%] py-2 text-center border-t">
                        <span class="block truncate text-sm text-slate-800">
                            {{ formatDate(eventItem.createdTime) || 'N/A' }}
                        </span>
                    </td>
                    <!-- ชื่ออคนลบ -->
                    <td class="w-[10%] py-2 text-center border-t">
                        <span class="block truncate text-sm text-slate-800">
                            {{ eventItem.deletedPerson || 'N/A' }}
                        </span>
                    </td>
                    <!-- เวลาลบ -->
                    <td class="w-[10%] py-2 text-center border-t">
                        <span class="block truncate text-sm text-slate-800">
                            {{ formatDate(eventItem.deletedTime) || 'N/A' }}
                        </span>
                    </td>

                </tr>

                <tr v-if="pagedRows.length === 0">
                    <td :colspan="9" class="px-3 py-6 text-center text-slate-600">
                        No data found
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Footer (limit/page + pagination + ช่วงข้อมูล) -->
    <div class="flex flex-col gap-3 bg-white px-3 py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <!-- ซ้าย: limit/page -->
            <div class="flex items-center gap-2 text-sm text-slate-700">
                <span>แสดง</span>
                <select class="rounded-lg border px-2 py-1 bg-white focus:outline-none focus:ring-2 focus:ring-rose-200"
                    :value="innerPageSize" @change="onChangePageSize">
                    <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <span>{{ rowStartIndex + 1  }}- {{pagedRows.length +rowStartIndex }} รายการ</span>
                <div class="text-xs text-slate-500">
                    {{ displayFrom }}{{ displayTo }} จาก {{ totalItems }} รายการ
                </div>
            </div>

        </div>
    </div>
    <!-- ===== Pagination ===== -->
    <div class="mt-4 flex items-center justify-center gap-3">
        <!-- ปุ่มลูกศรซ้าย -->
        <button class="pg-arrow" :disabled="page === 1" @click="goToPage(page - 1)">
            <svg viewBox="0 0 24 24">
                <path d="M6 12 L18 4 L18 20 Z" />
            </svg>
        </button>

        <!-- หมายเลขเพจ -->
        <template v-for="(it, idx) in pageItems" :key="idx">
            <button v-if="it.type === 'page'" class="pg-num" :class="{ 'pg-active': it.value === safePage }"
                :aria-current="it.value === page ? 'page' : null" @click="goToPage(it.value)">
                {{ it.value }}
            </button>

            <!-- จุดคั่น -->
            <span v-else class="pg-ellipsis">
                <i class="dot"></i><i class="dot"></i><i class="dot"></i>
            </span>
        </template>

        <button class="pg-arrow" :disabled="page === totalPages || totalPages === 0" @click="goToPage(page + 1)">
            <svg viewBox="0 0 24 24" style="transform: scaleX(-1)">
                <path d="M6 12 L18 4 L18 20 Z" />

            </svg>
        </button>
    </div>
</template>

<script>
export default {
    name: 'DataTableHistoryEvent',

    props: {
        /** ส่งเข้ามาเฉพาะ rows ที่ filter/sort แล้ว */
        rows: { type: Array, default: () => [
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },
            {
                title:'งานต่อยมวยมอสกับโอม',
                createdPerson: 'มอส',
                createdTime: '2025-10-15 14:30:00',
                deletedPerson: 'โอม',
                deletedTime: '2025-10-15 14:30:01'
            },

        ] },

        /** optional controlled-mode */
        page: { type: Number, default: undefined },
        pageSize: { type: Number, default: undefined },

        /** options */
        pageSizeOptions: { type: Array, default: () => [10, 20, 50, 100] },
    },

    emits: ['updatePage:page', 'updatePageSize:pageSize'],

    data() {
        return {
            /* ===== Pagination state (controlled / uncontrolled) ===== */
            innerPage: this.page ?? 1,
            innerPageSize: this.pageSize ?? 10,
        }
    },

    watch: {
        page(v) {
            if (typeof v === 'number') this.innerPage = v
        },
        pageSize(v) {
            if (typeof v === 'number') this.innerPageSize = v
        },
    },

    computed: {
        /* ===== Derived ===== */
        totalItems() {
            return this.rows.length
        },
        totalPages() {
            return Math.ceil(this.totalItems / this.innerPageSize) || 0
        },
        safePage() {
            const t = this.totalPages
            let p = this.innerPage
            if (t === 0) return 1
            if (p < 1) p = 1
            if (p > t) p = t
            return p
        },
        rowStartIndex() {
            return (this.safePage - 1) * this.innerPageSize
        },
        pagedRows() {
            const start = this.rowStartIndex
            return this.rows.slice(start, start + this.innerPageSize)
        },
        pageItems() {
            const total = this.totalPages || 1
            const cur = this.safePage
            const items = []

            if (total <= 7) {
                for (let i = 1; i <= total; i++) items.push({ type: 'page', value: i })
                return items
            }

            const addPage = (p) => items.push({ type: 'page', value: p })
            const addDots = () => items.push({ type: 'dots' })

            addPage(1)
            if (cur > 3) addDots()

            const s = Math.max(2, cur - 1)
            const e = Math.min(total - 1, cur + 1)
            for (let p = s; p <= e; p++) addPage(p)

            if (cur < total - 2) addDots()
            addPage(total)

            return items
        },
    },

    methods: {
        /* ===== Handlers ===== */
        onChangePageSize(e) {
            const next = Number(e?.target?.value ?? e) || 10
            this.innerPageSize = next
            // รีเซ็ตหน้าแรก
            this.goToPage(1)
            this.$emit('updatePageSize:pageSize', next) // if parent cares
        },

        goToPage(p) {
            const t = this.totalPages
            let next = Number(p)
            if (Number.isNaN(next) || t === 0) next = 1
            if (next < 1) next = 1
            if (next > t) next = t
            this.innerPage = next
            this.$emit('updatePage:page', next) // if parent cares
        },

        /* ===== Utils (เหมือนเดิม) ===== */
        formatDate(dateValue) {
            if (!dateValue) return 'N/A'
            const dateObj = new Date(dateValue)
            if (Number.isNaN(dateObj.getTime())) return dateValue
            const year = dateObj.getFullYear()
            const month = String(dateObj.getMonth() + 1).padStart(2, '0')
            const day = String(dateObj.getDate()).padStart(2, '0')
            // return `${year}-${month}-${day} ${hour}:${minute}:${second}`
            return `${day}/${month}/${year}`
        },

        timeText(startTime, endTime) {
            const start = startTime ? String(startTime).slice(0, 5) : '??:??'
            const end = endTime ? String(endTime).slice(0, 5) : '??:??'
            return `${start}-${end}`
        },

    },
}

</script>
