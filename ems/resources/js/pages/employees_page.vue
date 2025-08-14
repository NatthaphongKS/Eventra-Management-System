<template>
    <router-link to="/add-employee" class="btn-red"> + Add New </router-link>

    <!-- Controls -->
    <div class="toolbar">
      <input
        v-model.trim="search"
        class="input"
        placeholder="ค้นหา (รหัส/ชื่อ/อีเมล/โทรศัพท์/ตำแหน่ง/แผนก/ทีม)"
      />
      <div class="page-size">
        <span>แสดง</span>
        <select v-model.number="pageSize" class="select">
          <option v-for="s in [10,25,50,100]" :key="s" :value="s">{{ s }}</option>
        </select>
        <span>แถว</span>
      </div>
      <span class="summary">ทั้งหมด {{ filtered.length }} รายการ</span>
    </div>

    <div>
      <table border="1" cellpadding="8" cellspacing="0" style="margin-top: 10px; width: 100%">
        <thead>
          <tr>
            <th class="th">#</th>
            <th class="th sortable" @click="setSort('emp_id')">
              ID <span v-if="sortBy==='emp_id'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('emp_firstname')">
              Name <span v-if="sortBy==='emp_firstname'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('position_name')">
              Position <span v-if="sortBy==='position_name'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('email')">
              Email <span v-if="sortBy==='email'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('phone')">
              Phone <span v-if="sortBy==='phone'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('department_name')">
              Department <span v-if="sortBy==='department_name'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th sortable" @click="setSort('team_name')">
              Team <span v-if="sortBy==='team_name'">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="th">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(emp, i) in paged" :key="emp.id">
            <td>{{ (page-1)*pageSize + i + 1 }}</td>
            <td>{{ emp.emp_id || 'N/A' }}</td>
            <td>{{ (emp.emp_prefix ? emp.emp_prefix + ' ' : '') + (emp.emp_firstname || '') + ' ' + (emp.emp_lastname || '') }}</td>
            <td>{{ emp.position_name || 'N/A' }}</td>
            <td>{{ emp.email || 'N/A' }}</td>
            <td>{{ emp.phone || 'N/A' }}</td>
            <td>{{ emp.department_name || 'N/A' }}</td>
            <td>{{ emp.team_name || 'N/A' }}</td>
            <td>
              <button @click="editEmployee(emp.id)">✏ Edit</button>
              <button @click="deleteEmployee(emp.id)">🗑 Delete</button>
            </td>
          </tr>

          <tr v-if="paged.length === 0">
            <td :colspan="9" style="text-align: center">No data found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pager">
      <div>หน้า {{ page }} / {{ totalPages || 1 }}</div>
      <div class="pager-btns">
        <button @click="page=1" :disabled="page===1">«</button>
        <button @click="prevPage" :disabled="page===1">ก่อนหน้า</button>
        <button @click="nextPage" :disabled="page===totalPages || totalPages===0">ถัดไป</button>
        <button @click="page=totalPages" :disabled="page===totalPages || totalPages===0">»</button>
      </div>
    </div>
  </template>

  <script>
  import axios from "axios";
  axios.defaults.baseURL = "/api";
  axios.defaults.headers.common["Accept"] = "application/json";

  export default {
    data() {
      return {
        employees: [],
        // Data table state
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

  <style>
  .btn-red {
    background: red;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 4px;
  }

  /* Data table */
  .toolbar { display: flex; gap: .75rem; align-items: center; margin-top: 12px; }
  .input { border: 1px solid #ddd; padding: 6px 10px; border-radius: 6px; width: 100%; }
  .select { border: 1px solid #ddd; padding: 6px 8px; border-radius: 6px; }
  .page-size { display: flex; align-items: center; gap: .5rem; }
  .summary { margin-left: auto; font-size: 12px; color: #666; }

  .th { cursor: default; user-select: none; background: #f9fafb; }
  .sortable { cursor: pointer; }

  .pager { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
  .pager-btns { display: flex; gap: .5rem; }
  .pager button { padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; background: #fff; }
  .pager button:hover { background: #f3f4f6; }
  .pager button[disabled] { opacity: .5; cursor: not-allowed; }
  </style>
