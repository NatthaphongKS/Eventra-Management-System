<!-- PositionPage.vue -->
<template>
    <section class="p-0">
        <!-- Toolbar -->
        <div class="flex items-center gap-3">
            <SearchBar v-model="searchInput" placeholder="Search Position Name" @search="onSearch" class="" />
            <PositionFilter v-model="filters" :departments="departments" :teams="teams" :is-open="filterOpen"
                @toggle="filterOpen = !filterOpen" @update:modelValue="applyFilter" class="mt-6" ref="filterWrap" />
            <div class="mt-6" ref="sortWrap">
                <SortMenu :is-open="sortMenuOpen" :options="sortOptions" :sort-by="sortBy.key"
                    :sort-order="sortBy.order" @toggle="sortMenuOpen = !sortMenuOpen" @choose="onSortChoose" />
            </div>

            <div class="mt-6">
                <AddButton @click="openAdd" />
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :rows="paged" :columns="positionTableColumns" :page="page" :pageSize="pageSize"
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
                {{
                    sorted.length === 0
                        ? "ไม่พบตำแหน่ง"
                        : "ไม่มีข้อมูลในหน้านี้"
                }}
            </template>
        </DataTable>

        <!-- Modals -->
        <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
            :show-cancel="alert.showCancel" :ok-text="alert.okText" :cancel-text="alert.cancelText"
            @confirm="alert.onConfirm && alert.onConfirm()" @cancel="alert.onCancel && alert.onCancel()" />

        <PositionCreate v-model:open="addOpen" :duplicate="isDuplicate" :departments="departments" :teams="teams"
            @submit="createPosition" />
        <PositionEdit v-model:open="editOpen" :position="editing" :is-duplicate="isDupForEdit"
            :departments="departments" :teams="teams" @submit="updatePosition" />
    </section>
</template>

<script>
import axios from "@/plugin/axios";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import PositionCreate from "@/components/Position/PositionCreate.vue";
import PositionEdit from "@/components/Position/PositionEdit.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import SortMenu from "@/components/SortMenu.vue";
import AddButton from "@/components/AddButton.vue";
import PositionFilter from "@/components/Position/PositionFilter.vue";
import { Icon } from "@iconify/vue";

export default {
    name: "PositionPage",
    components: {
        DataTable,
        SearchBar,
        SortMenu,
        PositionCreate,
        PositionEdit,
        ModalAlert,
        AddButton,
        PositionFilter,
        Icon,
    },

    data() {
        return {
            rows: [],
            departments: [],
            teams: [],
            searchInput: "",
            search: "",
            page: 1,
            pageSize: 10,

            sortMenuOpen: false,
            filterOpen: false,
            sortBy: { key: "pst_name", order: "asc" },
            sortOptions: [
                {
                    key: "pst_name",
                    order: "asc",
                    label: "ชื่อ A-Z",
                    value: "az",
                },
                {
                    key: "pst_name",
                    order: "desc",
                    label: "ชื่อ Z-A",
                    value: "za",
                },
                {
                    key: "id",
                    order: "desc",
                    label: "ล่าสุด",
                    value: "newest",
                },
                {
                    key: "id",
                    order: "asc",
                    label: "เก่าสุด",
                    value: "oldest",
                },
            ],

            filters: { department: [], team: [] },

            alert: {
                open: false,
                type: "",
                title: "",
                message: "",
                showCancel: false,
                okText: "OK",
                cancelText: "Cancel",
            },

            addOpen: false,
            editOpen: false,
            editing: null,

            positionTableColumns: [
                {
                    key: "pst_name",
                    label: "Position Name",
                    class: "text-left w-[250px]",
                    sortable: true,
                },
                {
                    key: "department_name",
                    label: "Department",
                    class: "text-left w-[250px]",
                },
                {
                    key: "team_name",
                    label: "Team",
                    class: "text-left w-[250px]",
                },

            ],
        };
    },

    computed: {
        filtered() {
            const q = this.search.trim().toLowerCase();

            return this.rows.filter((r) => {
                // Search filter
                const matchesSearch =
                    !q || (r.pst_name && r.pst_name.toLowerCase().includes(q));

                // Department filter
                const matchesDept = this.filters.department.length === 0 ||
                    this.filters.department.includes(String(r.tm_department_id));

                // Team filter
                const matchesTeam = this.filters.team.length === 0 ||
                    this.filters.team.includes(String(r.pst_team_id));

                return matchesSearch && matchesDept && matchesTeam;
            });
        },

        sorted() {
            const { key, order } = this.sortBy;
            const dir = order === "asc" ? 1 : -1;

            return this.filtered.slice().sort((a, b) => {
                if (key === "pst_name") {
                    const an = (a.pst_name || "").toLowerCase();
                    const bn = (b.pst_name || "").toLowerCase();
                    return an.localeCompare(bn) * dir;
                }
                if (key === "id") {
                    return (a.id - b.id) * dir;
                }
                return 0;
            });
        },

        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },
    },

    async created() {
        await Promise.all([
            this.loadPositions(),
            this.loadDepartments(),
            this.loadTeams(),
        ]);
    },

    mounted() {
        document.addEventListener("click", this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocClick);
    },

    methods: {
        async loadPositions() {
            try {
                const res = await axios.get("/positions");
                this.rows = Array.isArray(res.data)
                    ? res.data
                    : Array.isArray(res.data?.data)
                        ? res.data.data
                        : [];
            } catch (e) {
                console.error(e);
                this.rows = [];
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Failed",
                    message: "Load positions failed.",
                };
            }
        },

        async loadDepartments() {
            try {
                const res = await axios.get("/departments-all");
                this.departments = Array.isArray(res.data)
                    ? res.data
                    : Array.isArray(res.data?.data)
                        ? res.data.data
                        : [];
            } catch (e) {
                console.error(e);
                this.departments = [];
            }
        },

        async loadTeams() {
            try {
                const res = await axios.get("/teams-all");
                this.teams = Array.isArray(res.data)
                    ? res.data
                    : Array.isArray(res.data?.data)
                        ? res.data.data
                        : [];
            } catch (e) {
                console.error(e);
                this.teams = [];
            }
        },

        onSearch(value) {
            this.search = value.trim();
            this.page = 1;
        },

        onChangePageSize(size) {
            this.pageSize = Number(size);
            this.page = 1;
        },

        onSortChoose(option) {
            if (!option || !option.key || !option.order) return;
            this.sortBy = { key: option.key, order: option.order };
            this.page = 1;
            this.sortMenuOpen = false;
        },

        applyFilter(filters) {
            this.filters = filters;
            this.page = 1;
        },

        onDocClick(e) {
            // 1. จัดการปิด Sort Menu (ของเดิม)
            if (this.sortMenuOpen) {
                const sortWrap = this.$refs.sortWrap;
                if (sortWrap && !sortWrap.contains(e.target)) {
                    this.sortMenuOpen = false;
                }
            }

            // 2. จัดการปิด Position Filter (เพิ่มเข้าไป)
            if (this.filterOpen) {
                // สำหรับ Component ลูกที่เป็น Vue เราต้องเข้าถึง $el
                const filterWrap = this.$refs.filterWrap?.$el || this.$refs.filterWrap;
                if (filterWrap && !filterWrap.contains(e.target)) {
                    this.filterOpen = false;
                }
            }
        },

        openAdd() {
            this.addOpen = true;
        },

        isDuplicate(name, teamId) {
            const n = (name || "").trim().toLowerCase();
            if (!n) return false;
            return this.rows.some(
                (r) =>
                    (r.pst_name || "").toLowerCase() === n &&
                    r.pst_team_id === teamId,
            );
        },

        async createPosition({ name, teamId, status }) {
            try {
                const n = (name || "").trim();
                if (!n) throw new Error("Name is empty.");
                if (this.isDuplicate(n, teamId)) {
                    this.alert = {
                        open: true,
                        type: "error",
                        title: "Duplicate",
                        message: "มีชื่อนี้อยู่แล้วในทีมนี้",
                    };
                    return;
                }
                const res = await axios.post("/positions", {
                    pst_name: n,
                    pst_team_id: teamId,
                    pst_delete_status: status || "active",
                });
                const created = res?.data?.data || res?.data || {};
                const team = this.teams.find((t) => t.id === teamId);
                this.rows.unshift({
                    id: created.id,
                    pst_name: created.pst_name || n,
                    pst_team_id: created.pst_team_id || teamId,
                    team_name: team?.tm_name || "",
                    department_name: team?.department_name || "",
                    pst_delete_status: created.pst_delete_status || "active",
                });
                this.addOpen = false;
                this.alert = {
                    open: true,
                    type: "success",
                    title: "Created",
                    message: "Position created successfully.",
                };
            } catch (err) {
                console.error(err?.response || err);
                const msg =
                    err?.response?.data?.message || "Cannot create position.";
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Failed",
                    message: msg,
                };
            }
        },

        openEdit(row) {
            this.editing = {
                id: row.id,
                pst_name: row.pst_name,
                pst_team_id: row.pst_team_id,
                pst_delete_status: row.pst_delete_status,
            };
            this.editOpen = true;
        },

        isDupForEdit(name, teamId, currentId) {
            const n = (name || "").trim().toLowerCase();
            if (!n) return false;
            return this.rows.some(
                (r) =>
                    r.id !== currentId &&
                    (r.pst_name || "").toLowerCase() === n &&
                    r.pst_team_id === teamId,
            );
        },

        async updatePosition({ id, name, teamId, status }) {
            const n = (name || "").trim();
            if (!n) {
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Failed",
                    message: "Name is empty.",
                };
                return;
            }
            if (this.isDupForEdit(n, teamId, id)) {
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Duplicate",
                    message: "มีชื่อนี้อยู่แล้วในทีมนี้",
                };
                return;
            }
            try {
                await axios.put(`/positions/${id}`, {
                    pst_name: n,
                    pst_team_id: teamId,
                    pst_delete_status: status || "active",
                });
                const team = this.teams.find((t) => t.id === teamId);
                this.rows = this.rows.map((r) =>
                    r.id === id
                        ? {
                            ...r,
                            pst_name: n,
                            pst_team_id: teamId,
                            team_name: team?.tm_name || "",
                            department_name: team?.department_name || "",

                        }
                        : r,
                );
                this.editOpen = false;
                this.alert = {
                    open: true,
                    type: "success",
                    title: "Updated",
                    message: "Position updated successfully.",
                };
            } catch (err) {
                console.error(err?.response || err);
                const msg =
                    err?.response?.data?.message ||
                    "Cannot update this position.";
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Failed",
                    message: msg,
                };
            }
        },

        requestDelete(row) {
            this.alert = {
                open: true,
                type: "confirm",
                title: "Confirm Delete",
                message: `Delete "${row.pst_name}"?`,
                showCancel: true,
                okText: "Delete",
                cancelText: "Cancel",
                onConfirm: () => this.confirmDelete(row.id),
                onCancel: () => (this.alert.open = false),
            };
        },

        async confirmDelete(id) {
            try {
                await axios.delete(`/positions/${id}`);
                this.rows = this.rows.filter((r) => r.id !== id);
                this.alert = {
                    open: true,
                    type: "success",
                    title: "Deleted",
                    message: "Position deleted successfully.",
                };
            } catch (err) {
                console.error(err?.response || err);
                const msg =
                    err?.response?.data?.message ||
                    "Cannot delete this position.";
                this.alert = {
                    open: true,
                    type: "error",
                    title: "Failed",
                    message: msg,
                };
            }
        },
    },
};
</script>
