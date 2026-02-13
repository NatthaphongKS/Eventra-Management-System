<!-- HistoryEmployee.vue -->
<template>
    <section class="p-0">
        <!-- Toolbar -->
        <div class="flex items-center gap-3">
            <SearchBar
                v-model="searchInput"
                placeholder="Search Employee ID / Name / Nickname"
                @search="onSearch"
            />

            <div class="relative z-[60] mt-8" ref="sortWrap">
                <SortMenu
                    :is-open="sortMenuOpen"
                    :options="sortOptions"
                    :sort-by="sortBy.key"
                    :sort-order="sortBy.order"
                    @toggle="sortMenuOpen = !sortMenuOpen"
                    @choose="onSortChoose"
                />
            </div>
        </div>

        <!-- DataTable -->
        <DataTable
            :rows="paged"
            :columns="columns"
            :page="page"
            :pageSize="pageSize"
            :total-items="sorted.length"
            :page-size-options="[10, 20, 50, 100]"
            @update:page="page = $event"
            @update:pageSize="onChangePageSize"
            class="mt-4"
        >
            <template #empty>
                {{
                    sorted.length === 0
                        ? "ไม่พบข้อมูลพนักงาน"
                        : "ไม่มีข้อมูลในหน้านี้"
                }}
            </template>

            <!-- Custom cell for Name -->
            <template #cell-emp_name="{ row }">
                {{ getFullName(row) }}
            </template>
        </DataTable>
    </section>
</template>

<script>
import axios from '@/plugin/axios';
import DataTable from '@/components/DataTable.vue';
import SearchBar from '@/components/SearchBar.vue';
import SortMenu from '@/components/SortMenu.vue';

export default {
    name: 'HistoryEmployee',
    components: { DataTable, SearchBar, SortMenu },

    data() {
        return {
            rows: [],
            searchInput: '',
            search: '',
            page: 1,
            pageSize: 10,

            sortMenuOpen: false,
            sortBy: { key: 'emp_delete_at', order: 'desc' },
            sortOptions: [
                {
                    key: 'emp_firstname',
                    order: 'asc',
                    label: 'ชื่อ A-Z',
                    value: 'az',
                },
                {
                    key: 'emp_firstname',
                    order: 'desc',
                    label: 'ชื่อ Z-A',
                    value: 'za',
                },
                {
                    key: 'created_at',
                    order: 'desc',
                    label: 'วันที่สร้างล่าสุด',
                    value: 'created_newest',
                },
                {
                    key: 'created_at',
                    order: 'asc',
                    label: 'วันที่สร้างเก่าสุด',
                    value: 'created_oldest',
                },
                {
                    key: 'emp_delete_at',
                    order: 'desc',
                    label: 'วันที่ลบล่าสุด',
                    value: 'deleted_newest',
                },
                {
                    key: 'emp_delete_at',
                    order: 'asc',
                    label: 'วันที่ลบเก่าสุด',
                    value: 'deleted_oldest',
                },
            ],

            columns: [
                {
                    key: 'emp_id',
                    label: 'ID',
                    class: 'text-left w-[100px]',
                },
                {
                    key: 'emp_name',
                    label: 'Name',
                    class: 'text-left w-[200px]',
                },
                {
                    key: 'emp_nickname',
                    label: 'Nickname',
                    class: 'text-center w-[120px]',
                },
                {
                    key: 'department_name',
                    label: 'Department',
                    class: 'text-center w-[120px]',
                },
                {
                    key: 'team_name',
                    label: 'Team',
                    class: 'text-center w-[120px]',
                },
                {
                    key: 'position_name',
                    label: 'Position',
                    class: 'text-center w-[150px]',
                },
                {
                    key: 'created_by_name',
                    label: 'Created By',
                    class: 'text-center w-[120px]',
                },
                {
                    key: 'created_at',
                    label: 'Created Date',
                    class: 'text-center w-[130px]',
                    format: (v) => v ? new Date(v).toLocaleDateString('th-TH') : '-',
                },
                {
                    key: 'deleted_by_name',
                    label: 'Deleted By',
                    class: 'text-center w-[120px]',
                },
                {
                    key: 'emp_delete_at',
                    label: 'Deleted Date',
                    class: 'text-center w-[130px]',
                    format: (v) => v ? new Date(v).toLocaleDateString('th-TH') : '-',
                },
            ],
        };
    },

    computed: {
        filtered() {
  const q = (this.search || '').trim().toLowerCase();
  if (!q) return this.rows;

  return this.rows.filter((r) => {
    const id = (r.emp_id || '').toLowerCase();
    const firstname = (r.emp_firstname || '').toLowerCase();
    const lastname = (r.emp_lastname || '').toLowerCase();
    const nickname = (r.emp_nickname || '').toLowerCase();

    const fullName = `${firstname} ${lastname}`.trim();

    return (
      id.includes(q) ||
      firstname.includes(q) ||
      lastname.includes(q) ||
      fullName.includes(q) ||
      nickname.includes(q)
    );
  });
},

        sorted() {
            const { key, order } = this.sortBy;
            const dir = order === 'asc' ? 1 : -1;

            return this.filtered.slice().sort((a, b) => {
                if (key === 'emp_firstname') {
                    return (
                        (a.emp_firstname || '')
                            .toLowerCase()
                            .localeCompare((b.emp_firstname || '').toLowerCase()) *
                        dir
                    );
                }
                if (key === 'created_at' || key === 'emp_delete_at') {
                    const ta = new Date(a[key]).getTime() || 0;
                    const tb = new Date(b[key]).getTime() || 0;
                    return (ta - tb) * dir;
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
        await this.loadHistoryEmployees();
    },

    mounted() {
        document.addEventListener('click', this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onDocClick);
    },

    methods: {
        async loadHistoryEmployees() {
            try {
                const res = await axios.get('/history/employees');
                const data = Array.isArray(res.data)
                    ? res.data
                    : Array.isArray(res.data?.data)
                    ? res.data.data
                    : [];

                this.rows = data.map((x) => ({
                    id: x.id,
                    emp_id: x.emp_id ?? '-',
                    emp_prefix: x.emp_prefix ?? '',
                    emp_firstname: x.emp_firstname ?? '',
                    emp_lastname: x.emp_lastname ?? '',
                    emp_nickname: x.emp_nickname ?? '-',
                    emp_delete_status: x.emp_delete_status ?? '-',
                    department_name: x.department_name ?? '-',
                    team_name: x.team_name ?? '-',
                    position_name: x.position_name ?? '-',
                    created_by_name: x.created_by_name ?? '-',
                    created_at: x.created_at ?? null,
                    deleted_by_name: x.deleted_by_name ?? '-',
                    emp_delete_at: x.emp_deleted_at ?? null,
                }));
            } catch (e) {
                console.error('Error loading history employees:', e);
                this.rows = [];
            }
        },

        getFullName(row) {
            const parts = [
                row.emp_prefix,
                row.emp_firstname,
                row.emp_lastname
            ].filter(Boolean);
            return parts.join(' ') || '-';
        },

        onSearch(value) {
            this.search = (value || '').trim();
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

        onDocClick(e) {
            if (!this.sortMenuOpen) return;
            const wrap = this.$refs.sortWrap;
            if (wrap && !wrap.contains(e.target)) this.sortMenuOpen = false;
        },
    },
};
</script>
