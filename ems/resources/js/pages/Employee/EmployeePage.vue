<template>
    <section class="p-0">
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 w-full gap-3"
        >
            <div class="flex-1">
                <SearchBar
                    v-model="searchInput"
                    placeholder="Search ID, Name, Nickname..."
                    @search="onSearchText"
                    class=""
                />
            </div>

            <div class="flex items-stretch gap-2 flex-shrink-0 mt-[30px]">
                <FilterEmployees
                    ref="filterDropdown"
                    v-model="filters"
                    :options="optionsMap"
                    class="[&_button]:h-full"
                />


                    <SortMenu
                        :is-open="sortMenuOpen"
                        :options="sortOptions"
                        :sort-by="sortBy.key"
                        :sort-order="sortBy.order"
                        @toggle="sortMenuOpen = !sortMenuOpen"
                        @choose="onSortChoose"
                        class="[&_button]:h-full"
                    />
                

                <AddButton
                    @click="goAdd"
                    class="h-full w-[44px] flex items-center justify-center"
                />
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
                <button
                    class="grid h-8 w-8 place-items-center rounded-full text-neutral-800 hover:text-emerald-600"
                    @click="editEmployee(row.emp_id)"
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
                    @click="openDelete(row.emp_id)"
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

        <ModalAlert
            :open="showModalAsk"
            type="confirm"
            title="ARE YOU SURE TO DELETE"
            message="This employee will be deleted permanently. Are you sure?"
            :show-cancel="true"
            okText="OK"
            cancelText="Cancel"
            @confirm="onConfirmDelete"
            @cancel="onCancelDelete"
        />

        <ModalAlert
            :open="showModalSuccess"
            type="success"
            title="DELETE SUCCESS!"
            message="Employee has been deleted successfully."
            :show-cancel="false"
            okText="OK"
            @confirm="onConfirmSuccess"
        />

        <ModalAlert
            :open="showModalFail"
            type="error"
            title="ERROR!"
            message="Sorry, Please try again later."
            :show-cancel="false"
            okText="OK"
            @confirm="onConfirmFail"
        />
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
            employees: [],

            // Delete Logic
            deleteId: null,
            showModalAsk: false,
            showModalSuccess: false,
            showModalFail: false,

            // Search & Filter
            searchInput: "",
            search: "",
            filters: { department: "all", team: "all", position: "all" },

            page: 1,
            pageSize: 10,
            sortMenuOpen: false,
            sortBy: { key: "", order: "" },

            sortOptions: [
                {
                    key: "emp_firstname",
                    order: "asc",
                    label: "ชื่อพนักงาน A–Z",
                },
                {
                    key: "emp_firstname",
                    order: "desc",
                    label: "ชื่อพนักงาน Z–A",
                },
                { key: "department_name", order: "asc", label: "แผนก A–Z" },
                { key: "department_name", order: "desc", label: "แผนก Z–A" },
                { key: "team_name", order: "asc", label: "ทีม A–Z" },
                { key: "team_name", order: "desc", label: "ทีม Z–A" },
                { key: "position_name", order: "asc", label: "ตำแหน่ง A–Z" },
                { key: "position_name", order: "desc", label: "ตำแหน่ง Z–A" },
                {
                    key: "created_at",
                    order: "desc",
                    label: "วันที่เพิ่มใหม่สุด",
                },
                {
                    key: "created_at",
                    order: "asc",
                    label: "วันที่เพิ่มเก่าสุด",
                },
            ],
            EmployeeTableColumns: [
                { key: "emp_id", label: "ID", class: "text-left w-[100px]" },
                {
                    key: "emp_fullname",
                    label: "Name",
                    class: "text-left w-[180px]",
                },
                {
                    key: "emp_nickname",
                    label: "Nickname",
                    class: "text-left w-[120px]",
                },
                {
                    key: "emp_phone",
                    label: "Phone",
                    class: "text-left w-[140px]",
                },
                {
                    key: "department_name",
                    label: "Department",
                    class: "text-left w-[140px]",
                },
                {
                    key: "team_name",
                    label: "Team",
                    class: "text-left w-[140px]",
                },
                {
                    key: "position_name",
                    label: "Position",
                    class: "text-left w-[140px]",
                },
                {
                    key: "created_at",
                    label: "Created date (D/M/Y)",
                    class: "text-center w-[160px]",
                    format: (v) =>
                        v ? new Date(v).toLocaleDateString("en-GB") : "-",
                },
            ],
        };
    },
    watch: {
        filters: {
            handler() {
                this.page = 1;
            },
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
        optionsMap() {
            return {
                department: [
                    ...new Set(
                        this.employees
                            .map((e) => e.department_name)
                            .filter(Boolean)
                    ),
                ],
                team: [
                    ...new Set(
                        this.employees.map((e) => e.team_name).filter(Boolean)
                    ),
                ],
                position: [
                    ...new Set(
                        this.employees
                            .map((e) => e.position_name)
                            .filter(Boolean)
                    ),
                ],
            };
        },
        filtered() {
            let result = this.employees;

            // Text Search
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) => {
                    const fullName = `${e.emp_firstname ?? ""} ${
                        e.emp_lastname ?? ""
                    }`.toLowerCase();
                    const nickname = (e.emp_nickname ?? "").toLowerCase();
                    const empId = (e.emp_id ?? "").toString().toLowerCase();
                    return (
                        empId.includes(q) ||
                        fullName.includes(q) ||
                        nickname.includes(q)
                    );
                });
            }

            // Dropdown Filters
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
            const { key, order } = this.sortBy;
            if (!key) {
                return this.filtered;
            }

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
            return this.sorted.slice(
                (this.page - 1) * this.pageSize,
                this.page * this.pageSize
            );
        },
    },
    methods: {
        async fetchEmployees() {
            try {
                // อย่าลืมแก้ API ฝั่งหลังบ้านให้ Select มาเฉพาะ status = 'active'
                const res = await axios.get("/get-employees");
                const data = Array.isArray(res.data)
                    ? res.data
                    : res.data?.data || [];
                this.employees = data.map((e) => ({
                    ...e,
                    emp_fullname: `${e.emp_firstname ?? ""} ${
                        e.emp_lastname ?? ""
                    }`.trim(),
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
            if (
                this.sortBy.key === option.key &&
                this.sortBy.order === option.order
            ) {
                this.sortBy = { key: "", order: "" };
            } else {
                this.sortBy = { key: option.key, order: option.order };
            }
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
        onSearchText() {
            this.search = this.searchInput.trim();
            this.page = 1;
            if (this.$refs.filterDropdown) {
                this.$refs.filterDropdown.close();
            }
        },
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        openDelete(id) {
            this.deleteId = id;
            this.showModalAsk = true;
        },

        // ✅ [UPDATED] ฟังก์ชันลบแบบ Soft Delete
        async onConfirmDelete() {
            const id = this.deleteId;
            if (!id) return;

            // 1. ดึง ID ของคนที่ Login อยู่ (Admin)
            // ⚠️ เช็ค: คุณเก็บใน localStorage ชื่อ 'userData' ใช่ไหม?
            const userStorage = localStorage.getItem("userData");
            const currentUser = userStorage ? JSON.parse(userStorage) : {};
            const myId = currentUser.emp_id; // ID คนลบ

            try {
                // 2. ใช้ PUT แทน DELETE และส่งข้อมูลคนลบไปด้วย
                await axios.put(`/employees/soft-delete/${id}`, {
                    emp_delete_by: myId,
                    emp_delete_status: "deleted",
                });

                this.showModalAsk = false;
                this.showModalSuccess = true;

                // 3. ลบข้อมูลจากหน้าจอโดยใช้ emp_id (ไม่ต้องโหลดใหม่)
                this.employees = this.employees.filter((e) => e.emp_id !== id);
                this.deleteId = null;
            } catch (err) {
                console.error("Delete failed:", err);
                this.showModalAsk = false;
                this.showModalFail = true;
            }
        },

        onCancelDelete() {
            this.showModalAsk = false;
            this.deleteId = null;
        },
        onConfirmSuccess() {
            this.showModalSuccess = false;
        },
        onConfirmFail() {
            this.showModalFail = false;
        },
    },
};
</script>
