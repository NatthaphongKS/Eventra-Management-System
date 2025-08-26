<template>
  <!-- Top bar -->
  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <router-link
      to="/add-employee"
      class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
    >
      <span class="text-lg leading-none">＋</span>
      <span>Add New</span>
    </router-link>

    <!-- Controls -->
    <div class="flex w-full flex-col gap-2 sm:max-w-3xl sm:flex-row sm:items-center">
      <div class="relative flex-1">
        <input
          v-model.trim="search"
          class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
          placeholder="ค้นหา (รหัส/ชื่อ/อีเมล/โทรศัพท์/ตำแหน่ง/แผนก/ทีม)"
        />
      </div>

      <div class="flex items-center gap-2 text-sm text-gray-700">
        <span>แสดง</span>
        <select
          v-model.number="pageSize"
          class="rounded-xl border border-gray-200 bg-white px-3 py-2 outline-none hover:bg-gray-50"
        >
          <option v-for="s in [10,25,50,100]" :key="s" :value="s">{{ s }}</option>
        </select>
        <span>แถว</span>
      </div>

      <span class="text-right text-xs text-gray-500 sm:text-sm sm:text-gray-600 sm:[min-width:10rem]">
        ทั้งหมด {{ filtered.length }} รายการ
      </span>
    </div>
  </div>

  <!-- Table -->
  <div class="w-full overflow-hidden rounded-2xl border border-gray-200">
    <table class="min-w-full table-fixed">
      <colgroup>
        <col class="w-12" />         <!-- # -->
        <col class="w-28" />         <!-- ID -->
        <col />                       <!-- Name -->
        <col class="md:w-44" />       <!-- Position -->
        <col class="md:w-56" />       <!-- Email -->
        <col class="md:w-40" />       <!-- Phone -->
        <col class="md:w-44" />       <!-- Department -->
        <col class="md:w-44" />       <!-- Team -->
        <col class="w-28" />          <!-- Action -->
      </colgroup>

      <thead class="bg-gray-100">
        <tr class="text-left text-sm font-semibold text-gray-700">
          <th class="px-4 py-3">#</th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('emp_id')">
              <span>ID</span>
              <SortIcon :active="sortBy==='emp_id'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('emp_firstname')">
              <span>Name</span>
              <SortIcon :active="sortBy==='emp_firstname'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('position_name')">
              <span>Position</span>
              <SortIcon :active="sortBy==='position_name'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('email')">
              <span>Email</span>
              <SortIcon :active="sortBy==='email'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('phone')">
              <span>Phone</span>
              <SortIcon :active="sortBy==='phone'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('department_name')">
              <span>Department</span>
              <SortIcon :active="sortBy==='department_name'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">
            <button class="inline-flex items-center gap-1 hover:opacity-80"
                    @click="setSort('team_name')">
              <span>Team</span>
              <SortIcon :active="sortBy==='team_name'" :dir="sortDir"/>
            </button>
          </th>

          <th class="px-4 py-3">Action</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
        <tr v-for="(emp, i) in paged" :key="emp.id" class="hover:bg-gray-50">
          <td class="px-4 py-3">{{ (page-1)*pageSize + i + 1 }}</td>
          <td class="px-4 py-3 whitespace-nowrap">{{ emp.emp_id || 'N/A' }}</td>

          <td class="px-4 py-3 truncate" :title="fullName(emp)">
            {{ fullName(emp) }}
          </td>

          <td class="px-4 py-3 whitespace-nowrap">{{ emp.position_name || 'N/A' }}</td>
          <td class="px-4 py-3 truncate" :title="emp.email || 'N/A'">{{ emp.email || 'N/A' }}</td>
          <td class="px-4 py-3 whitespace-nowrap">{{ emp.phone || 'N/A' }}</td>
          <td class="px-4 py-3 whitespace-nowrap">{{ emp.department_name || 'N/A' }}</td>
          <td class="px-4 py-3 whitespace-nowrap">{{ emp.team_name || 'N/A' }}</td>

          <td class="px-4 py-2">
            <div class="flex items-center gap-2">
              <button
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                @click="editEmployee(emp.id)"
              >
                ✏ Edit
              </button>
              <button
                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                @click="deleteEmployee(emp.id)"
              >
                🗑 Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="paged.length === 0">
          <td :colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">No data found</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="text-sm text-gray-600">
      หน้า {{ page }} / {{ totalPages || 1 }}
    </div>

    <div class="flex items-center gap-2">
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        @click="page=1"
        :disabled="page===1"
      >«</button>

      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        @click="prevPage"
        :disabled="page===1"
      >ก่อนหน้า</button>

      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        @click="nextPage"
        :disabled="page===totalPages || totalPages===0"
      >ถัดไป</button>

      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        @click="page=totalPages"
        :disabled="page===totalPages || totalPages===0"
      >»</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

/* สัญลักษณ์ sort เป็นคอมโพเนนต์เล็ก ๆ */
const SortIcon = {
  props: { active: Boolean, dir: String },
  template: `
    <svg v-if="active" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
         viewBox="0 0 20 20" fill="currentColor"
         :class="dir==='desc' ? '' : 'rotate-180'">
      <path d="M3.5 7.5l6.5 6.5 6.5-6.5H3.5z"/>
    </svg>
  `
};

export default {
  name: "EmployeeTable",
  components: { SortIcon },
  data() {
    return {
      employees: [],
      search: "",
      sortBy: "emp_id",
      sortDir: "asc", // 'asc' | 'desc'
      page: 1,
      pageSize: 10,
    };
  },
  async created() {
    await this.fetchEmployees();
  },
  watch: {
    search() { this.page = 1; },
    pageSize() { this.page = 1; },
    employees() { this.page = 1; },
  },
  computed: {
    normalized() {
      return this.employees.map(e => ({
        ...e,
        emp_id: e.emp_id ?? "",
        emp_prefix: e.emp_prefix ?? "",
        emp_firstname: e.emp_firstname ?? "",
        emp_lastname: e.emp_lastname ?? "",
        position_name: e.position_name ?? "",
        email: e.emp_email ?? "",
        phone: e.emp_phone ?? "",
        department_name: e.department_name ?? "",
        team_name: e.team_name ?? "",
      }));
    },
    filtered() {
      if (!this.search) return this.normalized;
      const q = this.search.toLowerCase();
      return this.normalized.filter(e =>
        `${e.emp_id} ${e.emp_prefix} ${e.emp_firstname} ${e.emp_lastname} ${e.position_name} ${e.emp_email} ${e.emp_phone} ${e.department_name} ${e.team_name}`
          .toLowerCase()
          .includes(q)
      );
    },
    sorted() {
      const arr = [...this.filtered];
      const key = this.sortBy;

      arr.sort((a, b) => {
        const va = (a[key] ?? "").toString().toLowerCase();
        const vb = (b[key] ?? "").toString().toLowerCase();
        if (va < vb) return this.sortDir === "asc" ? -1 : 1;
        if (va > vb) return this.sortDir === "asc" ? 1 : -1;
        return 0;
      });

      return arr;
    },
    totalPages() {
      return Math.ceil(this.sorted.length / this.pageSize);
    },
    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    },
  },
  methods: {
    fullName(emp) {
      return `${emp.emp_prefix ? emp.emp_prefix + ' ' : ''}${emp.emp_firstname || ''} ${emp.emp_lastname || ''}`.trim() || 'N/A';
    },
    async fetchEmployees() {
      try {
        const res = await axios.get("/get-employees");
        this.employees = Array.isArray(res.data) ? res.data : (res.data?.data || []);
      } catch (err) {
        console.error("Error fetching employees", err);
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
    prevPage() { if (this.page > 1) this.page--; },
    nextPage() { if (this.page < this.totalPages) this.page++; },

    editEmployee(id) {
      console.log("Edit employee ID:", id);
      // this.$router.push(`/edit-employee/${id}`)
    },
    async deleteEmployee(id) {
      if (confirm("Are you sure you want to delete this employee?")) {
        try {
          await axios.delete(`/employees/${id}`);
          this.fetchEmployees();
        } catch (err) {
          console.error("Error deleting employee", err);
        }
      }
    },
  },
};
</script>
