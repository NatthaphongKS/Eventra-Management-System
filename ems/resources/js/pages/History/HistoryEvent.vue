<!-- /**
 * ชื่อไฟล์: HistoryEvent.vue
 * คำอธิบาย: หน้าแสดงประวัติกิจกรรมที่ถูกลบทั้งหมด (Event Deletion History)
 * Input: ข้อมูลกิจกรรมที่ถูกลบจาก API /history/events
 * Output: ตารางแสดงรายการกิจกรรมที่ถูกลบ พร้อมฟังก์ชันค้นหาและเรียงลำดับ
 * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
 * วันที่จัดทำ/แก้ไข: 2025-12-14
 */ -->
<template>
    <section class="p-0">
        <div class="mt-3 mb-4 flex items-center gap-4">

            <div class="flex flex-1">
                <SearchBar
                    v-model="searchInput"
                    placeholder="Search Event / Created by / Delete by"
                    @search="applySearch"

                />
            </div>

            <SortMenu
                :is-open="isSortOpen"
                :options="sortOptions"
                :sort-by="sortBy"
                :sort-order="sortOrder"
                @toggle="toggleSortMenu"
                @choose="onPickSort"
                class="mt-8"
            />
        </div>

        <DataTable
            :rows="paged"
            :columns="historyTableColumns"
            :loading="loading"
            :total-items="sorted.length"
            :page-size-options="[10, 20, 50, 100]"
            :page="page"
            :pageSize="pageSize"
            :sortKey="sortBy"
            :sortOrder="sortOrder"
            @update:page="page = $event"
            @update:pageSize="handlePageSizeChange"
            @sort="handleHeaderSort"
            row-key="id"
            :show-row-number="true"
            class="mt-4"
        >
            <template #cell-evn_title="{ value, row }">
                <router-link
                    :to="{ name: 'history-event-detail', params: { id: row.id } }"
                    class="block w-full h-full pl-3 py-2 text-slate-700 font-medium truncate  cursor-pointer transition-colors"
                    title="Click to view details"
                >
                    {{ value }}
                </router-link>
            </template>

            <template #cell-created_by="{ value }">
                <span class="block py-2 text-slate-600 text-sm">
                    {{ value || '-' }}
                </span>
            </template>

            <template #cell-deleted_by="{ value }">
                <span class="block py-2 text-slate-600 text-sm">
                    {{ value || '-' }}
                </span>
            </template>

            <template #empty>
                <div class="py-10 text-center text-gray-500">
                    ไม่พบข้อมูลประวัติ
                </div>
            </template>
        </DataTable>

        <ModalAlert
            :open="showModalFail"
            type="error"
            title="ERROR!"
            message="Sorry, Please try again later."
            :show-cancel="false"
            okText="OK"
            @confirm="showModalFail = false"
        />

    </section>
</template>

<script>
import axios from "axios";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
// นำเข้า SortMenu (ตรวจสอบ path ให้ตรงกับโครงสร้างโปรเจกต์ของคุณ)
import SortMenu from "../../components/SortMenu.vue";

// ตั้งค่า axios พื้นฐาน
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = true;

export default {
    components: {
        SortMenu,
        DataTable,
        SearchBar,
        ModalAlert,
    },

    data() {
        return {
            // ตัวแปรสถานะการโหลดและข้อมูล
            loading: false,
            historyEvents: [],

            // ตัวแปรสำหรับการค้นหา
            searchInput: "",
            search: "",

            // ตัวแปรสำหรับการเรียงลำดับ (ส่งไปให้ SortMenu)
            sortBy: "deleted_at", // คอลัมน์ตั้งต้น
            sortOrder: "desc",    // ลำดับตั้งต้น
            isSortOpen: false,    // ควบคุมการเปิดปิดเมนู

            // รายการตัวเลือกสำหรับเมนู Sort
            sortOptions: [
                {
                    label: "วันที่ลบ (ล่าสุด)",
                    key: "deleted_at",
                    order: "desc",
                    type: "date",
                },
                {
                    label: "วันที่ลบ (เก่าสุด)",
                    key: "deleted_at",
                    order: "asc",
                    type: "date",
                },
                {
                    label: "วันที่สร้าง (ล่าสุด)",
                    key: "created_at",
                    order: "desc",
                    type: "date",
                },
                {
                    label: "ชื่อ Event (A-Z)",
                    key: "evn_title",
                    order: "asc",
                    type: "text",
                },
            ],

            // การจัดการหน้า (Pagination)
            page: 1,
            pageSize: 10,

            // กำหนดหัวตารางและคอลัมน์
            historyTableColumns: [
                {
                    key: "evn_title",
                    label: "Event",
                    class: "text-left",
                    headerClass: "w-[30%]",
                    sortable: true,
                },
                {
                    key: "created_by",
                    label: "Created by",
                    class: "text-left",
                    sortable: true,
                },
                {
                    key: "created_at",
                    label: "Created Date",
                    class: "text-center whitespace-nowrap",
                    format: this.formatDate,
                    sortable: true,
                },
                {
                    key: "deleted_by",
                    label: "Deleted by",
                    class: "text-left",
                    sortable: true,
                },
                {
                    key: "deleted_at",
                    label: "Deleted Date",
                    class: "text-center whitespace-nowrap",
                    format: this.formatDate,
                    sortable: true,
                },
            ],

            showModalFail: false,
        };
    },

    async created() {
        await this.fetchHistory();
    },

    computed: {
        // แปลงข้อมูลจาก API ให้เป็นรูปแบบที่พร้อมแสดงผล
        normalized() {
            return this.historyEvents.map((e) => ({
                id: e.id,
                evn_title: e.evn_title || "",
                created_by: e.created_by_name || "-",
                created_at: e.created_at || "",
                deleted_by: e.deleted_by_name || "-",
                deleted_at: e.deleted_at || "",
            }));
        },

        // กรองข้อมูลตามคำค้นหา
        filtered() {
            let arr = [...this.normalized];
            const q = this.search.toLowerCase().trim();

            if (q) {
                arr = arr.filter((e) =>
                    `${e.evn_title} ${e.created_by} ${e.deleted_by}`
                        .toLowerCase()
                        .includes(q)
                );
            }
            return arr;
        },

        // เรียงลำดับข้อมูล (Client-side)
        sorted() {
            const arr = [...this.filtered];

            // ใช้ sortBy และ sortOrder จาก data
            const key = this.sortBy;
            const order = this.sortOrder;

            // หาประเภทข้อมูล (type) จาก options เพื่อเลือกวิธีเปรียบเทียบที่ถูกต้อง
            const currentOption = this.sortOptions.find(o => o.key === key && o.order === order);
            const type = currentOption?.type || 'text';

            const dir = order === "desc" ? -1 : 1;

            const parseDate = (val) => {
                if (!val) return 0;
                return new Date(val).getTime() || 0;
            };

            const getVal = (row) => {
                if (type === "date") return parseDate(row[key]);
                if (type === "text") return String(row[key] ?? "").toLowerCase();
                return row[key];
            };

            arr.sort((a, b) => {
                const va = getVal(a);
                const vb = getVal(b);

                if (va < vb) return -1 * dir;
                if (va > vb) return 1 * dir;
                return 0;
            });

            return arr;
        },

        // คำนวณจำนวนหน้าทั้งหมด
        totalPages() {
            return Math.ceil(this.sorted.length / this.pageSize) || 1;
        },

        // ตัดข้อมูลเพื่อแสดงเฉพาะหน้าปัจจุบัน
        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },
    },

    watch: {
        // รีเซ็ตหน้าเมื่อมีการค้นหาหรือเปลี่ยนจำนวนแถว
        search() { this.page = 1; },
        pageSize() { this.page = 1; },
    },

    methods: {
        // จัดการการเปิดปิดเมนู Sort (รับ event @toggle จาก SortMenu)
        toggleSortMenu() {
            this.isSortOpen = !this.isSortOpen;
        },

        // ปิดเมนูเมื่อคลิกพื้นที่อื่น
        closeSortMenu() {
            this.isSortOpen = false;
        },

        // จัดการเมื่อเลือกตัวเลือกจากเมนู Sort (รับ event @choose จาก SortMenu)
        onPickSort(option) {
            this.sortBy = option.key;
            this.sortOrder = option.order;
            this.page = 1;
            this.closeSortMenu(); // ปิดเมนูหลังจากเลือกเสร็จ
        },

        // จัดการเมื่อกดค้นหา
        applySearch() {
            this.search = this.searchInput;
            this.page = 1;
        },

        // ดึงข้อมูลจาก API
        async fetchHistory() {
            this.loading = true;
            try {
                const res = await axios.get("/history/events");
                this.historyEvents = res.data || [];
            } catch (err) {
                console.error("fetchHistory error", err);
                this.historyEvents = [];
            } finally {
                this.loading = false;
            }
        },

        // จัดการเมื่อกดหัวตารางเพื่อเรียงลำดับ
        handleHeaderSort({ key, order }) {
            this.sortBy = key;
            this.sortOrder = order;
            this.page = 1;
        },

        // จัดการเมื่อเปลี่ยนจำนวนรายการต่อหน้า
        handlePageSizeChange(newSize) {
            this.pageSize = newSize;
            this.page = 1;
        },

        // ฟังก์ชันจัดรูปแบบวันที่
        formatDate(val) {
            if (!val) return "-";
            try {
                const d = new Date(val);
                const dd = String(d.getDate()).padStart(2, "0");
                const mm = String(d.getMonth() + 1).padStart(2, "0");
                const yyyy = d.getFullYear();
                return `${dd}/${mm}/${yyyy}`;
            } catch {
                return "-";
            }
        },
    },
};
</script>
