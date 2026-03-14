<!--
    ชื่อไฟล์: EventPage.vue
    คำอธิบาย: หน้าแสดงรายการกิจกรรม (Event list page) — มีการค้นหา, กรอง, เรียงลำดับ, แก้ไข, ลบ และเช็คอิน
    Input: ข้อมูลกิจกรรมจาก API (get-event), ข้อมูลหมวดหมู่ (event-info), สิทธิ์ผู้ใช้ (permission)
    Output: ตารางกิจกรรม, ฟังก์ชันค้นหา/กรอง/เรียงลำดับ, ปุ่มแก้ไข/ลบ/เช็คอิน, Modal แจ้งเตือนผลลัพธ์
    ชื่อผู้เขียน/แก้ไข: Yothin S.
    วันที่จัดทำ/แก้ไข: 09/03/2026
-->

<template>
    <section class="p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 w-full gap-3">
            <!-- Search -->
            <div class="flex-1">
                <SearchBar v-model="searchInput" placeholder="Search Event" @search="applySearch" class="" />
            </div>
            <div class="flex gap-2 flex-shrink-0 mt-[30px] items-stretch">
                <div class="flex flex-row mt-2">
                    <!-- DatePicker -->
                    <div class="h-[44px]">
                        <EventDatePicker v-model="selectedDate" class="h-full [&_button]:h-full [&_input]:h-full" />
                    </div>
                    <EventFilter v-model="filters" :categories="activeCategories" :status-options="statusOptions"
                        @update:modelValue="applyFilter" class="h-[44px] [&_button]:h-full" />

                    <EventSort v-model="selectedSort" :options="sortOptions" @change="onPickSort"
                        class="h-[44px] [&_button]:h-full" />
                </div>
                <!-- Add Button -->
                <AddButton @click="$router.push('/add-event')" />
            </div>
        </div>

        <!-- ตาราง -->
        <DataTable :rows="paged" :columns="eventTableColumns" :loading="false" :total-items="sorted.length"
            :page-size-options="[10, 20, 50, 100]" :page="page" :pageSize="pageSize" :sortKey="sortBy"
            :sortOrder="sortOrder" @update:page="page = $event" @update:pageSize="
                pageSize = $event;
            page = 1;
            " @sort="handleClientSort" row-key="id" :show-row-number="true" class="mt-4">
            <!-- คลิกได้ทั้งแถว -->
            <template #cell-evn_title="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full pl-3 py-2 text-slate-700 font-medium truncate   transition-colors"
                    :title="value">
                    {{ value && value.length > 35 ? value.substring(0, 35) + '...' : value }}
                </router-link>
            </template>

            <template #cell-cat_name="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full pl-2 py-2 text-slate-600 truncate   transition-colors"
                    :title="value">
                    {{ value && value.length > 35 ? value.substring(0, 35) + '...' : value }}
                </router-link>
            </template>

            <template #cell-evn_date="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full py-2 text-center text-slate-600   transition-colors">
                    {{ formatDate(value) }}
                </router-link>
            </template>

            <template #cell-evn_timestart="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full py-2 text-center text-slate-600   transition-colors">
                    {{ timeText(value, row.evn_timeend) }}
                </router-link>
            </template>

            <template #cell-evn_num_guest="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full py-2 text-center text-slate-600   transition-colors">
                    {{ value }}
                </router-link>
            </template>

            <template #cell-evn_sum_accept="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="block w-full h-full py-2 text-center text-slate-600   transition-colors">
                    {{ value }}
                </router-link>
            </template>

            <template #cell-evn_status="{ row, value }">
                <router-link :to="{ name: 'event.details', params: { id: row.id } }"
                    class="flex items-center justify-center w-full h-full py-2 transition-transform active:scale-95">
                    <span :class="badgeClass(value)">
                        {{ value || "N/A" }}
                    </span>
                </router-link>
            </template>

            <template #actions="{ row }">
                <!-- Edit -->
                <button @click="canEdit(row) && editEvent(row.id)" :disabled="!canEdit(row)"
                    class="rounded-lg p-1.5 transition-colors" :class="!canEdit(row)
                        ? 'cursor-not-allowed opacity-40'
                        : 'cursor-pointer'" title="Edit">
                    <Icon icon="material-symbols:edit-rounded" width="20" height="20" :class="!canEdit(row)
                        ? 'text-neutral-400'
                        : 'text-neutral-725 hover:text-[#059669]'" />
                </button>

                <!-- Delete -->
                <button @click="canDelete(row) && openDelete(row.id)" :disabled="!canDelete(row)"
                    class="rounded-lg p-1.5 transition-colors" :class="!canDelete(row)
                        ? 'cursor-not-allowed opacity-40'
                        : 'cursor-pointer'" title="Delete">
                    <Icon icon="fluent:delete-12-filled" width="20" height="20" :class="!canDelete(row)
                        ? 'text-neutral-400'
                        : 'text-neutral-725 hover:text-[#dc2626]'" />
                </button>

                <!-- Check-in -->
                <span v-if="!canCheckin(row)" class="rounded-lg p-1.5 cursor-not-allowed opacity-40"
                    :title="checkinDisabledTitle(row)">
                    <Icon icon="material-symbols:fact-check-rounded" width="20" height="20" class="text-neutral-400" />
                </span>

                <router-link v-else :to="`/EventCheckIn/eveId/${row.id}`" class="rounded-lg p-1.5 transition-colors"
                    title="Check-in">
                    <Icon icon="material-symbols:fact-check-rounded" width="20" height="20"
                        class="text-neutral-725 hover:text-[#0084d1]" />
                </router-link>
            </template>
        </DataTable>

        <ModalAlert :open="showModalAsk" type="confirm" title="ARE YOU SURE TO DELETE"
            message="This will be deleted permanently. Are you sure?" :show-cancel="true" okText="OK"
            cancelText="Cancel" @confirm="onConfirmDelete" @cancel="onCancelDelete" />
        <ModalAlert :open="showModalLoading" type="progress" title="Deleting..."
            message="Please wait while the record is being deleted" :progress="deleteProgress" :show-cancel="false"
            class="hide-progress-text" />
        <ModalAlert :open="showModalSuccess" type="success" title="DELETE SUCCESS!"
            message="We have already deleted event." :show-cancel="false" okText="OK" @confirm="onConfirmSuccess" />
        <ModalAlert :open="showModalFail" type="error" title="ERROR!" message="Sorry, Please try again later."
            :show-cancel="false" okText="OK" @confirm="onConfirmFail" />
    </section>
</template>

<script>
import ModalAlert from "../../components/Alert/ModalAlert.vue";
import axios from "axios";
import DataTable from "@/components/DataTable.vue";
import EventSort from "@/components/IndexEvent/EventSort.vue";
import EventFilter from "@/components/IndexEvent/EventFilter.vue";
import SearchBar from "@/components/SearchBar.vue";
import AddButton from "@/components/AddButton.vue";
import EventDatePicker from "@/components/IndexEvent/EventDatePicker.vue";
import { Icon } from '@iconify/vue'

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = true;

export default {
    components: {
        Icon,
        EventSort,
        DataTable,
        EventFilter,
        SearchBar,
        AddButton,
        EventDatePicker,
        ModalAlert,
    },

    data() {
        return {
            selectedDate: { start: null, end: null },
            empPermission: "employee",
            isDeleting: false,
            deleteId: null,

            event: [],
            categories: [],
            catMap: {},

            searchInput: "",
            search: "",

            sortBy: "evn_status",
            sortOrder: "asc",
            selectedSort: {
                id: "status_asc",
                key: "evn_status",
                order: "asc",
                type: "custom",
            },
            sortOptions: [
                { id: "title_asc", label: "ชื่องาน A–Z", key: "evn_title", order: "asc", type: "text" },
                { id: "title_desc", label: "ชื่องาน Z–A", key: "evn_title", order: "desc", type: "text" },
                { id: "invited_desc", label: "จำนวนคนเชิญมากสุด", key: "evn_num_guest", order: "desc", type: "number" },
                { id: "invited_asc", label: "จำนวนคนเชิญน้อยสุด", key: "evn_num_guest", order: "asc", type: "number" },
                { id: "accepted_desc", label: "จำนวนคนเข้าร่วมมากสุด", key: "evn_sum_accept", order: "desc", type: "number" },
                { id: "accepted_asc", label: "จำนวนคนเข้าร่วมน้อยสุด", key: "evn_sum_accept", order: "asc", type: "number" },
                { id: "date_desc", label: "วันที่จัดงานใหม่สุด", key: "evn_date", order: "desc", type: "date" },
                { id: "date_asc", label: "วันที่จัดงานเก่าสุด", key: "evn_date", order: "asc", type: "date" },
                { id: "status_asc", label: "สถานะ (Ongoing → Done)", key: "evn_status", order: "asc", type: "custom" },
                { id: "status_desc", label: "สถานะ (Done → Ongoing)", key: "evn_status", order: "desc", type: "custom" },
            ],

            page: 1,
            pageSize: 10,

            filters: { category: [], status: [] },

            statusOptions: [
                { label: "Done", value: "done" },
                { label: "Ongoing", value: "ongoing" },
                { label: "Upcoming", value: "upcoming" },
            ],

            eventTableColumns: [
                { key: "evn_title", label: "Event", class: "text-left", headerClass: "w-[450px]", cellClass: "pl-3 text-slate-800 font-medium truncate", sortable: true },
                { key: "cat_name", label: "Category", class: "text-left", headerClass: "pl-2", cellClass: "pl-3", sortable: true },
                { key: "evn_date", label: "Date (D/M/Y)", class: "w-[120px] text-center whitespace-nowrap", format: this.formatDate, sortable: true },
                { key: "evn_timestart", label: "Time", class: "w-[110px] text-center whitespace-nowrap justify-center", cellClass: "justify-center", format: (v, r) => this.timeText(v, r.evn_timeend) },
                { key: "evn_num_guest", label: "Invited", class: "w-20 text-center", sortable: true },
                { key: "evn_sum_accept", label: "Accepted", class: "w-20 text-center", sortable: true },
                { key: "evn_status", label: "Status", class: "text-center", sortable: true },
            ],
            showModalAsk: false,
            showModalLoading: false,
            showModalSuccess: false,
            showModalFail: false,
            deleteProgress: 0,
            deleteProgressInterval: null,
        };
    },

    async created() {
        await Promise.all([
            this.fetchPermission(),
            this.fetchEvent(),
            this.fetchCategories(),
        ]);
    },

    computed: {
        /**
         * ชื่อฟังก์ชัน: normalized
         * คำอธิบาย: แปลงข้อมูล event ให้เป็นรูปแบบที่ใช้ในตาราง
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        normalized() {
            return this.event.map((e) => ({
                id: e.id,
                evn_title: e.evn_title ?? e.evn_name ?? "",
                evn_cat_id: e.evn_cat_id ?? e.evn_category_id ?? "",
                cat_name: e.cat_name ?? e.category_name ?? this.catMap[String(e.evn_cat_id)] ?? "",
                evn_date: e.evn_date ?? "",
                evn_timestart: e.evn_timestart ?? "",
                evn_timeend: e.evn_timeend ?? "",
                evn_num_guest: Number(e.evn_num_guest ?? 0),
                evn_sum_accept: Number(e.evn_sum_accept ?? 0),
                evn_status: e.evn_status ?? "",
            }));
        },

        /**
         * ชื่อฟังก์ชัน: filtered
         * คำอธิบาย: กรองข้อมูล event ตามวันที่ คำค้นหา หมวดหมู่ และสถานะ
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        filtered() {
            const { start, end } = this.selectedDate || {};
            let arr = [...this.normalized];

            if (start || end) {
                const toTime = (val) => {
                    if (!val) return null;

                    // รองรับ "YYYY-MM-DD" (ที่ API มักส่งมา)
                    if (/^\d{4}-\d{2}-\d{2}/.test(val))
                        return new Date(val).getTime();

                    // รองรับ "DD/MM/YYYY" (กรณีแสดงผล/ข้อมูลเก่า)
                    if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(val)) {
                        let [d, m, y] = val
                            .split("/")
                            .map((n) => parseInt(n, 10));
                        if (y >= 2400) y -= 543; // เผื่อ พ.ศ.
                        return new Date(y, m - 1, d).getTime();
                    }
                    const t = new Date(val).getTime();
                    return Number.isFinite(t) ? t : null;
                };

                const startT = start ? toTime(start) : null;
                const endT = end ? toTime(end) : null;

                arr = arr.filter((e) => {
                    const evT = toTime(e.evn_date);
                    if (evT == null) return false;

                    if (startT != null && endT != null)
                        return evT >= startT && evT <= endT;
                    if (startT != null) return evT >= startT;
                    if (endT != null) return evT <= endT;
                    return true;
                });
            }

            const q = this.search.toLowerCase().trim();
            if (q) {
                arr = arr.filter((e) => String(e.evn_title || "").toLowerCase().includes(q));
            }
            if (this.filters.category.length > 0) {
                arr = arr.filter((e) => this.filters.category.includes(String(e.evn_cat_id)));
            }
            if (this.filters.status.length > 0) {
                arr = arr.filter((e) => this.filters.status.includes((e.evn_status || "").toLowerCase()));
            }
            return arr;
        },

        /**
         * ชื่อฟังก์ชัน: activeCategories
         * คำอธิบาย: คืนค่าหมวดหมู่ที่ยัง active
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        activeCategories() {
            return (this.categories || []).filter((c) => {
                const s = String(c.cat_delete_status ?? "").trim().toLowerCase();
                // ถ้าไม่มีค่า status -> ให้ถือว่าแอคทีฟ
                if (!s) return true;
                return s === "active";
            });
        },

        /**
         * ชื่อฟังก์ชัน: sorted
         * คำอธิบาย: เรียงลำดับข้อมูล event ตามตัวเลือกที่เลือก
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        sorted() {
            const arr = [...this.filtered];
            const { key, order, type } = this.selectedSort || {};
            const dir = order === "desc" ? -1 : 1;
            const statusOrder = { ongoing: 1, upcoming: 2, done: 3 };

            const parseDate = (val) => {
                if (!val) return 0;
                if (typeof val !== "string") return new Date(val).getTime() || 0;
                const s = val.trim();
                if (/^\d{4}-\d{2}-\d{2}/.test(s)) return new Date(s).getTime() || 0;
                if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(s)) {
                    let [d, m, y] = s.split("/").map((n) => parseInt(n, 10));
                    if (y >= 2400) y -= 543;
                    return new Date(y, m - 1, d).getTime();
                }
                return new Date(s).getTime() || 0;
            };

            const getVal = (row) => {
                if (type === "date") return parseDate(row[key]);
                if (type === "number") return Number(row[key] ?? 0);
                if (type === "text") return String(row[key] ?? "").toLowerCase();
                if (type === "custom" && key === "evn_status") return (statusOrder[(row.evn_status || "").toLowerCase()] ?? 99);
                return row[key];
            };

            arr.sort((a, b) => {
                if (type === "custom" && key === "evn_status") {
                    const sa = (a.evn_status || "").toLowerCase();
                    const sb = (b.evn_status || "").toLowerCase();
                    const oa = statusOrder[sa] ?? 99;
                    const ob = statusOrder[sb] ?? 99;
                    if (oa !== ob) return (oa - ob) * dir;
                    const da = parseDate(a.evn_date);
                    const db = parseDate(b.evn_date);
                    if (sa === "done") return db - da;
                    return da - db;
                }
                const va = getVal(a);
                const vb = getVal(b);
                if (type === "text") {
                    const cmp = va.localeCompare(vb);
                    if (cmp !== 0) return cmp * dir;
                } else {
                    if (va < vb) return -1 * dir;
                    if (va > vb) return 1 * dir;
                }
                const da = parseDate(a.evn_date);
                const db = parseDate(b.evn_date);
                return da - db;
            });
            return arr;
        },

        /**
         * ชื่อฟังก์ชัน: totalPages
         * คำอธิบาย: คืนค่าจำนวนหน้าทั้งหมดของข้อมูลที่ถูกแบ่งหน้า
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        totalPages() { return Math.ceil(this.sorted.length / this.pageSize) || 1; },

        /**
         * ชื่อฟังก์ชัน: paged
         * คำอธิบาย: คืนข้อมูล event เฉพาะหน้าปัจจุบัน
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },
    },

    watch: {
        /**
         * ชื่อฟังก์ชัน: watch search
         * คำอธิบาย: รีเซ็ตหน้าปัจจุบันเมื่อเปลี่ยนคำค้นหา
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        search() { this.page = 1; },

        /**
         * ชื่อฟังก์ชัน: watch selectedDate
         * คำอธิบาย: รีเซ็ตหน้าปัจจุบันเมื่อเปลี่ยนช่วงวันที่
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        selectedDate: {
            handler() { this.page = 1; },
            deep: true,
        },

        /**
         * ชื่อฟังก์ชัน: watch selectedSort
         * คำอธิบาย: อัปเดต key/order การเรียงลำดับและรีเซ็ตหน้าปัจจุบันเมื่อเปลี่ยนตัวเลือก
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        selectedSort: {
            handler(v) {
                if (!v) return;
                this.sortBy = v.key;
                this.sortOrder = v.order;
                this.page = 1;
            },
            immediate: true,
            deep: true,
        },

        /**
         * ชื่อฟังก์ชัน: watch page
         * คำอธิบาย: ตรวจสอบและปรับค่าหน้าปัจจุบันให้อยู่ในช่วงที่ถูกต้อง
         * ชื่อผู้เขียน/แก้ไข: NatthaphongKS
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        page(newPage) {
            const total = this.totalPages;
            if (newPage < 1) this.page = 1;
            else if (newPage > total) this.page = total;
        },

        /**
         * ชื่อฟังก์ชัน: watch pageSize
         * คำอธิบาย: รีเซ็ตหน้าปัจจุบันและปรับค่าหน้าหากเกินจำนวนหน้าทั้งหมดเมื่อเปลี่ยน pageSize
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        pageSize() {
            this.page = 1;
            if (this.page > this.totalPages) {
                this.page = this.totalPages;
            }
        },
    },

    methods: {
        onPickSort(sort) {
            /**
             * ชื่อฟังก์ชัน: onPickSort
             * คำอธิบาย: เลือกตัวเลือกการเรียงลำดับและรีเซ็ตหน้าปัจจุบัน
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            if (!sort) return;
            this.selectedSort = sort;
            this.sortBy = sort.key;
            this.sortOrder = sort.order;
            this.page = 1;
        },

        applyFilter() { this.page = 1; },
        /**
         * ชื่อฟังก์ชัน: applyFilter
         * คำอธิบาย: รีเซ็ตหน้าปัจจุบันเมื่อมีการกรองข้อมูล
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */

        statusOf(row) {
            /**
             * ชื่อฟังก์ชัน: statusOf
             * คำอธิบาย: คืนค่าสถานะของ event ในรูปแบบตัวพิมพ์เล็ก
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            return (row?.evn_status || "").toLowerCase();
        },

        roleOf() {
            /**
             * ชื่อฟังก์ชัน: roleOf
             * คำอธิบาย: คืนค่าบทบาทของผู้ใช้ในรูปแบบตัวพิมพ์เล็ก
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            return (this.empPermission || "employee").toLowerCase();
        },

        canEdit(row) {
            /**
             * ชื่อฟังก์ชัน: canEdit
             * คำอธิบาย: ตรวจสอบสิทธิ์การแก้ไข event ตามสถานะและบทบาท
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            const s = this.statusOf(row);
            const r = this.roleOf();

            if (r === "employee") return false;
            if (s === "upcoming") return r === "admin" || r === "hr";
            // ongoing / done แก้ไม่ได้ทุกกรณี
            return false;
        },

        canDelete(row) {
            /**
             * ชื่อฟังก์ชัน: canDelete
             * คำอธิบาย: ตรวจสอบสิทธิ์การลบ event ตามสถานะและบทบาท
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            const s = this.statusOf(row);
            const r = this.roleOf();

            if (r === "employee") return false;
            if (s === "upcoming") return r === "admin" || r === "hr";
            if (s === "done") return r === "admin";     // hr ทำไม่ได้
            // ongoing ลบไม่ได้
            return false;
        },

        canCheckin(row) {
            /**
             * ชื่อฟังก์ชัน: canCheckin
             * คำอธิบาย: ตรวจสอบสิทธิ์การเช็คชื่อ event ตามสถานะและบทบาท
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            const s = this.statusOf(row);
            const r = this.roleOf();

            if (r === "employee") return false;
            if (s === "ongoing") return r === "admin" || r === "hr";
            if (s === "done") return r === "admin";     // hr ทำไม่ได้
            // upcoming เช็คชื่อไม่ได้
            return false;
        },

        checkinDisabledTitle(row) {
            /**
             * ชื่อฟังก์ชัน: checkinDisabledTitle
             * คำอธิบาย: คืนข้อความแสดงเหตุผลที่ไม่สามารถเช็คชื่อได้
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            const s = this.statusOf(row);
            const r = this.roleOf();

            if (r === "employee") return "No permission";
            if (s === "upcoming") return "not available for upcoming event";
            if (s === "done" && r === "hr") return "No permission to check-in";
            return "No permission to check-in";
        },

        async fetchEvent() {
            /**
             * ชื่อฟังก์ชัน: fetchEvent
             * คำอธิบาย: ดึงข้อมูล event จาก API
             * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            try {
                const res = await axios.get("/get-event");
                this.event = Array.isArray(res.data) ? res.data : res.data?.data || [];
            } catch (err) {
                console.error("fetchEvent error", err);
                this.event = [];
            }
        },

        async fetchCategories() {
            /**
             * ชื่อฟังก์ชัน: fetchCategories
             * คำอธิบาย: ดึงข้อมูลหมวดหมู่ event จาก API
             * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            try {
                const res = await axios.get("/event-info");
                const cats = res.data?.categories || [];

                //  ใช้ id / cat_name ตามที่ EventFilter ต้องการ
                this.categories = cats.map((c) => ({
                    id: String(c.id),
                    cat_name: c.cat_name,
                    cat_delete_status: c.cat_delete_status, // เก็บสถานะ
                }));

                this.catMap = Object.fromEntries(
                    cats.map((c) => [String(c.id), c.cat_name])
                );
            } catch (err) {
                console.error("fetchCategories error", err);
                this.categories = [];
                this.catMap = {};
            }
        },

        async fetchPermission() {
            /**
             * ชื่อฟังก์ชัน: fetchPermission
             * คำอธิบาย: ดึงข้อมูลสิทธิ์ของผู้ใช้จาก API
             * ชื่อผู้เขียน/แก้ไข: Yothin S.
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            try {
                const res = await axios.get("/permission");
                this.empPermission = (res.data?.emp_permission || "employee").toLowerCase();
            } catch (err) {
                console.error("fetchPermission error", err);
                this.empPermission = "employee";
            }
        },

        applySearch() { this.search = this.searchInput; this.page = 1; },
        /**
         * ชื่อฟังก์ชัน: applySearch
         * คำอธิบาย: นำค่าค้นหาจาก input ไปใช้และรีเซ็ตหน้าปัจจุบัน
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */

        editEvent(id) { this.$router.push(`/EditEvent/${id}`); },
        /**
         * ชื่อฟังก์ชัน: editEvent
         * คำอธิบาย: นำทางไปยังหน้าสำหรับแก้ไข event
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */

        formatDate(val) {
            /**
             * ชื่อฟังก์ชัน: formatDate
             * คำอธิบาย: แปลงวันที่ให้อยู่ในรูปแบบ dd/mm/yyyy
             * ชื่อผู้เขียน/แก้ไข: NatthaphongKS
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            if (!val) return "N/A";
            try {
                const d = new Date(val);
                const dd = String(d.getDate()).padStart(2, "0");
                const mm = String(d.getMonth() + 1).padStart(2, "0");
                const yyyy = d.getFullYear();
                return `${dd}/${mm}/${yyyy}`;
            } catch { return "Invalid Date"; }
        },

        timeText(startTime, endTime) {
            /**
             * ชื่อฟังก์ชัน: timeText
             * คำอธิบาย: แปลงเวลาเริ่มและเวลาสิ้นสุดให้อยู่ในรูปแบบ hh:mm-hh:mm
             * ชื่อผู้เขียน/แก้ไข: NatthaphongKS
             * วันที่จัดทำ/แก้ไข: 2026-03-03
             */
            const format = (t) => (t ? String(t).slice(0, 5) : "??:??");
            return `${format(startTime)}-${format(endTime)}`;
        },

        // กำหนดคลาสของสี badge ตามสถานะ
        /**
         * ชื่อฟังก์ชัน: badgeClass
         * คำอธิบาย: คืนคลาส CSS สำหรับ badge ตามสถานะ
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        badgeClass(status) {
            const base = "inline-block min-w-[110px] rounded-md border px-2.5 py-1 text-xs capitalize";
            switch ((status || "").toLowerCase()) {
                case "done": return `${base} bg-[#DCFCE7] text-[#00A73D]`;
                case "upcoming": return `${base} bg-[#FFF9C2] text-[#FF9D00]`;
                case "ongoing": return `${base} bg-[#DFF3FE] text-[#0084D1]`;
                default: return `${base} bg-slate-100 text-slate-700`;
            }
        },

        /**
         * ชื่อฟังก์ชัน: openDelete
         * คำอธิบาย: เปิด modal ยืนยันการลบ event
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        openDelete(id) { this.deleteId = id; this.showModalAsk = true; },

        /**
         * ชื่อฟังก์ชัน: handleClientSort
         * คำอธิบาย: จัดการการเรียงลำดับข้อมูลฝั่ง client
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        handleClientSort({ key, order }) {
            this.sortBy = key;
            this.sortOrder = order;
            this.page = 1;
            this.selectedSort = this.sortOptions.find((opt) => opt.key === key && opt.order === order) || this.selectedSort;
        },

        /**
         * ชื่อฟังก์ชัน: onConfirmDelete
         * คำอธิบาย: ดำเนินการลบ event เมื่อผู้ใช้ยืนยัน
         * ชื่อผู้เขียน/แก้ไข: Yothin S.
         * วันที่จัดทำ/แก้ไข: 2026-03-09
         */
        async onConfirmDelete() {
            const id = this.deleteId;
            this.showModalAsk = false;
            if (!id) return;
            this.isDeleting = true;
            this.showModalLoading = true;
            this.deleteProgress = 0;

            // Add a global class to the body while the loading modal is active
            document.body.classList.add('hide-modal-records-text');

            // Fake loading progress
            this.deleteProgressInterval = setInterval(() => {
                if (this.deleteProgress < 80) {
                    const inc = Math.floor(Math.random() * 10) + 5;
                    this.deleteProgress = Math.min(this.deleteProgress + inc, 80);
                }
            }, 100);

            try {
                await axios.patch(`/event/${id}/deleted`);
                clearInterval(this.deleteProgressInterval);
                this.deleteProgress = 100;

                // Add a small delay so user sees 100% before it switches to success modal
                setTimeout(async () => {
                    document.body.classList.remove('hide-modal-records-text');
                    this.showModalLoading = false;
                    this.showModalSuccess = true;
                    this.deleteId = null;
                    await this.fetchEvent();
                }, 300);
            } catch (_) {
                document.body.classList.remove('hide-modal-records-text');
                clearInterval(this.deleteProgressInterval);
                this.showModalLoading = false;
                this.showModalFail = true;
            } finally {
                this.isDeleting = false;
            }
        },

        /**
         * ชื่อฟังก์ชัน: onCancelDelete
         * คำอธิบาย: ปิด modal ยกเลิกการลบ event
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        onCancelDelete() { this.showModalAsk = false; this.deleteId = null; },

        /**
         * ชื่อฟังก์ชัน: onConfirmSuccess
         * คำอธิบาย: ปิด modal หลังลบ event สำเร็จ
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        onConfirmSuccess() { this.showModalSuccess = false; },

        /**
         * ชื่อฟังก์ชัน: onConfirmFail
         * คำอธิบาย: ปิด modal หลังลบ event ไม่สำเร็จ
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        onConfirmFail() { this.showModalFail = false; },

        /**
         * ชื่อฟังก์ชัน: goDetails
         * คำอธิบาย: นำทางไปยังหน้ารายละเอียด event
         * ชื่อผู้เขียน/แก้ไข: katcharuek
         * วันที่จัดทำ/แก้ไข: 2026-03-03
         */
        goDetails(id) {
            try { this.$router.push({ name: "events.show", params: { id: String(id) } }); }
            catch (_) { this.$router.push({ path: `/events/${id}` }); }
        },
    },
};
</script>
