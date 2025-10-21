<!-- pages/event_page.vue -->
<template>

<div class="dashboard-grid">
  <!-- Event Table Section -->
  <div class="card event-card">
    <h2 class="table-title event-title">Event</h2>
    <div class="toolbar toolbar--pill">
      <!-- กลุ่ม search + ปุ่มค้นหาแดง -->
      <div class="search-group">
        <input
          v-model.trim="search"
          placeholder="Search"
          class="pill-input"
        />
        <button
          type="button"
          class="icon-btn icon-btn--solid"
          aria-label="Search"
          title="ค้นหา"
          disabled
          style="opacity:0.5;pointer-events:none;"
        >
          <MagnifyingGlassIcon class="icon" />
        </button>
      </div>

      <!-- ปุ่ม Filter (ตอนนี้ยัง UI เฉยๆ) -->
      <button type="button" class="text-btn" @click="toggleFilter">
        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="4" y1="7" x2="20" y2="7" />
          <line x1="6" y1="12" x2="16" y2="12" />
          <line x1="8" y1="17" x2="12" y2="17" />
        </svg>
        <span>Filter</span>
      </button>

      <!-- ปุ่ม Sort พร้อม dropdown สำหรับ employee table -->
      <div style="position:relative;">
        <button type="button" class="text-btn" @click="showEmpSort = !showEmpSort">
          <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
          </svg>
          <span>Sort</span>
        </button>
        <div v-if="showEmpSort" class="sort-dropdown">
          <div class="sort-title">Sort</div>
          <button v-for="opt in empSortOptions" :key="opt.value" :class="['sort-item', empSort.value===opt.value ? 'active' : '']" @click="setEmpSort(opt.value)">
            {{ opt.label }}
          </button>
        </div>
      </div>

      <!-- สรุปรายการ + ปุ่มเพิ่ม (เรียงแบบในภาพ) -->
      <span class="summary">ทั้งหมด {{ filtered.length }} รายการ</span>
      <button type="button" class="custom-btn report-btn" @click="onViewReport">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
          <path d="M3 6h18M6 12h12M10 18h8" stroke-linecap="round"/>
        </svg>
        <span>View Report</span>
      </button>
      <button type="button" class="custom-btn export-btn" @click="onExport">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
          <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
        </svg>
        <span>Export</span>
      </button>
    </div>
    <div class="table-wrap">
      <table class="table compact">
        <thead>
          <tr>
            <th class="th col-idx">
              <input type="checkbox" @change="selectAllEvents" v-model="selectAll" />
            </th>
            <th class="th col-title">Event</th>
            <th class="th col-cat">Category</th>
            <th class="th col-date">Date (D/M/Y)</th>
            <th class="th col-time">Time</th>
            <th class="th col-num">Invited</th>
            <th class="th col-num">Accepted</th>
            <th class="th col-status">Status</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(ev, i) in paged" :key="ev.id">
            <td class="col-idx">
              <input 
                type="radio" 
                :value="ev.id || ev.evn_id" 
                :checked="selectedEventId === (ev.id || ev.evn_id)"
                @change="onEventSelect(ev)"
                name="selectedEvent"
              />
              <br><small style="color: #999; font-size: 10px;">ID: {{ ev.id || ev.evn_id }}</small>
            </td>
            <td class="col-title"><span class="truncate">{{ ev.evn_title || 'N/A' }}</span></td>
            <td class="col-cat"><span class="truncate">{{ ev.cat_name || 'N/A' }}</span></td>
          <td class="col-date">{{ formatDate(ev.evn_date) }}</td>
          <td class="col-time">
            {{ ev.evn_timestart ? ev.evn_timestart.slice(0,5) : '??:??' }} -
            {{ ev.evn_timeend ? ev.evn_timeend.slice(0,5) : '??:??' }}
          </td>
          <td class="col-num">{{ ev.evn_num_guest ?? '0' }}</td>
          <td class="col-num">{{ ev.evn_sum_accept ?? 'N/A' }}</td>
          <td class="col-status">
            <span :class="['badge', ev.evn_status]">{{ ev.evn_status || 'N/A' }}</span>
            </td>
        </tr>

        <tr v-if="paged.length === 0">
            <td :colspan="8" style="text-align:center">No data found</td>
        </tr>
        </tbody>
    </table>
    </div>
    
    <!-- Event table info and pagination (same row) -->
    <div class="event-table-footer">
      <div class="table-info">
        แสดง 
        <select v-model="pageSize" class="page-size-select">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        {{ eventPaginationText }}
      </div>
      
      <div class="pager2">
      <button class="arrow-btn" :disabled="page===1" @click="goToPage(page-1)">‹</button>
      <template v-for="(it, idx) in pageItems" :key="idx">
        <button v-if="it.type==='page'" class="page-btn" :class="{ active: it.value===page }" @click="goToPage(it.value)">
          {{ it.value }}
        </button>
        <span v-else class="dots">…</span>
      </template>
      <button class="arrow-btn" :disabled="page===totalPages || totalPages===0" @click="goToPage(page+1)">›</button>
      </div>
    </div>
  </div>

  <!-- Summary/Graph Section - แสดงเมื่อเลือก event แล้ว -->
  <div v-if="selectedEventId" class="card summary-card">
    <div class="summary-grid">
      <!-- Actual Attendance -->
      <div class="summary-item chart-container actual-attendance-card">
        <DonutActualAttendance 
          :eventId="selectedEventId"
          :attendanceData="chartData"
        />
      </div>

      <!-- Event Participation Graph -->
      <div class="summary-item chart-container event-participation-card">
        <GraphEventParticipation 
          :eventId="selectedEventId"
          :data="participationData"
        />
      </div>

      <!-- Status Cards Row -->
      <div class="status-cards-row">
        <AttendingCard 
          :eventId="selectedEventId" 
          :value="chartData.attending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          @show-employees="showEmployeesByStatus"
        />
        <NotAttendingCard 
          :eventId="selectedEventId" 
          :value="chartData.notAttending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          @show-employees="showEmployeesByStatus"
        />
        <PendingCard 
          :eventId="selectedEventId" 
          :value="chartData.pending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          @show-employees="showEmployeesByStatus"
        />
      </div> <!-- Close status-cards-row -->
    </div>
  </div>

  <!-- ข้อความแจ้งเมื่อยังไม่ได้เลือกกิจกรรม -->
  <div v-if="!selectedEventId" class="card no-event-selected">
    <div class="text-center">
      <div class="icon-placeholder">📈</div>
      <h3>กรุณาเลือกกิจกรรมเพื่อดูกราฟและข้อมูลสถิติ</h3>
      <p class="text-muted">เลือกจากตารางกิจกรรมด้านบนเพื่อแสดงข้อมูลรายละเอียด</p>
    </div>
  </div>

  <!-- Employee Table Section - แสดงเมื่อกดการ์ด -->
  <div v-if="showEmployeeTable && selectedEventId" class="simple-table-container">
    <div class="employee-table-header">
      <button @click="hideEmployeeTable" class="close-table-btn" title="ปิดตาราง">✕</button>
    </div>
    <div class="table-wrap">
      <table class="table compact">
        <thead>
          <tr>
            <th class="th col-idx">#</th>
            <th class="th col-id">ID</th>
            <th class="th col-name">Name</th>
            <th class="th col-last">Lastname</th>
            <th class="th col-nickname">Nickname</th>
            <th class="th col-phone">Phone</th>
            <th class="th col-position">Position</th>
            <th class="th col-department">Department</th>
            <th class="th col-team">Team</th>
            <th class="th col-event">Event</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(emp, i) in paginatedEmployees" :key="emp.id">
            <td class="col-idx">{{ ((currentPage - 1) * itemsPerPage) + i + 1 }}</td>
            <td class="col-id">{{ emp.emp_id }}</td>
            <td class="col-name">{{ emp.emp_firstname }}</td>
            <td class="col-last">{{ emp.emp_lastname }}</td>
            <td class="col-nickname">{{ emp.emp_nickname }}</td>
            <td class="col-phone">{{ emp.emp_phone }}</td>
            <td class="col-position"><span class="truncate">{{ emp.position }}</span></td>
            <td class="col-department"><span class="truncate">{{ emp.department }}</span></td>
            <td class="col-team"><span class="truncate">{{ emp.team }}</span></td>
            <td class="col-event">{{ selectedEventData?.evn_title || 'N/A' }}</td>
          </tr>
          <tr v-if="paginatedEmployees.length === 0">
            <td :colspan="10" style="text-align:center">No data found</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Employee table info and pagination -->
    <div class="employee-table-footer">
      <div class="table-info">
        แสดง 
        <select v-model="itemsPerPage" class="page-size-select">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        {{ employeePaginationText }}
      </div>
      
      <!-- Pagination - Employee table style -->
      <div class="pager2" v-if="empTotalPages > 1">
      <button class="arrow-btn" :disabled="currentPage <= 1" @click="currentPage = 1">‹‹</button>
      <button class="arrow-btn" :disabled="currentPage <= 1" @click="currentPage--">‹</button>
      
      <template v-for="page in visiblePages" :key="page">
        <button 
          v-if="page !== '...'"
          class="page-btn"
          :class="{ active: page === currentPage }"
          @click="currentPage = page">
          {{ page }}
        </button>
        <span v-else class="dots">...</span>
      </template>
      
      <button class="arrow-btn" :disabled="currentPage >= empTotalPages" @click="currentPage++">›</button>
      <button class="arrow-btn" :disabled="currentPage >= empTotalPages" @click="currentPage = empTotalPages">››</button>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import axios from "axios";
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

// Import dashboard components
import AttendingCard from '../../components/dashboard/AttendingCard.vue';
import NotAttendingCard from '../../components/dashboard/NotAttendingCard.vue';
import PendingCard from '../../components/dashboard/PendingCard.vue';
import DonutActualAttendance from '../../components/dashboard/DonutActualAttendance.vue';
import GraphEventParticipation from '../../components/dashboard/GraphEventParticipation.vue';

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  components: { 
    MagnifyingGlassIcon,
    AttendingCard,
    NotAttendingCard,
    PendingCard,
    DonutActualAttendance,
    GraphEventParticipation
  },
  data() {
    return {
      event: [],
      categories: [],
      catMap: {},
      search: "",
      showFilter: false,
      showSort: false,
      sortBy: "evn_date",
      sortOrder: "asc",
      page: 1,
      pageSize: 10,
      employees: [],
      empPage: 1,
      empPageSize: 10,
      showEmpSort: false,
      empSort: { value: 'name_az' },
      empSortOptions: [
        { value: 'name_az', label: 'ชื่อพนักงาน A–Z' },
        { value: 'name_za', label: 'ชื่อพนักงาน Z–A' },
        { value: 'department_az', label: 'ชื่อแผนก A–Z' },
        { value: 'department_za', label: 'ชื่อแผนก Z–A' },
        { value: 'team_az', label: 'ชื่อทีม A–Z' },
        { value: 'team_za', label: 'ชื่อทีม Z–A' },
        { value: 'position_az', label: 'ชื่อตำแหน่ง A–Z' },
        { value: 'position_za', label: 'ชื่อตำแหน Z–A' },
        { value: 'id_asc', label: 'รหัสพนักงาน น้อย–มาก' },
        { value: 'id_desc', label: 'รหัสพนักงาน มาก–น้อย' },
      ],
      // Selected event for dashboard analysis
      selectedEventId: null,
      selectAll: false,
      // Employee table states (replace modal)
      showEmployeeTable: false,
      employeeTableType: null, // 'attending', 'not-attending', 'pending'
      filteredEmployeesForTable: [],
      // Pagination for employee table
      currentPage: 1,
      itemsPerPage: 10,
      // Team filter for chart
      selectedTeamFilter: '',
      // Data for charts
      chartData: {
        totalParticipation: 367,
        attending: 61,
        notAttending: 12,
        pending: 31,
        departments: [
          { name: 'Engineering', attending: 45, notAttending: 15, pending: 25 },
          { name: 'Mobile App Developer', attending: 25, notAttending: 20, pending: 35 },
          { name: 'Security Engineer', attending: 70, notAttending: 10, pending: 25 },
          { name: 'UI/UX Designer', attending: 35, notAttending: 40, pending: 90 }
        ]
      },
      // Participation data for GraphEventParticipation component
      participationData: {
        departments: [
          { name: 'Technology', attending: 3, notAttending: 0, pending: 0 },
          { name: 'Administration', attending: 0, notAttending: 0, pending: 0 }
        ]
      }
    };
  },
  async created() {
  await Promise.all([this.fetchEvent(), this.fetchCategories(), this.fetchEmployees()]);
  },
  watch: {
    search()   { this.page = 1; },
    pageSize() { this.page = 1; },
    event()    { this.page = 1; },
    // Reset pagination when employee filter changes
    filteredEmployeesForTable() {
      this.currentPage = 1;
    },
    itemsPerPage() {
      this.currentPage = 1;
    },
  },
  computed: {
    normalized() {
      return this.event.map(e => ({
        ...e,
        evn_title: e.evn_title ?? e.evn_name ?? "",
        evn_cat_id: e.evn_cat_id ?? e.evn_category_id ?? "",
        cat_name: e.cat_name ?? e.category_name ?? this.catMap[String(e.evn_cat_id)] ?? "",
        evn_date: e.evn_date ?? "",
        evn_timestart: e.evn_timestart ?? "",
        evn_timeend: e.evn_timeend ?? "",
        evn_num_guest: Number(e.evn_num_guest ?? 0),
        evn_sum_accept: Number(e.evn_sum_accept ?? 0),
        evn_status: e.evn_status ?? "",
      }));
    },
    filtered() {
  const q = this.search.toLowerCase().trim();
  return this.normalized.filter(e => {
    const status = String(e.evn_status || '').toLowerCase();
    const matchStatus = status === 'upcoming' || status === 'done';
    // ค้นหาจากหลายฟิลด์
    const match =
      e.evn_title.toLowerCase().includes(q) ||
      (e.evn_name && e.evn_name.toLowerCase().includes(q)) ||
      (e.cat_name && e.cat_name.toLowerCase().includes(q)) ||
      (e.evn_date && e.evn_date.toLowerCase().includes(q));
    return matchStatus && (!q || match);
  });
},
    sorted() {
      const key = this.sortBy;
      const order = this.sortOrder;
      const arr = [...this.filtered];
      const parseVal = v => {
        if (key.includes('date')) return new Date(v || 0).getTime();
        if (['evn_num_guest','evn_sum_accept'].includes(key)) return Number(v) || 0;
        return (v ?? '').toString().toLowerCase();
      };
      arr.sort((a,b) => {
        const va = parseVal(a[key]), vb = parseVal(b[key]);
        if (va < vb) return order === "asc" ? -1 : 1;
        if (va > vb) return order === "asc" ?  1 : -1;
        return 0;
      });
      return arr;
    },
    totalPages() { return Math.ceil(this.sorted.length / this.pageSize); },
    paged() {
      const start = (this.page - 1) * this.pageSize;
      return this.sorted.slice(start, start + this.pageSize);
    },
    pageItems() {
      const total = this.totalPages || 1;
      const cur = this.page;
      const items = [];
      if (total <= 7) { for (let i=1;i<=total;i++) items.push({ type:'page', value:i }); return items; }
      const addPage = p => items.push({ type:'page', value:p });
      const addDots = () => items.push({ type:'dots' });
      addPage(1);
      if (cur > 3) addDots();
      const s = Math.max(2, cur-1), e = Math.min(total-1, cur+1);
      for (let p=s; p<=e; p++) addPage(p);
      if (cur < total-2) addDots();
      addPage(total);
      return items;
    },
    // Employee table pagination
    totalEmployees() {
      return this.filteredEmployeesForTable.length;
    },
    empTotalPages() {
      return Math.ceil(this.totalEmployees / this.itemsPerPage);
    },
    paginatedEmployees() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredEmployeesForTable.slice(start, end);
    },
    paginationText() {
      const start = this.totalEmployees > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
      const end = Math.min(this.currentPage * this.itemsPerPage, this.totalEmployees);
      return `${start}-${end} จาก ${this.totalEmployees} รายการ`;
    },
    employeePaginationText() {
      const start = this.totalEmployees > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
      const end = Math.min(this.currentPage * this.itemsPerPage, this.totalEmployees);
      return `${start}-${end} จาก ${this.totalEmployees} รายการ`;
    },
    eventPaginationText() {
      const start = this.sorted.length > 0 ? (this.page - 1) * this.pageSize + 1 : 0;
      const end = Math.min(this.page * this.pageSize, this.sorted.length);
      return `${start}-${end} จาก ${this.sorted.length} รายการ`;
    },
    visiblePages() {
      const pages = [];
      const total = this.empTotalPages;
      const current = this.currentPage;
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i);
        }
      } else {
        if (current <= 4) {
          for (let i = 1; i <= 5; i++) pages.push(i);
          pages.push('...');
          pages.push(total);
        } else if (current >= total - 3) {
          pages.push(1);
          pages.push('...');
          for (let i = total - 4; i <= total; i++) pages.push(i);
        } else {
          pages.push(1);
          pages.push('...');
          for (let i = current - 1; i <= current + 1; i++) pages.push(i);
          pages.push('...');
          pages.push(total);
        }
      }
      return pages;
    },
    empPaged() {
      let arr = [...this.employees];
      switch (this.empSort.value) {
        case 'name_az':
          arr.sort((a,b) => a.emp_firstname.localeCompare(b.emp_firstname)); break;
        case 'name_za':
          arr.sort((a,b) => b.emp_firstname.localeCompare(a.emp_firstname)); break;
        case 'department_az':
          arr.sort((a,b) => (a.department||'').localeCompare(b.department||'')); break;
        case 'department_za':
          arr.sort((a,b) => (b.department||'').localeCompare(a.department||'')); break;
        case 'team_az':
          arr.sort((a,b) => (a.team||'').localeCompare(b.team||'')); break;
        case 'team_za':
          arr.sort((a,b) => (b.team||'').localeCompare(a.team||'')); break;
        case 'position_az':
          arr.sort((a,b) => (a.position||'').localeCompare(b.position||'')); break;
        case 'position_za':
          arr.sort((a,b) => (b.position||'').localeCompare(a.position||'')); break;
        case 'id_asc':
          arr.sort((a,b) => (a.emp_id||'').localeCompare(b.emp_id||'')); break;
        case 'id_desc':
          arr.sort((a,b) => (b.emp_id||'').localeCompare(a.emp_id||'')); break;
      }
      const start = (this.empPage - 1) * this.empPageSize;
      return arr.slice(start, start + this.empPageSize);
    },
    empTotalPages() {
      return Math.ceil(this.employees.length / this.empPageSize);
    },

    // Get selected event data
    selectedEventData() {
      if (!this.selectedEventId) return null;
      return this.normalized.find(event => (event.id || event.evn_id) == this.selectedEventId);
    },
  },
  methods: {
    // Chart calculation methods (moved from computed)
    getAttendingProgress() {
      if (this.chartData.totalParticipation === 0) return 0;
      return Math.round((this.chartData.attending / this.chartData.totalParticipation) * 251);
    },
    
    getNotAttendingProgress() {
      if (this.chartData.totalParticipation === 0) return 0;
      return Math.round((this.chartData.notAttending / this.chartData.totalParticipation) * 251);
    },
    
    getPendingProgress() {
      if (this.chartData.totalParticipation === 0) return 0;
      return Math.round((this.chartData.pending / this.chartData.totalParticipation) * 251);
    },

    getAttendingPercentage() {
      return Math.round((this.chartData.attending / 100) * 251);
    },
    
    getNotAttendingPercentage() {
      return Math.round((this.chartData.notAttending / 100) * 251);
    },
    
    getPendingPercentage() {
      return Math.round((this.chartData.pending / 100) * 251);
    },

    getAttendancePercentage() {
      const total = this.chartData.attending + this.chartData.notAttending + this.chartData.pending;
      if (total === 0) return 0;
      return Math.round((this.chartData.attending / total) * 100);
    },

    async fetchEmployees() {
      try {
        const res = await axios.get("/get-employees", {
          params: {
            page: this.empPage,
            per_page: this.empPageSize
          }
        });
        if (res.data && res.data.data) {
          this.employees = res.data.data.map(e => ({
            id: e.id,
            emp_id: e.emp_id,
            emp_prefix: e.emp_prefix,
            emp_firstname: e.emp_firstname,
            emp_lastname: e.emp_lastname,
            emp_nickname: e.emp_nickname,
            emp_email: e.emp_email,
            emp_phone: e.emp_phone,
            position: e.position_name,
            department: e.department_name,
            team: e.team_name,
            emp_delete_status: e.emp_delete_status,
          }));
          this.empTotal = res.data.total || this.employees.length;
        } else {
          // Handle case where res.data might not be an array
          const dataArray = Array.isArray(res.data) ? res.data : [];
          this.employees = dataArray.map(e => ({
            id: e.id,
            emp_id: e.emp_id,
            emp_prefix: e.emp_prefix,
            emp_firstname: e.emp_firstname,
            emp_lastname: e.emp_lastname,
            emp_nickname: e.emp_nickname,
            emp_email: e.emp_email,
            emp_phone: e.emp_phone,
            position: e.position_name,
            department: e.department_name,
            team: e.team_name,
            emp_delete_status: e.emp_delete_status,
          }));
          this.empTotal = this.employees.length;
        }
      } catch (err) {
        console.error("fetchEmployees error", err);
        this.employees = [];
        this.empTotal = 0;
      }
    },
    async fetchEvent() {
      try {
        const res = await axios.get("/get-event", {
          params: {
            page: this.page,
            per_page: this.pageSize,
            search: this.search
          }
        });
        let allEvents = Array.isArray(res.data) ? res.data : (res.data?.data || []);
        this.event = allEvents.filter(ev => {
          const status = String(ev.evn_status || ev.status || '').toLowerCase();
          return status === 'upcoming' || status === 'done';
        });
        this.eventTotal = res.data.total || this.event.length;
      } catch (err) { 
        console.error("fetchEvent error", err); 
        this.event = []; 
        this.eventTotal = 0;
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get("/event-info");
        const cats = res.data?.categories || [];
        this.categories = cats;
        this.catMap = Object.fromEntries(cats.map(c => [String(c.id), c.cat_name]));
      } catch (err) { console.error("fetchCategories error", err); this.categories = []; this.catMap = {}; }
    },

    goToPage(p) {
      if (p < 1) p = 1;
      if (p > this.totalPages) p = this.totalPages || 1;
      this.page = p;
    },

    editEvent(id) { //ส่วนส่ง id ไปให้หน้า edit_event
      console.log("Edit event ID:", id);
      this.$router.push(`/edit-event/${id}`)
    },
    // ไว้ให้ปุ่มไม่ error (ต่อยอดภายหลังได้)
    toggleFilter() { this.showFilter = !this.showFilter; },
    toggleSort() { this.showSort = !this.showSort; },

    goToPage(p) { if (p < 1) p = 1; if (p > this.totalPages) p = this.totalPages || 1; this.page = p; },
    async deleteEvent(id) {
      if (confirm("Delete?")) {
        try { await axios.delete(`/event/${id}`); this.fetchEvent(); }
        catch (err) { console.error("Error deleting event", err); }
      }
    }
    ,
    formatDate(val) {
      if (!val) return 'N/A';
      const d = new Date(val); if (isNaN(d)) return val;
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
    goToEmpPage(p) {
      if (p < 1) p = 1;
      if (p > this.empTotalPages) p = this.empTotalPages || 1;
      this.empPage = p;
    },
    setEmpSort(value) {
      this.showEmpSort = false;
      const order = value.startsWith('-') ? 'desc' : 'asc';
      const key = value.replace(/^-/, '');
      this.empSort = { value: key, order };
      this.empPage = 1;
    },
    onViewReport() {
      // ฟังก์ชันสำหรับดูรายงาน
      console.log('View Report clicked');
    },
    onExport() {
      // ฟังก์ชันสำหรับ export ข้อมูล
      console.log('Export clicked');
    },

    // Event selection methods
    onEventSelect(event) {
      console.log('Event selected:', event);
      console.log('Event keys:', Object.keys(event));
      
      // ลองหา ID field ที่ถูกต้อง - ใช้ id แทน evn_id
      const eventId = event.id || event.evn_id;
      console.log('Event ID found:', eventId);
      
      if (!eventId) {
        console.error('No event ID found in:', event);
        return;
      }
      
      this.selectedEventId = eventId;
      console.log('selectedEventId set to:', this.selectedEventId);
      this.loadEventStatistics(eventId);
    },

    async loadEventStatistics(eventId) {
      try {
        console.log('Loading stats for event ID:', eventId);
        // Fetch event participants data 
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.get(`/event/${eventId}/participants`, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        const data = response.data;
        
        console.log('API Response:', data);
        
        // Update chart data with real statistics
        this.chartData = {
          attending: data.statistics?.attending || 0,
          notAttending: data.statistics?.not_attending || 0,
          pending: data.statistics?.pending || 0,
          totalParticipation: data.statistics?.total || 0,
          departments: this.chartData.departments || []
        };
        
        console.log('Updated chart data:', this.chartData);
      } catch (error) {
        console.error('Error loading event statistics:', error);
        
        // แสดงข้อมูล mock สำหรับทดสอบ
        this.chartData = {
          attending: 25,
          notAttending: 8,
          pending: 12,
          totalParticipation: 45,
          departments: this.chartData.departments || []
        };
        
        console.log('Using mock data due to API error');
        console.warn('API Error details:', error.response?.data || error.message);
      }
    },

    // Employee table methods (replace modal methods)
    async showEmployeesByStatus(status) {
      if (!this.selectedEventId) {
        alert('กรุณาเลือกกิจกรรมก่อน');
        return;
      }

      console.log('showEmployeesByStatus called with status:', status);
      this.employeeTableType = status;
      this.showEmployeeTable = true;
      
      try {
        // Fetch employees for selected event and status from our API
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.get(`/event/${this.selectedEventId}/participants`, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        console.log('API response for participants:', response.data);
        
        // Filter participants based on status
        let filteredParticipants = [];
        if (response.data && response.data.participants) {
          filteredParticipants = response.data.participants.filter(participant => {
            const apiStatus = this.mapStatusForAPI(status);
            return participant.con_answer === apiStatus;
          });
        }
        
        // Map to our expected employee format
        this.filteredEmployeesForTable = filteredParticipants.map(participant => ({
          id: participant.id,
          emp_id: participant.emp_id,
          emp_firstname: participant.emp_firstname,
          emp_lastname: participant.emp_lastname,
          emp_nickname: participant.emp_nickname,
          emp_phone: participant.emp_phone,
          position: participant.position_name || 'N/A',
          department: participant.department_name || 'N/A',
          team: participant.team_name || 'N/A',
          emp_delete_status: participant.emp_delete_status
        }));
        
        console.log(`Loaded ${this.filteredEmployeesForTable.length} employees for status: ${status}`);
        
      } catch (error) {
        console.error('Error loading employees:', error);
        console.error('Error details:', error.response?.data || error.message);
        
        // Use empty array if API fails
        this.filteredEmployeesForTable = [];
        alert('ไม่สามารถโหลดข้อมูลพนักงานได้ กรุณาลองใหม่อีกครั้ง');
      }
    },

    hideEmployeeTable() {
      this.showEmployeeTable = false;
      this.employeeTableType = null;
      this.filteredEmployeesForTable = [];
    },

    formatDate(dateString) {
      if (!dateString) return 'ไม่ระบุวันที่';
      
      const date = new Date(dateString);
      const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        locale: 'th-TH'
      };
      
      return date.toLocaleDateString('th-TH', options);
    },

    mapStatusForAPI(status) {
      const statusMap = {
        'attending': 'accept',
        'not-attending': 'denied', 
        'pending': 'invalid'
      };
      return statusMap[status] || 'invalid';
    },

    selectAllEvents() {
      // Implementation for select all checkbox if needed
      console.log('Select all events');
    }
  }
};
</script>

<style>
/* Simple table container */
.simple-table-container {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(34,197,94,0.06);
  margin-top: 2rem;
}


.employee-table .badge {
  min-width: 70px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: lowercase;
  background: #e5e7eb;
  color: #374151;
}
.employee-table .badge.enabled {
  background: #dcfce7;
  color: #166534;
}
.employee-table .badge.deleted {
  background: #fee2e2;
  color: #991b1b;
}
/* Layout grid สำหรับ dashboard */
.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  width: 100%;
}
/* Card สำหรับ Event */
.event-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
/* Card สำหรับ Summary/Graph */
.summary-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
/* Chart Styles */
.donut-chart {
  position: relative;
  width: 140px;
  height: 140px;
  margin: 0 auto;
}

.donut-chart.small {
  width: 100px;
  height: 100px;
}

.donut-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.donut-ring {
  stroke-width: 8;
}

.donut-segment {
  stroke-width: 8;
  stroke-linecap: round;
  transition: stroke-dasharray 0.6s ease;
}

.donut-chart-inner {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.chart-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.donut-chart.small .chart-number {
  font-size: 1.8rem;
}

.chart-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  margin-top: 2px;
}

.chart-icon {
  font-size: 1.2rem;
  margin-top: 0.25rem;
  color: #6b7280;
}

/* Bar Chart Styles */
.bar-chart-container {
  display: flex;
  justify-content: space-around;
  align-items: end;
  height: 180px;
  padding: 20px 10px;
  gap: 8px;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  max-width: 60px;
}

.bar-label {
  font-size: 0.7rem;
  color: #6b7280;
  margin-bottom: 8px;
  text-align: center;
  word-wrap: break-word;
  line-height: 1.2;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bars {
  display: flex;
  gap: 2px;
  align-items: end;
  height: 120px;
  justify-content: center;
}

.bar {
  width: 8px;
  border-radius: 4px 4px 0 0;
  transition: height 0.6s ease;
  min-height: 8px;
}

.bar.attending {
  background: linear-gradient(180deg, #f8bbd9 0%, #e91e63 100%);
}

.bar.not-attending {
  background: linear-gradient(180deg, #ffccbc 0%, #ff5722 100%);
}

.bar.pending {
  background: linear-gradient(180deg, #e1bee7 0%, #9c27b0 100%);
}

.summary-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  grid-template-rows: auto auto;
  gap: 24px;
  margin-bottom: 2rem;
}

/* Top row layout */
.actual-attendance-card {
  grid-column: 1;
  grid-row: 1;
}

.event-participation-card {
  grid-column: 2;
  grid-row: 1;
}

/* Bottom row: Status cards spanning full width */
.status-cards-row {
  grid-column: 1 / -1;
  grid-row: 2;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-top: 24px;
}

/* Responsive design */
@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
    gap: 20px;
  }
  
  .actual-attendance-card,
  .event-participation-card {
    grid-column: 1;
  }
  
  .actual-attendance-card {
    grid-row: 1;
  }
  
  .event-participation-card {
    grid-row: 2;
  }
  
  .status-cards-row {
    grid-row: 3;
    margin-top: 0;
  }
}

@media (max-width: 768px) {
  .status-cards-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

.summary-item {
  background: #fff;
  border-radius: 20px;
  padding: 1.2rem;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  display: flex;
  flex-direction: column;
}

.summary-item.wide {
  grid-column: 2;
  grid-row: 1 / 3;
}

.chart-container {
  position: relative;
}

.summary-title {
  font-weight: 700;
  font-size: 1rem;
  margin-bottom: 1rem;
  color: #374151;
  text-align: left;
}

.chart-wrapper {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bar-chart {
  height: 220px;
}

.chart-legend {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: block;
}

.legend-text {
  color: #4b5563;
  font-weight: 500;
}

.department-filter {
  margin-bottom: 1rem;
  text-align: right;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #fff;
  font-size: 0.875rem;
  color: #374151;
  outline: none;
}

.filter-select:focus {
  border-color: #e91e63;
  box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
}

/* Clickable Cards */
.clickable {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

/* Empty state styles */
.no-event-selected {
  padding: 60px 40px;
  text-align: center;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 2px dashed #dee2e6;
  border-radius: 16px;
  margin: 20px 0;
}

.icon-placeholder {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.6;
}

.no-event-selected h3 {
  color: #6c757d;
  margin-bottom: 12px;
  font-size: 1.5rem;
  font-weight: 600;
}

.text-muted {
  color: #adb5bd !important;
  font-size: 1rem;
  line-height: 1.5;
}

/* Employee Table Card */




.employee-table-header {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
  position: relative;
}

.employee-table-header::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 60px;
  height: 2px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 1px;
}

.close-table-btn {
  background: #f3f4f6;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  color: #6b7280;
  transition: all 0.2s;
}

.close-table-btn:hover {
  background: #e5e7eb;
  color: #374151;
}

input[type="radio"] {
  accent-color: #e91e63;
}
.employee-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
}
/* Divider ระหว่าง Card */
.card-divider {
  width: 100%;
  height: 48px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e0e7ef 100%);
  border-radius: 18px;
  margin: 2.5rem 0 2.5rem 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
/* Card สำหรับ Employee */
.employee-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
  margin-top: 2.5rem;
}
.employee-title {
  font-size: 2rem;
  font-weight: 700;
  color: #22c55e;
  margin-bottom: 1rem;
  letter-spacing: 0.03em;
  text-align: left;
  background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
/* สไตล์หัวข้อ Event ด้านบนตาราง */
.event-title {
  font-size: 2rem;
  font-weight: 700;
  color: #f43f5e;
  margin-bottom: 1rem;
  letter-spacing: 0.03em;
  text-align: left;
  background: linear-gradient(90deg, #f43f5e 0%, #f87171 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
/* ปุ่มใหม่สำหรับ View Report และ Export */
.custom-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  padding: 0.5em 1.2em;
  border-radius: 999px;
  font-weight: 500;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.custom-btn .icon {
  width: 1.2em;
  height: 1.2em;
  vertical-align: middle;
}
.report-btn {
  background: linear-gradient(90deg, #f43f5e 0%, #f87171 100%);
  color: #fff;
  margin-right: 0.5em;
}
.report-btn:hover {
  background: linear-gradient(90deg, #be185d 0%, #f43f5e 100%);
  box-shadow: 0 4px 16px rgba(244,63,94,0.12);
  transform: translateY(-2px) scale(1.03);
}
.export-btn {
  background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
  color: #fff;
}
.export-btn:hover {
  background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
  box-shadow: 0 4px 16px rgba(34,197,94,0.12);
  transform: translateY(-2px) scale(1.03);
}
/* ตัวช่วย layout */
.toolbar--pill { display:flex; align-items:center; gap:16px; margin-top:12px; }
.search-group { display:flex; align-items:center; gap:12px; flex:1; }

/* ช่องค้นหาแบบ pill */
.pill-input {
  height: 44px;
  width: 100%;
  padding: 0 16px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  outline: none;
  background: #fff;
}

/* ปุ่มไอคอน */
.icon-btn {
  width: 44px; height: 44px; border-radius: 999px;
  border: 1px solid #e5e7eb; background: #fff;
  display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
}
.icon-btn:hover { background:#f3f4f6; }
.icon-btn--solid { background:#e11d48; color:#fff; border:none; }
.icon-btn--solid:hover { background:#be123c; }

.icon { width:20px; height:20px; display:block; }
.icon-sm { width:18px; height:18px; }

/* ปุ่มตัวอักษร Filter/Sort */
.text-btn {
  height:44px; padding:0 8px; border:none; background:transparent;
  color:#334155; font-weight:500; display:inline-flex; align-items:center; gap:8px; cursor:pointer;
}
.text-btn:hover { color:#0f172a; }

/* ปุ่ม + Add New เป็น pill และชิดขวา */
.pill-add {
  margin-left:auto; height:44px; padding:0 18px; border-radius:999px;
  background:#e11d48; color:#fff; text-decoration:none; display:inline-flex; align-items:center; font-weight:600;
}
.pill-add:hover { background:#be123c; }

/* Summary */
.summary { font-size:12px; color:#666; margin-left:16px; }

/* Table */
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; margin-top:10px; }
thead th { position:sticky; top:0; z-index:1; }
.th { cursor:default; user-select:none; background:#f9fafb; font-weight:600; border-bottom:1px solid #e5e7eb; }
th, td { vertical-align:middle; padding:7px 10px; border-top:1px solid #eee; font-size:14px; }

/* Column widths */
.col-idx{ width:48px; text-align:center; }
.col-title{ width:26%; text-align:center; }
.col-cat{ width:14%; text-align:center; }
.col-date{ width:110px; text-align:center; white-space:nowrap; }
.col-time{ width:92px; text-align:center; white-space:nowrap; }
.col-num{ width:80px; text-align:center; }
.col-status{ width:110px; text-align:center; }
.col-action{ width:120px; text-align:center; }

/* Employee table columns */
.col-id{ width:100px; text-align:center; }
.col-name{ width:150px; text-align:left; }
.col-last{ width:150px; text-align:left; }
.col-nickname{ width:100px; text-align:center; }
.col-phone{ width:120px; text-align:center; }
.col-position{ width:140px; text-align:left; }
.col-department{ width:140px; text-align:left; }
.col-team{ width:120px; text-align:left; }
.col-event{ width:180px; text-align:left; }

/* Rows */
tbody tr:nth-child(odd){ background:#fff; }
tbody tr:nth-child(even){ background:#fafafa; }
tbody tr:hover{ background:#f3f4f6; }

/* Text overflow */
.truncate{ display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Action buttons */
.btn-link{ background:transparent; border:none; color:#0ea5e9; font-weight:600; cursor:pointer; padding:2px 4px; font-size:13px; }
.btn-link:hover{ text-decoration:underline; }
.btn-link.danger{ color:#ef4444; }

/* Badge */
.badge { display:inline-block; min-width:70px; padding:3px 8px; border-radius:999px; font-size:12px; font-weight:700; text-transform:lowercase; background:#e5e7eb; color:#374151; }
.badge.deleted{ background:#fee2e2; color:#991b1b; }
.badge.done{ background:#dcfce7; color:#166534; }
.badge.upcoming{ background:#f4ce99; color:#714601; }

/* Pager */
.pager2 { display:flex; gap:.5rem; align-items:center; justify-content:center; margin-top:14px; }
.page-btn { min-width:36px; height:36px; padding:0 10px; border-radius:10px; border:2px solid #b71c1c; background:transparent; color:#b71c1c; font-weight:700; line-height:1; }
.page-btn.active { background:#b71c1c; color:#fff; border-color:#b71c1c; }
.page-btn:hover:not(.active){ background:#fff5f5; }
.arrow-btn { width:36px; height:36px; border-radius:10px; border:none; background:#b71c1c; color:#fff; font-weight:700; }
.arrow-btn:disabled{ opacity:.5; cursor:not-allowed; }
.dots{ padding:0 6px; color:#b71c1c; font-weight:700; }

.employee-table th.col-idx,
.employee-table th.col-id,
.employee-table th.col-phone {
  text-align: center;
}
.employee-table th.col-prefix,
.employee-table th.col-name,
.employee-table th.col-last,
.employee-table th.col-nickname,
.employee-table th.col-email,
.employee-table th.col-position,
.employee-table th.col-department,
.employee-table th.col-team {
  text-align: left;
}
.employee-table td.col-idx,
.employee-table td.col-id,
.employee-table td.col-phone {
  text-align: center;
}
.employee-table td.col-prefix,
.employee-table td.col-name,
.employee-table td.col-last,
.employee-table td.col-nickname,
.employee-table td.col-email,
.employee-table td.col-position,
.employee-table td.col-department,
.employee-table td.col-team {
  text-align: left;
}

/* Sort dropdown */
.sort-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 100;
  width: 200px;
  margin-top: 4px;
}
.sort-title {
  font-weight: 600;
  padding: 10px;
  font-size: 14px;
  color: #111;
  border-bottom: 1px solid #f3f4f6;
}
.sort-item {
  display: block;
  padding: 10px;
  font-size: 14px;
  color: #333;
  cursor: pointer;
  transition: background 0.2s;
}
.sort-item:hover {
  background: #f3f4f6;
}
.sort-item.active {
  background: #e0f2fe;
  color: #0c4a6e;
  font-weight: 500;
}

/* Sort dropdown for employee table */
.sort-dropdown {
  position: absolute;
  top: 44px;
  left: 0;
  min-width: 220px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.10);
  padding: 8px 0;
  z-index: 10;
}
.sort-title {
  font-weight: 700;
  color: #b71c1c;
  padding: 8px 16px 4px 16px;
  font-size: 15px;
}
.sort-item {
  width: 100%;
  text-align: left;
  background: none;
  border: none;
  padding: 8px 16px;
  font-size: 15px;
  color: #222;
  cursor: pointer;
}
.sort-item.active {
  background: #fee2e2;
  color: #b71c1c;
  font-weight: 700;
}
.sort-item:hover:not(.active) {
  background: #f9fafb;
}

/* Employee table footer */
.employee-table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 0 1rem 0;
  margin-top: 1rem;
}

.table-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #333;
}

.page-size-select {
  padding: 4px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background: white;
  font-size: 14px;
  color: #333;
  cursor: pointer;
  min-width: 50px;
}

.page-size-select:focus {
  outline: none;
  border-color: #b71c1c;
  box-shadow: 0 0 0 2px rgba(183, 28, 28, 0.1);
}

/* Event table footer with centered pagination */
.event-table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 0 1rem 0;
  margin-top: 1rem;
  position: relative;
}

.event-table-footer .pager2 {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.event-table-footer .table-info {
  z-index: 1;
}

/* Status Cards - New Design */
.status-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
  transition: all 0.3s ease;
  cursor: pointer;
}

.status-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.attending-card {
  background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
  border-left: 4px solid #16a34a;
}

.not-attending-card {
  background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
  border-left: 4px solid #dc2626;
}

.pending-card {
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  border-left: 4px solid #2563eb;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.card-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.attending-card .card-icon {
  color: #16a34a;
}

.not-attending-card .card-icon {
  color: #dc2626;
}

.pending-card .card-icon {
  color: #2563eb;
}

.icon {
  width: 24px;
  height: 24px;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.card-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.percentage {
  font-size: 36px;
  font-weight: 700;
  line-height: 1;
}

.attending-card .percentage {
  color: #16a34a;
}

.not-attending-card .percentage {
  color: #dc2626;
}

.pending-card .percentage {
  color: #2563eb;
}

.progress-ring {
  position: relative;
  width: 80px;
  height: 80px;
}

.progress-svg {
  width: 80px;
  height: 80px;
  transform: rotate(-90deg);
}

.progress-bg {
  stroke-linecap: round;
}

.progress-bar {
  stroke-linecap: round;
  transition: stroke-dasharray 0.6s ease;
}

.progress-number {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.card-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6b7280;
}

.bullet {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.bullet.green {
  background: #16a34a;
}

.bullet.red {
  background: #dc2626;
}

.bullet.blue {
  background: #2563eb;
}

.count {
  flex: 1;
}

.view-link {
  color: #3b82f6;
  font-weight: 500;
  transition: color 0.2s;
}

.view-link:hover {
  color: #1d4ed8;
}

/* Chart Header */
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.team-filter select {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
}

.team-filter select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Attendance Info */
.attendance-info {
  margin-top: 16px;
}

.attendance-stats {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6b7280;
}

.attendance-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #16a34a;
}

.attendance-text {
  font-weight: 500;
}

</style>
