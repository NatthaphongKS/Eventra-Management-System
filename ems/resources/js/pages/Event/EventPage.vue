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
                <!-- ✅ Add Button -->
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
                <span role="button" tabindex="0"
                    class="block w-full h-full pl-3 py-2 text-slate-800 font-medium truncate hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                    @click="goDetails(row.id)" @keydown.enter.prevent="goDetails(row.id)"
                    @keydown.space.prevent="goDetails(row.id)" title="ดูรายละเอียด">
                    {{ value }}
                </span>
            </template>

            <template #cell-cat_name="{ row, value }">
                <span role="button" tabindex="0"
                    class="block w-full h-full pl-3 py-2 hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                    @click="goDetails(row.id)" @keydown.enter.prevent="goDetails(row.id)"
                    @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_num_guest="{ row, value }">
                <span role="button" tabindex="0"
                    class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                    @click="goDetails(row.id)" @keydown.enter.prevent="goDetails(row.id)"
                    @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_sum_accept="{ row, value }">
                <span role="button" tabindex="0"
                    class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                    @click="goDetails(row.id)" @keydown.enter.prevent="goDetails(row.id)"
                    @keydown.space.prevent="goDetails(row.id)">
                    {{ value }}
                </span>
            </template>

            <template #cell-evn_status="{ row, value }">
                <span role="button" tabindex="0"
                    class="block w-full h-full py-1 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
                    @click="goDetails(row.id)" @keydown.enter.prevent="goDetails(row.id)"
                    @keydown.space.prevent="goDetails(row.id)">
                    <span :class="badgeClass(value)">
                        {{ value || "N/A" }}
                    </span>
                </span>
            </template>

            <template #actions="{ row }">
                <!-- ปุ่มแก้ไข (disabled ถ้า ongoing หรือ done) -->
                <button @click="
                    !['ongoing', 'done'].includes((row.evn_status || '').toLowerCase()) &&
                    editEvent(row.id)
                    " :disabled="['ongoing', 'done'].includes((row.evn_status || '').toLowerCase())"
                    class="rounded-lg p-1.5" :class="['ongoing', 'done'].includes((row.evn_status || '').toLowerCase())
                        ? 'cursor-not-allowed opacity-40'
                        : 'hover:bg-slate-100 cursor-pointer'" :title="['ongoing', 'done'].includes((row.evn_status || '').toLowerCase())
                            ? 'Cannot edit ongoing/done event'
                            : 'Edit'">
                    <Icon icon="material-symbols:edit-rounded" width="20" height="20" :class="['ongoing', 'done'].includes((row.evn_status || '').toLowerCase())
                        ? 'text-neutral-400'
                        : 'text-neutral-800'" />
                </button>

                <button @click="openDelete(row.id)" class="rounded-lg p-1.5" :disabled="!canDelete(row)" :class="!canDelete(row)
                    ? 'cursor-not-allowed opacity-40'
                    : 'hover:bg-slate-100 cursor-pointer'" :title="!canDelete(row) ? 'Cannot delete' : 'Delete'">
                    <!-- <Icon icon="fluent:delete-12-filled" width="20" height="20" class="text-neutral-800" /> -->
                    <Icon icon="fluent:delete-12-filled" width="20" height="20"
                        :class="!canDelete(row) ? 'text-neutral-400' : 'text-neutral-800'" />
                </button>

                <!-- ❌ Disabled เมื่อ upcoming หรือ (done + permission = disabled) -->
                <span v-if="
                    (row.evn_status || '').toLowerCase() === 'upcoming' ||
                    ((row.evn_status || '').toLowerCase() === 'done' &&
                        (empPermission || '').toLowerCase() === 'disabled')
                " class="rounded-lg p-1.5 cursor-not-allowed opacity-40" :title="(row.evn_status || '').toLowerCase() === 'upcoming'
                    ? 'not available for upcoming event'
                    : 'No permission to check-in'">
                    <Icon icon="material-symbols:fact-check-rounded" width="20" height="20" class="text-neutral-400" />
                </span>

                <!--  ใช้งานได้ เมื่อไม่ใช่ upcoming และไม่ใช่ (done + disabled) -->
                <router-link v-else :to="`/EventCheckIn/eveId/${row.id}`" class="rounded-lg p-1.5 hover:bg-slate-100"
                    title="Check-in">
                    <Icon icon="material-symbols:fact-check-rounded" width="20" height="20" class="text-neutral-800" />
                </router-link>
            </template>
        </DataTable>

        <ModalAlert :open="showModalAsk" type="confirm" title="ARE YOU SURE TO DELETE"
            message="This wil by deleted permanently. Are you sure?" :show-cancel="true" okText="OK" cancelText="Cancel"
            @confirm="onConfirmDelete" @cancel="onCancelDelete" />
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
import Filter from "@/components/Button/Filter.vue";
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
        Filter,
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

            showModalBlockedDone: false,
            showModalBlockedOngoing: false,
            empPermission: "disabled",
            showModalBlockedPermission: false,
            blockMessage: "",
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
            _appliedFlt: null,

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
            showModalSuccess: false,
            showModalFail: false,
        };
    },
    filters: { category: [], status: [] },
    statusOptions: [
        { label: "Done", value: "done" },
        { label: "Ongoing", value: "ongoing" },
        { label: "Upcoming", value: "upcoming" },
    ],

    async created() {
        await Promise.all([
            this.fetchPermission(),
            this.fetchEvent(),
            this.fetchCategories(),
        ]);
    },

    computed: {
        // (ส่วน Filter Logic เหมือนเดิม)
        filterFields() {
            const categoryOptions = this.activeCategories.map((c) => ({
                label: c.cat_name,
                value: String(c.id),
            }));
            const statusOptions = [
                { label: "Done", value: "done" },
                { label: "Ongoing", value: "ongoing" },
                { label: "Upcoming", value: "upcoming" },
            ];
            return [
                { fieldKey: "category", label: "Category", fieldType: "checkbox", sectionTitle: "Category", fieldOptions: categoryOptions },
                { fieldKey: "status", label: "Status", fieldType: "checkbox", sectionTitle: "Status", fieldOptions: statusOptions },
            ];
        },

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

        activeCategories() {
            return (this.categories || []).filter((c) => {
                const s = String(c.cat_delete_status ?? "").trim().toLowerCase();
                // ถ้าไม่มีค่า status -> ให้ถือว่าแอคทีฟ
                if (!s) return true;
                return s === "active";
            });
        },

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

        totalPages() { return Math.ceil(this.sorted.length / this.pageSize) || 1; },
        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },
    },

    watch: {
        search() { this.page = 1; },
        pageSize() { this.page = 1; },
        // 🔴🔴 จุดที่แก้ 2: ลบ selectedDate ออกจาก watch (เพราะย้ายไปทำใน onDateChange แล้ว)
        /*
        selectedDate: {
            deep: true,
            handler() {
                this.page = 1;
            },
        },
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
        page(newPage) {
            const total = this.totalPages;
            if (newPage < 1) this.page = 1;
            else if (newPage > total) this.page = total;
        },
        pageSize() {
            if (this.page > this.totalPages) {
                this.page = this.totalPages;
            }
        },
    },

    methods: {
        // 🔴🔴 จุดที่แก้ 3: สร้างฟังก์ชันรับค่าจากปฏิทินโดยเฉพาะ
        onDateChange(newDateVal) {
            // รับค่ามาแล้วอัปเดตตัวแปรทันที
            this.selectedDate = newDateVal;
            // สั่ง reset page ตรงนี้แทน (ทำงานครั้งเดียว ไม่ loop)
            this.page = 1;
        },

        onPickSort(opt) {
            this.sortBy = opt.key;
            this.sortOrder = opt.order;
            this.page = 1;
        },

        applyFilter() { this.page = 1; },

        canDelete(row) {
            const status = (row.evn_status || "").toLowerCase();
            const perm = (this.empPermission || "disabled").toLowerCase();
            if (status === "ongoing") return false;

            // enabled ลบได้ upcoming + done
            if (perm === "enabled")
                return status === "upcoming" || status === "done";

            // disabled ลบได้แค่ upcoming
            return status === "upcoming";
        },

        canCheckIn(row) {
            const status = (row.evn_status || "").toLowerCase();
            const perm = (this.empPermission || "disabled").toLowerCase();

            // upcoming ห้ามทุกกรณี
            if (status === "upcoming") return false;

            // done → disabled ห้าม, enabled ได้
            if (status === "done" && perm === "disabled") return false;

            // ที่เหลือ (ongoing, done+enabled)
            return true;
        },

        async fetchEvent() {
            try {
                const res = await axios.get("/get-event");
                this.event = Array.isArray(res.data) ? res.data : res.data?.data || [];
            } catch (err) {
                console.error("fetchEvent error", err);
                this.event = [];
            }
        },

        async fetchCategories() {
            try {
                const res = await axios.get("/event-info");
                const cats = res.data?.categories || [];

                // ✅ ใช้ id / cat_name ตามที่ EventFilter ต้องการ
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
            try {
                const res = await axios.get("/permission");
                // ตัวอย่าง response: { emp_permission: "enabled" }
                this.empPermission = (
                    res.data?.emp_permission || "disabled"
                ).toLowerCase();
            } catch (err) {
                console.error("fetchPermission error", err);
                this.empPermission = "disabled";
            }
        },

        applySearch() { this.search = this.searchInput; this.page = 1; },

        editEvent(id) { this.$router.push(`/EditEvent/${id}`); },

        async deleteEvent(id) {
            const ev = this.normalized.find((e) => e.id === id);
            const title = ev?.evn_title || "this event";
            const { isConfirmed } = await Swal.fire({ title: `Delete ${title}?`, showCancelButton: true });
            if (!isConfirmed) return;
            try {
                await axios.patch(`/event/${id}/deleted`);
                await Swal.fire("Deleted!", "", "success");
                this.fetchEvent();
            } catch (err) {
                console.error("Error deleting event", err);
                await Swal.fire("Error", "ไม่สามารถลบได้", "error");
            }
        },

        formatDate(val) {
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
            const format = (t) => (t ? String(t).slice(0, 5) : "??:??");
            return `${format(startTime)}-${format(endTime)}`;
        },

        badgeClass(status) {
            const base = "inline-block min-w-[110px] rounded-md border px-2.5 py-1 text-xs capitalize";
            switch ((status || "").toLowerCase()) {
                case "done": return `${base} bg-[#DCFCE7] text-[#00A73D]`;
                case "upcoming": return `${base} bg-[#FFF9C2] text-[#FDC800]`;
                case "ongoing": return `${base} bg-[#DFF3FE] text-[#0084D1]`;
                default: return `${base} bg-slate-100 text-slate-700`;
            }
        },

        openDelete(id) { this.deleteId = id; this.showModalAsk = true; },

        handleClientSort({ key, order }) {
            this.sortBy = key;
            this.sortOrder = order;
            this.page = 1;
            this.selectedSort = this.sortOptions.find((opt) => opt.key === key && opt.order === order) || this.selectedSort;
        },

        onPickSort(sort) {
            if (!sort) return;
            this.selectedSort = sort;
            this.sortBy = sort.key;
            this.sortOrder = sort.order;
            this.page = 1;
        },

        async onConfirmDelete() {
            const id = this.deleteId;
            this.showModal = false;
            if (!id) return;
            try {
                await axios.patch(`/event/${id}/deleted`);
                this.showModalAsk = false;
                this.showModalSuccess = true;
                this.deleteId = null;
                await this.fetchEvent();
            } catch (_) {
                this.showModalAsk = false;
                this.showModalFail = true;
            } finally {
                this.isDeleting = false;
            }
        },

        onCancelDelete() { this.showModalAsk = false; this.deleteId = null; },
        onConfirmSuccess() { this.showModalSuccess = false; },
        onConfirmFail() { this.showModalFail = false; },
        goDetails(id) {
            try { this.$router.push({ name: "events.show", params: { id: String(id) } }); }
            catch (_) { this.$router.push({ path: `/events/${id}` }); }
        },
    },
};
</script>
