<template>
  <section class="p-0">
    <!-- Toolbar -->
    <div class="flex items-center gap-3 mb-4 overflow-visible">

      <!-- Search bar -->
      <SearchBar
      v-model="searchInput"
      placeholder="Search Category / Created by"
      @search="onSearch"
      />

      <!-- Sort -->
      <CategorySort v-model="sortDir" />

      <button
        class="ml-auto inline-flex items-center h-10 px-4 rounded-full bg-red-700 text-white hover:bg-rose-700 whitespace-nowrap z-0"
        @click="openAdd"
      >
        + Add New
      </button>
    </div>

    <!-- DataTable -->
    <CategoryDataTable
      :rows="sorted"
      :page="page"
      :pageSize="pageSize"
      :startIndex="startIndex"
      :formatDate="formatDate"
      @edit="openEdit"
      @delete="requestDelete"
      @update:page="page = $event"
      @update:pageSize="onChangePageSize"
    />

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4 mt-4">
      <div
        v-for="(row, i) in mobilePaged"
        :key="row.id ?? i"
        class="p-4 rounded-xl border border-gray-200 shadow-sm bg-white"
      >
        <div class="flex justify-between items-center mb-2">
          <div class="font-semibold text-gray-800">{{ row.name }}</div>
          <span class="text-xs text-gray-500">#{{ startIndex + i + 1 }}</span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
          <div><span class="font-medium">Created by:</span> {{ row.createdBy || "-" }}</div>
          <div><span class="font-medium">Date:</span> {{ formatDate(row.createdAt) }}</div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="p-1.5 rounded-lg hover:bg-rose-100" @click="openEdit(row)" >Edit</button>
          <button class="p-1.5 rounded-lg hover:bg-rose-100" @click="requestDelete(row)">Delete</button>
        </div>
      </div>

      <div v-if="mobilePaged.length === 0" class="p-4 text-center text-gray-500">
        {{ sorted.length === 0 ? "No categories found" : "No data for this page" }}
      </div>
    </div>

    <!-- ConfirmDelete Component -->
    <ConfirmDelete
      v-model:open="confirmOpen"
      @cancel="cancelDelete"
      @confirm="confirmDelete"
    />

    <!-- DeleteSuccess Component -->
    <DeleteSucces
      v-model:open="successOpen"
      @close="successOpen = false"  />

    <!-- Create / Edit modals -->
    <CategoryCreate v-model:open="addOpen" :userName="userName" :duplicate="isDuplicate" @submit="createCategory" />
    <CategoryEdit
      v-model:open="editOpen"
      :category="editing"
      :is-duplicate="isDupForEdit"
      :formatDate="formatDate"
      @submit="updateCategory"
    />
  </section>
</template>

<script>
import axios from '@/plugin/axios'
import CategorySort from "@/components/Category/CategorySort.vue";
import CategoryDataTable from "@/components/Category/CategoryDataTable.vue";
import CategoryCreate from "@/components/Category/CategoryCreate.vue";
import CategoryEdit from "@/components/Category/CategoryEdit.vue";
import ConfirmDelete from "@/components/Alert/ConfirmDelete.vue";
import DeleteSucces from "@/components/Alert/Employee/EmloyeeDeleteSuccess.vue";
import SearchBar from "../../components/SearchBar.vue";




export default {
  name: "CategoryPage",
  components: {
    SearchBar,
    CategorySort,
    CategoryDataTable,
    CategoryCreate,
    CategoryEdit,
    ConfirmDelete,
    DeleteSucces,
  },
  data() {
    return {
      rows: [],
      searchInput: "",
      search: "",
      page: 1,
      pageSize: 10,
      sortDir: "desc",
      addOpen: false,
      editOpen: false,
      editing: null,
      newName: "",
      userName: "Admin",
      confirmOpen: false,
      successOpen: false,
      deleting: null,
    };
  },
  computed: {
    filtered() {
    const q = this.search.trim().toLowerCase();
    if (!q) return this.rows.slice();
    return this.rows.filter(
      (r) =>
        (r.name && r.name.toLowerCase().includes(q)) ||
        (r.createdBy && r.createdBy.toLowerCase().includes(q))
    );
    },

    sorted() {
      const dir = this.sortDir === "asc" ? 1 : -1;
      return this.filtered.slice().sort((a, b) => {
        const ta = a.createdAt ? new Date(a.createdAt).getTime() : 0;
        const tb = b.createdAt ? new Date(b.createdAt).getTime() : 0;
        return (ta - tb) * dir;
      });
    },
    total() {
      return this.sorted.length;
    },
    startIndex() {
      return (this.page - 1) * this.pageSize;
    },
    mobilePaged() {
      return this.sorted.slice(this.startIndex, this.startIndex + this.pageSize);
    },
    isDuplicate() {
      return this.rows.some((r) => (r.name || "").trim().toLowerCase() === (this.newName || "").trim().toLowerCase());
    },
    isDupForEdit() {
      return (name, id) => this.rows.some((r) => r.id !== id && (r.name || "").trim().toLowerCase() === (name || "").trim().toLowerCase());
    },
  },
  async created() {
    await this.loadCategories();
  },
  methods: {
    formatDate(iso) {
      if (!iso) return "-";
      const d = new Date(iso);
      if (isNaN(d.getTime())) return "-";
      return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
    },
    async loadCategories() {
      try {
        const res = await axios.get("/categories");
        const data = Array.isArray(res.data)
          ? res.data
          : Array.isArray(res.data?.data)
          ? res.data.data
          : Array.isArray(res.data?.categories)
          ? res.data.categories
          : [];
        this.rows = data.map((c) => ({
          id: c.id,
          name: c.cat_name ?? c.name ?? "",
          createdBy: c.created_by_name ?? c.created_by ?? "-",
          createdAt: c.cat_created_at ?? c.created_at ?? null,
        }));
      } catch (e) {
        console.error(e);
        this.rows = [];
      }
    },onSearch(value) {
    this.search = value.trim();
    this.page = 1;
    },
    openAdd() {
      this.newName = "";
      this.addOpen = true;
    },
    async createCategory(nameFromModal) {
      const name = (nameFromModal || "").trim();
      if (!name) return;
      if (this.rows.some((r) => (r.name || "").trim().toLowerCase() === name.toLowerCase())) return;
      try {
        await axios.get("/sanctum/csrf-cookie").catch(() => {});
        const res = await axios.post("/categories", { cat_name: name });
        const created = res.data?.data ?? res.data;
        this.rows.unshift({
          id: created.id,
          name: created.cat_name ?? name,
          createdBy: this.userName,
          createdAt: created.cat_created_at ?? created.created_at ?? null,
        });
        this.addOpen = false;
      } catch (e) {
        console.error(e);
      }
    },
    openEdit(row) {
      this.editing = { ...row };
      this.editOpen = true;
    },
    async updateCategory(payload) {
      try {
        await axios.get("/sanctum/csrf-cookie").catch(() => {});
        await axios.patch(`/categories/${payload.id}`, { cat_name: payload.name });
        const i = this.rows.findIndex((r) => r.id === payload.id);
        if (i !== -1) this.rows[i].name = payload.name;
        this.editOpen = false;
      } catch (e) {
        console.error(e);
      }
    },
    requestDelete(arg) {
      this.deleting = typeof arg === "number" ? this.rows.find((r) => r.id === arg) : arg;
      this.confirmOpen = true;
    },
    cancelDelete() {
      this.deleting = null;
      this.confirmOpen = false;
    },
    async confirmDelete() {
      if (!this.deleting) return;
      const id = this.deleting.id;
      const backup = [...this.rows];
      this.rows = this.rows.filter((r) => r.id !== id);
      this.confirmOpen = false;
      try {
        await axios.get("/sanctum/csrf-cookie").catch(() => {});
        await axios.delete(`/categories/${id}`);
        this.successOpen = true; // เปิด DeleteSuccess modal
      } catch (e) {
        console.error(e);
        this.rows = backup;
      } finally {
        this.deleting = null;
      }
    },
    onChangePageSize(newSize) {
      this.pageSize = Number(newSize) || 10;
      this.page = 1;
    }
  },
};
</script>

<style scoped>
/* เพิ่มสไตล์ถ้าต้องการ */
</style>
