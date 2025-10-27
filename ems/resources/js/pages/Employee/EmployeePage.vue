<template>
    <section class="p-0">
        <!-- =================== Toolbar =================== -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 w-full"
        >
            <!-- Search -->
            <div class="flex w-full sm:w-auto flex-1 items-center gap-2">
                <div class="relative flex-1">
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Search..."
                        @keyup.enter="applySearchAndFilters"
                        class="w-full h-11 rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm outline-none transition focus:border-red-300 focus:bg-white"
                    />
                </div>
                <button
                    @click="applySearchAndFilters"
                    class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
                    aria-label="search"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"
                        />
                    </svg>
                </button>
            </div>

            <!-- Filter + Sort + Add -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- ================= Filter ================= -->
                <div class="relative z-50" ref="filterBox">
                    <button
                        type="button"
                        @click="toggleFilter"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        :class="{ 'bg-gray-100': showFilter }"
                    >
                        <svg
                            class="w-5 h-5"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            viewBox="0 0 24 24"
                        >
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="6" y1="12" x2="16" y2="12" />
                            <line x1="8" y1="17" x2="12" y2="17" />
                        </svg>
                        <span>Filter</span>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-150"
                        enter-from-class="opacity-0 translate-y-1 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 translate-y-1 scale-95"
                    >
                        <div
                            v-if="showFilter"
                            ref="filterDropdown"
                            class="absolute top-full right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 z-50 p-4 space-y-4"
                            @click.stop
                        >
                            <h3 class="font-semibold text-gray-900 mb-3">
                                Filter
                            </h3>

                            <div v-for="(opts, key) in optionsMap" :key="key">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1 capitalize"
                                >
                                    {{ key }}
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="filtersStage[key]"
                                        class="w-full appearance-none rounded-xl border border-[#C91818] px-3 py-2 text-gray-700 text-sm outline-none focus:ring-2 focus:ring-red-200"
                                    >
                                        <option value="all">All</option>
                                        <option
                                            v-for="opt in opts"
                                            :key="opt"
                                            :value="opt"
                                        >
                                            {{ opt }}
                                        </option>
                                    </select>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#C91818]"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 9l6 6 6-6"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- ================= Sort ================= -->
                <div class="relative" ref="sortBox">
                    <button
                        @click="toggleSort"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"
                            />
                        </svg>
                        <span>Sort</span>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-150"
                        enter-from-class="opacity-0 translate-y-1 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 translate-y-1 scale-95"
                    >
                        <div
                            v-if="showSort"
                            ref="sortDropdown"
                            class="absolute right-0 z-10 mt-2 w-60 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg"
                            @click.stop
                        >
                            <button
                                v-for="opt in sortOptions"
                                :key="opt.key"
                                class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50"
                                @click="applySort(opt)"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </Transition>
                </div>

                <!-- ================= Add New ================= -->
                <router-link
                    to="/add-employee"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
                >
                    <span class="text-lg leading-none">＋</span>
                    <span>Add New</span>
                </router-link>
            </div>
        </div>

        <!-- =================== Table =================== -->
        <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-100 table-auto">
                <thead class="bg-gray-50 text-gray-600">
                    <tr class="text-left text-sm">
                        <th class="px-3 py-3 font-semibold text-center w-[40px]">#</th>
                        <th class="px-3 py-3 font-semibold">ID</th>
                        <th class="px-3 py-3 font-semibold">Name</th>
                        <th class="px-3 py-3 font-semibold">Nickname</th>
                        <th class="px-3 py-3 font-semibold">Phone</th>
                        <th class="px-3 py-3 font-semibold">Department</th>
                        <th class="px-3 py-3 font-semibold">Team</th>
                        <th class="px-3 py-3 font-semibold">Position</th>
                        <th class="px-3 py-3 font-semibold">Created Date</th>
                        <th class="px-3 py-3 font-semibold text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-xs text-gray-700 whitespace-normal break-words">
                    <tr
                        v-for="(emp, i) in paged"
                        :key="emp.id ?? emp.emp_id ?? i"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-3 py-2 text-center">{{ (page - 1) * pageSize + i + 1 }}</td>
                        <td class="px-3 py-2">{{ emp.emp_id || "N/A" }}</td>
                        <td class="px-3 py-2">{{ emp.emp_firstname }} {{ emp.emp_lastname }}</td>
                        <td class="px-3 py-2">{{ emp.emp_nickname || "N/A" }}</td>
                        <td class="px-3 py-2">{{ emp.emp_phone || emp.phone || "N/A" }}</td>
                        <td class="px-3 py-2">{{ emp.department_name || "N/A" }}</td>
                        <td class="px-3 py-2">{{ emp.team_name || "N/A" }}</td>
                        <td class="px-3 py-2">{{ emp.position_name || "N/A" }}</td>
                        <td class="px-3 py-2">
                            {{
                                emp.created_at
                                    ? new Date(emp.created_at).toLocaleDateString("en-GB")
                                    : "N/A"
                            }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    @click="editEmployee(emp.id)"
                                    class="inline-flex items-center justify-center rounded-full bg-blue-500 hover:bg-blue-600 p-1.5 transition"
                                >
                                    <Icon
                                        icon="mdi:pencil"
                                        class="h-5 w-5 text-white"
                                    />
                                </button>
                                <button
                                    @click="requestDelete(emp)"
                                    class="inline-flex items-center justify-center rounded-full bg-red-600 hover:bg-red-700 p-1.5 transition"
                                >
                                    <Icon
                                        icon="streamline:recycle-bin-2-solid"
                                        class="h-5 w-5 text-white"
                                    />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="paged.length === 0">
                        <td
                            colspan="10"
                            class="px-6 py-8 text-center text-xs text-gray-500"
                        >
                            ไม่พบข้อมูลที่ค้นหา
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ✅ ConfirmDelete Modal -->
        <ConfirmDelete
            :open="confirmOpen"
            :item="deleting"
            @cancel="cancelDelete"
            @confirm="confirmDelete"
        />

        <!-- ✅ Employee Delete Success -->
        <EmployeeDeleteSuccess
  :open="showDeleteSuccess"
  @close="showDeleteSuccess = false"
/>
    </section>
</template>

<script>
import axios from "axios";
import { Icon } from "@iconify/vue";
import ConfirmDelete from "@/components/Alert/ConfirmDelete.vue";
import EmployeeDeleteSuccess from "@/components/Alert/Employee/EmloyeeDeleteSuccess.vue"; // ✅ แก้ชื่อ import ให้ถูกและเพิ่ม component

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
    name: "EmployeesPage",
    components: { Icon, ConfirmDelete, EmployeeDeleteSuccess }, // ✅ เพิ่ม component
    data() {
        return {
            employees: [],
            deleting: null,
            confirmOpen: false,
            showDeleteSuccess: false, // ✅ state สำหรับแจ้งเตือนลบสำเร็จ
            searchInput: "",
            search: "",
            filtersStage: { department: "all", team: "all", position: "all" },
            filters: { department: "all", team: "all", position: "all" },
            showFilter: false,
            showSort: false,
            sortField: "created_at",
            sortDir: "desc",
            page: 1,
            pageSize: 10,
            sortOptions: [
                { key: "name_asc", field: "emp_firstname", dir: "asc", label: "ชื่อพนักงาน A–Z" },
                { key: "name_desc", field: "emp_firstname", dir: "desc", label: "ชื่อพนักงาน Z–A" },
                { key: "dept_asc", field: "department_name", dir: "asc", label: "แผนก A–Z" },
                { key: "dept_desc", field: "department_name", dir: "desc", label: "แผนก Z–A" },
                { key: "team_asc", field: "team_name", dir: "asc", label: "ทีม A–Z" },
                { key: "team_desc", field: "team_name", dir: "desc", label: "ทีม Z–A" },
                { key: "date_desc", field: "created_at", dir: "desc", label: "วันที่เพิ่มใหม่สุด" },
                { key: "date_asc", field: "created_at", dir: "asc", label: "วันที่เพิ่มเก่าสุด" },
            ],
        };
    },
    async created() {
        await this.fetchEmployees();
    },
    mounted() {
        document.addEventListener("click", this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
    },
    computed: {
        optionsMap() {
            return {
                department: [
                    ...new Set(this.employees.map((e) => e.department_name).filter(Boolean)),
                ],
                team: [...new Set(this.employees.map((e) => e.team_name).filter(Boolean))],
                position: [
                    ...new Set(this.employees.map((e) => e.position_name).filter(Boolean)),
                ],
            };
        },
        filtered() {
            let result = this.employees;
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) =>
                    `${e.emp_id} ${e.emp_firstname} ${e.emp_lastname} ${e.emp_nickname}`
                        .toLowerCase()
                        .includes(q)
                );
            }
            if (this.filters.department !== "all")
                result = result.filter((e) => e.department_name === this.filters.department);
            if (this.filters.team !== "all")
                result = result.filter((e) => e.team_name === this.filters.team);
            if (this.filters.position !== "all")
                result = result.filter((e) => e.position_name === this.filters.position);
            return result;
        },
        sorted() {
            const field = this.sortField;
            const dir = this.sortDir === "asc" ? 1 : -1;
            if (!field) return this.filtered;
            return this.filtered.slice().sort((a, b) => {
                const av = (a[field] || "").toString().toLowerCase();
                const bv = (b[field] || "").toString().toLowerCase();
                return av.localeCompare(bv, "th") * dir;
            });
        },
        paged() {
            return this.sorted.slice((this.page - 1) * this.pageSize, this.page * this.pageSize);
        },
    },
    methods: {
        async fetchEmployees() {
            try {
                const res = await axios.get("/get-employees");
                const data = Array.isArray(res.data) ? res.data : res.data?.data || [];
                this.employees = data.map((e) => ({
                    ...e,
                    phone: e.emp_phone ?? e.phone ?? "",
                    created_at: e.created_at ?? e.createdAt ?? null,
                }));
            } catch (err) {
                console.error("Error fetching employees", err);
            }
        },
        toggleFilter() {
            this.showFilter = !this.showFilter;
            if (this.showFilter) this.showSort = false;
        },
        toggleSort() {
            this.showSort = !this.showSort;
            if (this.showSort) this.showFilter = false;
        },
        applySort(opt) {
            this.sortField = opt.field;
            this.sortDir = opt.dir;
            this.showSort = false;
        },
        applySearchAndFilters() {
            this.search = this.searchInput.trim();
            this.filters = { ...this.filtersStage };
            this.page = 1;
            this.showFilter = false;
        },
        resetFilters() {
            this.filtersStage = { department: "all", team: "all", position: "all" };
            this.applySearchAndFilters();
        },
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        // ✅ กดถังขยะแล้วเปิด ConfirmDelete
        requestDelete(emp) {
            this.deleting = emp;
            this.confirmOpen = true;
        },
        // ✅ ปิด Modal
        cancelDelete() {
            this.deleting = null;
            this.confirmOpen = false;
        },
        // ✅ ยืนยันลบ
        async confirmDelete() {
            if (!this.deleting?.id) return;
            try {
                await axios.delete(`/employees/${this.deleting.id}`);
                this.employees = this.employees.filter((e) => e.id !== this.deleting.id);
                this.deleting = null;
                this.confirmOpen = false;

                // ✅ แสดงแจ้งเตือนลบสำเร็จ
                this.showDeleteSuccess = true;

            } catch (err) {
                console.error("Delete failed:", err);
            }
        },
        handleClickOutside(e) {
            const filterBox = this.$refs.filterBox;
            const sortBox = this.$refs.sortBox;
            if (filterBox && !filterBox.contains(e.target)) this.showFilter = false;
            if (sortBox && !sortBox.contains(e.target)) this.showSort = false;
        },
    },
};
</script>
