<!-- HistoryCategory.vue -->
<template>
    <section class="p-0">
        <!-- Toolbar -->
        <div class="flex items-center gap-3">
            <SearchBar
                v-model="searchInput"
                placeholder="Search Category / Created by / Deleted by"
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
                        ? "ไม่พบข้อมูลประวัติหมวดหมู่"
                        : "ไม่มีข้อมูลในหน้านี้"
                }}
            </template>
        </DataTable>
    </section>
</template>

<script>
import axios from "@/plugin/axios";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import SortMenu from "@/components/SortMenu.vue";

export default {
    name: "HistoryCategory",
    components: { DataTable, SearchBar, SortMenu },

    data() {
        return {
            rows: [],
            searchInput: "",
            search: "",
            page: 1,
            pageSize: 10,

            sortMenuOpen: false,
            sortBy: { key: "cat_deleted_at", order: "desc" }, // เริ่มจากลบล่าสุด
            sortOptions: [
                {
                    key: "cat_name",
                    order: "asc",
                    label: "ชื่อ A-Z",
                    value: "az",
                },
                {
                    key: "cat_name",
                    order: "desc",
                    label: "ชื่อ Z-A",
                    value: "za",
                },
                {
                    key: "cat_created_at",
                    order: "desc",
                    label: "วันที่สร้างล่าสุด",
                    value: "created_newest",
                },
                {
                    key: "cat_created_at",
                    order: "asc",
                    label: "วันที่สร้างเก่าสุด",
                    value: "created_oldest",
                },
                {
                    key: "cat_deleted_at",
                    order: "desc",
                    label: "วันที่ลบล่าสุด",
                    value: "deleted_newest",
                },
                {
                    key: "cat_deleted_at",
                    order: "asc",
                    label: "วันที่ลบเก่าสุด",
                    value: "deleted_oldest",
                },
            ],

            columns: [
                {
                    key: "cat_name_display",
                    label: "Category",
                    class: "text-left w-[520px] h-[60px] whitespace-pre-wrap break-all",
                },
                {
                    key: "created_by_name",
                    label: "Created by",
                    class: "text-center w-[160px]",
                },
                {
                    key: "cat_created_at",
                    label: "Created date (D/M/Y)",
                    class: "text-center w-[170px]",
                    format: (v) => (v ? new Date(v).toLocaleDateString() : "-"),
                },
                {
                    key: "deleted_by_name",
                    label: "Deleted by",
                    class: "text-center w-[160px]",
                },
                {
                    key: "cat_deleted_at",
                    label: "Deleted date (D/M/Y)",
                    class: "text-center w-[170px]",
                    format: (v) => (v ? new Date(v).toLocaleDateString() : "-"),
                },
            ],
        };
    },

    computed: {
        filtered() {
            const q = (this.search || "").trim().toLowerCase();
            if (!q) return this.rows;
            return this.rows.filter(
                (r) =>
                    (r.cat_name || "").toLowerCase().includes(q) ||
                    (r.created_by_name || "").toLowerCase().includes(q) ||
                    (r.deleted_by_name || "").toLowerCase().includes(q)
            );
        },

        sorted() {
            const { key, order } = this.sortBy;
            const dir = order === "asc" ? 1 : -1;

            return this.filtered.slice().sort((a, b) => {
                if (key === "cat_name") {
                    return (
                        (a.cat_name || "")
                            .toLowerCase()
                            .localeCompare((b.cat_name || "").toLowerCase()) *
                        dir
                    );
                }
                if (key === "cat_created_at" || key === "cat_deleted_at") {
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
        await this.loadHistoryCategories();
    },

    mounted() {
        document.addEventListener("click", this.onDocClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocClick);
    },

    methods: {
        wrapEveryChars(text, size = 65) {
            const s = String(text ?? "");
            const n = Number(size) || 65;
            if (!s) return s;

            let out = "";
            for (let i = 0; i < s.length; i += n) {
                out += (i === 0 ? "" : "\n") + s.slice(i, i + n);
            }
            return out;
        },

        async loadHistoryCategories() {
            try {
                // ดึงจาก backend
                const res = await axios.get("/history/categories");
                const data = Array.isArray(res.data)
                    ? res.data
                    : Array.isArray(res.data?.data)
                    ? res.data.data
                    : [];

                this.rows = data.map((x) => ({
                    id: x.id,
                    cat_name: x.cat_name ?? "-",
                    created_by_name: x.created_by_name ?? "-",
                    cat_created_at: x.cat_created_at ?? null,
                    deleted_by_name: x.deleted_by_name ?? "-",
                    cat_deleted_at: x.cat_deleted_at ?? null,
                    cat_name_display: this.wrapEveryChars(
                        x.cat_name ?? "-", 65
                    ),
                }));
            } catch (e) {
                console.error(e);
                this.rows = [];
            }
        },

        onSearch(value) {
            this.search = (value || "").trim();
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
