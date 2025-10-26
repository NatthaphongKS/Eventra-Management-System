<!-- CategoryPage.vue -->
<template>
  <section class="p-0">
    <!-- Toolbar -->
    <div class="flex items-center gap-3">
      <SearchBar
        v-model="searchInput"
        placeholder="Search Category / Created by"
        @search="onSearch"
      />
      <SortMenu :is-open="sortMenuOpen" :options="sortOptions" />
      <AddButton  @click="openAdd"/>
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
      <!-- ปุ่ม action -->
      <template #actions="{ row }">
        <button
          class="grid h-8 w-8 place-items-center rounded-full text-neutral-700 hover:text-emerald-600"
          @click="openEdit(row)"
          title="Edit"
          aria-label="edit"
        >
          <Icon icon="material-symbols:edit-rounded" width="20" height="20" />
        </button>

        <button
          class="grid h-8 w-8 place-items-center rounded-full text-neutral-700 hover:text-red-600"
          @click="requestDelete(row)"
          title="Delete"
          aria-label="delete"
        >
          <Icon icon="fluent:delete-12-filled" width="20" height="20" />
        </button>
      </template>

      <template #empty>
        {{ sorted.length === 0 ? 'ไม่พบหมวดหมู่' : 'ไม่มีข้อมูลในหน้านี้' }}
      </template>
    </DataTable>

    <!-- Modals -->
    <ModalAlert
      v-model:open="alert.open"
      :type="alert.type"
      :title="alert.title"
      :message="alert.message"
      :show-cancel="alert.showCancel"
      :ok-text="alert.okText"
      :cancel-text="alert.cancelText"
      @confirm="alert.onConfirm && alert.onConfirm()"
      @cancel="alert.onCancel && alert.onCancel()"
    />

    <CategoryCreate
      v-model:open="addOpen"
      :userName="userName"
      :duplicate="isDuplicate"
      @submit="createCategory"
    />
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
import DataTable from '@/components/DataTable.vue'
import SearchBar from "@/components/SearchBar.vue"
import CategorySort from "@/components/Category/CategorySort.vue"
import CategoryCreate from "@/components/Category/CategoryCreate.vue"
import CategoryEdit from "@/components/Category/CategoryEdit.vue"
import ModalAlert from "@/components/Alert/ModalAlert.vue"
import SortMenu from '../../components/SortMenu.vue'
import { Icon } from '@iconify/vue'
import AddButton from '@/components/AddButton.vue'


export default {
  name: "CategoryPage",
  components: { DataTable, SearchBar, SortMenu, CategorySort, CategoryCreate, CategoryEdit, ModalAlert , Icon ,AddButton},
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

      // state ของ modal แจ้งเตือน
      alert: { open: false, type: '', title: '', message: '', showCancel: false, okText: 'OK', cancelText: 'Cancel' },

      // modal CRUD
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
    /* --------- โหลดรายการ --------- */
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
          cat_name: c.cat_name ?? c.name ?? "-",
          created_by_name: c.created_by_name ?? c.createdBy ?? "-",
          cat_created_at: c.cat_created_at ?? c.created_at ?? null,
        }));
      } catch (e) {
        console.error(e);
        this.rows = [];
        this.alert = { open: true, type: 'error', title: 'Failed', message: 'Load categories failed.' };
      }
    },

    /* --------- ค้นหา/เปลี่ยนหน้า --------- */
    onSearch(value) {
      this.search = value.trim();
      this.page = 1;
    },
    onChangePageSize(size) {
      this.pageSize = Number(size);
      this.page = 1;
    },

    /* ================== CREATE ================== */
    openAdd() { this.addOpen = true; },

    isDuplicate(name) {
      const n = (name || "").trim().toLowerCase();
      if (!n) return false;
      return this.rows.some(r => (r.cat_name || "").toLowerCase() === n);
    },

    // รับ payload { name } จาก CategoryCreate
    async createCategory({ name }) {
      try {
        const n = (name || "").trim();
        if (!n) throw new Error("Name is empty.");

        // บางแบ็กเอนด์ใช้ name, บางที่ใช้ cat_name
        const res = await axios.post("/categories", {
          cat_name: n,
          created_by_name: this.userName,
        }).catch(() => axios.post("/categories", { name: n, created_by_name: this.userName }));

        const created = res?.data?.data || res?.data || {};
        this.rows.unshift({
          id: created.id,
          cat_name: created.cat_name || created.name || n,
          created_by_name: created.created_by_name || created.createdBy || this.userName,
          cat_created_at: created.cat_created_at || created.created_at || new Date().toISOString(),
        });

        this.addOpen = false;
        this.alert = { open: true, type: 'success', title: 'Created', message: 'Category created successfully.' };
      } catch (err) {
        console.error(err?.response || err);
        const msg = err?.response?.data?.message || 'Cannot create category.';
        this.alert = { open: true, type: 'error', title: 'Failed', message: msg };
      }
    },

    /* =================== EDIT =================== */
    openEdit(row) {
      this.editing = {
        id: row.id,
        name: row.cat_name,            // แปลง cat_name -> name
        createdBy: row.created_by_name,
        createdAt: row.cat_created_at,
      }
      this.editOpen = true
    },


    isDupForEdit(name, currentId) {
    const n = (name || "").trim().toLowerCase()
    if (!n) return false
    return this.rows.some(r => r.id !== currentId && (r.cat_name || "").toLowerCase() === n)
  },


    // รับ payload { id, name } จาก CategoryEdit
    // รับ payload { id, name } จาก CategoryEdit
    async updateCategory({ id, name }) {
      const n = (name || "").trim();
      if (!n) {
        this.alert = { open: true, type: "error", title: "Failed", message: "Name is empty." };
        return;
      }

      try {
        // 1) พยายาม PATCH /categories/:id
        await axios.patch(`/categories/${id}`, { cat_name: n, name: n });

      } catch (e1) {
        try {
          // 2) Laravel method spoofing: POST + _method=PATCH
          await axios.post(`/categories/${id}`, { cat_name: n, name: n, _method: "PATCH" });
        } catch (e2) {
          try {
            // 3) เส้นทางแบบ update
            await axios.post(`/categories/update/${id}`, { cat_name: n, name: n });
          } catch (e3) {
            const msg =
              e3?.response?.data?.message ||
              e2?.response?.data?.message ||
              e1?.response?.data?.message ||
              "Cannot update this category.";
            this.alert = { open: true, type: "error", title: "Failed", message: msg };
            return;
          }
        }
      }

      // อัปเดตในตาราง และปิดโมดัล
      this.rows = this.rows.map(r => (r.id === id ? { ...r, cat_name: n } : r));
      this.editOpen = false;
      this.alert = { open: true, type: "success", title: "Updated", message: "Category updated successfully." };
    },


    /* ================== DELETE ================== */
    requestDelete(row) {
      this.alert = {
        open: true,
        type: 'confirm',
        title: 'Confirm Delete',
        message: `Delete "${row.cat_name}"?`,
        showCancel: true,
        okText: 'Delete',
        cancelText: 'Cancel',
        onConfirm: () => this.confirmDelete(row.id),
        onCancel: () => (this.alert.open = false),
      };
    },

    async confirmDelete(id) {
      try {
        await axios.delete(`/categories/${id}`)
          .catch(() => axios.post(`/categories/${id}/delete`));

        this.rows = this.rows.filter(r => r.id !== id);
        this.alert = { open: true, type: 'success', title: 'Deleted', message: 'Category deleted successfully.' };
      } catch (err) {
        console.error(err?.response || err);
        const msg = err?.response?.data?.message || 'Cannot delete this category.';
        this.alert = { open: true, type: 'error', title: 'Failed', message: msg };
      }
    },

    /* --------------- utils สำหรับโมดัล --------------- */
    formatDate(iso) {
      if (!iso) return '-';
      const d = new Date(iso);
      if (isNaN(d)) return '-';
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
  },
};
</script>
