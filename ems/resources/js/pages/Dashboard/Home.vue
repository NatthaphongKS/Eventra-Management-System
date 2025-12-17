<!-- pages/event_page.vue -->
<template>

<!-- Event Table Section - Refactored to match EventPage -->
<section class="p-0">
  <div class="mt-3 mb-1 flex items-center gap-3">
    <!-- SearchBar -->
    <div class="flex flex-1">
      <SearchBar 
        v-model="searchInput" 
        placeholder="Search event..." 
        @search="applySearch"
        class="[&_input]:h-[44px] [&_input]:text-sm [&_button]:h-10 [&_button]:w-10 [&_svg]:w-5 [&_svg]:h-5"
      />
    </div>

    <!-- Date Icon Button -->
    <div class="relative mt-6">
      <input 
        type="date" 
        v-model="selectedDate"
        @change="filterByDate"
        id="dateInput"
        class="absolute opacity-0 w-11 h-11 cursor-pointer"
      />
      <label for="dateInput" class="flex items-center justify-center w-11 h-11 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </label>
    </div>

    <!-- Filter -->
    <EventFilter 
      v-model="filters" 
      :categories="categories" 
      :status-options="statusOptions"
      @update:modelValue="applyFilter" 
      class="mt-6" 
    />
    
    <!-- Sort -->
    <EventSort 
      v-model="selectedSort" 
      :options="sortOptions" 
      @change="onPickSort" 
      class="mt-6" 
    />
    
    <!-- Export Button -->
    <button 
      class="inline-flex h-11 items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 mt-6 transition-colors"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
      </svg>
      Export
    </button>
    
    <!-- Show Data Button -->
    <button 
      class="ml-auto inline-flex h-11 items-center rounded-full bg-[#b91c1c] px-6 font-semibold text-white hover:bg-[#991b1b] focus:outline-none focus:ring-2 focus:ring-red-300 mt-6 transition-colors"
    >
      Show Data
    </button>
  </div>

  <!-- DataTable -->
  <DataTable 
    :rows="paged" 
    :columns="eventTableColumns" 
    :loading="false" 
    :total-items="sorted.length"
    :page-size-options="[10, 20, 50, 100]" 
    :page="page" 
    :pageSize="pageSize" 
    :sortKey="sortBy"
    :sortOrder="sortOrder" 
    @update:page="page = $event" 
    @update:pageSize="pageSize = $event; page = 1;" 
    @sort="handleClientSort" 
    row-key="id" 
    :show-row-number="false" 
    :row-class="getRowClass"
    class="mt-4">
    
    <!-- Header checkbox for select all -->
    <template #header-checkbox>
      <input 
        type="checkbox"
        :checked="selectAll"
        @change="selectAllEvents"
        style="cursor: pointer; width: 16px; height: 16px;"
      />
    </template>
    
    <!-- Checkbox column for multi-select -->
    <template #cell-checkbox="{ row }">
      <input 
        type="checkbox"
        :checked="selectedEventIds.has(row.id || row.evn_id)"
        @change="toggleEventSelection(row)"
        style="cursor: pointer; width: 16px; height: 16px;"
      />
    </template>

    <!-- Row number column -->
    <template #cell-row_number="{ value }">
      {{ value }}
    </template>

    <!-- Title cell (clickable) -->
    <template #cell-evn_title="{ row, value }">
      <span role="button" tabindex="0"
        class="block w-full h-full pl-3 py-2 text-slate-800 font-medium truncate hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
        @click="goDetails(row.id)" 
        @keydown.enter.prevent="goDetails(row.id)"
        @keydown.space.prevent="goDetails(row.id)" 
        title="ดูรายละเอียด">
        {{ value }}
      </span>
    </template>

    <!-- Category cell (clickable) -->
    <template #cell-cat_name="{ row, value }">
      <span role="button" tabindex="0"
        class="block w-full h-full pl-3 py-2 hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
        @click="goDetails(row.id)" 
        @keydown.enter.prevent="goDetails(row.id)"
        @keydown.space.prevent="goDetails(row.id)">
        {{ value }}
      </span>
    </template>

    <!-- Invited cell (clickable) -->
    <template #cell-evn_num_guest="{ row, value }">
      <span role="button" tabindex="0"
        class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
        @click="goDetails(row.id)" 
        @keydown.enter.prevent="goDetails(row.id)"
        @keydown.space.prevent="goDetails(row.id)">
        {{ value }}
      </span>
    </template>

    <!-- Accepted cell (clickable) -->
    <template #cell-evn_sum_accept="{ row, value }">
      <span role="button" tabindex="0"
        class="block w-full h-full py-2 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
        @click="goDetails(row.id)" 
        @keydown.enter.prevent="goDetails(row.id)"
        @keydown.space.prevent="goDetails(row.id)">
        {{ value }}
      </span>
    </template>

    <!-- Status cell (with badge) -->
    <template #cell-evn_status="{ row, value }">
      <span role="button" tabindex="0"
        class="block w-full h-full py-1 text-center hover:bg-slate-50 focus:bg-slate-100 cursor-pointer"
        @click="goDetails(row.id)" 
        @keydown.enter.prevent="goDetails(row.id)"
        @keydown.space.prevent="goDetails(row.id)">
        <span :class="badgeClass(value)">
          {{ value || "N/A" }}
        </span>
      </span>
    </template>
  </DataTable>
</section>

<!-- No Selection Message -->
<div v-if="selectedEventIds.size === 0" class="card summary-card">
  <div class="flex flex-col items-center justify-center py-16">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
    <h3 class="text-xl font-semibold text-gray-700 mb-2">Please select an event</h3>
    <p class="text-gray-500">Select one or more events from the table above to view statistics and participant data</p>
  </div>
</div>

<!-- Summary/Graph Section - แสดงเมื่อเลือก event แล้ว -->
<div v-if="selectedEventIds.size > 0" class="card summary-card">
    <div class="summary-grid">
      <!-- Actual Attendance -->
      <div class="summary-item chart-container actual-attendance-card">
        <DonutActualAttendance 
          :eventId="Array.from(selectedEventIds)[0]"
          :attendanceData="{
            attending: chartData.attending || 0,
            notAttending: chartData.not_attending || 0,
            pending: chartData.pending || 0,
            total: chartData.total_participation || 0
          }"
          :loading="loadingParticipants"
        />
      </div>

      <!-- Event Participation Graph -->
      <div class="summary-item chart-container event-participation-card">
        <GraphEventParticipation 
          :eventId="Array.from(selectedEventIds)[0]"
          :data="participationData"
          :loading="loadingParticipants"
        />
      </div>

      <!-- Status Cards Row -->
      <div class="status-cards-row">
        <AttendingCard 
          :attending="chartData.attending || 0" 
          :total="chartData.total_participation || 0"
          :loading="loadingParticipants"
          :isClickable="true"
          @showAttendingEmployees="showEmployeesByStatus('attending')"
        />
        <NotAttendingCard 
          :notAttending="chartData.not_attending || 0" 
          :total="chartData.total_participation || 0"
          :loading="loadingParticipants"
          :isClickable="true"
          @showNotAttendingEmployees="showEmployeesByStatus('not-attending')"
        />
        <PendingCard 
          :pending="chartData.pending || 0" 
          :total="chartData.total_participation || 0"
          :loading="loadingParticipants"
          :isClickable="true"
          @showPendingEmployees="showEmployeesByStatus('pending')"
        />
      </div> <!-- Close status-cards-row -->
    </div>
  </div>

  <!-- Employee Table Section - แสดงเมื่อกดการ์ด -->
  <div v-if="showEmployeeTable && selectedEventIds.size > 0" class="employee-table-container">
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
            <td class="employee-td emp-col-id">{{ emp.emp_id || 'N/A' }}</td>
            <td class="employee-td emp-col-name">{{ emp.emp_firstname || emp.name || 'N/A' }}</td>
            <td class="employee-td emp-col-nickname">{{ emp.emp_nickname || emp.nickname || 'N/A' }}</td>
            <td class="employee-td emp-col-phone">{{ emp.emp_phone || emp.phone || 'N/A' }}</td>
            <td class="employee-td emp-col-department">{{ emp.department || emp.department_name || 'N/A' }}</td>
            <td class="employee-td emp-col-team">{{ emp.team || emp.team_name || 'N/A' }}</td>
            <td class="employee-td emp-col-position">{{ emp.position || emp.position_name || 'N/A' }}</td>
            <td class="employee-td emp-col-event">{{ emp.event_title || getEventTitlesText() }}</td>
          </tr>
          <tr v-if="paginatedEmployees.length === 0 && !loadingParticipants">
            <td colspan="9" class="employee-td no-data">No participants found for selected event(s)</td>
          </tr>
          <tr v-if="loadingParticipants">
            <td colspan="9" class="employee-td no-data">Loading participants...</td>
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
</template>

<script>
import axios from "axios";
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

// Import dashboard components
import AttendingCard from '../../components/Dashboard/AttendingCard.vue';
import NotAttendingCard from '../../components/Dashboard/NotAttendingCard.vue';
import PendingCard from '../../components/Dashboard/PendingCard.vue';
import DonutActualAttendance from '../../components/Dashboard/DonutActualAttendance.vue';
import GraphEventParticipation from '../../components/Dashboard/GraphEventParticipation.vue';
import Button from "../../components/Button.vue";
import SearchBar from "../../components/SearchBar.vue";
import EventFilter from "../../components/IndexEvent/EventFilter.vue";
import EventSort from "../../components/IndexEvent/EventSort.vue";
import DataTable from "@/components/DataTable.vue";

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
    EventSort,
    DataTable
  },
  data() {
    return {
      event: [],
      categories: [],
      catMap: {},
      
      searchInput: "",
      search: "",
      sortBy: "evn_status",
      sortOrder: "asc",
      selectedSort: {
        id: "status_asc",
        key: "evn_status",
        order: "asc",
        type: "custom",
      },
      sortOptions: [
        { id: "title_asc", label: "ชื่องาน A–Z", key: "evn_title", order: "asc", type: "text" },
        { id: "title_desc", label: "ชื่องาน Z–A", key: "evn_title", order: "desc", type: "text" },
        { id: "invited_desc", label: "จำนวนคนเชิญมากสุด", key: "evn_num_guest", order: "desc", type: "number" },
        { id: "invited_asc", label: "จำนวนคนเชิญน้อยสุด", key: "evn_num_guest", order: "asc", type: "number" },
        { id: "accepted_desc", label: "จำนวนคนเข้าร่วมมากสุด", key: "evn_sum_accept", order: "desc", type: "number" },
        { id: "accepted_asc", label: "จำนวนคนเข้าร่วมน้อยสุด", key: "evn_sum_accept", order: "asc", type: "number" },
        { id: "date_desc", label: "วันที่จัดงานใหม่สุด", key: "evn_date", order: "desc", type: "date" },
        { id: "date_asc", label: "วันที่จัดงานเก่าสุด", key: "evn_date", order: "asc", type: "date" },
        { id: "status_asc", label: "สถานะ (Ongoing → Done)", key: "evn_status", order: "asc", type: "custom" },
        { id: "status_desc", label: "สถานะ (Done → Ongoing)", key: "evn_status", order: "desc", type: "custom" },
      ],
      
      page: 1,
      pageSize: 10,
      
      filters: { category: [], status: [] },
      statusOptions: [
        { label: "Done", value: "done" },
        { label: "Ongoing", value: "ongoing" },
        { label: "Upcoming", value: "upcoming" },
      ],
      
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
      // Selected events for multi-select
      selectedEventIds: new Set(),
      selectAll: false,
      // Date filter
      selectedDate: '',
      // Employee table states
      showEmployeeTable: false,
      employeeTableType: null,
      filteredEmployeesForTable: [],
      currentPage: 1,
      itemsPerPage: 10,
      selectedTeamFilter: '',
      // Data for charts - will be updated based on selected events
      chartData: {
        totalParticipation: 0,
        attending: 0,
        notAttending: 0,
        pending: 0,
        departments: []
      },
      // Button testing data
      loadingTest: false,
      // Participation data for GraphEventParticipation component
      participationData: {
        departments: []
      },
      // Event participants data
      eventParticipants: [],
      loadingParticipants: false
    };
  },
  async created() {
  await Promise.all([this.fetchEvent(), this.fetchCategories(), this.fetchEmployees()]);
  },
  watch: {
    search() {
      this.page = 1;
    },
    pageSize() {
      this.page = 1;
    },
    selectedSort: {
      handler(v) {
        if (!v) return;
        this.sortBy = v.key;
        this.sortOrder = v.order;
        this.page = 1;
      },
      immediate: true,
      deep: true,
    },
    page(newPage) {
      const total = this.totalPages;
      if (newPage < 1) this.page = 1;
      else if (newPage > total) this.page = total;
    },
    pageSize() {
      if (this.page > this.totalPages) {
        this.page = this.totalPages;
      }
    },
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
      let arr = [...this.normalized];
      const q = this.search.toLowerCase().trim();

      // Search filter
      if (q) {
        arr = arr.filter((e) =>
          `${e.evn_title} ${e.cat_name} ${e.evn_date} ${e.evn_status}`
            .toLowerCase()
            .includes(q)
        );
      }

      // Category filter
      if (this.filters.category.length > 0) {
        arr = arr.filter((e) =>
          this.filters.category.includes(String(e.evn_cat_id))
        );
      }

      // Status filter
      if (this.filters.status.length > 0) {
        arr = arr.filter((e) =>
          this.filters.status.includes(
            (e.evn_status || "").toLowerCase()
          )
        );
      }

      // Date filter
      if (this.selectedDate) {
        arr = arr.filter((e) => {
          if (!e.evn_date) return false;
          // Extract date part from event date (format: YYYY-MM-DD)
          const eventDate = String(e.evn_date).split(' ')[0];
          return eventDate === this.selectedDate;
        });
      }

      return arr;
    },

    sorted() {
      const arr = [...this.filtered];
      const { key, order, type } = this.selectedSort || {};
      const dir = order === "desc" ? -1 : 1;

      const statusOrder = { ongoing: 1, upcoming: 2, done: 3 };

      const parseDate = (val) => {
        if (!val) return 0;
        if (typeof val !== "string")
          return new Date(val).getTime() || 0;
        const s = val.trim();
        if (/^\d{4}-\d{2}-\d{2}/.test(s))
          return new Date(s).getTime() || 0;
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(s)) {
          let [d, m, y] = s.split("/").map((n) => parseInt(n, 10));
          if (y >= 2400) y -= 543;
          return new Date(y, m - 1, d).getTime();
        }
        return new Date(s).getTime() || 0;
      };

      const getVal = (row) => {
        if (type === "date") return parseDate(row[key]);
        if (type === "number") return Number(row[key] ?? 0);
        if (type === "text")
          return String(row[key] ?? "").toLowerCase();
        if (type === "custom" && key === "evn_status")
          return (
            statusOrder[(row.evn_status || "").toLowerCase()] ?? 99
          );
        return row[key];
      };

      arr.sort((a, b) => {
        if (type === "custom" && key === "evn_status") {
          const sa = (a.evn_status || "").toLowerCase();
          const sb = (b.evn_status || "").toLowerCase();
          const oa = statusOrder[sa] ?? 99;
          const ob = statusOrder[sb] ?? 99;
          if (oa !== ob) return (oa - ob) * dir;

          const da = parseDate(a.evn_date);
          const db = parseDate(b.evn_date);
          if (sa === "done") return db - da;
          return da - db;
        }

        const va = getVal(a);
        const vb = getVal(b);

        if (type === "text") {
          const cmp = va.localeCompare(vb);
          if (cmp !== 0) return cmp * dir;
        } else {
          if (va < vb) return -1 * dir;
          if (va > vb) return 1 * dir;
        }

        const da = parseDate(a.evn_date);
        const db = parseDate(b.evn_date);
        return da - db;
      });

      return arr;
    },
    totalPages() {
      return Math.ceil(this.sorted.length / this.pageSize) || 1;
    },

    paged() {
      const start = (this.page - 1) * this.pageSize;
      const items = this.sorted.slice(start, start + this.pageSize);
      // Add row number to each item
      return items.map((item, index) => ({
        ...item,
        row_number: start + index + 1
      }));
    },

    eventTableColumns() {
      return [
        {
          key: "checkbox",
          label: "",
          class: "w-12 text-center",
          headerClass: "w-12",
        },
        {
          key: "row_number",
          label: "#",
          class: "w-12 text-center",
          headerClass: "w-12",
        },
        {
          key: "evn_title",
          label: "Event",
          class: "text-left",
          headerClass: "w-[450px]",
          cellClass: "pl-3 text-slate-800 font-medium truncate",
          sortable: true,
        },
        {
          key: "cat_name",
          label: "Category",
          class: "text-left",
          headerClass: "pl-2",
          cellClass: "pl-3",
          sortable: true,
        },
        {
          key: "evn_date",
          label: "Date (D/M/Y)",
          class: "w-[120px] text-center whitespace-nowrap",
          format: this.formatDate,
          sortable: true,
        },
        {
          key: "evn_timestart",
          label: "Time",
          class: "w-[110px] text-center whitespace-nowrap justify-center",
          cellClass: "justify-center",
          format: (v, r) => this.timeText(v, r.evn_timeend),
        },
        {
          key: "evn_num_guest",
          label: "Invited",
          class: "w-20 text-center",
          sortable: true,
        },
        {
          key: "evn_sum_accept",
          label: "Accepted",
          class: "w-20 text-center",
          sortable: true,
        },
        {
          key: "evn_status",
          label: "Status",
          class: "text-center",
          sortable: true,
        },
      ];
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
      // Always use filteredEmployeesForTable - it's populated by showEmployeesByStatus()
      const data = this.filteredEmployeesForTable;
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return data.slice(start, end);
    },
    totalEmployees() {
      // Always use filteredEmployeesForTable count
      return this.filteredEmployeesForTable.length;
    },
    employeePaginationText() {
      const total = this.totalEmployees;
      const start = total > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
      const end = Math.min(this.currentPage * this.itemsPerPage, total);
      return `${start}-${end} จาก ${total} รายการ`;
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

    // Get selected event data (first selected event)
    selectedEventData() {
      if (this.selectedEventIds.size === 0) return null;
      const firstEventId = Array.from(this.selectedEventIds)[0];
      return this.normalized.find(event => (event.id || event.evn_id) == firstEventId);
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
        const res = await axios.get("/get-event");
        this.event = Array.isArray(res.data)
          ? res.data
          : res.data?.data || [];
      } catch (err) {
        console.error("fetchEvent error", err);
        this.event = [];
      }
    },
    async fetchCategories() {
      try {
        const res = await axios.get("/event-info-dashboard");
        const cats = res.data?.categories || [];
        
        // Map to required format for EventFilter
        this.categories = cats.map(c => ({
          id: String(c.id),
          cat_name: c.cat_name
        }));
        
        this.catMap = Object.fromEntries(
          cats.map(c => [String(c.id), c.cat_name])
        );
      } catch (err) {
        console.error("fetchCategories error", err);
        this.categories = [];
        this.catMap = {};
      }
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
    
    // Date filter method
    filterByDate() {
      // Date filter is handled automatically by the filtered computed property
      // Reset to page 1 when filter changes
      this.page = 1;
    },

    // Fetch event statistics and participants for selected events
    async fetchEventStatistics() {
      console.log('🔄 fetchEventStatistics called with:', Array.from(this.selectedEventIds));
      
      if (this.selectedEventIds.size === 0) {
        console.log('⚠️ No events selected, resetting data');
        // Reset to default/empty state
        this.chartData = {
          total_participation: 0,
          attending: 0,
          not_attending: 0,
          pending: 0,
          departments: []
        };
        this.participationData = { departments: [] };
        this.eventParticipants = [];
        this.showEmployeeTable = false;
        return;
      }

      this.loadingParticipants = true;
      try {
        const eventIds = Array.from(this.selectedEventIds);
        
        console.log('📤 Sending POST /event-statistics with event_ids:', eventIds);
        
        // Fetch statistics for selected event(s)
        const res = await axios.post('/event-statistics', { event_ids: eventIds });
        
        console.log('📥 API Response:', res.data);
        
        if (res.data) {
          // Update chart data with aggregated statistics
          this.chartData = {
            total_participation: res.data.total_participation || 0,
            attending: res.data.attending || 0,
            not_attending: res.data.not_attending || 0,
            pending: res.data.pending || 0,
            departments: res.data.departments || []
          };
          
          // Update participation data for bar chart - map to correct format
          this.participationData = {
            departments: (res.data.departments || []).map(dept => ({
              name: dept.name,
              attending: dept.attending || 0,
              notAttending: dept.notAttending || 0,
              pending: dept.pending || 0
            }))
          };
          
          // Update participants list (remove duplicates)
          this.eventParticipants = res.data.participants || [];
          this.showEmployeeTable = true;
          
          console.log('✅ Chart data updated:', this.chartData);
          console.log('✅ Participation data updated:', this.participationData);
          console.log('✅ Participants count:', this.eventParticipants.length);
        }
      } catch (err) {
        console.error('❌ Error fetching event statistics:', err);
        console.error('Error response:', err.response?.data);
        console.error('Error status:', err.response?.status);
        // Show error message or fallback to empty data
        this.chartData = {
          total_participation: 0,
          attending: 0,
          not_attending: 0,
          pending: 0,
          departments: []
        };
        this.participationData = { departments: [] };
        this.eventParticipants = [];
      } finally {
        this.loadingParticipants = false;
      }
    },

    // Multi-select checkbox methods
    getRowClass(row) {
      const eventId = row.id || row.evn_id;
      return this.selectedEventIds.has(eventId) ? 'selected-row' : '';
    },
    
    toggleEventSelection(event) {
      const eventId = event.id || event.evn_id;
      if (!eventId) {
        console.error('No event ID found in:', event);
        return;
      }
      
      if (this.selectedEventIds.has(eventId)) {
        this.selectedEventIds.delete(eventId);
      } else {
        this.selectedEventIds.add(eventId);
      }
      
      // Update select-all checkbox state
      this.selectAll = this.selectedEventIds.size === this.sorted.length && this.sorted.length > 0;
      
      console.log('Updated selected events:', Array.from(this.selectedEventIds));
      
      // Manually trigger fetch since Set is not reactive
      this.fetchEventStatistics();
    },

    selectAllEvents(event) {
      // Toggle selectAll based on checkbox state
      this.selectAll = event.target.checked;
      
      if (this.selectAll) {
        // Select all events in sorted list
        this.selectedEventIds = new Set(this.sorted.map(e => e.id || e.evn_id));
      } else {
        // Deselect all
        this.selectedEventIds.clear();
      }
      console.log('Select all toggled:', this.selectAll, 'Selected count:', this.selectedEventIds.size);
      
      // Manually trigger fetch since Set is not reactive
      this.fetchEventStatistics();
    },

    // Get event titles text for display
    getEventTitlesText() {
      if (this.selectedEventIds.size === 0) return 'N/A';
      
      const selectedEvents = this.normalized.filter(event => 
        this.selectedEventIds.has(event.id || event.evn_id)
      );
      
      if (selectedEvents.length === 1) {
        return selectedEvents[0].evn_title || 'N/A';
      } else if (selectedEvents.length > 1) {
        return `${selectedEvents.length} events selected`;
      }
      
      return 'N/A';
    },

    // Filter & Sort handlers
    applySearch() {
      this.search = this.searchInput;
      this.page = 1;
    },

    applyFilter() {
      this.page = 1;
    },

    onPickSort(opt) {
      if (!opt) return;
      this.selectedSort = opt;
      this.sortBy = opt.key;
      this.sortOrder = opt.order;
      this.page = 1;
    },

    handleClientSort({ key, order }) {
      this.sortBy = key;
      this.sortOrder = order;
      this.page = 1;
      this.selectedSort =
        this.sortOptions.find(
          (opt) => opt.key === key && opt.order === order
        ) || this.selectedSort;
    },

    // Formatting methods
    formatDate(val) {
      if (!val) return "N/A";
      try {
        const d = new Date(val);
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
      } catch {
        return "Invalid Date";
      }
    },

    timeText(startTime, endTime) {
      const format = (t) => (t ? String(t).slice(0, 5) : "??:??");
      return `${format(startTime)}-${format(endTime)}`;
    },

    badgeClass(status) {
      const base =
        "inline-block min-w-[110px] rounded-md border px-2.5 py-1 text-xs capitalize";
      switch ((status || "").toLowerCase()) {
        case "done":
          return `${base} bg-[#DCFCE7] text-[#00A73D]`;
        case "upcoming":
          return `${base} bg-[#FFF9C2] text-[#FDC800]`;
        case "ongoing":
          return `${base} bg-[#DFF3FE] text-[#0084D1]`;
        default:
          return `${base} bg-slate-100 text-slate-700`;
      }
    },

    // Navigation
    goDetails(id) {
      try {
        this.$router.push({
          name: "events.show",
          params: { id: String(id) },
        });
      } catch (_) {
        this.$router.push({ path: `/events/${id}` });
      }
    },

    // Deprecated - kept for reference, use toggleEventSelection instead
    onEventSelect(event) {
      console.log('Event selected:', event);
      console.log('Event keys:', Object.keys(event));
      
      // ลองหา ID field ที่ถูกต้อง - ใช้ id แทน evn_id
      const eventId = event.id || event.evn_id;
      
      if (!eventId) {
        console.error('No event ID found in:', event);
        return;
      }
      
      this.toggleEventSelection(event);
      if (this.selectedEventIds.size > 0) {
        this.loadEventStatistics(eventId);
      }
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
      if (this.selectedEventIds.size === 0) {
        alert('กรุณาเลือกกิจกรรมก่อน');
        return;
      }

      console.log('showEmployeesByStatus called with status:', status);
      this.employeeTableType = status;
      this.showEmployeeTable = true;
      
      try {
        // Use existing eventParticipants data from fetchEventStatistics
        if (!this.eventParticipants || this.eventParticipants.length === 0) {
          console.warn('No participants data available');
          this.filteredEmployeesForTable = [];
          return;
        }
        
        // Filter participants based on status
        let filteredParticipants = [];
        const apiStatus = this.mapStatusForAPI(status);
        
        filteredParticipants = this.eventParticipants.filter(participant => {
          return participant.status === apiStatus;
        });
        
        // Map to our expected employee format
        this.filteredEmployeesForTable = filteredParticipants.map(participant => ({
          id: participant.id,
          emp_id: participant.emp_id,
          emp_prefix: participant.emp_prefix,
          emp_firstname: participant.emp_firstname,
          emp_lastname: participant.emp_lastname,
          emp_nickname: participant.emp_nickname,
          emp_phone: participant.emp_phone,
          emp_email: participant.emp_email,
          position: participant.position || 'N/A',
          department: participant.department || 'N/A',
          team: participant.team || 'N/A',
          event_title: participant.event_title || 'N/A', // Add event title
          emp_delete_status: 'active'
        }));
        
        console.log(`Loaded ${this.filteredEmployeesForTable.length} employees for status: ${status}`);
        
      } catch (error) {
        console.error('Error loading employees:', error);
        
        // Use empty array if filter fails
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

<style scoped>
/* Event Card Styling - Clean and minimal like EventPage */
.event-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

.event-card .toolbar--pill {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

.event-card .toolbar--pill > div:first-child {
  flex: 1;
}

.event-card .export-label {
  display: none; /* Hide the label */
}

/* Event Table Styling - Similar to Employee Table */
.event-table-wrap {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  margin-top: 1rem;
  background: #fff;
}

.event-table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  background: #ffffff;
  font-size: 14px;
}

/* Event Table Header */
.event-th {
  background: #ffffff;
  color: #374151;
  font-weight: 600;
  font-size: 13px;
  text-align: center;
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 1;
  white-space: nowrap;
}

/* Event Table Data */
.event-td {
  padding: 12px 16px;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
  color: #374151;
  vertical-align: middle;
}

/* Event Table Rows */
.event-row:nth-child(odd) {
  background-color: #ffffff;
}

.event-row:nth-child(even) {
  background-color: #ffffff;
}

.event-row:hover {
  background-color: #f9fafb;
  transition: background-color 0.2s ease;
}

/* Selected Row Styling */
.event-row.selected-row {
  background-color: #fef2f2 !important;
}

.event-row.selected-row:hover {
  background-color: #fee2e2 !important;
}

/* No Data Row for Event Table */
.event-td.no-data {
  text-align: center;
  color: #6b7280;
  font-style: italic;
  padding: 24px;
  background-color: #f9fafb;
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
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
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
.toolbar--pill { 
  display: flex; 
  align-items: center; 
  gap: 1rem; 
  margin-top: 0;
  flex-wrap: wrap;
}

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

/* ========================================
   Dashboard Reference UI Styling
   ======================================== */

.dashboard-container {
  padding: 24px;
  background: #f9fafb;
  min-height: 100vh;
}

.dashboard-title {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 20px 0;
}

.dashboard-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

/* Search Section */
.search-section {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.search-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
}

.search-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.search-input {
  flex: 1;
  height: 42px;
  padding: 0 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #111827;
  outline: none;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: #dc2626;
}

.search-input::placeholder {
  color: #9ca3af;
}

.clear-btn {
  width: 42px;
  height: 42px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #ffffff;
  color: #6b7280;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.search-btn {
  height: 42px;
  padding: 0 20px;
  border: none;
  border-radius: 8px;
  background: #dc2626;
  color: #ffffff;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.search-btn:hover {
  background: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.search-btn .material-symbols-outlined {
  font-size: 18px;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.action-btn {
  height: 38px;
  padding: 0 18px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #ffffff;
  color: #374151;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.action-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.action-btn .material-symbols-outlined {
  font-size: 18px;
}

.show-data-btn {
  height: 38px;
  padding: 0 20px;
  border: none;
  border-radius: 8px;
  background: #dc2626;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.show-data-btn:hover {
  background: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Table */
.table-wrapper {
  overflow-x: auto;
}

.event-table {
  width: 100%;
  border-collapse: collapse;
}

.event-table thead {
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  border-bottom: 2px solid #e5e7eb;
}

.event-table th {
  padding: 14px 16px;
  text-align: left;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
}

.event-table th.col-checkbox {
  width: 50px;
  text-align: center;
}

.event-table th.col-number {
  width: 60px;
  text-align: center;
}

.event-table th.col-event {
  min-width: 200px;
}

.event-table th.col-category {
  width: 140px;
}

.event-table th.col-date {
  width: 120px;
}

.event-table th.col-time {
  width: 130px;
}

.event-table th.col-invited,
.event-table th.col-accepted {
  width: 100px;
  text-align: center;
}

.event-table th.col-status {
  width: 120px;
  text-align: center;
}

.event-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s;
}

.event-table tbody tr:hover {
  background: #f9fafb;
}

.event-table tbody tr.selected-row {
  background: #fee2e2 !important;
}

.event-table td {
  padding: 14px 16px;
  font-size: 14px;
  color: #111827;
}

.event-table td:nth-child(1),
.event-table td:nth-child(2) {
  text-align: center;
}

.event-table td:nth-child(7),
.event-table td:nth-child(8),
.event-table td:nth-child(9) {
  text-align: center;
}

/* Checkbox */
.table-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #dc2626;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.status-ongoing {
  background: #dbeafe;
  color: #1e40af;
}

.status-upcoming {
  background: #fef3c7;
  color: #d97706;
}

.status-done {
  background: #d1fae5;
  color: #059669;
}

/* No Data */
.no-data {
  padding: 48px 24px;
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
}

/* Pagination */
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
}

.page-size-select {
  height: 36px;
  padding: 0 32px 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  color: #111827;
  font-size: 14px;
  cursor: pointer;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 8L2 4h8z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.page-arrow {
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  color: #374151;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-arrow:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.page-arrow:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-number {
  min-width: 36px;
  height: 36px;
  padding: 0 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-number:hover:not(.active) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.page-number.active {
  background: #dc2626;
  border-color: #dc2626;
  color: #ffffff;
  font-weight: 600;
}

.page-dots {
  padding: 0 8px;
  color: #9ca3af;
  font-weight: 600;
}

/* Highlight selected rows */
:deep(.selected-row) {
  background-color: #fce7f3 !important; /* Pink background */
}

:deep(.selected-row):hover {
  background-color: #fbcfe8 !important; /* Slightly darker pink on hover */
}

</style>
