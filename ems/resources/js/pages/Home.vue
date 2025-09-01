<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 font-prompt">
    <div class="max-w-7xl mx-auto">
      <!-- Dashboard Title -->
      <h2 class="text-4xl font-bold text-red-700 mb-8">Dashboard</h2>

      <!-- Statistics & Graphs -->
      <div class="grid grid-cols-2 gap-6 mb-8">
        <!-- Participation Overview -->
        <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
          <div class="text-lg font-semibold mb-2">Participation Overview</div>
          <div class="relative flex items-center justify-center mb-2">
            <svg width="140" height="140">
              <circle cx="70" cy="70" r="60" fill="#f7f7f7"/>
              <circle cx="70" cy="70" r="60" fill="none" stroke="#a855f7" stroke-width="14" stroke-dasharray="226" stroke-dashoffset="0"/>
              <circle cx="70" cy="70" r="60" fill="none" stroke="#ef4444" stroke-width="14" stroke-dasharray="38" stroke-dashoffset="188"/>
              <circle cx="70" cy="70" r="60" fill="none" stroke="#eab308" stroke-width="14" stroke-dasharray="113" stroke-dashoffset="75"/>
              <text x="70" y="85" text-anchor="middle" font-size="36" fill="#a855f7">{{ statistics.total }}</text>
            </svg>
          </div>
          <div class="flex justify-center gap-6 text-sm mb-2">
            <span class="flex items-center gap-1 text-purple-500"><span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span>Attending 60%</span>
            <span class="flex items-center gap-1 text-red-500"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>Not Attending 10%</span>
            <span class="flex items-center gap-1 text-yellow-500"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>Pending 30%</span>
          </div>
          <div class="text-center text-gray-500 text-xs">Person</div>
        </div>
        <!-- Event Participation Graph -->
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="flex justify-between items-center mb-2">
            <div class="text-lg font-semibold">Event Participation Graph</div>
            <select v-model="selectedTeam" class="border rounded-xl px-2 py-1 text-sm">
              <option value="">Team</option>
              <option v-for="team in teams" :key="team.name" :value="team.name">{{ team.name }}</option>
            </select>
          </div>
          <div class="flex items-end h-36 gap-6">
            <div v-for="team in teams" :key="team.name" class="flex flex-col items-center">
              <div :style="{height: team.value+'px'}" class="w-10 bg-gradient-to-t from-red-400 via-purple-400 to-pink-400 rounded-t-xl"></div>
              <span class="mt-2 text-xs font-medium text-gray-700">{{ team.name }}</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Mini Statistics -->
      <div class="grid grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
          <div class="text-base font-semibold mb-2">Attending</div>
          <div class="flex items-center">
            <svg width="70" height="70">
              <circle cx="35" cy="35" r="28" fill="#f3f3f3"/>
              <circle cx="35" cy="35" r="28" fill="none" stroke="#a855f7" stroke-width="8" stroke-dasharray="176" stroke-dashoffset="0"/>
              <text x="35" y="44" text-anchor="middle" font-size="22" fill="#a855f7">{{ statistics.attending }}</text>
            </svg>
            <span class="ml-2 text-gray-500 text-sm">person</span>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
          <div class="text-base font-semibold mb-2">Not Attending</div>
          <div class="flex items-center">
            <svg width="70" height="70">
              <circle cx="35" cy="35" r="28" fill="#f3f3f3"/>
              <circle cx="35" cy="35" r="28" fill="none" stroke="#ef4444" stroke-width="8" stroke-dasharray="29" stroke-dashoffset="147"/>
              <text x="35" y="44" text-anchor="middle" font-size="22" fill="#ef4444">{{ statistics.notAttending }}</text>
            </svg>
            <span class="ml-2 text-gray-500 text-sm">person</span>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow p-6 flex flex-col items-center">
          <div class="text-base font-semibold mb-2">Pending</div>
          <div class="flex items-center">
            <svg width="70" height="70">
              <circle cx="35" cy="35" r="28" fill="#f3f3f3"/>
              <circle cx="35" cy="35" r="28" fill="none" stroke="#eab308" stroke-width="8" stroke-dasharray="88" stroke-dashoffset="88"/>
              <text x="35" y="44" text-anchor="middle" font-size="22" fill="#eab308">{{ statistics.pending }}</text>
            </svg>
            <span class="ml-2 text-gray-500 text-sm">person</span>
          </div>
        </div>
      </div>

      <!-- Event Table -->
      <div class="bg-white rounded-2xl shadow p-6 mb-8">
        <div class="flex flex-wrap items-center mb-4 gap-2">
          <input v-model="search" class="border border-gray-200 rounded-xl px-4 py-3 w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="Search an event or select an event" />
          <button @click="onSearch" class="bg-red-600 hover:bg-red-700 text-white rounded-full px-4 py-3 shadow flex items-center">
            <i class="fas fa-search mr-2"></i> Search
          </button>
          <button class="bg-gray-200 px-4 py-3 rounded-xl shadow font-semibold">Filter</button>
          <div class="relative">
            <button @click="toggleSort" class="bg-gray-200 px-4 py-3 rounded-xl flex items-center shadow font-semibold">
              Sort <i class="fas fa-sort ml-2"></i>
            </button>
            <div v-if="showSort" class="absolute bg-white border rounded-xl shadow mt-2 z-10 w-48">
              <ul>
                <li v-for="option in sortOptions" :key="option" @click="setSort(option)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">{{ option }}</li>
              </ul>
            </div>
          </div>
          <button class="bg-gray-200 px-4 py-3 rounded-xl shadow font-semibold">Export</button>
          <button class="bg-red-600 hover:bg-red-700 text-white rounded-xl px-6 py-3 shadow font-semibold ml-auto">View Reports</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full border-separate border-spacing-0 rounded-2xl shadow bg-white">
            <thead>
              <tr class="bg-gray-100 text-gray-700 text-base">
                <th class="py-4 px-4 font-semibold rounded-tl-2xl">#</th>
                <th class="py-4 px-4 font-semibold">Event</th>
                <th class="py-4 px-4 font-semibold">Category</th>
                <th class="py-4 px-4 font-semibold">Date (D/M/Y)</th>
                <th class="py-4 px-4 font-semibold">Time</th>
                <th class="py-4 px-4 font-semibold">Accepted</th>
                <th class="py-4 px-4 font-semibold">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(event, index) in paginatedEvents" :key="event.id" class="hover:bg-gray-50 text-base text-gray-800">
                <td class="py-4 px-4 text-center">{{ index + 1 + (eventPage-1)*eventPerPage }}</td>
                <td class="py-4 px-4">{{ event.name }}</td>
                <td class="py-4 px-4">{{ event.category }}</td>
                <td class="py-4 px-4">{{ event.date }}</td>
                <td class="py-4 px-4">{{ event.time }}</td>
                <td class="py-4 px-4 text-center">{{ event.accepted }}</td>
                <td class="py-4 px-4 text-center">
                  <span :class="statusClass(event.status)">{{ event.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center space-x-2">
            <span>แสดง</span>
            <select v-model="eventPerPage" class="border rounded-xl px-2 py-1">
              <option v-for="n in [10,20,50]" :key="n" :value="n">{{ n }}</option>
            </select>
            <span>รายการ</span>
          </div>
          <div class="flex items-center space-x-2">
            <button @click="eventPage = 1" :disabled="eventPage === 1" class="px-3 py-1 rounded-xl border bg-white">ก่อนหน้า</button>
            <span>หน้า {{ eventPage }} / {{ eventTotalPages }}</span>
            <button @click="eventPage = eventTotalPages" :disabled="eventPage === eventTotalPages" class="px-3 py-1 rounded-xl border bg-white">ถัดไป</button>
          </div>
          <div>
            {{ paginatedEvents.length }} จาก {{ events.length }} รายการ
          </div>
        </div>
      </div>

      <!-- Employee Table -->
      <div class="bg-white rounded-2xl shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Employee Details</h2>
        <div class="overflow-x-auto">
          <table class="w-full border-separate border-spacing-0 rounded-2xl shadow bg-white">
            <thead>
              <tr class="bg-gray-100 text-gray-700 text-base">
                <th class="py-4 px-4 font-semibold rounded-tl-2xl">ID</th>
                <th class="py-4 px-4 font-semibold">Name</th>
                <th class="py-4 px-4 font-semibold">Nickname</th>
                <th class="py-4 px-4 font-semibold">Phone</th>
                <th class="py-4 px-4 font-semibold">Department</th>
                <th class="py-4 px-4 font-semibold">Team</th>
                <th class="py-4 px-4 font-semibold">Position</th>
                <th class="py-4 px-4 font-semibold">Event</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in paginatedEmployees" :key="employee.id" class="hover:bg-gray-50 text-base text-gray-800">
                <td class="py-4 px-4 text-center">{{ employee.id }}</td>
                <td class="py-4 px-4">{{ employee.name }}</td>
                <td class="py-4 px-4">{{ employee.nickname }}</td>
                <td class="py-4 px-4">{{ employee.phone }}</td>
                <td class="py-4 px-4">{{ employee.department }}</td>
                <td class="py-4 px-4">{{ employee.team }}</td>
                <td class="py-4 px-4">{{ employee.position }}</td>
                <td class="py-4 px-4">{{ employee.event }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center space-x-2">
            <span>แสดง</span>
            <select v-model="employeePerPage" class="border rounded-xl px-2 py-1">
              <option v-for="n in [10,20,50]" :key="n" :value="n">{{ n }}</option>
            </select>
            <span>รายการ</span>
          </div>
          <div class="flex items-center space-x-2">
            <button @click="employeePage = 1" :disabled="employeePage === 1" class="px-3 py-1 rounded-xl border bg-white">ก่อนหน้า</button>
            <span>หน้า {{ employeePage }} / {{ employeeTotalPages }}</span>
            <button @click="employeePage = employeeTotalPages" :disabled="employeePage === employeeTotalPages" class="px-3 py-1 rounded-xl border bg-white">ถัดไป</button>
          </div>
          <div>
            {{ paginatedEmployees.length }} จาก {{ employees.length }} รายการ
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "Dashboard",
  data() {
    return {
      search: "",
      showSort: false,
      sortOptions: [
        "ชื่อจาก A-Z", "ชื่อจาก Z-A", "จำนวนคนเข้าร่วมมากสุด", "จำนวนคนเข้าร่วมน้อยสุด",
        "จำนวนคนเข้าร่วมรับข้อมูลสูงสุด", "จำนวนคนเข้าร่วมรับข้อมูลต่ำสุด",
        "วันที่เริ่มต้นใหม่สุด", "วันที่เริ่มต้นเก่าสุด"
      ],
      selectedTeam: "",
      events: [],
      eventPage: 1,
      eventPerPage: 10,
      employees: [],
      employeePage: 1,
      employeePerPage: 10,
      statistics: {
        total: 367,
        attending: 61,
        notAttending: 12,
        pending: 31,
      },
      teams: [
        { name: "CodeCraft", value: 60 },
        { name: "DevDynamos", value: 80 },
        { name: "QuantumCoders", value: 40 },
        { name: "CyberGuardians", value: 90 }
      ]
    };
  },
  computed: {
    eventTotalPages() {
      return Math.ceil(this.events.length / this.eventPerPage) || 1;
    },
    paginatedEvents() {
      const start = (this.eventPage - 1) * this.eventPerPage;
      return this.events.slice(start, start + this.eventPerPage);
    },
    employeeTotalPages() {
      return Math.ceil(this.employees.length / this.employeePerPage) || 1;
    },
    paginatedEmployees() {
      const start = (this.employeePage - 1) * this.employeePerPage;
      return this.employees.slice(start, start + this.employeePerPage);
    }
  },
  methods: {
    statusClass(status) {
      if (status === "Done") return "bg-green-500 text-white px-2 py-1 rounded-full";
      if (status === "Upcoming") return "bg-gray-300 text-gray-700 px-2 py-1 rounded-full";
      return "bg-yellow-500 text-white px-2 py-1 rounded-full";
    },
    toggleSort() {
      this.showSort = !this.showSort;
    },
    setSort(option) {
      // TODO: implement sort logic
      this.showSort = false;
    },
    onSearch() {
      // TODO: implement search logic
    }
  },
  mounted() {
    // ดึงข้อมูลจาก API
    axios.get('/api/event-info').then(res => {
      this.events = res.data;
    });
    axios.get('/api/get-employees').then(res => {
      this.employees = res.data;
    });
   }
};
</script>

<style scoped>
.font-prompt {
  font-family: 'Prompt', sans-serif;
}
</style>