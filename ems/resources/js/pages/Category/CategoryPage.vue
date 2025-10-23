<template>
    <link rel="stylesheet"
  href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,0" />

  <!-- Toolbar -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Search -->
    <div class="flex w-full items-center gap-2 sm:max-w-xl">
      <div class="relative flex-1">
        <input
          v-model="searchInput"
          type="text"
          placeholder="Search Category / Created by / Delete by"
          @keyup.enter="onSearch"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
        />
      </div>

      <button
        class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="onSearch"
        aria-label="search"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
        </svg>
      </button>
    </div>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <CategorySort v-model="sortDir" />
      <button
        class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="openAdd"
      >
        <span class="text-lg leading-none">＋</span>
        <span>Add New</span>
      </button>
    </div>
  </div>

  <!-- ===== Data Table ===== -->
  <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
    <table class="min-w-full divide-y divide-gray-100">
      <thead class="bg-gray-50 text-gray-600">
        <tr class="text-left text-sm">
          <th class="px-6 py-3 w-14 font-semibold">#</th>
          <th class="px-6 py-3 font-semibold">Category</th>
          <th class="px-6 py-3 font-semibold">Created by</th>
          <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
          <th class="px-6 py-3 text-center w-20 font-semibold">Action</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, idx) in pagedRows"
          :key="row.id"
          class="text-sm text-gray-700 hover:bg-gray-50"
        >
          <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>
          <td class="px-6 py-3 truncate">{{ row.name }}</td>
          <td class="px-6 py-3">{{ row.createdBy }}</td>
          <td class="px-6 py-3">{{ formatDate(row.createdAt) }}</td>
          <td class="px-6 py-3 text-center flex justify-center gap-1">
            <!-- ปุ่ม Edit -->
            <button
              class="rounded-full p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50"
              @click="openEdit(row)"
              title="Edit"
            >
              <span class="material-symbols-outlined">edit</span>
            </button>

            <!-- ปุ่ม Delete -->
            <button
              class="rounded-full p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50"
              @click="remove(row.id)"
              title="Delete"
            >
              <span class="material-symbols-outlined">delete</span>
            </button>
          </td>
        </tr>

        <tr v-if="pagedRows.length === 0">
          <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
            ไม่พบข้อมูลที่ค้นหา
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- ===== Pagination ===== -->
  <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <span>แสดง</span>
      <select
        v-model.number="pageSize"
        class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none"
      >
        <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
      </select>
      <span>{{ visibleCountText }}</span>
    </div>

    <div class="flex items-center gap-2">
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === 1"
        @click="page--"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === totalPages || totalPages === 0"
        @click="page++"
      >
        ถัดไป
      </button>
    </div>
  </div>

  <!-- ===== Create Modal ===== -->
  <CategoryCreate
    v-model:open="addOpen"
    :userName="userName"
    :duplicate="isDuplicate"
    @submit="createCategory"
  />

  <!-- ===== Edit Modal ===== -->
  <CategoryEdit
    v-model:open="editOpen"
    :category="editing"
    :is-duplicate="isDupForEdit"
    :formatDate="formatDate"
    @submit="updateCategory"
  />
</template>

<script>
import axios from "axios";
import CategorySort from "@/components/Category/CategorySort.vue";
import CategoryDataTable from "@/components/Category/CategoryDataTable.vue";
import CategoryCreate from "@/components/Category/CategoryCreate.vue";
import CategoryEdit from "@/components/Category/CategoryEdit.vue";
import ConfirmDelete from "@/components/Alert/ConfirmDelete.vue";
import DeleteSucces from "@/components/Alert/Employee/EmloyeeDeleteSuccess.vue";
import SearchBar from "../../components/SearchBar.vue";


axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = true;

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
