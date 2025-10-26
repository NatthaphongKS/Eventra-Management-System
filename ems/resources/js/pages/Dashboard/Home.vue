<!-- pages/event_page.vue -->
<template>

<div class="dashboard-grid">
  
  <!-- Event Table Section -->
  <div class="card event-card">
     <!--created toolbar pill-->

    <div class="toolbar toolbar--pill">
      <!-- SearchBar Component -->
      <SearchBar 
        v-model="search" 
        placeholder="Search events..." 
        @search="handleSearch"
        class="flex-1"
      />

      <!-- EventFilter Component -->
      <EventFilter 
        v-model="filterValue"
        :categories="categories"
        @update:modelValue="handleFilter"
      />

      <!-- EventSort Component -->
      <EventSort 
        v-model="sortValue"
        @update:modelValue="handleSort"
      />

      <Button 
        variant="light" 
        icon="download" 
        shape="pill"
        class="h-10 min-w-[120px] flex items-center justify-center"
        @click="onViewReport"
      >
        Export
      </Button>
      <Button 
        variant="danger" 
        shape="pill"
        class="h-10 min-w-[120px] flex items-center justify-center ml-2"
        @click="onExport"
      >
        Show Data
      </Button>
      
    </div>

    <!-- Button Testing Section - UI Design Matching -->
    <div class="ui-button-showcase">
      <div class="showcase-title">Button Showcase - Design Matching</div>
      
      <!-- Top Row Buttons -->
      <div class="ui-button-row top-row">
        <Button 
          variant="ok"
        >
          OK
        </Button>
        
        <Button 
          variant="cancel"
        >
          Cancel
        </Button>
        
        <Button 
          variant="confirm"
        >
          Confirm
        </Button>
        
        <Button 
          variant="create"
        >
          Create
        </Button>
        
        <Button 
          variant="back"
        >
          Back
        </Button>
      </div>

      <!-- Bottom Row Buttons -->
      <div class="ui-button-row bottom-row">
        <Button 
          variant="add"
        >
          Add
        </Button>
        
        <Button 
          variant="export"
        >
          Export
        </Button>
        
        <Button 
          variant="show-data"
        >
          Show Data
        </Button>
      </div>
      
      <!-- Export Button Label -->
      <div class="export-label">Export Button</div>
    </div>
    <div class="event-table-wrap">
      <table class="event-table">
        <thead>
          <tr>
            <th class="event-th evt-col-idx">
              <input type="checkbox" @change="selectAllEvents" v-model="selectAll" />
            </th>
            <th class="event-th evt-col-id">Event ID</th>
            <th class="event-th evt-col-title">Event</th>
            <th class="event-th evt-col-cat">Category</th>
            <th class="event-th evt-col-date">Date (D/M/Y)</th>
            <th class="event-th evt-col-time">Time</th>
            <th class="event-th evt-col-num">Invited</th>
            <th class="event-th evt-col-num">Accepted</th>
            <th class="event-th evt-col-status">Status</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(ev, i) in paged" :key="ev.id" 
              :class="['event-row', { 'selected-row': selectedEventId === (ev.id || ev.evn_id) }]">
            <td class="event-td evt-col-idx">
              <input 
                type="radio" 
                :value="ev.id || ev.evn_id" 
                :checked="selectedEventId === (ev.id || ev.evn_id)"
                @change="onEventSelect(ev)"
                name="selectedEvent"
              />
            </td>
            <td class="event-td evt-col-id">{{ ev.id || ev.evn_id || 'N/A' }}</td>
            <td class="event-td evt-col-title">{{ ev.evn_title || 'N/A' }}</td>
            <td class="event-td evt-col-cat">{{ ev.cat_name || 'N/A' }}</td>
            <td class="event-td evt-col-date">{{ formatDate(ev.evn_date) }}</td>
            <td class="event-td evt-col-time">
              {{ ev.evn_timestart ? ev.evn_timestart.slice(0,5) : '??:??' }} -
              {{ ev.evn_timeend ? ev.evn_timeend.slice(0,5) : '??:??' }}
            </td>
            <td class="event-td evt-col-num">{{ ev.evn_num_guest ?? '0' }}</td>
            <td class="event-td evt-col-num">{{ ev.evn_sum_accept ?? 'N/A' }}</td>
            <td class="event-td evt-col-status">
              <span :class="['badge', ev.evn_status]">{{ ev.evn_status || 'N/A' }}</span>
            </td>
          </tr>

          <tr v-if="paged.length === 0">
            <td colspan="9" class="event-td no-data">No data found</td>
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
          :attending="chartData.attending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          :loading="isLoading"
          :isClickable="true"
          @showAttendingEmployees="showEmployeesByStatus('attending')"
        />
        <NotAttendingCard 
          :notAttending="chartData.notAttending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          :loading="isLoading"
          :isClickable="true"
          @showNotAttendingEmployees="showEmployeesByStatus('not-attending')"
        />
        <PendingCard 
          :pending="chartData.pending || 0" 
          :total="(chartData.attending + chartData.notAttending + chartData.pending) || 0"
          :loading="isLoading"
          :isClickable="true"
          @showPendingEmployees="showEmployeesByStatus('pending')"
        />
      </div> <!-- Close status-cards-row -->
    </div>
  </div>

  <!-- Employee Table Section - แสดงเมื่อกดการ์ด -->
  <div v-if="showEmployeeTable && selectedEventId" class="employee-table-container">
    <div class="employee-table-wrap">
      <table class="employee-table">
        <thead>
          <tr>
            <th class="employee-th emp-col-idx">#</th>
            <th class="employee-th emp-col-id">ID</th>
            <th class="employee-th emp-col-name">Name</th>
            <th class="employee-th emp-col-nickname">Nickname</th>
            <th class="employee-th emp-col-phone">Phone</th>
            <th class="employee-th emp-col-department">Department</th>
            <th class="employee-th emp-col-team">Team</th>
            <th class="employee-th emp-col-position">Position</th>
            <th class="employee-th emp-col-event">Event</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(emp, i) in paginatedEmployees" :key="emp.id" class="employee-row">
            <td class="employee-td emp-col-idx">{{ ((currentPage - 1) * itemsPerPage) + i + 1 }}</td>
            <td class="employee-td emp-col-id">{{ emp.emp_id }}</td>
            <td class="employee-td emp-col-name">{{ emp.emp_firstname }}</td>
            <td class="employee-td emp-col-nickname">{{ emp.emp_nickname }}</td>
            <td class="employee-td emp-col-phone">{{ emp.emp_phone }}</td>
            <td class="employee-td emp-col-department">{{ emp.department }}</td>
            <td class="employee-td emp-col-team">{{ emp.team || 'N/A' }}</td>
            <td class="employee-td emp-col-position">{{ emp.position }}</td>
            <td class="employee-td emp-col-event">{{ selectedEventData?.evn_title || 'N/A' }}</td>
          </tr>
          <tr v-if="paginatedEmployees.length === 0">
            <td colspan="9" class="employee-td no-data">No data found</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Employee table info and pagination -->
    <div class="employee-table-footer">
      <div class="employee-table-info">
        แสดง 
        <select v-model="itemsPerPage" class="employee-page-size-select">
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
import Button from "../../components/Button.vue";
import SearchBar from "../../components/SearchBar.vue";
import EventFilter from "../../components/IndexEvent/EventFilter.vue";
import EventSort from "../../components/IndexEvent/EventSort.vue";

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  components: { 
    MagnifyingGlassIcon,
    AttendingCard,
    NotAttendingCard,
    PendingCard,
    DonutActualAttendance,
    GraphEventParticipation,
    Button,
    SearchBar,
    EventFilter,
    EventSort
  },
  data() {
    return {
      event: [],
      categories: [],
      catMap: {},
      search: "",
      sortBy: "evn_date",
      sortOrder: "asc",
      page: 1,
      pageSize: 10,
      
      // Filter และ Sort values สำหรับ components ใหม่
      filterValue: {
        category: new Set(),
        status: new Set()
      },
      sortValue: {
        id: 'date_desc',
        key: 'evn_date',
        order: 'desc',
        type: 'date'
      },
      employees: [],
      empPage: 1,
      empPageSize: 10,
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
      // Button testing data
      loadingTest: false,
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
        // Status filter
        const status = String(e.evn_status || '').toLowerCase();
        const matchStatus = status === 'upcoming' || status === 'done';
        
        // Category filter
        const categoryFilter = this.filterValue.category;
        const matchCategory = categoryFilter.size === 0 || categoryFilter.has(String(e.evn_cat_id));
        
        // Status filter from component
        const statusFilter = this.filterValue.status;
        const matchStatusFilter = statusFilter.size === 0 || statusFilter.has(status);
        
        // Search text filter
        const matchSearch = !q || 
          e.evn_title.toLowerCase().includes(q) ||
          (e.evn_name && e.evn_name.toLowerCase().includes(q)) ||
          (e.cat_name && e.cat_name.toLowerCase().includes(q)) ||
          (e.evn_date && e.evn_date.toLowerCase().includes(q));
        
        return matchStatus && matchCategory && matchStatusFilter && matchSearch;
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

    // Get selected event data
    selectedEventData() {
      if (!this.selectedEventId) return null;
      return this.normalized.find(event => (event.id || event.evn_id) == this.selectedEventId);
    },
  },
  methods: {
    // Search handling
    handleSearch(searchValue) {
      this.search = searchValue;
      // Additional search logic if needed
    },

    // Filter handling
    handleFilter(filterData) {
      this.filterValue = filterData;
      this.page = 1; // Reset to first page when filtering
      // Additional filter logic if needed
    },

    // Sort handling  
    handleSort(sortData) {
      this.sortValue = sortData;
      this.sortBy = sortData.key;
      this.sortOrder = sortData.order;
      this.page = 1; // Reset to first page when sorting
    },

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
      this.$router.push(`/edit-event/${id}`)
    },

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
      const order = value.startsWith('-') ? 'desc' : 'asc';
      const key = value.replace(/^-/, '');
      this.empSort = { value: key, order };
      this.empPage = 1;
    },
    onViewReport() {
      // ฟังก์ชันสำหรับดูรายงาน
    },
    onExport() {
      // ฟังก์ชันสำหรับ export ข้อมูล
    },
    onAddEvent() {
      // ฟังก์ชันสำหรับเพิ่ม event ใหม่
      console.log('Add Event clicked!');
      alert('Add Event button clicked! 🎉');
    },

    // Event selection methods
    onEventSelect(event) {
      console.log('Event selected:', event);
      console.log('Event keys:', Object.keys(event));
      
      // ลองหา ID field ที่ถูกต้อง - ใช้ id แทน evn_id
      const eventId = event.id || event.evn_id;
      
      if (!eventId) {
        console.error('No event ID found in:', event);
        return;
      }
      
      this.selectedEventId = eventId;
      this.loadEventStatistics(eventId);
    },

    async loadEventStatistics(eventId) {
      this.isLoading = true;
      try {
        // Fetch event participants data using correct API endpoint
        const response = await axios.get(`/api/event/${eventId}/participants`);
        
        console.log('Event statistics response:', response.data);
        
        if (response.data.success) {
          const statistics = response.data.data.statistics;
          
          // Update chart data with real statistics
          this.chartData = {
            attending: statistics.attending || 0,
            notAttending: statistics.not_attending || 0,
            pending: statistics.pending || 0
          };
          
          // Update participation data for chart
          this.participationData = {
            labels: ['เข้าร่วม', 'ไม่เข้าร่วม', 'รอตอบกลับ'],
            datasets: [{
              data: [
                this.chartData.attending,
                this.chartData.notAttending,
                this.chartData.pending
              ],
              backgroundColor: ['#4CAF50', '#F44336', '#FF9800']
            }]
          };
          
          console.log('Updated chart data:', this.chartData);
        } else {
          console.error('Failed to load event statistics:', response.data.message);
        }
      } catch (error) {
        console.error('Error loading event statistics:', error);
        // Reset to default values on error
        this.chartData = { attending: 0, notAttending: 0, pending: 0 };
        this.participationData = {
          labels: ['เข้าร่วม', 'ไม่เข้าร่วม', 'รอตอบกลับ'],
          datasets: [{ data: [0, 0, 0], backgroundColor: ['#4CAF50', '#F44336', '#FF9800'] }]
        };
      } finally {
        this.isLoading = false;
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
        const response = await axios.get(`/api/event/${this.selectedEventId}/participants`);
        
        console.log('API response for participants:', response.data);
        
        // Check if API call was successful
        if (!response.data.success) {
          throw new Error(response.data.message || 'Failed to fetch participants');
        }
        
        // Get participants from API response
        const participants = response.data.data.participants || [];
        
        // Filter participants based on status
        let filteredParticipants = [];
        if (participants.length > 0) {
          filteredParticipants = participants.filter(participant => {
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
    },

    // Button testing methods
    testClick(buttonType) {
      console.log(`Button clicked: ${buttonType}`);
      alert(`🎯 ${buttonType.charAt(0).toUpperCase() + buttonType.slice(1)} button clicked!`);
    },

    testLoading() {
      this.loadingTest = true;
      setTimeout(() => {
        this.loadingTest = false;
        alert('✅ Loading test completed!');
      }, 2000);
    }
  }
};
</script>

<style>
/* Event Table Styling - Similar to Employee Table */
.event-table-wrap {
  overflow-x: auto;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  margin-top: 10px;
}

.event-table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  background: #ffffff;
}

/* Event Table Header */
.event-th {
  background: #f8fafc;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  padding: 16px 12px;
  border-bottom: 2px solid #e5e7eb;
  border-right: none;
  position: sticky;
  top: 0;
  z-index: 1;
}

/* Event Table Data */
.event-td {
  padding: 14px 12px;
  text-align: center;
  border-bottom: 1px solid #f1f5f9;
  border-right: none;
  font-size: 14px;
  color: #374151;
  vertical-align: middle;
}

/* Event Table Rows */
.event-row:nth-child(even) {
  background-color: #f9fafb;
}

.event-row:nth-child(odd) {
  background-color: #ffffff;
}

.event-row:hover {
  background-color: #f3f4f6;
  transition: background-color 0.2s ease;
}

/* Selected Row Styling */
.event-row.selected-row {
  background-color: #fee2e2 !important;
  border-left: 4px solid #dc2626;
}

.event-row.selected-row:hover {
  background-color: #fecaca !important;
}

/* No Data Row for Event Table */
.event-td.no-data {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 24px;
  background-color: #f9fafb;
}

/* Event Table Column Widths */
.evt-col-idx {
  width: 60px;
  min-width: 60px;
}

.evt-col-id {
  width: 80px;
  min-width: 80px;
}

.evt-col-title {
  width: 200px;
  min-width: 200px;
}

.evt-col-cat {
  width: 140px;
  min-width: 140px;
}

.evt-col-date {
  width: 120px;
  min-width: 120px;
}

.evt-col-time {
  width: 140px;
  min-width: 140px;
}

.evt-col-num {
  width: 80px;
  min-width: 80px;
}

.evt-col-status {
  width: 110px;
  min-width: 110px;
}

/* Employee Table Styling */
.employee-table-container {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-top: 1.5rem;
  border: 1px solid #e5e7eb;
}

.employee-table-wrap {
  overflow-x: auto;
}

.employee-table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  background: #ffffff;
}

/* Employee Table Header */
.employee-th {
  background: #f8fafc;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  padding: 16px 12px;
  border-bottom: 2px solid #e5e7eb;
  border-right: none;
  position: sticky;
  top: 0;
  z-index: 1;
}

/* Employee Table Data */
.employee-td {
  padding: 14px 12px;
  text-align: center;
  border-bottom: 1px solid #f1f5f9;
  border-right: none;
  font-size: 14px;
  color: #374151;
  vertical-align: middle;
}

/* Employee Table Rows */
.employee-row:nth-child(even) {
  background-color: #f9fafb;
}

.employee-row:nth-child(odd) {
  background-color: #ffffff;
}

.employee-row:hover {
  background-color: #f3f4f6;
  transition: background-color 0.2s ease;
}

/* No Data Row */
.employee-td.no-data {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 24px;
  background-color: #f9fafb;
}

/* Column Specific Styling */
.emp-col-idx {
  width: 60px;
  min-width: 60px;
}

.emp-col-id {
  width: 80px;
  min-width: 80px;
}

.emp-col-name {
  width: 150px;
  min-width: 150px;
}

.emp-col-nickname {
  width: 100px;
  min-width: 100px;
}

.emp-col-phone {
  width: 120px;
  min-width: 120px;
}

.emp-col-department,
.emp-col-team {
  width: 160px;
  min-width: 160px;
}

.emp-col-position {
  width: 180px;
  min-width: 180px;
}

.emp-col-event {
  width: 200px;
  min-width: 200px;
}

/* Responsive Design for Event Table */
@media (max-width: 1024px) {
  .event-table-wrap {
    margin: 10px -1rem 0 -1rem;
    border-radius: 0;
    border-left: none;
    border-right: none;
  }
  
  .event-th,
  .event-td {
    padding: 10px 8px;
    font-size: 13px;
  }
  
  .evt-col-title {
    width: 160px;
    min-width: 160px;
  }
  
  .evt-col-cat,
  .evt-col-time {
    width: 120px;
    min-width: 120px;
  }
}

@media (max-width: 768px) {
  .event-th,
  .event-td {
    padding: 8px 6px;
    font-size: 12px;
  }
  
  .evt-col-title {
    width: 140px;
    min-width: 140px;
  }
  
  .evt-col-cat,
  .evt-col-time {
    width: 100px;
    min-width: 100px;
  }
  
  .evt-col-date {
    width: 90px;
    min-width: 90px;
  }
}

/* Text Overflow Protection for Event Table */
.event-td {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Hover Effects for Event Table */
.event-table {
  transition: all 0.3s ease;
}

.event-row {
  transition: all 0.2s ease;
}

/* Responsive Design for Employee Table */
@media (max-width: 1024px) {
  .employee-table-container {
    margin: 1rem -1rem 0 -1rem;
    border-radius: 0;
    border-left: none;
    border-right: none;
  }
  
  .employee-th,
  .employee-td {
    padding: 10px 8px;
    font-size: 13px;
  }
  
  .emp-col-position,
  .emp-col-department,
  .emp-col-team,
  .emp-col-event {
    width: 120px;
    min-width: 120px;
  }
}

@media (max-width: 768px) {
  .employee-th,
  .employee-td {
    padding: 8px 6px;
    font-size: 12px;
  }
  
  .emp-col-name {
    width: 120px;
    min-width: 120px;
  }
  
  .emp-col-department,
  .emp-col-team {
    width: 110px;
    min-width: 110px;
  }
  
  .emp-col-position {
    width: 140px;
    min-width: 140px;
  }
  
  .emp-col-event {
    width: 150px;
    min-width: 150px;
  }
  
  .employee-table-footer {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .employee-table-info {
    justify-content: center;
  }
}

/* Text Overflow Protection */
.employee-td {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Hover Effects */
.employee-table {
  transition: all 0.3s ease;
}

.employee-row {
  transition: all 0.2s ease;
}

/* Focus States */
.employee-table:focus-within {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}





.export-label {
  text-align: center;
  color: #e5e7eb;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  font-style: italic;
  opacity: 0.8;
}

.employee-table .badge {
  min-width: 70px;
  padding: 3px 8px;
  border-radius: 4px;
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
  background: #f5f5f5; /* neutral-100 ตาม Color Palette */
  min-height: 100vh;
  padding: 1rem;
}
/* Card สำหรับ Event */
.event-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  padding: 2rem 2rem 1.5rem 2rem;
  border: 1px solid #f1f5f9;
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
  transform: rotate(0deg);
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

.text-muted {
  color: #adb5bd !important;
  font-size: 1rem;
  line-height: 1.5;
}

/* Employee Table Card */




/* Input styling */
input[type="radio"],
input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 16px;
  height: 16px;
  border: 2px solid #d1d5db;
  border-radius: 3px;
  background: #fff;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
}

input[type="radio"]:checked,
input[type="checkbox"]:checked {
  background: #dc2626;
  border-color: #dc2626;
}

input[type="radio"]:checked::after,
input[type="checkbox"]:checked::after {
  content: '';
  position: absolute;
  top: 1px;
  left: 4px;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

input[type="radio"]:hover,
input[type="checkbox"]:hover {
  border-color: #9ca3af;
  box-shadow: 0 0 0 2px rgba(156, 163, 175, 0.1);
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

/* Legacy export-btn (keeping for compatibility) */
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
.col-id{ width:80px; text-align:center; }
.col-title{ width:24%; text-align:center; }
.col-cat{ width:12%; text-align:center; }
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

/* Selected row highlighting */
tbody tr.selected-row { 
  background: #fee2e2 !important; 
  border-left: 4px solid #dc2626;
}
tbody tr.selected-row:hover { 
  background: #fecaca !important; 
}

/* Text overflow */
.truncate{ display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Action buttons */
.btn-link{ background:transparent; border:none; color:#0ea5e9; font-weight:600; cursor:pointer; padding:2px 4px; font-size:13px; }
.btn-link:hover{ text-decoration:underline; }
.btn-link.danger{ color:#ef4444; }

/* Badge - ใช้สีตาม Color Palette */
.badge { 
  display:inline-block; 
  min-width:70px; 
  padding:3px 8px; 
  border-radius:4px; 
  font-size:12px; 
  font-weight:700; 
  text-transform:lowercase; 
  background:#f5f5f5; /* neutral-100 */
  color:#525252; /* neutral-600 */
}
.badge.deleted{ 
  background:#fecaca; /* red-100 */
  color:#991b1b; /* red-800 */
}
.badge.done{ 
  background:#bbf7d0; /* green-200 */
  color:#059669; /* green-600 */
}
.badge.upcoming{ 
  background:#fef3c7; /* yellow-200 */
  color:#d97706; /* yellow-400 */
}

/* Pager - ใช้สี red-700 ตาม Color Palette */
.pager2 { display:flex; gap:.5rem; align-items:center; justify-content:center; margin-top:14px; }
.page-btn { 
  min-width:36px; 
  height:36px; 
  padding:0 10px; 
  border-radius:10px; 
  border:2px solid #b91c1c; /* red-700 */
  background:transparent; 
  color:#b91c1c; /* red-700 */
  font-weight:700; 
  line-height:1; 
}
.page-btn.active { 
  background:#b91c1c; /* red-700 */
  color:#fff; 
  border-color:#b91c1c; /* red-700 */
}
.page-btn:hover:not(.active){ 
  background:#fecaca; /* red-100 */
}
.arrow-btn { 
  width:36px; 
  height:36px; 
  border-radius:10px; 
  border:none; 
  background:#b91c1c; /* red-700 */
  color:#fff; 
  font-weight:700; 
}
.arrow-btn:disabled{ opacity:.5; cursor:not-allowed; }
.dots{ 
  padding:0 6px; 
  color:#b91c1c; /* red-700 */
  font-weight:700; 
}

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

/* Employee Table Footer */
.employee-table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: #f8fafc;
  border-top: 1px solid #e5e7eb;
  border-radius: 0 0 16px 16px;
}

.employee-table-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
  font-weight: 500;
}

.employee-page-size-select {
  padding: 6px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
  min-width: 60px;
  transition: all 0.2s ease;
}

.employee-page-size-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.employee-page-size-select:hover {
  border-color: #9ca3af;
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
  border-color: #dc2626;
  box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1);
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
  transform: rotate(0deg);
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
