<template>
    <!-- Toolbar -->
    <div
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <!-- Search -->
        <div class="flex w-full items-center gap-2 sm:max-w-xl">
            <div class="relative flex-1">
                <input
                    v-model="query"
                    type="text"
                    placeholder="Search..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
                />
            </div>
            <button
                class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
                @click="onSearch"
                aria-label="search"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path
                        d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"
                    />
                </svg>
            </button>
        </div>

        <!-- Right controls -->
        <div class="flex items-center gap-2">
            <div class="relative" @keydown.escape="sortOpen = false">
                <button
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="sortOpen = !sortOpen"
                    :title="
                        sortDir === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"
                        />
                    </svg>
                    <span>Sort</span>
                    <span class="text-gray-400"
                        >({{
                            sortDir === "desc"
                                ? "วันที่ล่าสุด"
                                : "วันที่เก่าสุด"
                        }})</span
                    >
                </button>

                <div
                    v-if="sortOpen"
                    class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg"
                >
                    <button
                        class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50"
                        @click="applySort('desc')"
                    >
                        วันที่ล่าสุด
                    </button>
                    <button
                        class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50"
                        @click="applySort('asc')"
                    >
                        วันที่เก่าสุด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-200">
        <table class="min-w-full table-fixed">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr class="text-gray-700 text-sm font-semibold">
                    <th class="w-14 px-4 py-3 text-left">#</th>
                    <th class="w-28 px-4 py-3 text-left whitespace-nowrap">
                        Event
                    </th>
                    <th class="w-56 px-4 py-3 text-left">Category</th>
                    <th class="w-32 px-4 py-3 text-left">Date(D/M/Y)</th>
                    <th class="w-56 px-4 py-3 text-left">Time</th>
                    <th class="w-56 px-4 py-3 text-left">Invited</th>
                    <th class="w-56 px-4 py-3 text-left">Accepted</th>
                    <th class="w-40 px-4 py-3 text-left">Status</th>
                    <!-- เปลี่ยนชื่อหัวคอลัมน์เป็น Deleted Date -->
                    <th class="w-40 px-4 py-3 text-left whitespace-nowrap">
                        Deleted Date
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                <tr
                    v-for="(ev, idx) in pagedRows"
                    :key="ev.id"
                    class="text-sm text-gray-700 hover:bg-gray-50"
                >
                    <td class="px-4 py-3">{{ startIndex + idx + 1 }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ ev.evn_title || "N/A" }}
                    </td>
                    <td class="px-4 py-3">{{ ev.category || "N/A" }}</td>
                    <td class="px-4 py-3">{{ formatDate(ev.date) }}</td>
                    <td class="px-4 py-3">{{ ev.time || "N/A" }}</td>
                    <td class="px-4 py-3">{{ ev.invited || "N/A" }}</td>
                    <td class="px-4 py-3">{{ ev.accepted || "N/A" }}</td>
                    <td class="px-4 py-3">{{ ev.status || "N/A" }}</td>
                    <!-- แสดงวันที่ถูกลบ -->
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ formatDate(ev.deleted_at) }}
                    </td>
                </tr>

                <tr v-if="!loading && pagedRows.length === 0">
                    <td
                        colspan="9"
                        class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                        ไม่พบข้อมูลที่ค้นหา
                    </td>
                </tr>

                <tr v-if="loading">
                    <td
                        colspan="9"
                        class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                        กำลังโหลดข้อมูล...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div
        class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
    >
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <span>แสดง</span>
            <select
                v-model.number="pageSize"
                class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none"
            >
                <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">
                    {{ n }}
                </option>
            </select>
            <span>{{ visibleCountText }}</span>
        </div>

        <div class="flex items-center gap-2">
            <button
                class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                :disabled="page === 1"
                @click="page--"
            >
                ก่อนหน้า
            </button>
            <span class="text-sm text-gray-600"
                >หน้า {{ page }} / {{ totalPages || 1 }}</span
            >
            <button
                class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                :disabled="page === totalPages || totalPages === 0"
                @click="page++"
            >
                ถัดไป
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "HistoryEvent",
    data() {
        return {
            events: [],
            query: "",
            sortDir: "desc",
            sortOpen: false,
            page: 1,
            pageSize: 10,
            loading: false,
        };
    },
    created() {
        this.fetchEvents();
    },
    computed: {
        // 1) กรองด้วยคำค้น
        filteredRows() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.events || [];
            return (this.events || []).filter((ev) => {
                const name = (ev.evn_title ?? "").toLowerCase();
                const cat = (ev.category ?? "").toLowerCase();
                const date = (ev.date ?? "").toLowerCase();
                const time = (ev.time ?? "").toLowerCase();
                return (
                    name.includes(q) ||
                    cat.includes(q) ||
                    date.includes(q) ||
                    time.includes(q)
                );
            });
        },

        // 2) เรียงตามวันที่ (ถ้าแปลงเป็น Date ไม่ได้ จะตกไปท้าย/ต้นตามที่กำหนด)
        sortedRows() {
            const rows = [...this.filteredRows];
            const dir = this.sortDir === "asc" ? 1 : -1;
            // พยายาม parse date แบบยืดหยุ่น
            const toTS = (d) => {
                if (!d) return NaN;
                // รองรับ dd/mm/yyyy หรือ yyyy-mm-dd
                if (/^\d{2}\/\d{2}\/\d{4}$/.test(d)) {
                    const [dd, mm, yyyy] = d.split("/");
                    return new Date(`${yyyy}-${mm}-${dd}T00:00:00`).getTime();
                }
                return new Date(d).getTime();
            };
            rows.sort((a, b) => {
                const A = toTS(a.date);
                const B = toTS(b.date);
                if (isNaN(A) && isNaN(B)) return 0;
                if (isNaN(A)) return 1 * dir; // ไม่มีค่าวางท้าย/ต้น
                if (isNaN(B)) return -1 * dir;
                return (A - B) * dir;
            });
            return rows;
        },

        // 3) หน้าที่แสดง
        pagedRows() {
            const start = this.startIndex;
            const end = start + this.pageSize;
            return this.sortedRows.slice(start, end);
        },

        totalPages() {
            const n = this.sortedRows.length;
            return n === 0 ? 0 : Math.ceil(n / this.pageSize);
        },

        startIndex() {
            return (this.page - 1) * this.pageSize;
        },

        visibleCountText() {
            const total = this.sortedRows.length;
            if (total === 0) return "0 รายการ";
            const from = this.startIndex + 1;
            const to = Math.min(this.startIndex + this.pageSize, total);
            return `${from}–${to} จาก ${total} รายการ`;
        },
    },
    watch: {
        // เวลาเปลี่ยน pageSize ให้รีเซ็ตไปหน้า 1
        pageSize() {
            this.page = 1;
        },
        // เวลาเปลี่ยน query ให้รีเซ็ตไปหน้า 1
        query() {
            this.page = 1;
        },
    },
    methods: {
        async fetchEvents() {
            this.loading = true;
            try {
                const { data } = await axios.get("/api/history-events", {
                    headers: { Accept: "application/json" },
                });
                const payload = res.data;
                // ปรับรูปแบบให้อยู่ในรูป Array เสมอ
                let rows = [];
                if (Array.isArray(payload)) {
                    rows = payload;
                } else if (payload && typeof payload === "object") {
                    if (Array.isArray(payload.data))
                        rows = payload.data; // กรณี { data: [...] }
                    else if (Array.isArray(payload.events))
                        rows = payload.events; // กรณี { events: [...] }
                    else if (Array.isArray(payload.items))
                        rows = payload.items; // กรณี { items: [...] }
                    else if (Array.isArray(payload.results))
                        rows = payload.results; // กรณี { results: [...] }
                } else if (typeof payload === "string") {
                    // ได้ HTML / error page มาก็จะเข้าก้อนนี้
                    throw new Error(
                        "Expected JSON array, but got string/HTML from API"
                    );
                }

                this.events = rows.map((ev) => ({
                    ...ev,
                    event__name: ev.evn_title ?? "N/A",
                    category: ev.category ?? "N/A",
                    date: ev.evn_date ?? ev.evn_start_date ?? "N/A",
                    time:
                        ev.evn_time ??
                        (ev.evn_start_time && ev.evn_end_time
                            ? `${ev.evn_start_time} - ${ev.evn_end_time}`
                            : "N/A"),
                    invited: ev.invited ?? ev.invited_count ?? 0,
                    accepted: ev.accepted ?? ev.accepted_count ?? 0,
                    status: ev.evn_status ?? "N/A",
                    deleted_at: ev.deleted_at ?? null,
                }));
            } catch (err) {
                console.error("Error fetching events:", err);
                this.events = []; // กัน UI พัง
            } finally {
                this.loading = false;
            }
        },

        formatDate(val) {
            if (!val) return "—";
            // รองรับ iso หรือ dd/mm/yyyy
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) return val;
            const dt = new Date(val);
            if (isNaN(dt)) return "—";
            const dd = String(dt.getDate()).padStart(2, "0");
            const mm = String(dt.getMonth() + 1).padStart(2, "0");
            const yy = String(dt.getFullYear());
            return `${dd}/${mm}/${yy}`;
        },

        applySort(dir) {
            this.sortDir = dir;
            this.sortOpen = false;
            // รีหน้าไปหน้าแรกเพื่อให้ผลคาดเดาได้
            this.page = 1;
        },

        onSearch() {
            // ให้ผลเหมือนพิมพ์แล้ว enter: แค่รีหน้าไปหน้า 1
            this.page = 1;
        },
    },
};
</script>
