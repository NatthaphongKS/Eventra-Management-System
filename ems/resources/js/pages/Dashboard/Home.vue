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
        class=""
      />
    </div>

    <!-- ✅ DatePicker -->
    <div class="flex gap-2 flex-shrink-20 mt-[30px] items-stretch">
      <!-- DatePicker -->
      <div class="h-[44px]">
        <EventDatePicker v-model="selectedDate" class="h-full [&_button]:h-full [&_input]:h-full" />
      </div>
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

    <!-- Export Dropdown -->
    <ExportDropdown
      :selectedEvents="selectedEventsArray"
      :disabled="selectedEventIds.size === 0"
      @export-start="handleExportStart"
      @export-progress="handleExportProgress"
      @export-complete="handleExportComplete"
      @export-error="handleExportError"
      @export-end="handleExportEnd"
      class="mt-6"
    />

    <!-- Show Data Button -->
    <button
      @click="showDataHandler"
      class="ml-auto inline-flex h-11 items-center rounded-full bg-[#b91c1c] px-6 font-semibold text-white hover:bg-[#991b1b] focus:outline-none focus:ring-2 focus:ring-red-300 mt-6 transition-colors"
      :disabled="selectedEventIds.size === 0"
      :class="{'opacity-50 cursor-not-allowed': selectedEventIds.size === 0}"
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
      />
    </template>

    <!-- Checkbox column for multi-select -->
    <template #cell-checkbox="{ row }">
      <input
        type="checkbox"
        :checked="selectedEventIds.has(row.id || row.evn_id)"
        @change="toggleEventSelection(row)"
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

<!-- Export Progress Overlay -->
<div v-if="isExporting" class="export-overlay">
  <div class="export-modal">
    <div class="export-spinner"></div>
    <h3 class="export-title">กำลัง Export ข้อมูล...</h3>
    <p class="export-text">{{ exportMessage }}</p>
    <div class="export-progress-bar">
      <div class="export-progress-fill" :style="{ width: exportProgressPercent + '%' }"></div>
    </div>
    <p class="export-hint">⏳ กรุณารอสักครู่ อย่าปิดหน้าต่างนี้</p>
  </div>
</div>

<!-- Summary/Graph Section - แสดงเมื่อเลือก event และกดปุ่ม Show Data แล้ว -->
  <div
  v-if="selectedEventIds.size > 0 && showStatistics"
  class="summary-card mt-6 w-full scroll-mt-24">
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    <!-- Actual Attendance (Left) -->
    <div class="lg:col-span-5">
      <div
        class=""
      >
        <DonutActualAttendance
          :eventId="Array.from(selectedEventIds)[0]"
          :attendanceData="{
            attending: chartData.actual_attendance?.attended || 0,
            total: chartData.actual_attendance?.total_assigned || 0
          }"
          :loading="loadingParticipants"
        />
      </div>
    </div>

    <!-- Event Participation Graph (Right) -->
    <div class="lg:col-span-7">
      <div
        class="h-full min-h-[320px] rounded-2xl p-5 "
      >
        <GraphEventParticipation
          :eventId="Array.from(selectedEventIds)[0]"
          :data="participationData"
          :loading="loadingParticipants"
        />
      </div>
    </div>

    <!-- Status Cards Row (Bottom) -->
    <div class="lg:col-span-12">
      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="h-full">
          <AttendingCard
            :attending="chartData.attending || 0"
            :total="chartData.total_participation || 0"
            :loading="loadingParticipants"
            :isClickable="true"
            @showAttendingEmployees="showEmployeesByStatus('attending')"
          />
        </div>

        <div class="h-full">
          <NotAttendingCard
            :notAttending="chartData.not_attending || 0"
            :total="chartData.total_participation || 0"
            :loading="loadingParticipants"
            :isClickable="true"
            @showNotAttendingEmployees="showEmployeesByStatus('not-attending')"
          />
        </div>

        <div class="h-full">
          <PendingCard
            :pending="chartData.pending || 0"
            :total="chartData.total_participation || 0"
            :loading="loadingParticipants"
            :isClickable="true"
            @showPendingEmployees="showEmployeesByStatus('pending')"
          />
        </div>
      </div>
    </div>
  </div>
</div>
<DataTable
    v-if="showEmployeeTable && selectedEventIds.size > 0 && showStatistics"
    :rows="paginatedEmployees"
    :columns="employeeColumns"
    :loading="loadingParticipants"
    v-model:page="currentPage"
    v-model:pageSize="itemsPerPage"
    :totalItems="totalEmployees"
    :pageSizeOptions="[10, 25, 50, 100]"
    rowKey="unique_key"
    :showRowNumber="true"
    class="mt-6"
  >
    <template #empty>
      <div class="py-6 text-center text-neutral-700">
        No participants found for selected event(s)
      </div>
    </template>
  </DataTable>
</template>

<script>
import axios from "axios";
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

// นำเข้า Component สำหรับ Dashboard
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
import EventDatePicker from "../../components/IndexEvent/EventDatePicker.vue";
import ExportDropdown from "../../components/ExportDropdown.vue";

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
    DataTable,
    EventDatePicker,
    ExportDropdown
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
      // Event ที่เลือกสำหรับแสดงสถิติ
      selectedEventIds: new Set(),
      // กรองตามวันที่
      selectedDate: { start: null, end: null },
      // สถานะตารางพนักงาน
      showEmployeeTable: false,
      employeeTableType: null,
      filteredEmployeesForTable: [],
      currentPage: 1,
      itemsPerPage: 10,
      selectedTeamFilter: '',
      // ข้อมูลสำหรับกราฟ - อัพเดตตาม event ที่เลือก
      chartData: {
        total_participation: 0,
        attending: 0,
        not_attending: 0,
        pending: 0,
        departments: [],
        actual_attendance: { attended: 0, total_assigned: 0 }
      },
      // ข้อมูลทดสอบปุ่ม
      loadingTest: false,
      // ข้อมูลสำหรับกราฟแท่ง (Bar Chart)
      participationData: {
        departments: [],
        teams: []
      },
      // รายชื่อผู้เข้าร่วมทั้งหมด
      eventParticipants: [],
      loadingParticipants: false,
      // ควบคุมการแสดงผลกราฟและตาราง
      showStatistics: false,
      // Export state
      isExporting: false,
      exportProgress: {
        current: 0,
        total: 0,
        eventName: ''
      }
    };
  }, // <--- แก้ไขจุดที่ผิด: เอา } ที่เกินมาออก และให้ created() อยู่ต่อจากนี้ภายใน object เดียวกัน

  async created() {
    await Promise.all([this.fetchEvent(), this.fetchCategories(), this.fetchEmployees()]);
  },

  mounted() {
    // เพิ่ม event listener สำหรับ auto-refresh เมื่อกลับมาที่หน้านี้
    window.addEventListener('focus', this.handleWindowFocus);
  },

  beforeUnmount() {
    // ลบ event listener เมื่อ component ถูกทำลาย
    window.removeEventListener('focus', this.handleWindowFocus);
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
    filteredEmployeesForTable() {
      this.currentPage = 1;
    },
    itemsPerPage() {
      this.currentPage = 1;
    },
  },

  computed: {
    normalized() {
      return this.event.map(e => {
        // ดึง category ID ที่ถูกต้อง
        const catId = e.evn_cat_id ?? e.evn_category_id ?? e.evn_category ?? "";
        // ดึง category name โดยให้ความสำคัญกับข้อมูลที่มาจาก API ก่อน
        let catName = e.cat_name ?? e.category_name ?? "";

        // ถ้ายังไม่มี category name ให้ลองหาจาก catMap
        if (!catName && catId) {
          catName = this.catMap[String(catId)] ?? "";
        }

        return {
          ...e,
          evn_title: e.evn_title ?? e.evn_name ?? "",
          evn_cat_id: catId,
          cat_name: catName || "ไม่มีหมวดหมู่",
          evn_date: e.evn_date ?? "",
          evn_timestart: e.evn_timestart ?? "",
          evn_timeend: e.evn_timeend ?? "",
          evn_location: e.evn_location ?? "",
          evn_details: e.evn_description ?? "", // ใช้ evn_description จาก database
          evn_num_guest: Number(e.evn_num_guest ?? 0),
          evn_sum_accept: Number(e.evn_sum_accept ?? 0),
          evn_status: e.evn_status ?? "",
        };
      });
    },
    filtered() {
      let arr = [...this.normalized];
      const q = this.search.toLowerCase().trim();

      // ตัวกรองการค้นหา - ค้นหาเฉพาะชื่อกิจกรรม (evn_title) เท่านั้น
      if (q) {
        arr = arr.filter((e) => {
          return (e.evn_title || '').toLowerCase().includes(q);
        });
      }

      // ตัวกรองหมวดหมู่
      if (this.filters.category.length > 0) {
        arr = arr.filter((e) =>
          this.filters.category.includes(String(e.evn_cat_id))
        );
      }

      // ตัวกรองสถานะ
      if (this.filters.status.length > 0) {
        arr = arr.filter((e) =>
          this.filters.status.includes(
            (e.evn_status || "").toLowerCase()
          )
        );
      }

      // ตัวกรองวันที่ (Date Range)
      if (this.selectedDate?.start || this.selectedDate?.end) {
        arr = arr.filter((e) => {
          if (!e.evn_date) return false;
          // ดึงส่วนวันที่จากวันที่ของอีเวนต์ (รูปแบบ: YYYY-MM-DD)
          const eventDate = String(e.evn_date).split(' ')[0];
          const eventTime = new Date(eventDate).getTime();

          const startTime = this.selectedDate.start ? new Date(this.selectedDate.start).getTime() : null;
          const endTime = this.selectedDate.end ? new Date(this.selectedDate.end).getTime() : null;

          // ถ้ามีทั้ง start และ end
          if (startTime && endTime) {
            return eventTime >= startTime && eventTime <= endTime;
          }
          // ถ้ามีแค่ start
          if (startTime && !endTime) {
            return eventTime >= startTime;
          }
          // ถ้ามีแค่ end
          if (!startTime && endTime) {
            return eventTime <= endTime;
          }

          return true;
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
      // เพิ่มหมายเลขแถวให้แต่ละรายการ
      return items.map((item, index) => ({
        ...item,
        row_number: start + index + 1
      }));
    },
    // Check if all visible rows on current page are selected
    selectAll() {
      if (this.paged.length === 0) return false;
      return this.paged.every(row => this.selectedEventIds.has(row.id || row.evn_id));
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
    employeeColumns() {
      return [
        {
          key: "emp_id",
          label: "ID",
          class: "w-20 text-center",
          cellClass: "text-center",
          format: (v) => v || 'N/A',
        },
        {
          key: "emp_firstname",
          label: "Name",
          class: "w-32 text-left",
          cellClass: "text-left",
          format: (v, row) => v || row.name || 'N/A',
        },
        {
          key: "emp_nickname",
          label: "Nickname",
          class: "w-24 text-center",
          cellClass: "text-center",
          format: (v, row) => v || row.nickname || 'N/A',
        },
        {
          key: "emp_phone",
          label: "Phone",
          class: "w-28 text-center",
          cellClass: "text-center",
          format: (v, row) => v || row.phone || 'N/A',
        },
        {
          key: "department",
          label: "Department",
          class: "w-32 text-left",
          cellClass: "text-left",
          format: (v, row) => v || row.department_name || 'N/A',
        },
        {
          key: "team",
          label: "Team",
          class: "w-28 text-left",
          cellClass: "text-left",
          format: (v, row) => v || row.team_name || 'N/A',
        },
        {
          key: "position",
          label: "Position",
          class: "w-36 text-left",
          cellClass: "text-left",
          format: (v, row) => v || row.position_name || 'N/A',
        },
        {
          key: "event_title",
          label: "Event",
          class: "w-40 text-left",
          cellClass: "text-left",
          format: (v) => v || this.getEventTitlesText(),
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
    // การแบ่งหน้าในตารางพนักงาน
    totalEmployees() {
      return this.filteredEmployeesForTable.length;
    },
    paginatedEmployees() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredEmployeesForTable.slice(start, end);
    },
    eventPaginationText() {
      const start = this.sorted.length > 0 ? (this.page - 1) * this.pageSize + 1 : 0;
      const end = Math.min(this.page * this.pageSize, this.sorted.length);
      return `${start}-${end} จาก ${this.sorted.length} รายการ`;
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
    // ดึงข้อมูลอีเวนต์ที่เลือก (อีเวนต์ที่เลือกแรก)
    selectedEventData() {
      if (this.selectedEventIds.size === 0) return null;
      const firstEventId = Array.from(this.selectedEventIds)[0];
      return this.normalized.find(event => (event.id || event.evn_id) == firstEventId);
    },
    // ดึงข้อมูลทุกอีเวนต์ที่เลือก (สำหรับ Export)
    selectedEventsArray() {
      if (this.selectedEventIds.size === 0) return [];
      const selectedIds = Array.from(this.selectedEventIds);
      return this.normalized.filter(event =>
        selectedIds.includes(event.id || event.evn_id)
      );
    },
    // Export progress message
    exportMessage() {
      if (this.exportProgress.total === 0) {
        return 'กำลังเตรียมข้อมูล...';
      }
      return `กำลัง Export: ${this.exportProgress.eventName}\n(${this.exportProgress.current} จาก ${this.exportProgress.total})`;
    },
    // Export progress percentage
    exportProgressPercent() {
      if (this.exportProgress.total === 0) return 0;
      return Math.round((this.exportProgress.current / this.exportProgress.total) * 100);
    }
  },

  methods: {
    // Search handling
    handleSearch(searchValue) {
      this.search = searchValue;
    },
    // Filter handling
    handleFilter(filterData) {
      this.filters = { ...this.filters, ...filterData }; // Updated to merge filters properly
      this.page = 1;
    },
    // Sort handling
    handleSort(sortData) {
      this.handleClientSort(sortData);
    },
    // เมธอดคำนวณข้อมูลสำหรับแสดงกราฟ (ย้ายมาจาก computed)
    getAttendingProgress() {
      if (this.chartData.total_participation === 0) return 0;
      return Math.round((this.chartData.attending / this.chartData.total_participation) * 251);
    },
    getNotAttendingProgress() {
      if (this.chartData.total_participation === 0) return 0;
      return Math.round((this.chartData.not_attending / this.chartData.total_participation) * 251);
    },
    getPendingProgress() {
      if (this.chartData.total_participation === 0) return 0;
      return Math.round((this.chartData.pending / this.chartData.total_participation) * 251);
    },
    getAttendingPercentage() {
      return Math.round((this.chartData.attending / 100) * 251);
    },
    getNotAttendingPercentage() {
      return Math.round((this.chartData.not_attending / 100) * 251);
    },
    getPendingPercentage() {
      return Math.round((this.chartData.pending / 100) * 251);
    },
    getAttendancePercentage() {
      const total = this.chartData.attending + this.chartData.not_attending + this.chartData.pending;
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
          // กรณีที่ res.data อาจจะไม่ใช่อาเรย์
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

        // แปลงเป็นรูปแบบที่ EventFilter ต้องการ
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
    },
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
      this.$router.push('/create-event');
    },
    // Date filter method
    filterByDate() {
      // การกรองวันที่จะถูกจัดการโดย computed property ที่ชื่อ filtered โดยอัตโนมัติ
      // รีเซ็ตเป็นหน้า 1 เมื่อมีการเปลี่ยนแปลงตัวกรอง
      this.page = 1;
    },
    // ดึงสถิติและรายชื่อผู้เข้าร่วมของอีเวนต์ที่เลือกไว้
    async fetchEventStatistics() {
      console.log('🔄 fetchEventStatistics called with:', Array.from(this.selectedEventIds));

      if (this.selectedEventIds.size === 0) {
        console.log('❌ No events selected, resetting data');
        // รีเซ็ตเป็นค่าว่าง
        this.chartData = {
          total_participation: 0,
          attending: 0,
          not_attending: 0,
          pending: 0,
          departments: [],
          actual_attendance: { attended: 0, total_assigned: 0 }
        };
        this.participationData = { departments: [], teams: [] };
        this.eventParticipants = [];
        this.showEmployeeTable = false;
        return;
      }

      this.loadingParticipants = true;
      try {
        const eventIds = Array.from(this.selectedEventIds);

        console.log('📡 Sending POST /event-statistics with event_ids:', eventIds);

        // ดึงสถิติจาก API
        const res = await axios.post('/event-statistics', { event_ids: eventIds });

        console.log('📊 API Response received:', res.data);
        console.log('📊 Actual Attendance from API:', {
          attended: res.data.actual_attendance?.attended,
          total_assigned: res.data.actual_attendance?.total_assigned,
          calculation: `${res.data.actual_attendance?.attended} / ${res.data.actual_attendance?.total_assigned}`
        });

        if (res.data) {
          // อัพเดตข้อมูลกราฟ
          this.chartData = {
            total_participation: res.data.total_participation || 0,
            attending: res.data.attending || 0,
            not_attending: res.data.not_attending || 0,
            pending: res.data.pending || 0,
            departments: res.data.departments || [],
            actual_attendance: res.data.actual_attendance || { attended: 0, total_assigned: 0 }
          };

          console.log('✅ Chart data updated:', this.chartData);
          console.log('📊 Donut will show:', {
            attending: this.chartData.actual_attendance.attended,
            total: this.chartData.actual_attendance.total_assigned,
            percentage: this.chartData.actual_attendance.total_assigned > 0
              ? ((this.chartData.actual_attendance.attended / this.chartData.actual_attendance.total_assigned) * 100).toFixed(2) + '%'
              : '0%'
          });

          // อัพเดตข้อมูลกราฟแท่ง
          this.participationData = {
            departments: (res.data.departments || []).map(dept => ({
              name: dept.name,
              attending: dept.attending || 0,
              notAttending: dept.notAttending || 0,
              pending: dept.pending || 0
            })),
            teams: (res.data.teams || []).map(team => ({
              name: team.name,
              attending: team.attending || 0,
              notAttending: team.notAttending || 0,
              pending: team.pending || 0
            }))
          };

          // อัพเดตรายชื่อผู้เข้าร่วม
          this.eventParticipants = res.data.participants || [];
          this.showEmployeeTable = true;

          console.log('✅ Participation data updated:', this.participationData);
          console.log('✅ Participants loaded:', this.eventParticipants.length);
        }
      } catch (err) {
        console.error('❌ Error fetching event statistics:', err);
        console.error('❌ Error response:', err.response?.data);
        console.error('❌ Error status:', err.response?.status);
        // เกิดข้อผิดพลาด - รีเซ็ตเป็นค่าว่าง
        this.chartData = {
          total_participation: 0,
          attending: 0,
          not_attending: 0,
          pending: 0,
          departments: [],
          actual_attendance: { attended: 0, total_assigned: 0 }
        };
        this.participationData = { departments: [], teams: [] };
        this.eventParticipants = [];
      } finally {
        this.loadingParticipants = false;
      }
    },
    // ฟังก์ชันจัดการ checkbox เลือกหลาย event - สำหรับ highlight row
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
      // รีเซ็ตการแสดงผลเมื่อมีการเปลี่ยนแปลงการเลือก
      this.showStatistics = false;
    },
    selectAllEvents(event) {
      const isChecked = event.target.checked;

      if (isChecked) {
         // เลือกทุกอีเวนต์ในหน้าปัจจุบัน (Visible Page)
        this.paged.forEach(row => {
          const eventId = row.id || row.evn_id;
          if (eventId) {
            this.selectedEventIds.add(eventId);
          }
        });
      } else {
        // ยกเลิกการเลือกเฉพาะแถวที่แสดงในหน้าปัจจุบัน
        this.paged.forEach(row => {
          const eventId = row.id || row.evn_id;
          if (eventId) {
            this.selectedEventIds.delete(eventId);
          }
        });
      }
      // รีเซ็ตการแสดงผลเมื่อมีการเปลี่ยนแปลงการเลือก
      this.showStatistics = false;
    },
    // ดึงชื่ออีเวนต์มาแสดงผล
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
    // เมธอดสำหรับจัดรูปแบบข้อมูล
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
    onEventSelect(event) {
      const eventId = event.id || event.evn_id;
      if(eventId) this.toggleEventSelection(event);
    },
    async loadEventStatistics(eventId) {
      this.isLoading = true;
      try {
        // ดึงข้อมูลผู้เข้าร่วมกิจกรรมจาก API ที่ถูกต้อง
        const response = await axios.get(`/api/event/${eventId}/participants`);
        console.log('Event statistics response:', response.data);

        if (response.data.success) {
          const statistics = response.data.data.statistics;
          // อัปเดตข้อมูลกราฟด้วยสถิติจริง
          this.chartData = {
            attending: statistics.attending || 0,
            notAttending: statistics.not_attending || 0,
            pending: statistics.pending || 0
          };
          // อัพเดตกราฟ
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
    async showEmployeesByStatus(status) {
      if (this.selectedEventIds.size === 0) {
        alert('กรุณาเลือกกิจกรรมก่อน');
        return;
      }

      this.employeeTableType = status;
      this.showEmployeeTable = true;
      
      try {
        if (!this.eventParticipants || this.eventParticipants.length === 0) {
          console.warn('⚠️ No participants data available');
          this.filteredEmployeesForTable = [];
          return;
        }

        // กรองผู้เข้าร่วมตามสถานะ
        let filteredParticipants = [];
        
        if (status === 'attending') {
          filteredParticipants = this.eventParticipants.filter(participant => {
            return participant.status === 'accepted';
          });
        } else if (status === 'not-attending') {
          filteredParticipants = this.eventParticipants.filter(participant => {
            return participant.status === 'denied';
          });
        } else if (status === 'pending') {
          filteredParticipants = this.eventParticipants.filter(participant => {
            return participant.status !== 'accepted' && participant.status !== 'denied';
          });
        }
        
        console.log(`📊 Filter: ${status} | Total: ${this.eventParticipants.length} → Filtered: ${filteredParticipants.length}`);

        // กรองข้อมูลซ้ำโดยใช้ Map กับ unique key (emp_id + event_id + status)
        const uniqueParticipants = new Map();
        filteredParticipants.forEach(participant => {
          const uniqueKey = `${participant.emp_id}_${participant.event_id}_${participant.con_checkin_status}_${participant.status}`;
          // เก็บเฉพาะ record แรกที่พบ ไม่เอาข้อมูลซ้ำ
          if (!uniqueParticipants.has(uniqueKey)) {
            uniqueParticipants.set(uniqueKey, participant);
          }
        });
        // แปลง Map กลับเป็น Array และเรียงข้อมูล
        const deduplicatedParticipants = Array.from(uniqueParticipants.values());
        deduplicatedParticipants.sort((a, b) => {
          // เรียงตามชื่อกิจกรรมก่อน
          const eventCompare = (a.event_title || '').localeCompare(b.event_title || '');
          if (eventCompare !== 0) return eventCompare;

          // ถ้าชื่อกิจกรรมเท่ากัน ให้เรียงตามชื่อพนักงาน
          const nameCompare = (a.emp_firstname || '').localeCompare(b.emp_firstname || '');
          if (nameCompare !== 0) return nameCompare;

          // ถ้าชื่อเท่ากัน ให้เรียงตาม emp_id
          const empCompare = (a.emp_id || '').localeCompare(b.emp_id || '');
          if (empCompare !== 0) return empCompare;

          // ถ้า emp_id เท่ากัน ให้เรียงตาม event_id
          return (a.event_id || 0) - (b.event_id || 0);
        });

        // แปลงเป็นรูปแบบพนักงานสำหรับตาราง พร้อม unique_key
        this.filteredEmployeesForTable = deduplicatedParticipants.map(participant => ({
          id: participant.id,
          unique_key: `${participant.emp_id}_${participant.event_id}_${participant.id}`,
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
          event_title: participant.event_title || 'N/A',
          emp_delete_status: 'active'
        }));
        // Reset pagination เมื่อโหลดข้อมูลใหม่
        this.currentPage = 1;

        console.log(`✅ Table rows: ${this.filteredEmployeesForTable.length}`);

      } catch (error) {
        console.error('Error loading employees:', error);
        // ใช้ array ว่างหากการกรองล้มเหลว
        this.filteredEmployeesForTable = [];
        alert('ไม่สามารถโหลดข้อมูลพนักงานได้ กรุณาลองใหม่อีกครั้ง');
      }
    },
    mapStatusForAPI(status) {
      const statusMap = {
        'attending': 'accepted',
        'not-attending': 'denied',
        'pending': 'pending'
      };
      return statusMap[status] || 'pending';
    },
    // Button testing methods
    testClick(buttonType) {
      console.log(`Button clicked: ${buttonType}`);
      alert(`${buttonType.charAt(0).toUpperCase() + buttonType.slice(1)} button clicked!`);
    },
    testLoading() {
      this.loadingTest = true;
      setTimeout(() => {
        this.loadingTest = false;
        alert('Loading test completed!');
      }, 2000);
    },
    // Show data handler - scroll to charts and fetch statistics
    showDataHandler() {
      if (this.selectedEventIds.size === 0) {
        alert('กรุณาเลือกกิจกรรมอย่างน้อย 1 รายการ');
        return;
      }
      // เปิดการแสดงผลกราฟและตาราง
      this.showStatistics = true;
      // เรียก fetch statistics
      this.fetchEventStatistics();
      // Scroll to summary section
      this.$nextTick(() => {
        const summaryCard = document.querySelector('.summary-card');
        if (summaryCard) {
          summaryCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    },

    // Handle window focus - auto refresh เมื่อกลับมาที่หน้านี้
    handleWindowFocus() {
      // ถ้ามีการแสดงสถิติอยู่ และมี event ที่เลือก ให้ refresh อัตโนมัติ
      if (this.showStatistics && this.selectedEventIds.size > 0) {
        console.log('🔄 Auto-refreshing data on window focus...');
        this.fetchEventStatistics();
      }
    },

    // Refresh data - อัพเดตข้อมูลล่าสุดหลังเช็คชื่อ
    async refreshData() {
      if (this.selectedEventIds.size === 0) {
        return;
      }

      console.log('🔄 Refreshing data...');
      
      // เรียก fetch statistics อีกครั้งเพื่อดึงข้อมูลล่าสุด
      await this.fetchEventStatistics();
      
      // แสดง notification (optional)
      console.log('✅ Data refreshed successfully!');
    },
    // Export handlers
    handleExportStart() {
      this.isExporting = true;
      this.exportProgress = { current: 0, total: 0, eventName: '' };
    },
    handleExportProgress(progress) {
      this.exportProgress = progress;
    },
    handleExportComplete(result) {
      console.log('Export completed:', result);
    },
    handleExportError(error) {
      console.error('Export error:', error);
    },
    handleExportEnd() {
      this.isExporting = false;
      this.exportProgress = { current: 0, total: 0, eventName: '' };
    }
  } // End methods
} // End export default
</script>
