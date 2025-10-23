<template>
    <div class="overflow-hidden rounded-2xl border border-neutral-200 stroke-neutral-200">
        <table class="w-full table-auto  ">
            <thead>
                <!-- หัวตาราง -->
                <tr class="bg-neutral-100">
                    <th class="w-12 py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">#</th>
                    <th class="w-[26%] py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">Event</th>
                    <th class="w-[14%] py-3 text-center text-neutral-800 font-semibold">Category</th>
                    <th
                        class="w-[110px] py-3 text-center whitespace-nowrap text-neutral-800 font-semibold font-[Poppins] text-[16px]">
                        Date (D/M/Y)</th>
                    <th class="w-[92px] py-3 text-center whitespace-nowrap text-neutral-800 font-semibold font-[Poppins] text-[16px]">
                        Time</th>
                    <th class="w-20 py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">Invited</th>
                    <th class="w-20 py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">Accepted</th>
                    <th class="w-[110px] py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">Status</th>
                    <th class="w-28 py-3 text-center text-neutral-800 font-semibold font-[Poppins] text-[16px]">Action</th>
                </tr>
            </thead>

            <!-- เนื้อหาตาราง -->
            <tbody>
                <tr v-for="(eventItem, eventIndex) in pagedRows" :key="eventItem.id ?? eventIndex"
                    class="odd:bg-white hover:bg-slate-100">
                    <!-- ลำดับ -->
                    <td class="px-2 py-2 text-center text-neutral-800 border-t font-medium font-[Poppins] text-[15px]">
                        {{ rowStartIndex + eventIndex + 1 }}
                    </td>

                    <!-- ชื่ออีเวนต์ -->
                    <td class="px-3 py-2 text-center border-t font-medium font-[Poppins] text-[15px]">
                        <span class="block truncate  text-slate-800">
                            {{ eventItem.evn_title || 'N/A' }}
                        </span>
                    </td>

                    <!-- หมวดหมู่ -->
                    <td class="px-3 py-2 text-center border-t font-medium font-[Poppins] text-[15px]">
                        <span class="block truncate  text-slate-800">
                            {{ eventItem.cat_name || 'N/A' }}
                        </span>
                    </td>

                    <!-- วันที่ -->
                    <td class="px-3 py-2 text-center text-neutral-800 border-t font-medium font-[Poppins] text-[15px]">
                        {{ formatDate(eventItem.evn_date) }}
                    </td>

                    <!-- เวลา -->
                    <td
                        class="px-3 py-2 text-center text-neutral-800 border-t whitespace-nowrap font-medium font-[Poppins] text-[15px]">
                        {{ timeText(eventItem.evn_timestart, eventItem.evn_timeend) }}
                    </td>

                    <!-- จำนวนเชิญ -->
                    <td class="px-3 py-2 text-center text-neutral-800 border-t font-medium font-[Poppins] text-[15px]">
                        {{ eventItem.evn_num_guest ?? '0' }}
                    </td>

                    <!-- จำนวนตอบรับ -->
                    <td class="px-3 py-2 text-center text-neutral-800 border-t font-medium font-[Poppins] text-[15px]">
                        {{ eventItem.evn_sum_accept ?? 'N/A' }}
                    </td>

                    <!-- สถานะ -->
                    <td class="px-3 py-2 text-center border-t font-medium font-[Poppins] text-[15px]">
                        <span :class="badgeClass(eventItem.evn_status)">
                            {{ eventItem.evn_status || 'N/A' }}
                        </span>
                    </td>

                    <!-- Action -->
                    <td class="px-3 py-2 text-center  border-t">
                        <div class="flex items-center justify-center gap-1.5">
                            <button @click="$emit('edit', eventItem.id)"
                                class="rounded-lg p-1.5 hover:bg-slate-100 focus:outline-none  " title="Edit">
                                <!-- icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                    width="24px" fill="neutral-800">
                                    <path
                                        d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z" />
                                </svg>
                            </button>

                            <button @click="$emit('delete', eventItem.id)"
                                class="rounded-lg p-1.5 hover:bg-slate-100 focus:outline-none " title="Delete">
                                <!-- icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                    width="24px" fill="neutral-800">
                                    <path
                                        d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z" />
                                </svg>
                            </button>

                            <router-link :to="`/EventCheckIn/${eventItem.id}`"
                                class="rounded-lg p-1.5 hover:bg-slate-100 focus:outline-none " title="Check-in">
                                <!-- icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                    width="24px" fill="neutral-800">
                                    <path
                                        d="M160-120q-33 0-56.5-23.5T80-200v-560q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v560q0 33-23.5 56.5T800-120H160Zm0-80h640v-560H160v560Zm40-80h200v-80H200v80Zm382-80 198-198-57-57-141 142-57-57-56 57 113 113Zm-382-80h200v-80H200v80Zm0-160h200v-80H200v80Zm-40 400v-560 560Z" />
                                </svg>
                            </router-link>
                        </div>
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
            <div class="flex items-center gap-2 font-regular font-[Poppins]">
                <span>แสดง</span>

                <!-- ⬇️ กล่อง select + ลูกศรแดง -->
                <div class="relative inline-block">
                    <select class="appearance-none rounded-[20px] border border-red-700 bg-white px-2 py-1 pr-8
                 focus:outline-none focus:ring-2 focus:ring-rose-200 outline-red-700" :value="innerPageSize"
                        @change="onChangePageSize">
                        <option v-for="opt in pageSizeOptions" :key="opt" :value="opt" class="px-2 py-1 pr-8">{{ opt }}</option>
                    </select>
                    <!-- ลูกศรลงสีแดง -->
                    <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </div>

                <span>{{ rowStartIndex + 1 }}-{{ pagedRows.length + rowStartIndex }} </span>
                <div class="font-regular font-poppins">
                    จาก {{ totalItems }} รายการ
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
            <button v-if="it.type === 'page'" class="pg-num" :class="{ 'pg-active': it.value === page }"
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
                />
            </svg>
        </button>
    </div>
    <!-- ขวา: ช่วงข้อมูล -->
</template>

<script setup>
import { computed, ref, watch } from 'vue'


/** ส่งเข้ามาเฉพาะ rows ที่ filter/sort แล้ว
props เป็นส่วนรับข้อมมูลจาก parent component*/
const props = defineProps({
    rows: { type: Array, default: () => [] },

    /** optional controlled-mode */
    page: { type: Number, default: undefined },
    pageSize: { type: Number, default: undefined },

    /** limitpage */
    pageSizeOptions: { type: Array, default: () => [10, 20, 50, 100] },
})

const emit = defineEmits([
    'edit', 'delete',
    /** optional v-model สองทาง ถ้า parent อยากเก็บ state เอง */
    'update:page', 'update:pageSize'
])

/* ===== Pagination state (รองรับทั้ง controlled และ uncontrolled) ===== */
const innerPage = ref(props.page ?? 1)
const innerPageSize = ref(props.pageSize ?? 10)

watch(() => props.page, (v) => {
    if (typeof v === 'number') innerPage.value = v
})
watch(() => props.pageSize, (v) => {
    if (typeof v === 'number') innerPageSize.value = v
})

/* ===== Derived ===== */
const totalItems = computed(() => props.rows.length)
const totalPages = computed(() => Math.ceil(totalItems.value / innerPageSize.value) || 0)

const safePage = computed(() => {
    const t = totalPages.value
    let p = innerPage.value
    if (t === 0) return 1
    if (p < 1) p = 1
    if (p > t) p = t
    return p
})

const rowStartIndex = computed(() => (safePage.value - 1) * innerPageSize.value)

const pagedRows = computed(() => {
    const start = rowStartIndex.value
    return props.rows.slice(start, start + innerPageSize.value)
})

const pageItems = computed(() => {
    const total = totalPages.value || 1
    const cur = safePage.value
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
})

/* ===== Handlers ===== */
function onChangePageSize(e) {
    const next = Number(e.target.value) || 10
    innerPageSize.value = next
    // รีเซ็ตหน้าแรก
    goToPage(1)
    emit('update:pageSize', next) // if parent cares
}

function goToPage(p) {
    const t = totalPages.value
    let next = Number(p)
    if (Number.isNaN(next) || t === 0) next = 1
    if (next < 1) next = 1
    if (next > t) next = t
    innerPage.value = next
    emit('update:page', next) // if parent cares
}

/* ===== Utils (เหมือนเดิม) ===== */
function formatDate(dateValue) {
    if (!dateValue) return 'N/A'
    const dateObj = new Date(dateValue)
    if (Number.isNaN(dateObj.getTime())) return dateValue
    const day = String(dateObj.getDate()).padStart(2, '0')
    const month = String(dateObj.getMonth() + 1).padStart(2, '0')
    const year = dateObj.getFullYear()
    return `${day}/${month}/${year}`
}
function timeText(startTime, endTime) {
    const start = startTime ? String(startTime).slice(0, 5) : '??:??'
    const end = endTime ? String(endTime).slice(0, 5) : '??:??'
    return `${start}-${end}`
}
function badgeClass(status) {
    const base = 'inline-block min-w-[70px] rounded-full px-2.5 py-1 text-xs font-bold capitalize'
    switch ((status || '').toLowerCase()) {
        case 'done': return `${base} bg-emerald-100 text-emerald-700`
        case 'upcoming': return `${base} bg-amber-200 text-amber-900`
        case 'ongoing': return `${base} bg-sky-200 text-sky-800`
        default: return `${base} bg-slate-200 text-neutral-800`
    }
}
</script>
