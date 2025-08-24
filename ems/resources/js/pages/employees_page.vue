<template>
    <section class="p-0">
        <div
            class="w-full max-w-screen-2xl mx-auto bg-white rounded-2xl shadow border border-gray-200 p-5 md:p-6"
        >
            <!-- =================== Toolbar =================== -->
            <div class="flex items-center gap-3 mb-4 overflow-visible">
                <!-- Search -->
                <input
                    v-model.trim="searchInput"
                    placeholder="Search..."
                    @keyup.enter="applySearchAndFilters"
                    class="flex-1 h-10 px-4 rounded-full border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-500"
                />
                <button
                    class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center hover:bg-rose-700"
                    @click="applySearchAndFilters"
                    aria-label="Search"
                    title="ค้นหา/ใช้ฟิลเตอร์ (คลิกหรือกด Enter)"
                >
                    <MagnifyingGlassIcon class="w-5 h-5" />
                </button>

                <!-- Filter -->
                <div class="relative z-50">
                    <button
                        type="button"
                        @click="toggleFilter"
                        aria-label="Filter"
                        :aria-expanded="showFilter"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100"
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
                        <span class="hidden sm:inline">Filter</span>
                        <span
                            v-if="hasActiveFilters"
                            class="w-2 h-2 bg-rose-600 rounded-full"
                        ></span>
                    </button>

                    <!-- Filter Card -->
                    <div
                        v-if="showFilter"
                        class="absolute top-full right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                        @click.stop
                    >
                        <div class="p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800">
                                    Filter
                                </h3>
                                <button
                                    @click="clearStageFilters"
                                    class="text-xs text-rose-600 hover:text-rose-800"
                                >
                                    Clear
                                </button>
                            </div>

                            <!-- เรนเดอร์ SelectField แบบ loop -->
                            <SelectField
                                v-for="def in filterDefs"
                                :key="def.field"
                                :label="def.label"
                                :field="def.field"
                                :modelValue="def.modelValue"
                                :options="def.options"
                                :isOpen="openSelect === def.field"
                                @toggle="toggleSelect"
                                @choose="chooseStage"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sort -->
                <SortMenu
                    :isOpen="showSort"
                    :options="sortOptions"
                    :sortBy="sortBy"
                    :sortOrder="sortOrder"
                    @toggle="toggleSort"
                    @choose="toggleSortOption"
                />

                <!-- Add New -->
                <router-link
                    to="/add-employee"
                    class="ml-auto inline-flex items-center h-10 px-4 rounded-full bg-rose-600 text-white hover:bg-rose-700 whitespace-nowrap z-0"
                >
                    + Add New
                </router-link>
            </div>

            <!-- =================== Chips =================== -->
            <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-4">
                <div
                    v-for="k in filterFields"
                    :key="k"
                    v-show="filters[k] !== 'all'"
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full"
                    :class="chipClass(k)"
                >
                    {{ chipText(k) }}
                    <button @click="removeFilter(k)" class="hover:opacity-80">
                        ✕
                    </button>
                </div>
            </div>

            <!-- =================== Table =================== -->
            <div class="overflow-auto">
                <table class="w-full table-fixed">
                    <colgroup>
                        <col class="w-12" />
                        <col class="w-24" />
                        <col class="w-[20%]" />
                        <col class="w-28" />
                        <col class="w-24" />
                        <col class="w-32" />
                        <col class="w-24" />
                        <col class="w-32" />
                        <col class="w-36" />
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th
                                class="px-2.5 py-2 font-semibold text-[13px] text-center"
                            >
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
                        </tr>
                    </thead>
                    <tbody class="text-[15px]">
                        <tr
                            v-for="(emp, i) in paged"
                            :key="emp.id ?? emp.emp_id ?? i"
                            class="border-b border-gray-200 last:border-0 hover:bg-rose-50"
                        >
                            <td class="px-2.5 py-2 text-center">
                                {{ (page - 1) * pageSize + i + 1 }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.emp_id || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                <span class="block truncate">
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
                                {{ emp.team_name || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.position_name || "N/A" }}
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
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <button
                                        @click="editEmployee(emp.id)"
                                        aria-label="Edit"
                                        class="p-1.5 rounded-lg hover:bg-gray-100"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon
                                            class="w-4 h-4 text-gray-600"
                                        />
                                    </button>
                                    <button
                                        @click="requestDelete(emp)"
                                        aria-label="Delete"
                                        class="p-1.5 rounded-lg hover:bg-rose-50"
                                        title="Delete"
                                    >
                                        <TrashIcon
                                            class="w-4 h-4 text-rose-600"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paged.length === 0">
                            <td
                                :colspan="10"
                                class="px-3 py-6 text-center text-gray-500"
                            >
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

            <!-- =================== Pagination =================== -->
            <div class="flex flex-wrap items-center gap-3 pt-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-700">แสดง</span>
                    <div ref="pageSizeWrap" class="relative inline-block">
                        <button
                            type="button"
                            @click="togglePageSize()"
                            class="relative h-9 min-w-[72px] px-4 pr-9 rounded-full border-2 border-[#D40E11] bg-white text-[14px] font-medium text-black text-left select-none focus:outline-none focus:ring-2 focus:ring-[#D40E11]/20"
                            :aria-expanded="openPageSize"
                            aria-haspopup="listbox"
                        >
                            {{ pageSize }}
                            <svg
                                class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 fill-[#D40E11] transition-transform"
                                :class="openPageSize ? 'rotate-180' : ''"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.16l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.39a.75.75 0 0 1 .02-1.18z"
                                />
                            </svg>
                        </button>
                        <div
                            v-if="openPageSize"
                            class="absolute z-50 mt-1.5 w-full rounded-2xl border-2 border-[#D40E11] bg-white shadow-lg overflow-hidden"
                            role="listbox"
                        >
                            <ul class="py-1">
                                <li v-for="s in [10, 25, 50, 100]" :key="s">
                                    <button
                                        type="button"
                                        @click="choosePageSize(s)"
                                        class="w-full text-left px-3 py-1.5 text-[14px] hover:bg-rose-50"
                                        :class="
                                            s === pageSize
                                                ? 'text-[#D40E11] font-semibold bg-rose-50/50'
                                                : 'text-black'
                                        "
                                        role="option"
                                        :aria-selected="s === pageSize"
                                    >
                                        {{ s }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <span class="ml-2 text-[11px] text-gray-500"
                        >{{ page }}-{{ totalPages }} จาก
                        {{ sorted.length }} รายการ</span
                    >
                </div>

                <div class="flex-1 flex justify-center">
                    <div class="flex items-center gap-2">
                        <button
                            class="w-10 h-10 flex items-center justify-center rounded-full text-2xl leading-none text-rose-800 hover:text-rose-900 disabled:opacity-45 disabled:hover:text-rose-800"
                            @click="prevPage"
                            :disabled="page === 1"
                            aria-label="Previous page"
                        >
                            ◄
                        </button>
                        <button
                            v-for="(p, idx) in pagesToShow"
                            :key="idx"
                            :disabled="p === '…'"
                            @click="p !== '…' && goToPage(p)"
                            :aria-current="p === page ? 'page' : null"
                            class="w-9 h-9 px-3 rounded-full border transition disabled:cursor-default select-none"
                            :class="[
                                p === '…'
                                    ? 'border-transparent bg-transparent text-gray-400 cursor-default'
                                    : 'border-rose-600',
                                p !== page && p !== '…'
                                    ? 'bg-white text-rose-600 hover:bg-rose-50 hover:text-rose-700'
                                    : '',
                                p === page ? 'bg-rose-600 text-white' : '',
                            ]"
                        >
                            {{ p }}
                        </button>
                        <button
                            class="w-10 h-10 flex items-center justify-center rounded-full text-2xl leading-none text-rose-800 hover:text-rose-900 disabled:opacity-45 disabled:hover:text-rose-800"
                            @click="nextPage"
                            :disabled="page === totalPages || totalPages === 0"
                            aria-label="Next page"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- คลิกนอกเพื่อปิด Filter/Sort -->
        <div
            v-if="showFilter || showSort"
            @click="
                showFilter = false;
                showSort = false;
                openSelect = null;
            "
            class="fixed inset-0 z-40"
        ></div>

        <!-- Modals -->
        <ConfirmDelete
            :open="confirmOpen"
            @cancel="cancelDelete"
            @confirm="confirmDelete"
        />
        <DeleteSuccess :open="successOpen" @close="closeSuccess" />
    </section>
</template>

<script>
import axios from "axios";
import { inject } from "vue";
import {
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
import SelectField from "@/components/SelectField.vue";
import SortMenu from "@/components/SortMenu.vue";
import ConfirmDelete from "@/components/ConfirmDelete.vue";
import DeleteSuccess from "@/components/DeleteSuccess.vue";

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

const FILTER_LABELS = {
    id: "ID",
    department: "Department",
    team: "Team",
    position: "Position",
};

export default {
    name: "EmployeesPage",
    components: {
        MagnifyingGlassIcon,
        PencilSquareIcon,
        TrashIcon,
        SelectField,
        SortMenu,
        ConfirmDelete,
        DeleteSuccess,
    },

    data() {
        return {
            employees: [],
            // Search / Filters
            searchInput: "",
            search: "",
            filterFields: ["id", "department", "team", "position"],
            filtersStage: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },
            filters: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },

            // Sort
            sortBy: null,
            sortOrder: null,

            // UI
            showFilter: false,
            showSort: false,
            openSelect: null,

            // Modals
            confirmOpen: false,
            successOpen: false,
            deleting: null,

            // Paging
            page: 1,
            pageSize: 10,
            openPageSize: false,
        };
    },

    async created() {
        await this.fetchEmployees();
    },

    setup() {
        const setLayoutBlur = inject("setLayoutBlur", () => {});
        return { setLayoutBlur };
    },

    mounted() {
        document.addEventListener("click", this.onClickOutsidePageSize);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onClickOutsidePageSize);
    },

    computed: {
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

        // unique options
        uniqueIds() {
            return [
                ...new Set(
                    this.normalized.map((e) => e.emp_id).filter(Boolean)
                ),
            ].sort((a, b) =>
                String(a).localeCompare(String(b), "en", { numeric: true })
            );
        },
        uniqueDepartments() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.department_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },
        uniqueTeams() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.team_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },
        uniquePositions() {
            return [
                ...new Set(
                    this.normalized
                        .map((e) => e.position_name)
                        .filter((v) => v && v !== "N/A")
                ),
            ].sort((a, b) => a.localeCompare(b, "th"));
        },

        optionsMap() {
            return {
                id: this.uniqueIds,
                department: this.uniqueDepartments,
                team: this.uniqueTeams,
                position: this.uniquePositions,
            };
        },

        filterDefs() {
            return this.filterFields.map((f) => ({
                field: f,
                label: FILTER_LABELS[f],
                modelValue: this.filtersStage[f],
                options: this.optionsMap[f] || [],
            }));
        },

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

        hasActiveFilters() {
            return this.filterFields.some((k) => this.filters[k] !== "all");
        },

        filtered() {
            let result = this.normalized;
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) =>
                    `${e.emp_id} ${e.emp_prefix} ${e.emp_firstname} ${e.emp_lastname} ${e.emp_nickname} ${e.position_name} ${e.email} ${e.phone} ${e.department_name} ${e.team_name}`
                        .toLowerCase()
                        .includes(q)
                );
            }
            if (this.filters.id !== "all")
                result = result.filter((e) => e.emp_id === this.filters.id);
            if (this.filters.department !== "all")
                result = result.filter(
                    (e) => e.department_name === this.filters.department
                );
            if (this.filters.team !== "all")
                result = result.filter(
                    (e) => e.team_name === this.filters.team
                );
            if (this.filters.position !== "all")
                result = result.filter(
                    (e) => e.position_name === this.filters.position
                );
            return result;
        },

        sorted() {
            const out = [...this.filtered];
            if (!this.sortBy || !this.sortOrder) return out;

            const getters = {
                name: (e) => `${e.emp_firstname} ${e.emp_lastname}`.trim(),
                id: (e) => e.emp_id ?? "",
                department: (e) => e.department_name ?? "",
                position: (e) => e.position_name ?? "",
                team: (e) => e.team_name ?? "",
            };
            const get = getters[this.sortBy] || (() => "");

            const cmp = (a, b) => {
                const A = get(a),
                    B = get(b);
                return this.sortBy === "id"
                    ? String(A).localeCompare(String(B), "en", {
                          numeric: true,
                      })
                    : String(A).localeCompare(String(B), "th");
            };
            out.sort((a, b) =>
                this.sortOrder === "asc" ? cmp(a, b) : -cmp(a, b)
            );
            return out;
        },

        totalPages() {
            return Math.ceil(this.sorted.length / this.pageSize);
        },
        paged() {
            const s = (this.page - 1) * this.pageSize;
            return this.sorted.slice(s, s + this.pageSize);
        },

        pagesToShow() {
            const total = this.totalPages,
                cur = this.page;
            if (total <= 7)
                return Array.from({ length: total }, (_, i) => i + 1);
            const pages = [1, 2],
                left = Math.max(3, cur - 1),
                right = Math.min(total - 2, cur + 1);
            if (left > 3) pages.push("…");
            for (let p = left; p <= right; p++) pages.push(p);
            if (right < total - 2) pages.push("…");
            pages.push(total - 1, total);
            return pages.filter((v, i) => pages.indexOf(v) === i);
        },
    },

    methods: {
        async fetchEmployees() {
            try {
                const res = await axios.get("/get-employees");
                this.employees = Array.isArray(res.data)
                    ? res.data
                    : res.data?.data || [];
            } catch (e) {
                console.error("Error fetching employees", e);
            }
        },

        applySearchAndFilters() {
            this.search = this.searchInput;
            this.filters = { ...this.filtersStage };
            this.page = 1;
            this.showFilter = false;
            this.openSelect = null;
        },

        toggleFilter() {
            this.showFilter = !this.showFilter;
            if (this.showFilter) {
                this.showSort = false;
                this.openSelect = null;
            }
        },
        toggleSelect(name) {
            this.openSelect = this.openSelect === name ? null : name;
        },
        chooseStage(field, value) {
            this.filtersStage[field] = value;
            this.openSelect = null;
        },
        clearStageFilters() {
            this.filtersStage = {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            };
            this.openSelect = null;
        },
        removeFilter(k) {
            this.filters[k] = "all";
            this.filtersStage[k] = "all";
            this.page = 1;
        },

        chipClass(k) {
            return k === "id"
                ? "bg-gray-100 text-gray-800"
                : k === "department"
                ? "bg-rose-100 text-rose-800"
                : k === "team"
                ? "bg-blue-100 text-blue-800"
                : "bg-green-100 text-green-800";
        },
        chipText(k) {
            return k === "id" ? `ID: ${this.filters.id}` : this.filters[k];
        },

        // Sort
        toggleSort() {
            this.showSort = !this.showSort;
            if (this.showSort) {
                this.showFilter = false;
                this.openSelect = null;
            }
        },
        toggleSortOption(opt) {
            if (this.sortBy === opt.key && this.sortOrder === opt.order) {
                this.sortBy = null;
                this.sortOrder = null;
            } else {
                this.sortBy = opt.key;
                this.sortOrder = opt.order;
            }
            this.showSort = false;
            this.page = 1;
        },

        // Pagination
        prevPage() {
            if (this.page > 1) this.page--;
        },
        nextPage() {
            if (this.page < this.totalPages) this.page++;
        },
        goToPage(n) {
            if (typeof n === "number" && n >= 1 && n <= this.totalPages)
                this.page = n;
        },

        // Page size dropdown
        togglePageSize() {
            this.openPageSize = !this.openPageSize;
        },
        choosePageSize(s) {
            this.pageSize = s;
            this.openPageSize = false;
            this.page = 1;
        },
        onClickOutsidePageSize(e) {
            const el = this.$refs.pageSizeWrap;
            if (this.openPageSize && el && !el.contains(e.target))
                this.openPageSize = false;
        },

        // Actions
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        requestDelete(emp) {
            this.deleting = emp;
            this.confirmOpen = true;
            this.setLayoutBlur(true);
        },
        cancelDelete() {
            this.confirmOpen = false;
            this.deleting = null;
            this.setLayoutBlur(false);
        },
        async confirmDelete() {
            if (!this.deleting) return;
            try {
                await axios.delete(`/employees/${this.deleting.id}`);
                await this.fetchEmployees();
                this.confirmOpen = false;
                this.successOpen = true;
                this.setLayoutBlur(true);
            } catch (e) {
                console.error("Error deleting employee", e);
                this.cancelDelete();
            } finally {
                this.deleting = null;
            }
        },
        closeSuccess() {
            this.successOpen = false;
            this.setLayoutBlur(false);
        },
    },
};
</script>
