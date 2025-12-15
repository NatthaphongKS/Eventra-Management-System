<!-- EmployeesPage.vue -->
<template>
    <section class="p-0">
        <!-- =================== Toolbar =================== -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 w-full"
        >
            <!-- ✅ SearchBar -->
            <SearchBar
                v-model="searchInput"
                placeholder="Search ID, Name, Nickname..."
                @search="applySearchAndFilters"
            />

            <!-- =================== Filter + Sort + Add =================== -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Filter -->
                <div class="relative z-50" ref="filterBox">
                    <button
                        type="button"
                        @click="toggleFilter"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-neutral-800 hover:bg-gray-50"
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
                            <h3 class="font-semibold text-neutral-800 mb-3">
                                Filter
                            </h3>

                            <div v-for="(opts, key) in optionsMap" :key="key">
                                <label
                                    class="block text-sm font-medium text-neutral-800 mb-1 capitalize"
                                >
                                    {{ key }}
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="filtersStage[key]"
                                        class="w-full appearance-none rounded-xl border border-red-700 px-3 py-2 text-neutral-800 text-sm outline-none focus:ring-2 focus:ring-red-300"
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
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-red-700"
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

                <!-- ✅ Sort -->
                <div class="relative z-[60]" ref="sortWrap">
                    <SortMenu
                        :is-open="sortMenuOpen"
                        :options="sortOptions"
                        :sort-by="sortBy.key"
                        :sort-order="sortBy.order"
                        @toggle="sortMenuOpen = !sortMenuOpen"
                        @choose="onSortChoose"
                    />
                </div>

                <!-- ✅ AddButton -->
                <Button variant="add" @click="goAdd" />
            </div>
        </div>

        <!-- =================== DataTable =================== -->
        <DataTable
            :rows="paged"
            :columns="EmployeeTableColumns"
            :page="page"
            :pageSize="pageSize"
            :total-items="sorted.length"
            :page-size-options="[10, 20, 50, 100]"
            @update:page="page = $event"
            @update:pageSize="
                (s) => {
                    pageSize = s;
                    page = 1;
                }
            "
            class="mt-4"
        >
            <template #actions="{ row }">
                <button
                    class="grid h-8 w-8 place-items-center rounded-full text-neutral-800 hover:text-emerald-600"
                    @click="editEmployee(row.id)"
                    title="Edit"
                    aria-label="edit"
                >
                    <Icon
                        icon="material-symbols:edit-rounded"
                        width="20"
                        height="20"
                    />
                </button>

                <button
                    class="grid h-8 w-8 place-items-center rounded-full text-neutral-800 hover:text-red-600"
                    @click="requestDelete(row)"
                    title="Delete"
                    aria-label="delete"
                >
                    <Icon
                        icon="fluent:delete-12-filled"
                        width="20"
                        height="20"
                    />
                </button>
            </template>

            <template #empty>
                <p class="text-center text-neutral-800">ไม่พบข้อมูลที่ค้นหา</p>
            </template>
        </DataTable>

        <!-- Confirm Delete -->
        <ConfirmDelete
            :open="confirmOpen"
            :item="deleting"
            @cancel="cancelDelete"
            @confirm="confirmDelete"
        />

        <!-- Delete Success -->
        <EmployeeDeleteSuccess
            :open="showDeleteSuccess"
            @close="showDeleteSuccess = false"
        />
    </section>
</template>

<script>
import axios from "axios";
import { Icon } from "@iconify/vue";
import SearchBar from "@/components/SearchBar.vue";
import DataTable from "@/components/DataTable.vue";
import SortMenu from "@/components/SortMenu.vue";
import Button from "@/components/Button.vue";
import ConfirmDelete from "@/components/Alert/ConfirmDelete.vue";
import EmployeeDeleteSuccess from "@/components/Alert/Employee/EmloyeeDeleteSuccess.vue";

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
    name: "EmployeesPage",
    components: {
        Icon,
        SearchBar,
        DataTable,
        SortMenu,
        Button,
        ConfirmDelete,
        EmployeeDeleteSuccess,
    },
    data() {
        return {
            employees: [],
            deleting: null,
            confirmOpen: false,
            showDeleteSuccess: false,
            searchInput: "",
            search: "",
            filtersStage: { department: "all", team: "all", position: "all" },
            filters: { department: "all", team: "all", position: "all" },
            showFilter: false,
            page: 1,
            pageSize: 10,
            sortMenuOpen: false,
            sortBy: { key: "created_at", order: "desc" },
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
            EmployeeTableColumns: [
                { key: "emp_id", label: "ID", class: "text-left w-[100px]" },
                { key: "emp_fullname", label: "Name", class: "text-left w-[180px]" },
                { key: "emp_nickname", label: "Nickname", class: "text-left w-[120px]" },
                { key: "emp_phone", label: "Phone", class: "text-left w-[140px]" },
                { key: "department_name", label: "Department", class: "text-left w-[140px]" },
                { key: "team_name", label: "Team", class: "text-left w-[140px]" },
                { key: "position_name", label: "Position", class: "text-left w-[140px]" },
                {
                    key: "created_at",
                    label: "Created date (D/M/Y)",
                    class: "text-center w-[160px]",
                    format: (v) => (v ? new Date(v).toLocaleDateString("en-GB") : "-"),
                },
            ],
        };
    },
    async created() {
        await this.fetchEmployees();
    },
    mounted() {
        document.addEventListener("click", this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocClick);
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
                result = result.filter((e) => {
                    const fullName = `${e.emp_firstname ?? ""} ${e.emp_lastname ?? ""}`.toLowerCase();
                    const nickname = (e.emp_nickname ?? "").toLowerCase();
                    const empId = (e.emp_id ?? "").toString().toLowerCase();
                    return empId.includes(q) || fullName.includes(q) || nickname.includes(q);
                });
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
            const { key, order } = this.sortBy;
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
        paged() {
            return this.sorted.slice((this.page - 1) * this.pageSize, this.page * this.pageSize);
        },
    },
    methods: {
        async fetchEmployees() {
            try {
                const res = await axios.get("/get-employees");
                const data = Array.isArray(res.data)
                    ? res.data
                    : res.data?.data || [];
                this.employees = data.map((e) => ({
                    ...e,
                    emp_fullname: `${e.emp_firstname ?? ""} ${e.emp_lastname ?? ""}`.trim(),
                    emp_phone: e.emp_phone ?? e.phone ?? "-",
                    created_at:
                        e.emp_create_at ?? e.created_at ?? e.createdAt ?? null,
                }));
            } catch (err) {
                console.error("Error fetching employees", err);
            }
        },
        goAdd() {
            this.$router.push("/add-employee");
        },
        onSortChoose(option) {
            if (!option || !option.key || !option.order) return;
            this.sortBy = { key: option.key, order: option.order };
            this.page = 1;
            this.sortMenuOpen = false;
        },
        onDocClick(e) {
            if (!this.sortMenuOpen) return;
            const wrap = this.$refs.sortWrap;
            if (wrap && !wrap.contains(e.target)) {
                this.sortMenuOpen = false;
            }
        },
        toggleFilter() {
            this.showFilter = !this.showFilter;
        },
        applySearchAndFilters() {
            this.search = this.searchInput.trim();
            this.filters = { ...this.filtersStage };
            this.page = 1;
            this.showFilter = false;
        },
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        requestDelete(emp) {
            this.deleting = emp;
            this.confirmOpen = true;
        },
        cancelDelete() {
            this.deleting = null;
            this.confirmOpen = false;
        },
        async confirmDelete() {
            if (!this.deleting?.id) return;
            try {
                await axios.delete(`/employees/${this.deleting.id}`);
                this.employees = this.employees.filter((e) => e.id !== this.deleting.id);
                this.deleting = null;
                this.confirmOpen = false;
                this.showDeleteSuccess = true;
            } catch (err) {
                console.error("Delete failed:", err);
            }
        },
    },
};
</script>
