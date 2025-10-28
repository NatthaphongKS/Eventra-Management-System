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

      <!-- ✅ ห่อไว้เพื่อจับคลิกรอบนอก -->
      <div class="relative z-[60]" ref="sortWrap">
        <SortMenu
          :is-open="sortMenuOpen"
          :options="sortOptions"
          :sort-by="sortBy.key"
          :sort-order="sortBy.order"
          @toggle="sortMenuOpen = !sortMenuOpen"
          @choose="onSortChoose"
        />
      </div>

      <Button variant="add" @click="openAdd">Add Category</Button>
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
import SearchBar from '@/components/SearchBar.vue'
import CategoryCreate from '@/components/Category/CategoryCreate.vue'
import CategoryEdit from '@/components/Category/CategoryEdit.vue'
import ModalAlert from '@/components/Alert/ModalAlert.vue'
import SortMenu from '@/components/SortMenu.vue'
import Button from '@/components/Button.vue'
import { Icon } from '@iconify/vue'

export default {
  name: 'CategoryPage',
  components: { DataTable, SearchBar, SortMenu, CategoryCreate, CategoryEdit, ModalAlert, Button, Icon },

  data() {
    return {
      rows: [],
      searchInput: '',
      search: '',
      page: 1,
      pageSize: 10,

      /* ✅ สถานะซอร์ต */
      sortMenuOpen: false,
      sortBy: { key: 'cat_created_at', order: 'desc' }, // เริ่มที่ “วันที่ล่าสุด”
      sortOptions: [
        { key: 'cat_name',       order: 'asc',  label: 'ชื่อ A-Z', value: 'az' },
        { key: 'cat_name',       order: 'desc', label: 'ชื่อ Z-A', value: 'za' },
        { key: 'cat_created_at', order: 'desc', label: 'วันที่สร้างล่าสุด', value: 'newest' },
        { key: 'cat_created_at', order: 'asc',  label: 'วันที่สร้างเก่าสุด', value: 'oldest' },
      ],

      alert: { open: false, type: '', title: '', message: '', showCancel: false, okText: 'OK', cancelText: 'Cancel' },

      addOpen: false,
      editOpen: false,
      editing: null,

      userName: 'Admin',

      CategoryTableColumns: [
        { key: 'cat_name', label: 'Category', class: 'text-left w-[867px] h-[60px]', sortable: true },
        { key: 'created_by_name', label: 'Created by', class: 'text-center w-[134px]' },
        { key: 'cat_created_at', label: 'Created date (D/M/Y)', class: 'text-center w-[202px]', format: v => new Date(v).toLocaleDateString() },
      ],
    }
  },

  computed: {
    filtered() {
      const q = this.search.trim().toLowerCase()
      if (!q) return this.rows
      return this.rows.filter(
        r =>
          (r.cat_name && r.cat_name.toLowerCase().includes(q)) ||
          (r.created_by_name && r.created_by_name.toLowerCase().includes(q)),
      )
    },

    /* ✅ เรียงตาม sortBy.key + sortBy.order */
    sorted() {
      const { key, order } = this.sortBy
      const dir = order === 'asc' ? 1 : -1

      return this.filtered.slice().sort((a, b) => {
        if (key === 'cat_name') {
          const an = (a.cat_name || '').toLowerCase()
          const bn = (b.cat_name || '').toLowerCase()
          return an.localeCompare(bn) * dir
        }
        if (key === 'cat_created_at') {
          const ta = new Date(a.cat_created_at).getTime() || 0
          const tb = new Date(b.cat_created_at).getTime() || 0
          return (ta - tb) * dir
        }
        return 0
      })
    },

    paged() {
      const start = (this.page - 1) * this.pageSize
      return this.sorted.slice(start, start + this.pageSize)
    },
  },

  async created() {
    await this.loadCategories()
  },

  mounted() {
    // ✅ ใช้ bubbling phase (ไม่ใส่ true) เพื่อให้ @click.stop ในเมนูทำงานก่อน
    document.addEventListener('click', this.onDocClick)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onDocClick)
  },

  methods: {
    /* --------- โหลดรายการ --------- */
    async loadCategories() {
      try {
        const res = await axios.get('/categories')
        const data = Array.isArray(res.data) ? res.data : Array.isArray(res.data?.data) ? res.data.data : []
        this.rows = data.map(c => ({
          id: c.id,
          cat_name: c.cat_name ?? c.name ?? '-',
          created_by_name: c.created_by_name ?? c.createdBy ?? '-',
          cat_created_at: c.cat_created_at ?? c.created_at ?? null,
        }))
      } catch (e) {
        console.error(e)
        this.rows = []
        this.alert = { open: true, type: 'error', title: 'Failed', message: 'Load categories failed.' }
      }
    },

    /* --------- ค้นหา/เปลี่ยนหน้า --------- */
    onSearch(value) {
      this.search = value.trim()
      this.page = 1
    },
    onChangePageSize(size) {
      this.pageSize = Number(size)
      this.page = 1
    },

    /* ================== SORT ================== */
    onSortChoose(option) {
      // option = { key, order, label, value }
      if (!option || !option.key || !option.order) return
      this.sortBy = { key: option.key, order: option.order }
      this.page = 1
      this.sortMenuOpen = false
    },

    // ปิดเมนูเมื่อคลิกรอบนอก
    onDocClick(e) {
      if (!this.sortMenuOpen) return
      const wrap = this.$refs.sortWrap
      if (wrap && !wrap.contains(e.target)) {
        this.sortMenuOpen = false
      }
    },

    /* ================== CREATE/EDIT/DELETE (เดิม) ================== */
    openAdd() { this.addOpen = true },
    isDuplicate(name) {
      const n = (name || '').trim().toLowerCase()
      if (!n) return false
      return this.rows.some(r => (r.cat_name || '').toLowerCase() === n)
    },
    async createCategory({ name }) {
      try {
        const n = (name || '').trim()
        if (!n) throw new Error('Name is empty.')
        if (this.isDuplicate(n)) {
          this.alert = { open: true, type: 'error', title: 'Duplicate', message: 'มีชื่อนี้อยู่แล้วในรายการ' }
          return
        }
        const res = await axios.post('/categories', { cat_name: n })
        const created = res?.data?.data || res?.data || {}
        this.rows.unshift({
          id: created.id,
          cat_name: created.cat_name || n,
          created_by_name: created.created_by_name || this.userName,
          cat_created_at: created.cat_created_at || new Date().toISOString(),
        })
        this.addOpen = false
        this.alert = { open: true, type: 'success', title: 'Created', message: 'Category created successfully.' }
      } catch (err) {
        console.error(err?.response || err)
        const msg = err?.response?.data?.message || 'Cannot create category.'
        this.alert = { open: true, type: 'error', title: 'Failed', message: msg }
      }
    },
    openEdit(row) {
      this.editing = { id: row.id, name: row.cat_name, createdBy: row.created_by_name, createdAt: row.cat_created_at }
      this.editOpen = true
    },
    isDupForEdit(name, currentId) {
      const n = (name || '').trim().toLowerCase()
      if (!n) return false
      return this.rows.some(r => r.id !== currentId && (r.cat_name || '').toLowerCase() === n)
    },
    async updateCategory({ id, name }) {
      const n = (name || '').trim()
      if (!n) { this.alert = { open: true, type: 'error', title: 'Failed', message: 'Name is empty.' }; return }
      if (this.isDupForEdit(n, id)) {
        this.alert = { open: true, type: 'error', title: 'Duplicate', message: 'มีชื่อนี้อยู่แล้วในรายการ' }; return
      }
      try {
        await axios.put(`/categories/${id}`, { cat_name: n })
        this.rows = this.rows.map(r => (r.id === id ? { ...r, cat_name: n } : r))
        this.editOpen = false
        this.alert = { open: true, type: 'success', title: 'Updated', message: 'Category updated successfully.' }
      } catch (err) {
        console.error(err?.response || err)
        const msg = err?.response?.data?.message || 'Cannot update this category.'
        this.alert = { open: true, type: 'error', title: 'Failed', message: msg }
      }
    },
    requestDelete(row) {
      this.alert = {
        open: true, type: 'confirm', title: 'Confirm Delete', message: `Delete "${row.cat_name}"?`,
        showCancel: true, okText: 'Delete', cancelText: 'Cancel',
        onConfirm: () => this.confirmDelete(row.id), onCancel: () => (this.alert.open = false),
      }
    },
    async confirmDelete(id) {
      try {
        await axios.delete(`/categories/${id}`)
        this.rows = this.rows.filter(r => r.id !== id)
        this.alert = { open: true, type: 'success', title: 'Deleted', message: 'Category deleted successfully.' }
      } catch (err) {
        console.error(err?.response || err)
        const msg = err?.response?.data?.message || 'Cannot delete this category.'
        this.alert = { open: true, type: 'error', title: 'Failed', message: msg }
      }
    },
  },
}
</script>
