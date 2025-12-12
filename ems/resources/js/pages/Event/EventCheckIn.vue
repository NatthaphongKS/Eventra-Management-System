<!-- src/pages/EventCheckIn.vue -->
<template>
    <!-- ===== Page Header ===== -->
    <p class="ml-6 text-[32px] font-semibold text-neutral-800">
        {{ eventTitle }}
    </p>

    <div class="m-6 flex flex-col gap-3">
        <!-- ===== Search & Filters ===== -->
        <div class="flex flex-wrap items-center gap-3 w-full">
            <!-- ✅ Search bar ขยายเต็มแนว -->
            <div class="flex-1">
                <SearchBar
                    v-model="search"
                    placeholder="Search Employee ID / Name / Nickname"
                    @search="() => (page = 1)"
                    class="!w-full [&_input]:h-[44px] [&_input]:text-sm [&_button]:h-10 [&_button]:w-10 [&_svg]:w-5 [&_svg]:h-5"
                />
            </div>

            <!-- ✅ ส่วน filter ชิดขวา -->
            <div class="flex flex-row flex-wrap items-center gap-2 mt-8">
                <EmployeeDropdown
                    label="Company ID"
                    v-model="selectedCompanyIds"
                    :options="companyIdOptions"
                />
                <EmployeeDropdown
                    label="Department"
                    v-model="selectedDepartmentIds"
                    :options="departmentOptions"
                />
                <EmployeeDropdown
                    label="Team"
                    v-model="selectedTeamIds"
                    :options="teamOptions"
                />
                <EmployeeDropdown
                    label="Position"
                    v-model="selectedPositionIds"
                    :options="positionOptions"
                />
            </div>
        </div>

        <!-- ===== Status Summary Pills ===== -->
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <button
                class="status-pill"
                :class="
                    statusFilter === 'accepted' ? 'ring-2 ring-emerald-300' : ''
                "
                @click="setStatus('accepted')"
            >
                <span
                    class="inline-flex h-2 w-2 rounded-full bg-emerald-500"
                ></span>
                <span class="ml-2"
                    >{{ counts.accepted }}/{{ totals.accepted }} Accepted</span
                >
            </button>

            <button
                class="status-pill"
                :class="statusFilter === 'dinied' ? 'ring-2 ring-rose-300' : ''"
                @click="setStatus('dinied')"
            >
                <span
                    class="inline-flex h-2 w-2 rounded-full bg-rose-500"
                ></span>
                <span class="ml-2"
                    >{{ counts.dinied }}/{{ totals.dinied }} dinied</span
                >
            </button>

            <button
                class="status-pill"
                :class="
                    statusFilter === 'pending' ? 'ring-2 ring-amber-300' : ''
                "
                @click="setStatus('pending')"
            >
                <span
                    class="inline-flex h-2 w-2 rounded-full bg-amber-500"
                ></span>
                <span class="ml-2"
                    >{{ counts.pending }}/{{ totals.pending }} Pending</span
                >
            </button>

            <button
                class="status-pill"
                :class="
                    statusFilter === 'notinvited' ? 'ring-2 ring-slate-300' : ''
                "
                @click="setStatus('notinvited')"
            >
                <span
                    class="inline-flex h-2 w-2 rounded-full bg-slate-400"
                ></span>
                <span class="ml-2"
                    >{{ counts.notinvited }}/{{ totals.notinvited }} Not
                    Invited</span
                >
            </button>

            <button
                class="status-pill bg-violet-600 text-white hover:bg-violet-700"
                :class="statusFilter === 'all' ? 'ring-2 ring-violet-300' : ''"
                @click="setStatus('all')"
            >
                {{ filteredBase.length }}/{{ rows.length }} All
            </button>
        </div>

        <!-- ===== Data Table ===== -->
        <DataTable
            :rows="pagedRows"
            :columns="columns"
            :loading="loading"
            v-model:page="page"
            v-model:pageSize="pageSize"
            :totalItems="sortedRows.length"
            rowKey="empId"
            selectable
            v-model="selectedIds"
            v-model:sortKey="sortKey"
            v-model:sortOrder="sortOrder"
            @sort="onSort"
            :showRowNumber="true"
            :pageSizeOptions="[10, 20, 50, 100]"
            :rowClass="rowClass"
            @checkbox-checkin="handleCheckin"
            @check-all-page="handleCheckAllOnPage"
        >
            <!-- ✅ Column: Full Name (display + secondary line optional) -->
            <template #cell-fullName="{ row }">
                <div class="flex flex-col">
                    <span class="font-medium text-slate-800">{{
                        row.empFullname
                    }}</span>
                    <!-- <span class="text-xs text-slate-500">{{ row.empNickname }}</span> -->
                </div>
            </template>

            <!-- ✅ Column: Invite Status (with badge color) -->
            <template #cell-status="{ row }">
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="statusClass(mapInvite(row.empInviteStatus))"
                >
                    {{ statusLabel(mapInvite(row.empInviteStatus)) }}
                </span>
            </template>
        </DataTable>

        <!-- ===== Error Bar ===== -->
        <p
            v-if="error"
            class="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700"
        >
            {{ error }}
        </p>
    </div>
</template>

<script setup>
/**
 * EventCheckIn.vue
 * - จัดระเบียบโค้ดเป็นหมวดหมู่: Fetch • Normalize • Filters • Search/Status • Derivations • Columns • Sort/Paginate • Handlers
 * - เพิ่มคอมเมนท์อธิบายเพื่อบำรุงรักษาง่าย และแก้จุดสับสนเรื่อง key/field
 */
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRoute, onBeforeRouteUpdate } from "vue-router";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import axios from "axios";

/* ===== Routing / Params ===== */
const route = useRoute();
const eveId = computed(() => route.params.eveId);

/* ===== State ===== */
const loading = ref(true);
const error = ref("");
const rows = ref([]);
const eventTitle = ref("Event Check-In"); // จะอัปเดตจาก API ถ้ามี

/* ===== Fetch ===== */
let controller = null;
async function fetchEmployeeForCheckin(id) {
    controller?.abort?.();
    controller = new AbortController();
    const res = await fetch(`/api/getEmployeeForCheckin/eveId/${id}`, {
        headers: { Accept: "application/json" },
        credentials: "include",
        signal: controller.signal,
    });
    if (!res.ok) throw new Error(`โหลดข้อมูลไม่สำเร็จ (${res.status})`);
    return res.json();
}

async function load(id) {
    loading.value = true;
    error.value = "";
    try {
        const data = await fetchEmployeeForCheckin(id);

        // ชื่ออีเวนต์: รองรับทั้ง array และ object
        if (Array.isArray(data) && data.length) {
            if (data[0]?.eveTitle) eventTitle.value = data[0].eveTitle;
        } else if (data?.eveTitle) {
            eventTitle.value = data.eveTitle;
        }

        // แปลงข้อมูลให้ฟิลด์แน่นก่อนเข้า DataTable
        rows.value = Array.isArray(data)
            ? normalize(data)
            : normalize(data?.data ?? data?.items ?? []);
        selectedIds.value = rows.value
            .filter((r) => Number(r.empCheckinStatus) === 1)
            .map((r) => r.empId);

        buildFilterOptions();
    } catch (e) {
        console.error(e);
        error.value = e?.message || "เกิดข้อผิดพลาดในการโหลดข้อมูล";
        rows.value = [];
        buildFilterOptions();
    } finally {
        loading.value = false;
    }
}

// ✅ normalize: map ฟิลด์ให้แน่น (รองรับสะกดผิด empCompamyId)
function normalize(list) {
    if (!Array.isArray(list)) return [];
    return list.map((r) => ({
        eveId: r.eveId ?? null,
        eveTitle: r.eveTitle ?? "",
        empId: r.empId, // ใช้เป็น rowKey หลัก
        empCompanyId: r.empCompanyId ?? r.empCompamyId ?? "",
        empFullname: r.empFullname ?? "",
        empNickname: r.empNickname ?? "",
        empDepartment: r.empDepartment ?? "",
        empTeam: r.empTeam ?? "",
        empPosition: r.empPosition ?? "",
        empInviteStatus: (r.empInviteStatus ?? "").toString(),
        empCheckinStatus: Number(r.empCheckinStatus ?? 0),
    }));
}

onMounted(() => load(eveId.value));
onUnmounted(() => controller?.abort?.());

// เมื่อเปลี่ยน route param: โหลดข้อมูลใหม่
onBeforeRouteUpdate((to) => {
    if (to.params.eveId && to.params.eveId !== eveId.value) {
        load(to.params.eveId);
    }
});

/* ===== Filter Options ===== */
const companyIdOptions = ref([]);
const departmentOptions = ref([]);
const teamOptions = ref([]);
const positionOptions = ref([]);

const selectedCompanyIds = ref([]);
const selectedDepartmentIds = ref([]);
const selectedTeamIds = ref([]);
const selectedPositionIds = ref([]);

function toOptions(arr, getLabel = (x) => x, getValue = (x) => x) {
    const uniq = [...new Set(arr.filter(Boolean))];
    return uniq.map((v) => ({ label: getLabel(v), value: getValue(v) }));
}

function buildFilterOptions() {
    companyIdOptions.value = toOptions(
        rows.value.map((r) => r.empCompanyId),
        (v) => String(v),
        (v) => v
    );
    departmentOptions.value = toOptions(rows.value.map((r) => r.empDepartment));
    teamOptions.value = toOptions(rows.value.map((r) => r.empTeam));
    positionOptions.value = toOptions(rows.value.map((r) => r.empPosition));
}

/* ===== Search & Status ===== */
const search = ref("");
const statusFilter = ref("all");

function setStatus(s) {
    statusFilter.value = s;
    page.value = 1;
}

function mapInvite(ans) {
    const a = String(ans || "").toLowerCase();
    if (a.includes("accept")) return "accepted";
    if (a.includes("deny") || a.includes("reject") || a.includes("decline"))
        return "dinied";
    if (a.includes("pending")) return "pending";
    return "notinvited";
}

function statusLabel(key) {
    return {
        accepted: "Accepted",
        dinied: "dinied",
        pending: "Pending",
        notinvited: "Not Invited",
    }[key];
}

function statusClass(key) {
    return {
        accepted: "bg-emerald-100 text-emerald-700",
        dinied: "bg-rose-100 text-rose-700",
        pending: "bg-amber-100 text-amber-700",
        notinvited: "bg-slate-100 text-slate-600",
    }[key];
}

/* ===== Base Filtered List ===== */
const filteredBase = computed(() => {
    const q = search.value.trim().toLowerCase();
    let list = rows.value;

    if (q) {
        list = list.filter((r) =>
            [String(r.empCompanyId), r.empFullname, r.empNickname].some((f) =>
                f?.toLowerCase().includes(q)
            )
        );
    }

    if (statusFilter.value !== "all") {
        list = list.filter(
            (r) => mapInvite(r.empInviteStatus) === statusFilter.value
        );
    }

    if (selectedCompanyIds.value?.length) {
        const needles = selectedCompanyIds.value
            .map((x) => String(x).trim())
            .filter(Boolean);
        list = list.filter((r) => {
            const idStr = String(r.empCompanyId ?? "");
            return needles.some((n) => idStr.includes(n));
        });
    }

    if (selectedDepartmentIds.value?.length) {
        const set = new Set(selectedDepartmentIds.value);
        list = list.filter((r) => set.has(r.empDepartment));
    }

    if (selectedTeamIds.value?.length) {
        const set = new Set(selectedTeamIds.value);
        list = list.filter((r) => set.has(r.empTeam));
    }

    if (selectedPositionIds.value?.length) {
        const set = new Set(selectedPositionIds.value);
        list = list.filter((r) => set.has(r.empPosition));
    }

    return list;
});

/* ===== Status Counters ===== */
const totals = computed(() => {
    const t = { accepted: 0, dinied: 0, pending: 0, notinvited: 0 };
    rows.value.forEach((r) => t[mapInvite(r.empInviteStatus)]++);
    return t;
});

const counts = computed(() => {
    const c = { accepted: 0, dinied: 0, pending: 0, notinvited: 0 };
    filteredBase.value.forEach((r) => c[mapInvite(r.empInviteStatus)]++);
    return c;
});

/* ===== Table Columns ===== */
const columns = [
    {
        key: "empCompanyId",
        label: "ID",
        sortable: true,
        class: "min-w-[120px] text-left",
    },
    {
        key: "fullName",
        label: "Name",
        sortable: true,
        class: "min-w-[220px] text-left",
    },
    {
        key: "empNickname",
        label: "Nickname",
        sortable: true,
        class: "min-w-[120px] text-left",
    },
    {
        key: "empDepartment",
        label: "Department",
        sortable: true,
        class: "min-w-[180px] text-left",
    },
    {
        key: "empTeam",
        label: "Team",
        sortable: true,
        class: "min-w-[160px] text-left",
    },
    {
        key: "empPosition",
        label: "Position",
        sortable: true,
        class: "min-w-[200px] text-left",
    },
    {
        key: "status",
        label: "Invite",
        sortable: false,
        class: "min-w-[120px] text-center",
    },
];

/* ===== Sort & Paginate ===== */
const page = ref(1);
const pageSize = ref(10);
const sortKey = ref("empCompanyId");
const sortOrder = ref("asc");
const selectedIds = ref([]);

const sortedRows = computed(() => {
    if (!sortKey.value) return filteredBase.value;
    const dir = sortOrder.value === "desc" ? -1 : 1;
    const getter = (r) =>
        sortKey.value === "fullName"
            ? (r.empFullname || "").trim()
            : r[sortKey.value];

    return [...filteredBase.value].sort((a, b) => {
        const va = getter(a);
        const vb = getter(b);
        if (va == null && vb == null) return 0;
        if (va == null) return -1 * dir;
        if (vb == null) return 1 * dir;
        return String(va).localeCompare(String(vb), "th") * dir;
    });
});

const pagedRows = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    return sortedRows.value.slice(start, start + pageSize.value);
});

function onSort({ key, order }) {
    sortKey.value = key;
    sortOrder.value = order;
}

function rowClass(row) {
    // ✅ แก้
    return Number(row?.empCheckinStatus) === 1
        ? "bg-red-100 text-left" // แดงค้างเมื่อเช็กแล้ว
        : "text-left";
}

/* ===== Handlers ===== */
/**
 * handleCheckin
 * - keys มาจาก DataTable (สอดคล้องกับ rowKey = empId)
 * - แนบ eveId ปัจจุบันไปกับ route (ใช้ .value)
 */
async function handleCheckin({ keys, checked }) {
    console.log(
        "Handle check-in for keys:",
        keys,
        "checked:",
        checked,
        "eveId:",
        eveId.value
    );
    for (const empId of keys) {
        try {
            const res = await fetch(
                `/api/updateEmployeeAttendance/empId/${empId}/eveId/${eveId.value}`,
                {
                    method: "PUT", // ใช้ PUT หรือ PATCH แทน GET เพราะเป็นการอัปเดต
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                }
            );
            const result = await res.json(); // ✅ ต้องใส่วงเล็บ () และ await
            console.log(result);
        } catch (err) {
            console.error("❌ update failed:", err);
        }
    }
}

async function handleCheckAllOnPage() {
    res = await axios
        .put(`/updateEmployeeAttendanceAll/eveId/${eveId.value}`)
        .then((response) => {
            console.log(response.data);
        })
        .catch((error) => {
            console.error(
                "/api/updateEmployeeAttendanceAll/eveId/ failed",
                error
            );
        });
    console.log("Check all on page for eveId:", eveId);
    fetchEmployeeForCheckin(eveId);
}

watch(rows, (list) => {
    selectedIds.value = list
        .filter((r) => Number(r.empCheckinStatus) === 1)
        .map((r) => r.empId);
});
</script>

<style scoped>
.status-pill {
    @apply inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-sm text-neutral-700 hover:bg-neutral-50;
}
</style>
