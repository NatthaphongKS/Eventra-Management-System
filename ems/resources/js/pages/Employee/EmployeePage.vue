<template>
    <section class="p-0">

        <!-- =================== Toolbar =================== -->
        <div class="flex items-center gap-3 mb-4 overflow-visible">
            <!-- ช่องค้นหา: ผูกค่ากับ searchInput กด Enter จะ apply -->
            <input v-model.trim="searchInput" placeholder="Search..." @keyup.enter="applySearchAndFilters"
                class="flex-1 h-10 px-4 rounded-full border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-500" />
            <button
                class="w-10 h-10 rounded-full bg-red-700 text-white flex items-center justify-center hover:bg-rose-700"
                @click="applySearchAndFilters" aria-label="Search" title="ค้นหา/ใช้ฟิลเตอร์ (คลิกหรือกด Enter)">
                <MagnifyingGlassIcon class="w-5 h-5" />
            </button>

            <!-- 🔁 ใช้ EmployeeFilter.vue -->
            <EmployeeFilter 
                v-model="filters" 
                :filter-fields="employeeFilterFields" 
                :show="showFilter"
                @toggle="toggleFilter"
                @filter="handleFilter"
            />


            <!-- เมนูเรียงข้อมูล: เปิด/ปิด และเลือกตัวเลือก -->
            <SortMenu :isOpen="showSort" :options="sortOptions" :sortBy="sortBy" :sortOrder="sortOrder"
                @toggle="toggleSort" @choose="toggleSortOption" />

            <!-- ปุ่มลิงก์ไปหน้าเพิ่มพนักงาน -->
            <router-link to="/add-employee"
                class="ml-auto inline-flex items-center h-10 px-4 rounded-full bg-red-700 text-white hover:bg-rose-700 whitespace-nowrap z-0">
                + Add New
            </router-link>
        </div>

        <!-- =================== Chips =================== -->
        <!-- แถวแท็กเมื่อเลือก Filter -->
        <!-- <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-4">
            <div v-for="k in filterFields" :key="k" v-show="filters[k] !== 'all'"
                class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full" :class="chipClass(k)">
                {{ chipText(k) }}
                <button @click="removeFilter(k)" class="hover:opacity-80">
                    ✕
                </button>
            </div>
        </div> -->

        <!-- =================== Table (Desktop) =================== -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr class="text-left">
                        <th class="px-2.5 py-2 font-semibold text-[13px] text-center">
                            #
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            ID
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Name
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Nickname
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Phone
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Department
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Team
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Position
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]">
                            Date Add (D/M/Y)
                        </th>
                        <th class="px-2.5 py-2 font-semibold text-[13px]"></th>
                    </tr>
                </thead>
                <tbody class="text-[15px]">
                    <tr v-for="(emp, i) in paged" :key="emp.id ?? emp.emp_id ?? i"
                        class="border-b border-gray-200 last:border-0 hover:bg-rose-50">
                        <td class="px-2.5 py-2 text-center">
                            {{ (page - 1) * pageSize + i + 1 }}
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            {{ emp.emp_id || "N/A" }}
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            <span class="block truncate" :title="`${emp.emp_prefix ?? ''} ${emp.emp_firstname ?? ''
                                } ${emp.emp_lastname ?? ''}`">
                                {{
                                    (emp.emp_prefix
                                        ? emp.emp_prefix + " "
                                        : "") +
                                    (emp.emp_firstname || "") +
                                    " " +
                                    (emp.emp_lastname || "")
                                }}
                            </span>
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            {{ emp.emp_nickname || "N/A" }}
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            {{ emp.phone || "N/A" }}
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            {{ emp.department_name || "N/A" }}
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            <span class="block truncate" :title="emp.team_name">
                                {{ emp.team_name || "N/A" }}
                            </span>
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            <span class="block truncate" :title="emp.position_name">
                                {{ emp.position_name || "N/A" }}
                            </span>
                        </td>
                        <td class="px-2.5 py-2 whitespace-nowrap">
                            {{
                                emp.created_at
                                    ? new Date(
                                        emp.created_at
                                    ).toLocaleDateString("en-GB")
                                    : "N/A"
                            }}
                        </td>
                        <td class="px-2.5 py-2">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="editEmployee(emp.id)" aria-label="Edit"
                                    class="p-1.5 rounded-lg hover:bg-rose-100" title="Edit">
                                    <PencilIcon class="w-4 h-4 text-gray-600" />
                                </button>
                                <button @click="requestDelete(emp)" aria-label="Delete"
                                    class="p-1.5 rounded-lg hover:bg-rose-100" title="Delete">
                                    <TrashIcon class="w-4 h-4 text-gray-600" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="paged.length === 0">
                        <td :colspan="10" class="px-3 py-6 text-center text-gray-500">
                            {{
                                filtered.length === 0 && hasActiveFilters
                                    ? "No employees match the selected filters"
                                    : "No data found"
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- =================== Card Layout (Mobile ) =================== -->
        <div class="md:hidden space-y-4">
            <div v-for="(emp, i) in paged" :key="emp.id ?? i"
                class="p-4 rounded-xl border border-gray-200 shadow-sm bg-white">
                <div class="flex justify-between items-center mb-2">
                    <div class="font-semibold text-gray-800">
                        {{ emp.emp_firstname }} {{ emp.emp_lastname }}
                    </div>
                    <span class="text-xs text-gray-500">
                        #{{ (page - 1) * pageSize + i + 1 }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">ID:</span>
                        {{ emp.emp_id || "N/A" }}
                    </div>
                    <div>
                        <span class="font-medium">Nickname:</span>
                        {{ emp.emp_nickname || "N/A" }}
                    </div>
                    <div>
                        <span class="font-medium">Phone:</span>
                        {{ emp.phone || "N/A" }}
                    </div>
                    <div>
                        <span class="font-medium">Department:</span>
                        {{ emp.department_name || "N/A" }}
                    </div>
                    <div>
                        <span class="font-medium">Team:</span>
                        {{ emp.team_name || "N/A" }}
                    </div>
                    <div>
                        <span class="font-medium">Position:</span>
                        {{ emp.position_name || "N/A" }}
                    </div>
                    <div class="col-span-2">
                        <span class="font-medium">Date:</span>
                        {{
                            emp.created_at
                                ? new Date(
                                    emp.created_at
                                ).toLocaleDateString("en-GB")
                                : "N/A"
                        }}
                    </div>
                </div>
            </div>

            <div v-if="paged.length === 0" class="p-4 text-center text-gray-500">
                {{
                    filtered.length === 0 && hasActiveFilters
                        ? "No employees match the selected filters"
                        : "No data found"
                }}
            </div>
        </div>

        <!-- =================== Pagination =================== -->
        <div class="flex flex-wrap items-center gap-3 pt-3">
            <!-- ส่วนเลือก Page Size และสถานะ -->
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                <button
                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                    :disabled="page === 1" @click="page--">
                    ก่อนหน้า
                </button>
                <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
                <button
                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                    :disabled="page === totalPages || totalPages === 0" @click="page++">
                    ถัดไป
                </button>
            </div>
        </div>

        <!-- โมดัลยืนยันลบ -->
        <!-- <ConfirmDelete
            :open="confirmOpen"
            @cancel="cancelDelete"
            @confirm="confirmDelete"
        /> -->

        <!-- โมดัลแจ้งลบสำเร็จ -->
        <!-- <DeleteSuccess :open="successOpen" @close="closeSuccess" /> -->
    </section>
</template>

<script>
// เริ่มส่วนสคริปต์ของคอมโพเนนต์
// ใช้เรียก API
import axios from "axios";
// ใช้ inject รับฟังก์ชันจาก parent
import { inject } from "vue";
// ไอคอน Heroicons
import {
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
// คอมโพเนนต์เมนูเรียงลำดับ
import SortMenu from "@/components/SortMenu.vue";
// คอมโพเนนต์ปุ่มฟิลเตอร์
import EmployeeFilter from "@/components/IndexEmployee/EmployeeFilter.vue";
// โมดัลยืนยันลบ
// import ConfirmDelete from "@/components/ConfirmDelete.vue";
// โมดัลแจ้งลบสำเร็จ
// import DeleteSucces from "@/components/EmloyeeDeleteSuccess.vue";

// ตั้งค่า base URL ของ axios
axios.defaults.baseURL = "/api";
// บอกว่าจะรับ JSON
axios.defaults.headers.common["Accept"] = "application/json";

// แมปชื่อแสดงผลของฟิลเตอร์
const FILTER_LABELS = {
    id: "ID",
    department: "Department",
    team: "Team",
    position: "Position",
};

export default {
    // ประกาศคอมโพเนนต์แบบ Options API
    name: "EmployeesPage",
    // ลงทะเบียนคอมโพเนนต์ลูกที่ใช้ใน template
    components: {
        MagnifyingGlassIcon,
        PencilIcon,
        TrashIcon,
        SortMenu,
        EmployeeFilter,
        // ConfirmDelete,
        // DeleteSucces,
    },

    // สถานะภายในคอมโพเนนต์
    data() {
        return {
            // เก็บรายการพนักงานที่ดึงมาจาก API
            employees: [],
            // Search / Filters
            // ค่าที่พิมพ์ในช่องค้นหา (ทันที)
            searchInput: "",
            // ค่าค้นหาที่ apply แล้ว
            search: "",
            // แสดง/ซ่อน filter panel
            showFilter: false,
            // ลำดับฟิลเตอร์
            filterFields: ["id", "department", "team", "position"],
            // ค่าฟิลเตอร์ในแผง (ยังไม่ apply)
            filtersStage: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },
            // ค่าฟิลเตอร์ที่ใช้งานจริงบนตาราง
            filters: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },

            // Sort
            // ฟิลด์ที่ใช้เรียง
            sortBy: null,
            // ทิศทางเรียง (asc/desc)
            sortOrder: null,

            // UI
            // เปิด/ปิดเมนู sort
            showSort: false,

            // Modals
            // เปิด/ปิดโมดัลยืนยันลบ
            confirmOpen: false,
            // เปิด/ปิดโมดัลลบสำเร็จ
            successOpen: false,
            // เก็บ item ที่กำลังจะลบ
            deleting: null,

            // Paging
            // หน้าปัจจุบัน
            page: 1,
            // จำนวนแถวต่อหน้า
            pageSize: 10,
            // เปิด/ปิดเมนูเลือก page size
            openPageSize: false,
        };
    },

    // ไลฟ์ไซเคิล: เรียกทันทีเมื่อถูกสร้าง (ก่อน mount)
    async created() {
        // โหลดข้อมูลพนักงานครั้งแรก
        await this.fetchEmployees();
    },

    // ใช้ Composition API บางส่วนเพื่อ inject ฟังก์ชันจาก layout
    setup() {
        // รับฟังก์ชันเบลอพื้นหลัง (เผื่อไม่มีให้ fallback)
        const setLayoutBlur = inject("setLayoutBlur", () => { });
        // ส่งให้ template/methods ใช้งาน
        return { setLayoutBlur };
    },

    // ไลฟ์ไซเคิล: หลังผูก DOM แล้ว
    mounted() {
        // ฟังคลิกนอกเมนู page size เพื่อปิด
        document.addEventListener("click", this.onClickOutsidePageSize);
    },
    // ไลฟ์ไซเคิล: ก่อนคอมโพเนนต์ถูกถอด
    beforeUnmount() {
        // ถอน event listener
        document.removeEventListener("click", this.onClickOutsidePageSize);
    },

    // ค่าคำนวณจาก state (แคชตาม dependency)
    computed: {
        // ทำข้อมูลพนักงานให้มีค่าเริ่มต้นไม่เป็น undefined
        normalized() {
            return this.employees.map((e) => ({
                ...e,
                emp_id: e.emp_id ?? "",
                emp_prefix: e.emp_prefix ?? "",
                emp_firstname: e.emp_firstname ?? "",
                emp_lastname: e.emp_lastname ?? "",
                emp_nickname: e.emp_nickname ?? "",
                position_name: e.position_name ?? "",
                email: e.emp_email ?? "",
                phone: e.emp_phone ?? "",
                department_name: e.department_name ?? "",
                team_name: e.team_name ?? "",
                created_at: e.created_at ?? "",
            }));
        },

        // สร้างรายการรหัสพนักงานไม่ซ้ำ สำหรับตัวเลือกฟิลเตอร์
        uniqueIds() {
            return [
                ...new Set(
                    this.normalized.map((e) => e.emp_id).filter(Boolean)
                ),
            ].sort((a, b) =>
                String(a).localeCompare(String(b), "en", { numeric: true })
            );
        },
        // รายชื่อแผนกไม่ซ้ำ (เรียงตามภาษาไทย)
        uniqueDepartments() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.department_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },
        // รายชื่อทีมไม่ซ้ำ
        uniqueTeams() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.team_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },
        // รายชื่อตำแหน่งไม่ซ้ำ
        uniquePositions() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.position_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },

        // ตัวเลือกการเรียง (แสดงใน SortMenu)
        sortOptions() {
            return [
                { key: "name", order: "asc", label: "ชื่อพนักงาน A–Z" },
                { key: "name", order: "desc", label: "ชื่อพนักงาน Z–A" },
                { key: "department", order: "asc", label: "ชื่อแผนก A–Z" },
                { key: "department", order: "desc", label: "ชื่อแผนก Z–A" },
                { key: "team", order: "asc", label: "ชื่อทีม A–Z" },
                { key: "team", order: "desc", label: "ชื่อทีม Z–A" },
                { key: "position", order: "asc", label: "ชื่อตำแหน่ง A–Z" },
                { key: "position", order: "desc", label: "ชื่อตำแหน่ง Z–A" },
                { key: "id", order: "asc", label: "รหัสพนักงาน น้อย–มาก" },
                { key: "id", order: "desc", label: "รหัสพนักงาน มาก–น้อย" },
            ];
        },

        // true ถ้ามีฟิลเตอร์ใดๆ ที่ไม่ใช่ 'all'
        hasActiveFilters() {
            return this.filterFields.some((k) => this.filters[k] !== "all");
        },

        // กรองข้อมูลตาม search และ filters
        filtered() {
            // เริ่มจากข้อมูล normalize
            let result = this.normalized;
            // ถ้ามีคำค้นหา
            if (this.search) {
                // แปลงเป็นตัวเล็ก
                const q = this.search.toLowerCase();
                // แมตช์อย่างง่ายจากการรวมสตริง
                result = result.filter((e) =>
                    `${e.emp_id} ${e.emp_prefix} ${e.emp_firstname} ${e.emp_lastname} ${e.emp_nickname} ${e.position_name} ${e.email} ${e.phone} ${e.department_name} ${e.team_name}`
                        .toLowerCase()
                        .includes(q)
                );
            }
            // กรองตามรหัส
            if (this.filters.id !== "all")
                result = result.filter((e) => e.emp_id === this.filters.id);
            // กรองตามแผนก
            if (this.filters.department !== "all")
                result = result.filter(
                    (e) => e.department_name === this.filters.department
                );
            // กรองตามทีม
            if (this.filters.team !== "all")
                result = result.filter(
                    (e) => e.team_name === this.filters.team
                );
            // กรองตามตำแหน่ง
            if (this.filters.position !== "all")
                result = result.filter(
                    (e) => e.position_name === this.filters.position
                );
            // คืนรายการที่ผ่านการกรอง
            return result;
        },

        // เรียงข้อมูลตาม sortBy/sortOrder
        sorted() {
            // ก็อปเพื่อไม่แก้ของเดิม
            const out = [...this.filtered];
            // หากไม่ตั้งค่า sort ให้คืนทันที
            if (!this.sortBy || !this.sortOrder) return out;

            // ฟังก์ชันดึงคีย์ที่ใช้เรียง
            const getters = {
                name: (e) => `${e.emp_firstname} ${e.emp_lastname}`.trim(),
                id: (e) => e.emp_id ?? "",
                department: (e) => e.department_name ?? "",
                position: (e) => e.position_name ?? "",
                team: (e) => e.team_name ?? "",
            };
            // เลือก getter ตามคีย์
            const get = getters[this.sortBy] || (() => "");

            // ตัวเปรียบเทียบตามคีย์
            const cmp = (a, b) => {
                const A = get(a),
                    B = get(b);
                return this.sortBy === "id"
                    ? String(A).localeCompare(String(B), "en", {
                        numeric: true,
                    })
                    : String(A).localeCompare(String(B), "th");
            };
            // เรียงตามทิศทาง
            out.sort((a, b) =>
                this.sortOrder === "asc" ? cmp(a, b) : -cmp(a, b)
            );
            // คืนผลลัพธ์ที่เรียงแล้ว
            return out;
        },

        // จำนวนหน้าทั้งหมด
        totalPages() {
            return Math.ceil(this.sorted.length / this.pageSize);
        },
        // ตัดข้อมูลเฉพาะหน้าปัจจุบัน
        paged() {
            const s = (this.page - 1) * this.pageSize;
            return this.sorted.slice(s, s + this.pageSize);
        },

        // คำนวณรายการหมายเลขหน้าที่จะแสดง (มี '…')
        pagesToShow() {
            const total = this.totalPages,
                cur = this.page;
            // ถ้าน้อยแสดงทั้งหมด
            if (total <= 7)
                return Array.from({ length: total }, (_, i) => i + 1);
            const pages = [1, 2],
                left = Math.max(3, cur - 1),
                right = Math.min(total - 2, cur + 1);
            // จุดซ้าย
            if (left > 3) pages.push("…");
            // แทรกช่วงกลาง
            for (let p = left; p <= right; p++) pages.push(p);
            // จุดขวา
            if (right < total - 2) pages.push("…");
            pages.push(total - 1, total);
            // ลบซ้ำ
            return pages.filter((v, i) => pages.indexOf(v) === i);
        },
        employeeFilterFields() {
            const asOptions = (arr) => arr.map(v => ({ label: String(v), value: String(v) }))
            return [
                { fieldKey: 'id', label: 'ID', fieldType: 'select', allValue: 'all', fieldOptions: asOptions(this.uniqueIds) },
                { fieldKey: 'department', label: 'Department', fieldType: 'select', allValue: 'all', fieldOptions: asOptions(this.uniqueDepartments) },
                { fieldKey: 'team', label: 'Team', fieldType: 'select', allValue: 'all', fieldOptions: asOptions(this.uniqueTeams) },
                { fieldKey: 'position', label: 'Position', fieldType: 'select', allValue: 'all', fieldOptions: asOptions(this.uniquePositions) },
            ]
        },
    },

    // ฟังก์ชันการทำงานต่างๆ
    methods: {
        // โหลดรายชื่อพนักงานจาก API
        async fetchEmployees() {
            try {
                // เรียก GET
                const res = await axios.get("/get-employees");
                // รองรับทั้ง {data:[]} หรือ []
                this.employees = Array.isArray(res.data)
                    ? res.data
                    : res.data?.data || [];
            } catch (e) {
                // ล็อกเมื่อผิดพลาด
                console.error("Error fetching employees", e);
            }
        },

        // เมื่อกดค้นหา/กด Enter: นำค่า stage มาใช้จริง
        applySearchAndFilters() {
            // ย้ายค่า search input -> search (จริง)
            this.search = this.searchInput;
            // ย้ายฟิลเตอร์ stage -> ใช้งานจริง
            this.filters = { ...this.filtersStage };
            // รีเซ็ตไปหน้าแรก
            this.page = 1;
        },
        // กดลบชิป -> เซ็ตฟิลเตอร์นั้นเป็น 'all'
        removeFilter(k) {
            this.filters[k] = "all";
            this.filtersStage[k] = "all";
            // รีเซ็ตหน้า
            this.page = 1;
        },

        // คืนคลาสสีของชิปตามชนิดฟิลเตอร์
        chipClass(k) {
            return k === "id"
                ? "bg-gray-100 text-gray-800"
                : k === "department"
                    ? "bg-rose-100 text-rose-800"
                    : k === "team"
                        ? "bg-blue-100 text-blue-800"
                        : "bg-green-100 text-green-800";
        },
        // ข้อความบนชิป (id จะแสดงเป็น 'ID: xxx')
        chipText(k) {
            return k === "id" ? `ID: ${this.filters.id}` : this.filters[k];
        },

        // เปิด/ปิดเมนู sort
        toggleSort() {
            this.showSort = !this.showSort;
            if (this.showSort) {
                // เปิด sort ให้ปิดฟิลเตอร์
                this.showFilter = false;
                this.openSelect = null;
            }
        },
        
        // เปิด/ปิด filter panel
        toggleFilter() {
            this.showFilter = !this.showFilter;
            if (this.showFilter) {
                // เปิด filter ให้ปิด sort
                this.showSort = false;
            }
        },
        
        // รับค่า filter จาก EmployeeFilter component
        handleFilter(newFilters) {
            this.filters = { ...newFilters };
            this.filtersStage = { ...newFilters };
        },
        // เลือกตัวเลือก sort (กดซ้ำเพื่อยกเลิก)
        toggleSortOption(opt) {
            if (this.sortBy === opt.key && this.sortOrder === opt.order) {
                // ยกเลิกการเรียง
                this.sortBy = null;
                this.sortOrder = null;
            } else {
                // ตั้งค่าการเรียงใหม่
                this.sortBy = opt.key;
                this.sortOrder = opt.order;
            }
            // ปิดเมนู
            this.showSort = false;
            // รีเซ็ตหน้า
            this.page = 1;
        },

        // ไปหน้าก่อนหน้า (ถ้ามี)
        prevPage() {
            if (this.page > 1) this.page--;
        },
        // ไปหน้าถัดไป (ถ้ามี)
        nextPage() {
            if (this.page < this.totalPages) this.page++;
        },
        // ไปหน้าที่ระบุ (ตรวจสอบขอบเขต)
        goToPage(n) {
            if (typeof n === "number" && n >= 1 && n <= this.totalPages)
                this.page = n;
        },

        // เปิด/ปิดเมนูเลือกขนาดหน้า
        togglePageSize() {
            this.openPageSize = !this.openPageSize;
        },
        // เลือกขนาดหน้าใหม่
        choosePageSize(s) {
            this.pageSize = s;
            this.openPageSize = false;
            // รีเซ็ตหน้า
            this.page = 1;
        },
        // คลิกนอกเมนู page size -> ปิด
        onClickOutsidePageSize(e) {
            const el = this.$refs.pageSizeWrap;
            if (this.openPageSize && el && !el.contains(e.target))
                this.openPageSize = false;
        },

        // ไปหน้าแก้ไขพนักงานตาม id
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        // เปิดโมดัลยืนยันลบ พร้อมเบลอเลย์เอาต์
        requestDelete(emp) {
            this.deleting = emp;
            this.confirmOpen = true;
            this.setLayoutBlur(true);
        },
        // ยกเลิกลบ -> ปิดโมดัลและเอาเบลอออก
        cancelDelete() {
            this.confirmOpen = false;
            this.deleting = null;
            this.setLayoutBlur(false);
        },
        // ยืนยันลบ -> เรียก API ลบ แล้วรีโหลดข้อมูล
        // async confirmDelete() {
        //     if (!this.deleting) return;
        //     try {
        //         await axios.delete(`/employees/${this.deleting.id}`);
        //         await this.fetchEmployees();
        //         this.confirmOpen = false;
        //         this.successOpen = true;
        //         this.setLayoutBlur(true);
        //     } catch (e) {
        //         console.error("Error deleting employee", e);
        //         // ปิดทุกอย่างเมื่อ error
        //         this.cancelDelete();
        //     } finally {
        //         // เคลียร์สถานะ
        //         this.deleting = null;
        //     }
        // },
        // ปิดโมดัลสำเร็จ และเอาเบลอออก
        closeSuccess() {
            this.successOpen = false;
            this.setLayoutBlur(false);
        },
    },
};
</script>
