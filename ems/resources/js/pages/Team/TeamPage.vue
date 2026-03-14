<template>
    <section class="p-0">
        <div class="flex items-center gap-3">
            <SearchBar v-model="searchInput" placeholder="Search Team Name" @search="onSearch" />

            <div class="mt-6" ref="filterWrap">
                <TeamFilter v-model="filters" :departments="departments" :is-open="filterMenuOpen"
                    @update:modelValue="applyFilter" @toggle="toggleFilterMenu" />
            </div>

            <div class="mt-6" ref="sortWrap">
                <SortMenu :is-open="sortMenuOpen" :options="sortOptions" :sort-by="sortBy.key"
                    :sort-order="sortBy.order" @toggle="toggleSortMenu" @choose="onSortChoose" />
            </div>

            <div class="mt-6">
                <AddButton @click="openAdd" />
            </div>
        </div>

        <DataTable :rows="paged" :columns="teamTableColumns" :page="page" :pageSize="pageSize"
            :total-items="sorted.length" :page-size-options="[10, 20, 50, 100]" @update:page="page = $event"
            @update:pageSize="onChangePageSize" class="mt-4">
            <template #actions="{ row }">
                <button class="grid h-8 w-8 place-items-center rounded-full text-neutral-700 hover:text-emerald-600"
                    @click="openEdit(row)" title="Edit" aria-label="edit">
                    <Icon icon="material-symbols:edit-rounded" width="20" height="20" />
                </button>

                <button class="grid h-8 w-8 place-items-center rounded-full text-neutral-700 hover:text-red-600"
                    @click="requestDelete(row)" title="Delete" aria-label="delete">
                    <Icon icon="fluent:delete-12-filled" width="20" height="20" />
                </button>
            </template>

            <template #empty>
                {{ sorted.length === 0 ? "ไม่พบทีม" : "ไม่มีข้อมูลในหน้านี้" }}
            </template>
        </DataTable>

        <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
            :show-cancel="alert.showCancel" :ok-text="alert.okText" :cancel-text="alert.cancelText"
            @confirm="alert.onConfirm && alert.onConfirm()" @cancel="alert.onCancel && alert.onCancel()" />

        <TeamCreate v-model:open="addOpen" :duplicate="isDuplicate" :departments="departments" @submit="createTeam" />
        <TeamEdit v-model:open="editOpen" :team="editing" :is-duplicate="isDupForEdit" :departments="departments"
            @submit="updateTeam" />
    </section>
</template>

<script>
import axios from "@/plugin/axios";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import TeamCreate from "@/components/Team/TeamCreate.vue";
import TeamEdit from "@/components/Team/TeamEdit.vue";
import TeamFilter from "@/components/Team/TeamFilter.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import SortMenu from "@/components/SortMenu.vue";
import AddButton from "@/components/AddButton.vue";
import { Icon } from "@iconify/vue";

export default {
    name: "TeamPage",
    components: {
        DataTable, SearchBar, SortMenu, TeamCreate,
        TeamEdit, ModalAlert, AddButton, TeamFilter, Icon,
    },

    data() {
        return {
            filterMenuOpen: false,
            sortMenuOpen: false,
            rows: [],
            departments: [],
            searchInput: "",
            search: "",
            page: 1,
            pageSize: 10,
            sortBy: { key: "tm_name", order: "asc" },
            sortOptions: [
                { key: "tm_name", order: "asc", label: "ชื่อ A-Z", value: "az" },
                { key: "tm_name", order: "desc", label: "ชื่อ Z-A", value: "za" },
                { key: "id", order: "desc", label: "ล่าสุด", value: "newest" },
                { key: "id", order: "asc", label: "เก่าสุด", value: "oldest" },
            ],
            filters: { department: [] },
            alert: {
                open: false, type: "", title: "", message: "",
                showCancel: false, okText: "OK", cancelText: "Cancel",
            },
            addOpen: false,
            editOpen: false,
            editing: null,
            teamTableColumns: [
                { key: "tm_name", label: "Team Name", class: "text-left w-[300px]", sortable: true },
                { key: "department_name", label: "Department", class: "text-left w-[300px]" },
            ],
        };
    },

    computed: {
        filtered() {
            const q = this.search.trim().toLowerCase();
            return this.rows.filter((r) => {
                const matchesSearch = !q || (r.tm_name && r.tm_name.toLowerCase().includes(q));
                const matchesDept = this.filters.department.length === 0 ||
                    this.filters.department.includes(String(r.tm_department_id));
                return matchesSearch && matchesDept;
            });
        },
        sorted() {
            const { key, order } = this.sortBy;
            const dir = order === "asc" ? 1 : -1;
            return this.filtered.slice().sort((a, b) => {
                if (key === "tm_name") {
                    return (a.tm_name || "").toLowerCase().localeCompare((b.tm_name || "").toLowerCase()) * dir;
                }
                if (key === "id") return (a.id - b.id) * dir;
                return 0;
            });
        },
        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },
    },

    async created() {
        await Promise.all([this.loadTeams(), this.loadDepartments()]);
    },

    mounted() {
        document.addEventListener("click", this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocClick);
    },

    methods: {
        // ฟังก์ชันเปิด-ปิด Filter
        toggleFilterMenu() {
            this.filterMenuOpen = !this.filterMenuOpen;
            if (this.filterMenuOpen) {
                this.sortMenuOpen = false; // บังคับปิด Sort ทันทีที่เปิด Filter
            }
        },

        // ฟังก์ชันเปิด-ปิด Sort
        toggleSortMenu() {
            this.sortMenuOpen = !this.sortMenuOpen;
            if (this.sortMenuOpen) {
                this.filterMenuOpen = false; // บังคับปิด Filter ทันทีที่เปิด Sort
            }
        },

        // แก้ไข Logic การคลิกข้างนอกให้ครอบคลุมทั้งคู่
        onDocClick(e) {
            const sortWrap = this.$refs.sortWrap;
            const filterWrap = this.$refs.filterWrap;

            // ถ้าคลิกนอกพื้นที่ Sort ให้ปิด Sort
            if (this.sortMenuOpen && sortWrap && !sortWrap.contains(e.target)) {
                this.sortMenuOpen = false;
            }

            // ถ้าคลิกนอกพื้นที่ Filter ให้ปิด Filter
            if (this.filterMenuOpen && filterWrap && !filterWrap.contains(e.target)) {
                this.filterMenuOpen = false;
            }
        },

        // เมื่อเลือก Sort เสร็จ ให้ปิดเมนูด้วย
        onSortChoose(option) {
            if (!option || !option.key || !option.order) return;
            this.sortBy = { key: option.key, order: option.order };
            this.page = 1;
            this.sortMenuOpen = false; // ปิดเมนู Sort หลังเลือกเสร็จ
        },

        applyFilter(filters) {
            this.filters = filters;
            this.page = 1;
            // หากต้องการให้เลือก Filter เสร็จแล้วปิดเลย ให้ปลดคอมเมนต์บรรทัดล่าง:
            // this.filterMenuOpen = false;
        },

        // --- Data Loading ---
        async loadTeams() {
            try {
                const res = await axios.get("/teams");
                this.rows = Array.isArray(res.data) ? res.data : res.data?.data || [];
            } catch (e) {
                this.alert = { open: true, type: "error", title: "Failed", message: "Load teams failed." };
            }
        },
        async loadDepartments() {
            try {
                const res = await axios.get("/departments-all");
                this.departments = Array.isArray(res.data) ? res.data : res.data?.data || [];
            } catch (e) { console.error(e); }
        },
        onSearch(value) {
            this.search = value.trim();
            this.page = 1;
        },
        onChangePageSize(size) {
            this.pageSize = Number(size);
            this.page = 1;
        },

        // --- CRUD Operations ---
        openAdd() { this.addOpen = true; },
        isDuplicate(name) {
            const n = (name || "").trim().toLowerCase();
            return n ? this.rows.some(r => (r.tm_name || "").toLowerCase() === n) : false;
        },
        async createTeam({ name, departmentId, status }) {
            try {
                const n = (name || "").trim();
                if (!n) throw new Error("Name is empty.");
                await axios.post("/teams", {
                    tm_name: n,
                    tm_department_id: departmentId,
                    tm_delete_status: status || "active",
                });
                await this.loadTeams();
                this.addOpen = false;
                this.alert = { open: true, type: "success", title: "Created", message: "Team created successfully." };
            } catch (err) {
                this.alert = { open: true, type: "error", title: "Failed", message: err?.response?.data?.message || "Cannot create team." };
            }
        },
        openEdit(row) {
            this.editing = { id: row.id, tm_name: row.tm_name, tm_department_id: row.tm_department_id, tm_delete_status: row.tm_delete_status };
            this.editOpen = true;
        },
        isDupForEdit(name, currentId) {
            const n = (name || "").trim().toLowerCase();
            return n ? this.rows.some(r => r.id !== currentId && (r.tm_name || "").toLowerCase() === n) : false;
        },
        async updateTeam({ id, name, departmentId, status }) {
            try {
                await axios.put(`/teams/${id}`, { tm_name: name.trim(), tm_department_id: departmentId });
                await this.loadTeams();
                this.editOpen = false;
                this.alert = { open: true, type: "success", title: "Updated", message: "Team updated successfully." };
            } catch (err) {
                this.alert = { open: true, type: "error", title: "Failed", message: err?.response?.data?.message || "Cannot update team." };
            }
        },
        requestDelete(row) {
            this.alert = {
                open: true, type: "confirm", title: "Confirm Delete", message: `Delete "${row.tm_name}"?`,
                showCancel: true, okText: "Delete", cancelText: "Cancel",
                onConfirm: () => this.confirmDelete(row.id),
                onCancel: () => (this.alert.open = false),
            };
        },
        async confirmDelete(id) {
            try {
                await axios.delete(`/teams/${id}`);
                this.rows = this.rows.filter((r) => r.id !== id);
                this.alert = { open: true, type: "success", title: "Deleted", message: "Team deleted successfully." };
            } catch (err) {
                this.alert = { open: true, type: "error", title: "Failed", message: "Cannot delete team." };
            }
        },
    },
};
</script>
