<template>
    <p class="ml-6 text-[32px] font-semibold text-neutral-800">
        {{ eventTitle }}
    </p>

    <div class="m-6 flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-3 w-full">
            <div class="flex-1">
                <SearchBar
                    v-model="search"
                    placeholder="Search Employee ID / Name / Nickname"
                    @search="() => (page = 1)"
                    class=""
                />
            </div>

            <div class="flex flex-row flex-wrap items-center gap-2 mt-8">
                <div @click="refreshOthers('company')">
                    <EmployeeDropdown
                        :key="dropdownKeys.company"
                        label="Company ID"
                        v-model="selectedCompanyIds"
                        :options="companyIdOptions"
                    />
                </div>

                <div @click="refreshOthers('department')">
                    <EmployeeDropdown
                        :key="dropdownKeys.department"
                        label="Department"
                        v-model="selectedDepartmentIds"
                        :options="departmentOptions"
                    />
                </div>

                <div @click="refreshOthers('team')">
                    <EmployeeDropdown
                        :key="dropdownKeys.team"
                        label="Team"
                        v-model="selectedTeamIds"
                        :options="teamOptions"
                    />
                </div>

                <div @click="refreshOthers('position')">
                    <EmployeeDropdown
                        :key="dropdownKeys.position"
                        label="Position"
                        v-model="selectedPositionIds"
                        :options="positionOptions"
                    />
                </div>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'accepted'
                        ? 'bg-[#00A73D] text-white border-[#00A73D] shadow-lg'
                        : 'bg-[#F1FDF4] text-[#00A73D] border-[#E9E9E9] hover:bg-[#E2FBE9]'
                "
                @click="setStatus('accepted')"
            >
                <span class="ml-2"
                    >{{ checkinStats.accepted }}/{{
                        totals.accepted
                    }}
                    Accepted</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'denied'
                        ? 'bg-[#C91C23] text-white border-[#C91C23] shadow-lg'
                        : 'bg-[#FFF8F8] text-[#C91C23] border-[#E9E9E9] hover:bg-[#FFD4D4]'
                "
                @click="setStatus('denied')"
            >
                <span class="ml-2"
                    >{{ checkinStats.denied }}/{{
                        totals.denied
                    }}
                    Declined</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'pending'
                        ? 'bg-[#389FDC] text-white border-[#389FDC] shadow-lg'
                        : 'bg-[#F0F9FF] text-[#389FDC] border-[#E9E9E9] hover:bg-[#CEEBFF]'
                "
                @click="setStatus('pending')"
            >
                <span class="ml-2"
                    >{{ checkinStats.pending }}/{{
                        totals.pending
                    }}
                    Pending</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'notInvited'
                        ? 'bg-[#696969] text-white border-[#696969] shadow-lg'
                        : 'bg-[#F5F5F5] text-[#696969] border-[#E9E9E9] hover:bg-[#E3E0E0]'
                "
                @click="setStatus('notInvited')"
            >
                <span class="ml-2"
                    >{{ checkinStats.notInvited }}/{{ totals.notInvited }} Not
                    Invited</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'all'
                        ? 'bg-[#7107E7] text-white border-[#7107E7] shadow-lg'
                        : 'bg-[#F5F3FF] text-[#7107E7] border-[#E9E9E9] hover:bg-[#E9E4FF]'
                "
                @click="setStatus('all')"
            >
                <span class="ml-2"
                    >{{ checkinStats.notInvited + checkinStats.denied + checkinStats.pending + checkinStats.accepted }}/{{ rows.length }} All</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'CheckIn'
                        ? 'bg-[#007A56] text-white border-[#007A56] shadow-lg'
                        : 'bg-[#ECFEF6] text-[#007A56] border-[#E9E9E9] hover:bg-[#D1FAE5]'
                "
                @click="setStatus('CheckIn')"
            >
                <span class="ml-2"
                    >{{ rows.length - totals.notCheckIn }}/{{ rows.length }} Attended</span
                >
            </button>

            <button
                class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                :class="
                    statusFilter === 'notCheckIn'
                        ? 'bg-[#C70036] text-white border-[#C70036] shadow-lg'
                        : 'bg-[#FFF2F2] text-[#C70036] border-[#E9E9E9] hover:bg-[#FFE4E6]'
                "
                @click="setStatus('notCheckIn')"
            >
                <span class="ml-2"
                    >{{ rows.length - totals.checkIn }}/{{ rows.length }} Not
                    Attended</span
                >
            </button>
        </div>

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
            <template #cell-fullName="{ row }">
                <div class="flex flex-col">
                    <span class="font-medium text-slate-800">{{
                        row.empFullname
                    }}</span>
                </div>
            </template>

            <template #cell-status="{ row }">
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="statusClass(mapInvite(row.empInviteStatus))"
                >
                    {{ statusLabel(mapInvite(row.empInviteStatus)) }}
                </span>
            </template>
        </DataTable>

        <span class="inline-block">
            <BackButton @click="handleBack">Back</BackButton>
        </span>

        <p
            v-if="error"
            class="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700"
        >
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRoute, onBeforeRouteUpdate, useRouter } from "vue-router";
import DataTable from "@/components/DataTable.vue";
import SearchBar from "@/components/SearchBar.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import BackButton from "@/components/BackButton.vue";
import axios from "axios";

const router = useRouter();
const route = useRoute();
const eveId = computed(() => route.params.eveId);

/* ===== State ===== */
const loading = ref(true);
const error = ref("");
const rows = ref([]);
const eventTitle = ref("Event Check-In");
const selectedIds = ref([]);
const page = ref(1);
const pageSize = ref(10);
const sortKey = ref("empCompanyId");
const sortOrder = ref("asc");
const statusFilter = ref("all");

/* ===== Logic สำหรับการเปิด Dropdown ทีละอัน (แบบไม่แก้ Component) ===== */
const dropdownKeys = ref({
    company: 0,
    department: 0,
    team: 0,
    position: 0,
});

const refreshOthers = (current) => {
    // เมื่อคลิกที่อันใดอันหนึ่ง เราจะบวกค่า key ของอันอื่นๆ
    // เพื่อให้ Vue บังคับ Re-render อันที่ไม่ได้ถูกคลิก (ซึ่งจะทำให้สถานะ open ภายในกลับเป็น false)
    Object.keys(dropdownKeys.value).forEach((key) => {
        if (key !== current) {
            dropdownKeys.value[key]++;
        }
    });
};

/* ===== Fetch & Data Normalization ===== */
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

function normalize(list) {
    if (!Array.isArray(list)) return [];
    return list.map((r) => ({
        eveId: r.eveId ?? null,
        eveTitle: r.eveTitle ?? "",
        empId: r.empId,
        empFullId: r.empFullId ?? "",
        empCompanyId: r.empCompanyId,
        empFullname: r.empFullname ?? "",
        empNickname: r.empNickname ?? "",
        empDepartment: r.empDepartment ?? "",
        empTeam: r.empTeam ?? "",
        empPosition: r.empPosition ?? "",
        empInviteStatus: (r.empInviteStatus ?? "").toString(),
        empCheckinStatus: Number(r.empCheckinStatus ?? 0),
    }));
}

async function load(id) {
    //loading.value = true;
    error.value = "";
    try {
        const data = await fetchEmployeeForCheckin(id);
        if (Array.isArray(data) && data.length) {
            if (data[0]?.eveTitle) eventTitle.value = data[0].eveTitle;
        }
        rows.value = normalize(Array.isArray(data) ? data : data?.data ?? []);
        buildFilterOptions();
    } catch (e) {
        if (e.name !== "AbortError") {
            console.error(e);
            error.value = e?.message || "เกิดข้อผิดพลาดในการโหลดข้อมูล";
        }
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(eveId.value));
onUnmounted(() => controller?.abort?.());
onBeforeRouteUpdate((to) => {
    if (to.params.eveId && to.params.eveId !== eveId.value)
        load(to.params.eveId);
});

/* ===== Filter Logic ===== */
const companyIdOptions = ref([]);
const departmentOptions = ref([]);
const teamOptions = ref([]);
const positionOptions = ref([]);

const selectedCompanyIds = ref([]);
const selectedDepartmentIds = ref([]);
const selectedTeamIds = ref([]);
const selectedPositionIds = ref([]);

function buildFilterOptions() {
    const toOpt = (arr) =>
        [...new Set(arr.filter(Boolean))].map((v) => ({
            label: String(v),
            value: v,
        }));
    companyIdOptions.value = toOpt(rows.value.map((r) => r.empCompanyId));
    departmentOptions.value = toOpt(rows.value.map((r) => r.empDepartment));
    teamOptions.value = toOpt(rows.value.map((r) => r.empTeam));
    positionOptions.value = toOpt(rows.value.map((r) => r.empPosition));
}

function mapInvite(ans) {
    const a = String(ans || "").toLowerCase();
    if (a.includes("accept")) return "accepted";
    if (a.includes("denied")) return "denied";
    if (a.includes("invalid")) return "pending";
    return "notInvited";
}

function mapCheckIn(ans) {
    const a = String(ans || "").toLowerCase();
    if (a.includes("accept")) return "accepted";
    if (a.includes("denied")) return "denied";
    if (a.includes("invalid")) return "pending";
    return "notInvited";
}

const statusLabel = (key) =>
    ({
        accepted: "Accepted",
        denied: "Declined",
        pending: "Pending",
        notInvited: "Not Invited",
        checkIn: "Attended",
        notCheckIn: "Not Attended",
    }[key]);
const statusClass = (key) =>
    ({
        accepted: "bg-[#F1FDF4] text-[#00A73D] border-[#E9E9E9] ",
        denied: "bg-[#FFF8F8] text-[#C91C23] border-[#E9E9E9] ",
        pending: "bg-[#F0F9FF] text-[#389FDC] border-[#E9E9E9] ",
        notInvited: "bg-[#F5F5F5] text-[#696969] border-[#E9E9E9] ",
    }[key]);

const setStatus = (s) => {
    statusFilter.value = s;
    page.value = 1;
};

/* ===== Statistics ===== */
const totals = computed(() => {
    const t = {
        accepted: 0,
        denied: 0,
        pending: 0,
        notInvited: 0,
        checkIn: 0,
        notCheckIn: 0,
    };

    rows.value.forEach((r) => {
        // นับตาม Invite Status
        t[mapInvite(r.empInviteStatus)]++;

        // นับตามการมาเข้าร่วมจริง (Check-in Status)
        if (Number(r.empCheckinStatus) === 1) {
            t.checkIn++;
        } else {
            t.notCheckIn++;
        }
    });
    return t;
});

const checkinStats = computed(() => {
    const s = {
        accepted: 0,
        denied: 0,
        pending: 0,
        notInvited: 0,
        total: 0,
        checkIn: 0,
        notCheckIn: 0,
    };
    rows.value.forEach((r) => {
        if (Number(r.empCheckinStatus) === 1) {
            s[mapInvite(r.empInviteStatus)]++;
            s.total++;
        }
    });
    return s;
});

/* ===== Search & Filtered List ===== */
const filteredBase = computed(() => {
    let list = rows.value;
    const q = search.value.trim().toLowerCase();

    if (q) {
        list = list.filter((r) =>
            [String(r.empFullId), r.empFullname, r.empNickname].some((f) =>
                f?.toLowerCase().includes(q)
            )
        );
    }

    if (statusFilter.value === "CheckIn") {
        // กรองเฉพาะคนที่ Check-in แล้ว (status = 1)
        list = list.filter((r) => Number(r.empCheckinStatus) === 1);
    } else if (statusFilter.value === "notCheckIn") {
        // กรองคนที่ยังไม่ได้ Check-in (status = 0)
        list = list.filter((r) => Number(r.empCheckinStatus) === 0);
    } else if (statusFilter.value !== "all") {
        // กรองตาม Invite Status (Accepted, Denied, etc.)
        list = list.filter(
            (r) => mapInvite(r.empInviteStatus) === statusFilter.value
        );
    }

    if (selectedCompanyIds.value?.length) {
        const needles = selectedCompanyIds.value.map((x) => String(x).trim());
        list = list.filter((r) =>
            needles.some((n) => String(r.empCompanyId).includes(n))
        );
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

/* ===== Sorting & Pagination ===== */
const sortedRows = computed(() => {
    const dir = sortOrder.value === "desc" ? -1 : 1;
    return [...filteredBase.value].sort((a, b) => {
        const va =
            sortKey.value === "fullName" ? a.empFullname : a[sortKey.value];
        const vb =
            sortKey.value === "fullName" ? b.empFullname : b[sortKey.value];
        return String(va || "").localeCompare(String(vb || ""), "th") * dir;
    });
});

const pagedRows = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    return sortedRows.value.slice(start, start + pageSize.value);
});

const onSort = ({ key, order }) => {
    sortKey.value = key;
    sortOrder.value = order;
};

/* ===== Handlers ===== */
async function handleCheckin({ keys, checked }) {
    for (const empId of keys) {
        try {
            await axios.put(
                `/updateEmployeeAttendance/eveId/${eveId.value}/empId/${empId}`);
            const idx = rows.value.findIndex((r) => r.empId === empId);
            if (idx !== -1) rows.value[idx].empCheckinStatus = checked ? 1 : 0;
        } catch (err) {
            console.error("❌ update failed:", err);
        }
    }
}

async function handleCheckAllOnPage(data) {
    // 1. ดึงค่า action และ pageKeys ออกจาก object ที่ DataTable ส่งมา
    const { action, pageKeys } = data;

    // 2. console log ไอดีตามที่ต้องการ
    console.log("IDs to check-in:", pageKeys);

    try {
        // 3. ส่งอาร์เรย์ pageKeys ไปใน request body ของ axios.put
        // หมายเหตุ: ชื่อ field (เช่น employeeIds) ให้ปรับตามที่ API หลังบ้านกำหนดไว้ครับ
        await axios.put(`/updateEmployeeAttendanceAll/eveId/${eveId.value}`, {
            employeeIds: pageKeys,
        });

        // 4. โหลดข้อมูลใหม่
        load(eveId.value);
    } catch (error) {
        console.error("Check all failed", error);
    }
}

watch(
    rows,
    (list) => {
        selectedIds.value = list
            .filter((r) => Number(r.empCheckinStatus) === 1)
            .map((r) => r.empId);
    },
    { deep: true }
);

const handleBack = () => router.push("/event");

const search = ref("");
const columns = [
    {
        key: "empFullId",
        label: "Employee ID",
        sortable: true,
        class: "min-w-[125px] text-left",
    },
    {
        key: "fullName",
        label: "Name",
        sortable: true,
        class: "min-w-[215px] text-left",
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
</script>

<style scoped></style>
