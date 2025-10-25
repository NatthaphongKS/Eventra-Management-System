<!-- CategoryPage.vue -->
<template>
  <section class="p-0">
    <!-- Toolbar -->
    <div class="flex items-center gap-3 mb-4 overflow-visible">
      <SearchBar v-model="searchInput" placeholder="Search Category / Created by" @search="onSearch" />
      <SortMenu :is-open="sortMenuOpen" :options="sortOptions" />
      <button
        class="ml-auto inline-flex items-center h-10 px-4 rounded-full bg-red-700 text-white hover:bg-rose-700 whitespace-nowrap z-0"
        @click="openAdd">
        + Add New
      </button>
    </div>

    <!-- DataTable -->
    <DataTable
      :rows="paged"
      :columns="CategoryTableColumns"
      :page="page"
      :pageSize="pageSize"
      :total-items="sorted.length"
      :page-size-options="[10, 20, 50, 100]"
      @update:page="page = $event"
      @update:pageSize="onChangePageSize"
      class="mt-4"
    >
      <!-- ช่องปุ่ม action -->
      <template #actions="{ row }">
        <button
            class="grid h-8 w-8 place-items-center rounded-full text-neutal-800 hover:text-emerald-600"
            @click="openEdit(row)"
            title="Edit"
            aria-label="edit"
        >
            <Icon icon="material-symbols:edit-rounded" width="20" height="20" />
        </button>

        <button
            class="grid h-8 w-8 place-items-center rounded-full text-neutal-00 hover:text-red-600"
            @click="requestDelete(row)"
            title="Delete"
            aria-label="delete"
        >
            <Icon icon="fluent:delete-12-filled" width="20" height="20" />
        </button>
    </template>

      <!-- ✅ ถ้าไม่มีข้อมูล -->
      <template #empty>
        {{ sorted.length === 0 ? 'ไม่พบหมวดหมู่' : 'ไม่มีข้อมูลในหน้านี้' }}
      </template>
    </DataTable>

    <!-- Modal ต่างๆ -->
    <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
      :show-cancel="alert.showCancel" :ok-text="alert.okText" :cancel-text="alert.cancelText"
      @confirm="alert.onConfirm && alert.onConfirm()" @cancel="alert.onCancel && alert.onCancel()" />

    <CategoryCreate v-model:open="addOpen" :userName="userName" :duplicate="isDuplicate" @submit="createCategory" />
    <CategoryEdit v-model:open="editOpen" :category="editing" :is-duplicate="isDupForEdit" :formatDate="formatDate"
      @submit="updateCategory" />
  </section>
</template>

<script>
import axios from '@/plugin/axios'
import DataTable from '@/components/DataTable.vue'
import SearchBar from "@/components/SearchBar.vue"
import CategorySort from "@/components/Category/CategorySort.vue"
import CategoryCreate from "@/components/Category/CategoryCreate.vue"
import CategoryEdit from "@/components/Category/CategoryEdit.vue"
import ModalAlert from "@/components/Alert/ModalAlert.vue"
import SortMenu from '../../components/SortMenu.vue'
import { Icon } from '@iconify/vue'


export default {
  name: "CategoryPage",
  components: { DataTable, SearchBar,SortMenu, CategorySort, CategoryCreate, CategoryEdit, ModalAlert , Icon },
  data() {
    return {
      rows: [],
      searchInput: "",
      search: "",
      page: 1,
      pageSize: 10,
      sortDir: "desc",
      sortMenuOpen: false,
      sortOptions: [
      { key: 'cat_created_at', order: 'asc', label: 'Created date (Oldest)' },
      { key: 'cat_created_at', order: 'desc', label: 'Created date (Newest)' }
    ],
    
      alert: { open: false, type: '', title: '', message: '', showCancel: false },
      addOpen: false,
      editOpen: false,
      editing: null,
      userName: "Admin",

      CategoryTableColumns: [
        { key: 'cat_name', label: 'Category', class: 'text-left w-[867px] h-[60px]', sortable: true },
        { key: 'created_by_name', label: 'Created by', class: 'text-center w-[134px]' },
        { key: 'cat_created_at', label: 'Created date (D/M/Y)', class: 'text-center w-[202px]', format: v => new Date(v).toLocaleDateString() }
      ],
    }
  },
  computed: {
    filtered() {
      const q = this.search.trim().toLowerCase();
      if (!q) return this.rows;
      return this.rows.filter(
        r =>
          (r.cat_name && r.cat_name.toLowerCase().includes(q)) ||
          (r.created_by_name && r.created_by_name.toLowerCase().includes(q))
      );
    },
    sorted() {
      const dir = this.sortDir === "asc" ? 1 : -1;
      return this.filtered.slice().sort((a, b) => {
        const ta = new Date(a.cat_created_at).getTime() || 0;
        const tb = new Date(b.cat_created_at).getTime() || 0;
        return (ta - tb) * dir;
      });
    },
    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    },
  },
  async created() {
    await this.loadCategories();
  },
  methods: {
    async loadCategories() {
      try {
        const res = await axios.get("/categories");
        const data = Array.isArray(res.data)
          ? res.data
          : Array.isArray(res.data?.data)
            ? res.data.data
            : [];
        this.rows = data.map(c => ({
          id: c.id,
          cat_name: c.cat_name ?? "-",
          created_by_name: c.created_by_name ?? "-",
          cat_created_at: c.cat_created_at ?? null,
        }));
      } catch (e) {
        console.error(e);
        this.rows = [];
      }
    },
    onSearch(value) {
      this.search = value.trim();
      this.page = 1;
    },
    openAdd() { this.addOpen = true; },
    openEdit(row) { this.editing = { ...row }; this.editOpen = true; },
    requestDelete(row) {
      this.alert = {
        open: true,
        type: 'confirm',
        title: 'Confirm Delete',
        message: `Delete "${row.cat_name}"?`,
        showCancel: true,
        onConfirm: () => this.confirmDelete(row.id),
        onCancel: () => (this.alert.open = false),
      }
    },
    async confirmDelete(id) {
      try {
        await axios.delete(`/categories/${id}`);
        this.rows = this.rows.filter(r => r.id !== id);
        this.alert = { open: true, type: 'success', title: 'Deleted', message: 'Category deleted successfully.' };
      } catch (err) {
        console.error(err);
        this.alert = { open: true, type: 'error', title: 'Failed', message: 'Cannot delete this category.' };
      }
    },
    onChangePageSize(size) {
      this.pageSize = Number(size);
      this.page = 1;
    },
  },
};
</script>
