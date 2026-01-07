<template>
    <section class="p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 w-full gap-3">
            <div class="flex-1">
                <SearchBar
                    v-model="searchInput"
                    placeholder="Search Employee ID / Name / Nickname"
                    @search="onSearchText"
                />
            </div>

            <div class="flex items-stretch gap-2 flex-shrink-0 mt-[30px]">
                <FilterEmployees
                    ref="filterDropdown"
                    v-model="filters"
                    :options="optionsMap"
                    @toggle="onFilterToggle"
                    class="[&_button]:h-full"
                />

                <div ref="sortWrap" @click.stop class="flex items-center">
                    <SortMenu
                        :is-open="sortMenuOpen"
                        :options="sortOptions"
                        :sort-by="sortBy.key"
                        :sort-order="sortBy.order"
                        @toggle="toggleSort"
                        @choose="onSortChoose"
                        class="[&_button]:h-full transition-all duration-200 ease-in-out"
                    />
                </div>

                <AddButton @click="goAdd" class="h-full w-[44px] flex items-center justify-center" />
            </div>
        </div>

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
                <button class="grid h-8 w-8 place-items-center rounded-full text-neutral-800 hover:text-emerald-600"
                    @click="editEmployee(row.emp_id)" title="Edit">
                    <Icon icon="material-symbols:edit-rounded" width="20" height="20" />
                </button>

                <button :disabled="!canDelete" class="grid h-8 w-8 place-items-center rounded-full transition
                   text-neutral-800 cursor-pointer hover:text-red-600 disabled:text-neutral-400
                   disabled:cursor-not-allowed disabled:opacity-40"
                    @click="openDelete(row.emp_id)"
                    :title="canDelete ? 'Delete' : 'You do not have permission'">
                    <Icon icon="fluent:delete-12-filled" width="20" height="20" />
                </button>
            </template>

            <template #empty>
                <p class="text-center text-neutral-800">ไม่พบข้อมูลที่ค้นหา</p>
            </template>
        </DataTable>

        <ModalAlert :open="showModalAsk" type="confirm" title="ARE YOU SURE TO DELETE"
            message="This employee will be deleted permanently. Are you sure?" :show-cancel="true" okText="OK"
            @confirm="onConfirmDelete" @cancel="onCancelDelete" />

        <ModalAlert :open="showModalSuccess" type="success" title="DELETE SUCCESS!"
            message="Employee has been deleted successfully." @confirm="onConfirmSuccess" />

        <ModalAlert :open="showModalFail" type="error" title="ERROR!" message="Sorry, Please try again later."
            @confirm="onConfirmFail" />
    </section>
</template>

<script>
import axios from "axios";
import { Icon } from "@iconify/vue";
import SearchBar from "@/components/SearchBar.vue";
import DataTable from "@/components/DataTable.vue";
import SortMenu from "@/components/SortMenu.vue";
import AddButton from "@/components/AddButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import FilterEmployees from "@/components/Button/FilterEmployees.vue";

export default {
    name: "EmployeesPage",
    components: {
        Icon, SearchBar, DataTable, SortMenu, AddButton, ModalAlert, FilterEmployees,
    },
    data() {
        return {
            employees: [],
            deleteId: null,
            showModalAsk: false,
            showModalSuccess: false,
            showModalFail: false,
            searchInput: "",
            search: "",
            // ✅ แก้ไข: เปลี่ยนจาก "all" เป็น Array ว่าง []
            filters: {
                "Company ID": [],
                department: [],
                team: [],
                position: []
            },
            page: 1,
            pageSize: 10,
            sortMenuOpen: false,
            sortBy: { key: "", order: "" },
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
    watch: {
        filters: {
            handler() { this.page = 1; },
            deep: true,
        },
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
        currentUser() {
            const u = localStorage.getItem("userData");
            return u ? JSON.parse(u) : {};
        },
        canDelete() {
            return this.currentUser.emp_permission === "admin";
        },
        optionsMap() {
            return {
                "Company ID": [...new Set(this.employees.map((e) => e.company_id).filter((v) => v && v !== "-"))],
                department: [...new Set(this.employees.map((e) => e.department_name).filter(Boolean))],
                team: [...new Set(this.employees.map((e) => e.team_name).filter(Boolean))],
                position: [...new Set(this.employees.map((e) => e.position_name).filter(Boolean))],
            };
        },
        filtered() {
            let result = this.employees;

            // 1. Text Search
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) => {
                    const fullName = (e.emp_fullname || "").toLowerCase();
                    const nickname = (e.emp_nickname || "").toLowerCase();
                    const empId = (e.emp_id || "").toString().toLowerCase();
                    const compId = (e.company_id || "").toString().toLowerCase();
                    return empId.includes(q) || fullName.includes(q) || nickname.includes(q) || compId.includes(q);
                });
            }

            // 2. Dropdown Filters (✅ แก้ไข Logic การ Filter เป็นแบบ Array)
            if (this.filters.department.length > 0) {
                result = result.filter((e) => this.filters.department.includes(e.department_name));
            }
            if (this.filters.team.length > 0) {
                result = result.filter((e) => this.filters.team.includes(e.team_name));
            }
            if (this.filters.position.length > 0) {
                result = result.filter((e) => this.filters.position.includes(e.position_name));
            }
            if (this.filters["Company ID"].length > 0) {
                result = result.filter((e) => this.filters["Company ID"].includes(e.company_id));
            }

            return result;
        },
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
        paged() {
            return this.sorted.slice((this.page - 1) * this.pageSize, this.page * this.pageSize);
        },
    },
    methods: {
        async fetchEmployees() {
            try {
                const res = await axios.get("/get-employees");
                const data = Array.isArray(res.data) ? res.data : res.data?.data || [];
                this.employees = data.map((e) => {
                    let extractedId = "-";
                    if (e.emp_id) {
                        const match = e.emp_id.toString().match(/^[A-Za-zก-๙-]+/);
                        extractedId = match ? match[0] : e.emp_id.toString().replace(/[0-9]/g, '');
                    }
                    return {
                        ...e,
                        company_id: extractedId || "-",
                        emp_fullname: `${e.emp_firstname ?? ""} ${e.emp_lastname ?? ""}`.trim(),
                        emp_phone: e.emp_phone ?? e.phone ?? "-",
                        created_at: e.emp_create_at ?? e.created_at ?? e.createdAt ?? null,
                    };
                });
            } catch (err) {
                console.error("Error fetching employees", err);
            }
        },
        goAdd() { this.$router.push("/add-employee"); },
        toggleSort() {
            this.sortMenuOpen = !this.sortMenuOpen;
            if (this.sortMenuOpen && this.$refs.filterDropdown) {
                // ต้องมั่นใจว่าใน FilterEmployees มี method close()
                this.$refs.filterDropdown.close();
            }
        },
        onFilterToggle() {
            this.sortMenuOpen = false;
        },
        onSortChoose(option) {
            if (!option || !option.key) return;
            this.sortBy = (this.sortBy.key === option.key && this.sortBy.order === option.order)
                ? { key: "", order: "" }
                : { key: option.key, order: option.order };
            this.page = 1;
            this.sortMenuOpen = false;
        },
        onDocClick(e) {
            if (!this.sortMenuOpen) return;
            if (this.$refs.sortWrap && !this.$refs.sortWrap.contains(e.target)) {
                this.sortMenuOpen = false;
            }
        },
        onSearchText() {
            this.search = this.searchInput.trim();
            this.page = 1;
            if (this.$refs.filterDropdown) {
                this.$refs.filterDropdown.close();
            }
        },
        editEmployee(id) { this.$router.push(`/edit-employee/${id}`); },
        openDelete(id) {
            this.deleteId = id;
            this.showModalAsk = true;
        },
        async onConfirmDelete() {
            try {
                const user = JSON.parse(localStorage.getItem("userData") || "{}");
                await axios.put(`/employees/soft-delete/${this.deleteId}`, {
                    emp_delete_by: user.emp_id,
                    emp_delete_status: "deleted",
                });
                this.showModalAsk = false;
                this.showModalSuccess = true;
                this.employees = this.employees.filter((e) => e.emp_id !== this.deleteId);
            } catch (err) {
                this.showModalAsk = false;
                this.showModalFail = true;
            }
        },
        onCancelDelete() { this.showModalAsk = false; },
        onConfirmSuccess() { this.showModalSuccess = false; },
        onConfirmFail() { this.showModalFail = false; },
    },
};
</script>
