<template>
  <div class="p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-[#C91818]">Category</h1>

    <!-- Toolbar -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <!-- Search -->
      <div class="flex w-full items-center gap-2 sm:max-w-xl">
        <div class="relative flex-1">
          <input
            v-model="search"
            type="text"
            placeholder="Search..."
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
            @keydown.enter="page=1"
          />
        </div>
        <button
          class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
          @click="page=1"
          aria-label="search"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
          </svg>
        </button>
      </div>

      <!-- Right controls -->
      <div class="flex items-center gap-2">
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="setSort('created_at')"
          :title="sortDir==='desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
          </svg>
          <span>Sort ({{ sortDir==='asc' ? 'เก่าสุด' : 'ล่าสุด' }})</span>
        </button>

        <button
          class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
          @click="addCategory"
        >
          <span class="text-lg leading-none">＋</span>
          <span>Add New</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl ring-1 ring-gray-100">
      <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50 text-gray-600">
          <tr class="text-left text-sm">
            <th class="w-14 px-6 py-3 font-semibold">#</th>
            <th class="px-6 py-3 font-semibold">Category</th>
            <th class="px-6 py-3 font-semibold">Created by</th>
            <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
            <th class="w-16 px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="(row, idx) in paged" :key="row.id" class="text-sm text-gray-700 hover:bg-gray-50">
            <td class="px-6 py-3">{{ (page-1)*pageSize + idx + 1 }}</td>
            <td class="px-6 py-3">{{ row.cat_name }}</td>
            <td class="px-6 py-3">{{ row.created_by || 'สมปอง' /* ตัวอย่าง */ }}</td>
            <td class="px-6 py-3">{{ fmtDate(row.created_at) }}</td>
            <td class="px-6 py-3">
              <button
                class="rounded-lg p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600"
                @click="deleteCategory(row.id)"
                aria-label="delete"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.278l.84 12.255A2 2 0 008.114 22h7.772a2 2 0 001.996-2.745L18.722 7H19a1 1 0 100-2h-3V4a1 1 0 00-1-1H9zm2 4a1 1 0 112 0v10a1 1 0 11-2 0V7z"/>
                </svg>
              </button>
            </td>
          </tr>

          <tr v-if="!loading && paged.length===0">
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">ไม่พบข้อมูลที่ค้นหา</td>
          </tr>

          <tr v-if="loading">
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">กำลังโหลด...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
      <div class="flex items-center gap-2 text-sm text-gray-600">
        <span>แสดง</span>
        <select v-model.number="pageSize" class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none">
          <option v-for="n in [10,20,50]" :key="n" :value="n">{{ n }}</option>
        </select>
        <span>{{ `${(page-1)*pageSize + paged.length} จาก ${sorted.length} รายการ` }}</span>
      </div>

      <div class="flex items-center gap-2">
        <button
          class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
          :disabled="page===1"
          @click="prevPage"
        >ก่อนหน้า</button>
        <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages }}</span>
        <button
          class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
          :disabled="page===totalPages"
          @click="nextPage"
        >ถัดไป</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

// ====== ตั้ง BASE URL ให้ชี้ไป Laravel ======
axios.defaults.baseURL = import.meta.env?.VITE_API_BASE || "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  data() {
    return {
      categories: [],
      search: "",
      sortBy: "created_at",
      sortDir: "desc",
      page: 1,
      pageSize: 10,
      loading: false,
    };
  },

  async created() {
    await this.fetchCategories();
  },

  watch: {
    search()   { this.page = 1; },
    pageSize() { this.page = 1; },
    categories(){ this.page = 1; },
  },

  computed: {
    filtered() {
      if (!this.search) return this.categories;
      const q = this.search.toLowerCase();
      return this.categories.filter(c =>
        `${c.cat_name} ${c.created_by ?? ''} ${c.created_at ?? ''}`.toLowerCase().includes(q)
      );
    },
    sorted() {
      const arr = [...this.filtered];
      const key = this.sortBy;
      arr.sort((a,b)=>{
        const va = (a[key] ?? '').toString().toLowerCase();
        const vb = (b[key] ?? '').toString().toLowerCase();
        if (va < vb) return this.sortDir === 'asc' ? -1 : 1;
        if (va > vb) return this.sortDir === 'asc' ?  1 : -1;
        return 0;
      });
      return arr;
    },
    totalPages() {
      const t = Math.ceil((this.sorted.length || 1) / this.pageSize);
      return t || 1;
    },
    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    }
  },

  methods: {
    async fetchCategories() {
      this.loading = true;
      try {
        const res = await axios.get("/categories");
        this.categories = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (e) {
        console.error("GET /categories error", e?.response || e);
        Swal.fire("Error", e?.response?.data?.message || "โหลดข้อมูลไม่สำเร็จ", "error");
      } finally {
        this.loading = false;
      }
    },

    async addCategory() {
      const { value: name } = await Swal.fire({
        title: "เพิ่มหมวดหมู่",
        input: "text",
        inputPlaceholder: "เช่น ประชุม",
        confirmButtonText: "บันทึก",
        showCancelButton: true,
        cancelButtonText: "ยกเลิก",
        inputValidator: (v)=> !v ? "กรอกชื่อหมวดหมู่ก่อนนะคะ" : undefined
      });
      if (!name) return;

      try {
        const res = await axios.post("/categories", { cat_name: name });
        this.categories.push(res.data);
        Swal.fire("สำเร็จ", "บันทึกหมวดหมู่แล้ว", "success");
      } catch (e) {
        console.error("POST /categories error", e?.response || e);
        Swal.fire("Error", e?.response?.data?.message || "บันทึกไม่สำเร็จ", "error");
      }
    },

    async deleteCategory(id) {
      const ok = await Swal.fire({
        title: "ลบหมวดหมู่?",
        text: "ยืนยันการลบหมวดหมู่นี้",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ลบ",
        cancelButtonText: "ยกเลิก",
      }).then(r => r.isConfirmed);
      if (!ok) return;

      try {
        await axios.delete(`/categories/${id}`);
        this.categories = this.categories.filter(c => c.id !== id);
        Swal.fire("สำเร็จ", "ลบหมวดหมู่แล้ว", "success");
      } catch (e) {
        console.error("DELETE /categories error", e?.response || e);
        Swal.fire("Error", e?.response?.data?.message || "ลบไม่สำเร็จ", "error");
      }
    },

    setSort(field) {
      if (this.sortBy === field) {
        this.sortDir = this.sortDir === "asc" ? "desc" : "asc";
      } else {
        this.sortBy = field;
        this.sortDir = "asc";
      }
    },

    prevPage(){ if (this.page > 1) this.page--; },
    nextPage(){ if (this.page < this.totalPages) this.page++; },

    fmtDate(iso) {
      if (!iso) return "-";
      const d = new Date(iso);
      if (isNaN(d.getTime())) return iso;
      const dd = String(d.getDate()).padStart(2,"0");
      const mm = String(d.getMonth()+1).padStart(2,"0");
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
  }
};
</script>
