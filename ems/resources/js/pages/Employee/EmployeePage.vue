<template>
    <section class="p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 w-full gap-3">
            <div class="flex-1">
                <SearchBar v-model="searchInput" placeholder="Search Employee ID / Name / Nickname" @search="onSearchText" class="" />
            </div>

            <div class="flex items-stretch gap-2 flex-shrink-0 mt-[30px]">
                <FilterEmployees ref="filterDropdown" v-model="filters" :options="optionsMap" class="[&_button]:h-full" />

                <div ref="sortWrap" class="h-[58px]">
                    <SortMenu :is-open="sortMenuOpen" :options="sortOptions" :sort-by="sortBy.key" :sort-order="sortBy.order" @toggle="sortMenuOpen = !sortMenuOpen" @choose="onSortChoose" class="[&_button]:h-full h-full" />
                </div>

                <AddButton @click="goAdd" class="h-full w-[44px] flex items-center justify-center" />
            </div>
        </div>

        <DataTable :rows="paged" :columns="employeeTableColumns" :page="page" :pageSize="pageSize" :total-items="sorted.length" :page-size-options="[10, 20, 50, 100]" @update:page="page = $event" @update:pageSize="
                (s) => {
                    pageSize = s;
                    page = 1;
                }
            " class="mt-4">

            <template #actions="{ row }">
                <button class="grid h-8 w-8 place-items-center rounded-full text-neutral-800 hover:text-emerald-600" @click="editEmployee(row.emp_id)" title="Edit" aria-label="edit">
                    <Icon icon="material-symbols:edit-rounded" width="20" height="20" />
                </button>

                <button :disabled="!canDelete" class="grid h-8 w-8 place-items-center rounded-full transition
           text-neutral-800 cursor-pointer
           hover:text-red-600
           disabled:text-neutral-400
           disabled:cursor-not-allowed
           disabled:opacity-40" @click="openDelete(row.emp_id)" :title="canDelete ? 'Delete' : 'You do not have permission'" aria-label="delete">
                    <Icon icon="fluent:delete-12-filled" width="20" height="20" />
                </button>
            </template>

            <template #empty>
                <p class="text-center text-neutral-800">ไม่พบข้อมูลที่ค้นหา</p>
            </template>
        </DataTable>

        <ModalAlert :open="showModalAsk" type="confirm" title="ARE YOU SURE TO DELETE" message="This employee will be deleted permanently. Are you sure?" :show-cancel="true" okText="OK" cancelText="Cancel" @confirm="onConfirmDelete" @cancel="onCancelDelete" />

        <ModalAlert :open="showModalSuccess" type="success" title="DELETE SUCCESS!" message="Employee has been deleted successfully." :show-cancel="false" okText="OK" @confirm="onConfirmSuccess" />

        <ModalAlert :open="showModalFail" type="error" title="ERROR!" message="Sorry, Please try again later." :show-cancel="false" okText="OK" @confirm="onConfirmFail" />
    </section>
</template>

<script>
/**
 * ชื่อไฟล์: EmployeesPage.vue
 * คำอธิบาย: Component สำหรับแสดงและจัดการข้อมูลรายชื่อพนักงานทั้งหมด (ค้นหา, กรอง, จัดเรียง, ลบ)
 * Input: -
 * Output: หน้าตารางจัดการพนักงาน (UI)
 * ชื่อผู้เขียน/แก้ไข: katcharuek sriphirom
 * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
 */

import axios from "axios";
import { Icon } from "@iconify/vue";
import SearchBar from "@/components/SearchBar.vue";
import DataTable from "@/components/DataTable.vue";
import SortMenu from "@/components/SortMenu.vue";
import AddButton from "@/components/AddButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import FilterEmployees from "@/components/Button/FilterEmployees.vue";

// ตั้งค่าเริ่มต้นสำหรับการเรียก API
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
    name: "EmployeesPage",
    components: {
        Icon,
        SearchBar,
        DataTable,
        SortMenu,
        AddButton,
        ModalAlert,
        FilterEmployees,
    },
    data() {
        return {
            // ตัวแปรเก็บข้อมูลและสถานะการแสดงผล
            employees: [],
            deleteId: null,
            showModalAsk: false,
            showModalSuccess: false,
            showModalFail: false,
            searchInput: "",
            search: "",

            // [แก้ตามมาตรฐาน] เปลี่ยนจาก "Company ID" เป็น companyId (camelCase)
            filters: {
                companyId: [],
                department: [],
                team: [],
                position: []
            },

            // ตัวแปรสำหรับการจัดการหน้าเพจและจัดเรียงข้อมูล
            page: 1,
            pageSize: 10,
            sortMenuOpen: false,
            sortBy: { key: "", order: "" },

            // ตัวเลือกสำหรับการจัดเรียงข้อมูลในตาราง
            sortOptions: [
                { key: "emp_firstname", order: "asc", label: "ชื่อพนักงาน A–Z" },
                { key: "emp_firstname", order: "desc", label: "ชื่อพนักงาน Z–A" },
                { key: "department_name", order: "asc", label: "แผนก A–Z" },
                { key: "department_name", order: "desc", label: "แผนก Z–A" },
                { key: "team_name", order: "asc", label: "ทีม A–Z" },
                { key: "team_name", order: "desc", label: "ทีม Z–A" },
                { key: "position_name", order: "asc", label: "ตำแหน่ง A–Z" },
                { key: "position_name", order: "desc", label: "ตำแหน่ง Z–A" },
                { key: "created_at", order: "desc", label: "วันที่เพิ่มใหม่สุด" },
                { key: "created_at", order: "asc", label: "วันที่เพิ่มเก่าสุด" },
            ],

            // [แก้ตามมาตรฐาน] คอลัมน์ตาราง เปลี่ยนชื่อตัวแปรให้เป็น camelCase
            employeeTableColumns: [
                { key: "emp_id", label: "Employee ID", class: "text-left w-[140px]" },
                { key: "emp_fullname", label: "Name", class: "text-left w-[180px]" },
                { key: "emp_nickname", label: "Nickname", class: "text-left w-[120px]" },
                { key: "emp_phone", label: "Phone", class: "text-left w-[140px]" },
                { key: "department_name", label: "Department", class: "text-left w-[140px]" },
                { key: "team_name", label: "Team", class: "text-left w-[140px]" },
                { key: "position_name", label: "Position", class: "text-left w-[140px]" },
                {
                    key: "created_at",
                    label: "Date Add (D/M/Y)",
                    class: "text-center w-[160px]",
                    format: (v) => (v ? new Date(v).toLocaleDateString("en-GB") : "-"),
                },
            ],
        };
    },
    watch: {
        // เคลียร์หน้ากลับไปหน้าแรกเสมอหากมีการเปลี่ยนเงื่อนไขการกรอง
        filters: {
            handler() {
                this.page = 1;
            },
            deep: true,
        },
    },
    async created() {
        // ดึงข้อมูลรายชื่อพนักงานทันทีที่โหลดคอมโพเนนต์
        await this.fetchEmployees();
    },
    mounted() {
        document.addEventListener("click", this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocClick);
    },
    computed: {
        // ตรวจสอบสิทธิ์ผู้ใช้งานปัจจุบัน
        currentUser() {
            const u = localStorage.getItem("userData");
            return u ? JSON.parse(u) : {};
        },
        // สิทธิ์ในการลบพนักงานต้องเป็น admin เท่านั้น
        canDelete() {
            return this.currentUser.emp_permission === "admin";
        },
        // ดึงข้อมูลที่ไม่ซ้ำกันมาทำเป็นตัวเลือกใน Dropdown Filter
        optionsMap() {
            return {
                companyId: [
                    ...new Set(
                        this.employees
                            .map((e) => e.company_id)
                            .filter((v) => v && v !== "-")
                    ),
                ],
                department: [
                    ...new Set(this.employees.map((e) => e.department_name).filter(Boolean)),
                ],
                team: [
                    ...new Set(this.employees.map((e) => e.team_name).filter(Boolean)),
                ],
                position: [
                    ...new Set(this.employees.map((e) => e.position_name).filter(Boolean)),
                ],
            };
        },
        // ประมวลผลข้อมูลหลังจากการค้นหาและกรอง (Filter)
        filtered() {
            let result = this.employees;

            // กรองด้วยการค้นหาข้อความ
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) => {
                    const fullName = `${e.emp_firstname ?? ""} ${e.emp_lastname ?? ""}`.toLowerCase();
                    const nickname = (e.emp_nickname ?? "").toLowerCase();
                    const empId = (e.emp_id ?? "").toString().toLowerCase();
                    const compId = (e.company_id ?? "").toString().toLowerCase();

                    return (
                        empId.includes(q) ||
                        fullName.includes(q) ||
                        nickname.includes(q) ||
                        compId.includes(q)
                    );
                });
            }

            // กรองข้อมูลด้วยเงื่อนไข Multi-select
            if (this.filters.department && this.filters.department.length > 0) {
                result = result.filter((e) => this.filters.department.includes(e.department_name));
            }

            if (this.filters.team && this.filters.team.length > 0) {
                result = result.filter((e) => this.filters.team.includes(e.team_name));
            }

            if (this.filters.position && this.filters.position.length > 0) {
                result = result.filter((e) => this.filters.position.includes(e.position_name));
            }

            if (this.filters.companyId && this.filters.companyId.length > 0) {
                result = result.filter((e) => this.filters.companyId.includes(e.company_id));
            }

            return result;
        },
        // ประมวลผลข้อมูลหลังจากการจัดเรียง (Sorting)
        sorted() {
            const { key, order } = this.sortBy;
            if (!key) return this.filtered;

            const dir = order === "asc" ? 1 : -1;
            return this.filtered.slice().sort((a, b) => {
                if (key.includes("create")) {
                    const ta = new Date(a[key]).getTime() || 0;
                    const tb = new Date(b[key]).getTime() || 0;
                    return (ta - tb) * dir;
                }
                const av = (a[key] || "").toString().toLowerCase();
                const bv = (b[key] || "").toString().toLowerCase();
                return av.localeCompare(bv, "th") * dir;
            });
        },
        // จัดแบ่งข้อมูลตามหน้าปัจจุบันเพื่อแสดงผลในตาราง
        paged() {
            return this.sorted.slice(
                (this.page - 1) * this.pageSize,
                this.page * this.pageSize
            );
        },
    },
    methods: {
        // ฟังก์ชันดึงรายชื่อพนักงาน
        async fetchEmployees() {
            try {
                // [ไม่ดี] ตามมาตรฐานควรเป็น axios.get("/employees") แต่คงไว้เพื่อไม่ให้การเชื่อมต่อ Backend ขาดหาย
                const res = await axios.get("/get-employees");
                const data = Array.isArray(res.data) ? res.data : res.data?.data || [];

                this.employees = data.map((e) => {
                    // Logic ตัดตัวเลขออกจาก emp_id เพื่อสร้าง Company ID
                    let extractedId = "-";
                    if (e.emp_id) {
                        const match = e.emp_id.toString().match(/^[A-Za-zก-๙-]+/);
                        if (match) {
                            extractedId = match[0];
                        } else {
                            extractedId = e.emp_id.toString().replace(/[0-9]/g, '');
                        }
                    }

                    // [ไม่ดี] เช็ค created_at ซ้อนหลายเงื่อนไข ควรไปรวมให้เป็นมาตรฐานเดียวกันที่ API Resource
                    return {
                        ...e,
                        company_id: extractedId || "-",
                        emp_fullname: `${e.emp_firstname ?? ""} ${e.emp_lastname ?? ""}`.trim(),
                        emp_phone: e.emp_phone ?? e.phone ?? "-",
                        created_at: e.emp_created_at ?? e.created_at ?? e.createdAt ?? null,
                    };
                });
            } catch (err) {
                console.error("Error fetching employees", err);
            }
        },
        // นำทางไปหน้าเพิ่มข้อมูลพนักงาน
        goAdd() {
            this.$router.push("/add-employee");
        },
        // เลือกคอลัมน์และทิศทางในการจัดเรียงข้อมูล
        onSortChoose(option) {
            if (!option || !option.key || !option.order) return;
            if (this.sortBy.key === option.key && this.sortBy.order === option.order) {
                this.sortBy = { key: "", order: "" };
            } else {
                this.sortBy = { key: option.key, order: option.order };
            }
            this.page = 1;
            this.sortMenuOpen = false;
        },
        // ปิดเมนู Sort เมื่อคลิกพื้นที่อื่น
        onDocClick(e) {
            if (!this.sortMenuOpen) return;
            const wrap = this.$refs.sortWrap;
            if (wrap && !wrap.contains(e.target)) {
                this.sortMenuOpen = false;
            }
        },
        // ค้นหาข้อมูลจากช่อง Search
        onSearchText() {
            this.search = this.searchInput.trim();
            this.page = 1;
            if (this.$refs.filterDropdown) {
                if(typeof this.$refs.filterDropdown.close === 'function') {
                     this.$refs.filterDropdown.close();
                }
            }
        },
        // นำทางไปหน้าแก้ไขข้อมูลพนักงานที่เลือก
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        // เปิดหน้าต่างยืนยันการลบข้อมูล
        openDelete(id) {
            this.deleteId = id;
            this.showModalAsk = true;
        },
        // ยืนยันกระบวนการลบพนักงาน (Soft Delete)
        async onConfirmDelete() {
            const id = this.deleteId;
            if (!id) return;

            // ดึงรหัสพนักงานของผู้ทำรายการ
            const userStorage = localStorage.getItem("userData");
            const currentUser = userStorage ? JSON.parse(userStorage) : {};
            const myId = currentUser.emp_id;

            try {
                // อัปเดตสถานะพนักงานเป็น "deleted"
                await axios.put(`/employees/soft-delete/${id}`, {
                    emp_delete_by: myId,
                    emp_delete_status: "deleted",
                });
                this.showModalAsk = false;
                this.showModalSuccess = true;
                // ตัดรายการที่ถูกลบออกจากอาร์เรย์ในหน้าจอ
                this.employees = this.employees.filter((e) => e.emp_id !== id);
                this.deleteId = null;
            } catch (err) {
                console.error("Delete failed:", err);
                this.showModalAsk = false;
                this.showModalFail = true;
            }
        },
        // ยกเลิกการลบข้อมูล
        onCancelDelete() {
            this.showModalAsk = false;
            this.deleteId = null;
        },
        // ยอมรับหน้าต่างแจ้งเตือนทำรายการสำเร็จ
        onConfirmSuccess() {
            this.showModalSuccess = false;
        },
        // ยอมรับหน้าต่างแจ้งเตือนเกิดข้อผิดพลาด
        onConfirmFail() {
            this.showModalFail = false;
        },
    },
};
</script>
