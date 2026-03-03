<!-- /**
 * ชื่อไฟล์: EventCheckIn.vue
 * คำอธิบาย: หน้าสำหรับเช็คชื่อผู้เข้าร่วมกิจกรรม
 * Input: ข้อมูลพนักงานและการเช็คชื่อจาก API /getEmployeeForCheckin/eveId/{eveId}
 * Output: หน้าจอสำหรับการเช็คชื่อ และพร้อมสำหรับการเช็คชื่อผู้เข้าร่วม
 * คนแก้ไข: Natthaphong Kongsinl
 * วันที่แก้ไข: 2026-02-27
 */ -->
<!-- pages/ReplyForm.vue -->
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

        <div class="mb-4 flex flex-wrap items-center justify-between">
            <div class="flex gap-2">
                <button
                    class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                    :class="
                        statusFilter === 'accepted'
                            ? 'bg-green-600 text-white border-green-600 shadow-lg'
                            : 'bg-green-50 text-green-600 border-gray-200 hover:bg-green-100'
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
                            ? 'bg-red-600 text-white border-red-600 shadow-lg'
                            : 'bg-red-50 text-red-600 border-gray-200 hover:bg-red-100'
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
                            ? 'bg-blue-500 text-white border-blue-500 shadow-lg'
                            : 'bg-blue-50 text-blue-500 border-gray-200 hover:bg-blue-100'
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
                            ? 'bg-gray-500 text-white border-gray-500 shadow-lg'
                            : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'
                    "
                    @click="setStatus('notInvited')"
                >
                    <span class="ml-2"
                        >{{ checkinStats.notInvited }}/{{
                            totals.notInvited
                        }}
                        Not Invited</span
                    >
                </button>

                <button
                    class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                    :class="
                        statusFilter === 'all'
                            ? 'bg-purple-600 text-white border-purple-600 shadow-lg'
                            : 'bg-purple-50 text-purple-600 border-gray-200 hover:bg-purple-100'
                    "
                    @click="setStatus('all')"
                >
                    <span class="ml-2"
                        >{{
                            checkinStats.notInvited +
                            checkinStats.denied +
                            checkinStats.pending +
                            checkinStats.accepted
                        }}/{{ rows.length }} All</span
                    >
                </button>
            </div>
            <div class="flex gap-2">
                <button
                    class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                    :class="
                        statusFilter === 'CheckIn'
                            ? 'bg-emerald-700 text-white border-emerald-700 shadow-lg'
                            : 'bg-emerald-50 text-emerald-700 border-gray-200 hover:bg-emerald-100'
                    "
                    @click="setStatus('CheckIn')"
                >
                    <span class="ml-2"
                        >{{ rows.length - totals.notCheckIn }}/{{
                            rows.length
                        }}
                        Attended</span
                    >
                </button>

                <button
                    class="inline-flex items-center px-5 py-3 rounded-[20px] border transition-all duration-200 font-semibold text-[16px]"
                    :class="
                        statusFilter === 'notCheckIn'
                            ? 'bg-rose-600 text-white border-rose-600 shadow-lg'
                            : 'bg-rose-50 text-rose-600 border-gray-200 hover:bg-rose-100'
                    "
                    @click="setStatus('notCheckIn')"
                >
                    <span class="ml-2"
                        >{{ rows.length - totals.checkIn }}/{{
                            rows.length
                        }}
                        Not Attended</span
                    >
                </button>
            </div>
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
                    class="inline-flex items-center justify-center rounded-[5px] px-2.5 py-0.5 text-xs font-medium w-[125px] h-[30px]"
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
            v-if="errorMessage"
            class="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700"
        >
            {{ errorMessage }}
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
const errorMessage = ref("");
const rows = ref([]);
const eventTitle = ref("Event Check-In");
const selectedIds = ref([]);
const page = ref(1);
const pageSize = ref(10);
const sortKey = ref("empCompanyId");
const sortOrder = ref("asc");
const statusFilter = ref("all");

/* ===== Logic สำหรับการเปิด Dropdown ทีละอัน ===== */
const dropdownKeys = ref({
    company: 0,
    department: 0,
    team: 0,
    position: 0,
});

const refreshOthers = (current) => {
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
    return list.map((record) => ({
        eveId: record.eveId ?? null,
        eveTitle: record.eveTitle ?? "",
        empId: record.empId,
        empFullId: record.empFullId ?? "",
        empCompanyId: record.empCompanyId,
        empFullname: record.empFullname ?? "",
        empNickname: record.empNickname ?? "",
        empDepartment: record.empDepartment ?? "",
        empTeam: record.empTeam ?? "",
        empPosition: record.empPosition ?? "",
        empInviteStatus: (record.empInviteStatus ?? "").toString(),
        empCheckinStatus: Number(record.empCheckinStatus ?? 0),
    }));
}

async function load(id) {
    errorMessage.value = "";
    try {
        const data = await fetchEmployeeForCheckin(id);
        if (Array.isArray(data) && data.length) {
            if (data[0]?.eveTitle) eventTitle.value = data[0].eveTitle;
        }
        rows.value = normalize(Array.isArray(data) ? data : (data?.data ?? []));
        buildFilterOptions();
    } catch (error) {
        if (error.name !== "AbortError") {
            errorMessage.value =
                error?.message || "เกิดข้อผิดพลาดในการโหลดข้อมูล";
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
        [...new Set(arr.filter(Boolean))].map((value) => ({
            label: String(value),
            value: value,
        }));
    companyIdOptions.value = toOpt(
        rows.value.map((record) => record.empCompanyId),
    );
    departmentOptions.value = toOpt(
        rows.value.map((record) => record.empDepartment),
    );
    teamOptions.value = toOpt(rows.value.map((record) => record.empTeam));
    positionOptions.value = toOpt(
        rows.value.map((record) => record.empPosition),
    );
}

function mapInvite(answer) {
    const statusText = String(answer || "").toLowerCase();
    if (statusText.includes("accepted")) return "accepted";
    if (statusText.includes("denied")) return "denied";
    if (statusText.includes("pending")) return "pending";
    if (statusText.includes("not_invite")) return "notInvited";
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
    })[key];

const statusClass = (key) =>
    ({
        accepted: "bg-green-50 text-green-600 border-gray-200",
        denied: "bg-red-50 text-red-600 border-gray-200",
        pending: "bg-blue-50 text-blue-500 border-gray-200",
        notInvited: "bg-gray-100 text-gray-500 border-gray-200",
    })[key];

const setStatus = (statusFilterValue) => {
    statusFilter.value = statusFilterValue;
    page.value = 1;
};

/* ===== Statistics ===== */
const totals = computed(() => {
    const counts = {
        accepted: 0,
        denied: 0,
        pending: 0,
        notInvited: 0,
        checkIn: 0,
        notCheckIn: 0,
    };

    rows.value.forEach((record) => {
        counts[mapInvite(record.empInviteStatus)]++;

        if (Number(record.empCheckinStatus) === 1) {
            counts.checkIn++;
        } else {
            counts.notCheckIn++;
        }
    });
    return counts;
});

const checkinStats = computed(() => {
    const stats = {
        accepted: 0,
        denied: 0,
        pending: 0,
        notInvited: 0,
        total: 0,
        checkIn: 0,
        notCheckIn: 0,
    };
    rows.value.forEach((record) => {
        if (Number(record.empCheckinStatus) === 1) {
            stats[mapInvite(record.empInviteStatus)]++;
            stats.total++;
        }
    });
    return stats;
});

/* ===== Search & Filtered List ===== */
const filteredBase = computed(() => {
    let list = rows.value;
    const query = search.value.trim().toLowerCase();

    if (query) {
        list = list.filter((record) =>
            [
                String(record.empFullId),
                record.empFullname,
                record.empNickname,
            ].some((field) => field?.toLowerCase().includes(query)),
        );
    }

    if (statusFilter.value === "CheckIn") {
        list = list.filter((record) => Number(record.empCheckinStatus) === 1);
    } else if (statusFilter.value === "notCheckIn") {
        list = list.filter((record) => Number(record.empCheckinStatus) === 0);
    } else if (statusFilter.value !== "all") {
        list = list.filter(
            (record) =>
                mapInvite(record.empInviteStatus) === statusFilter.value,
        );
    }

    if (selectedCompanyIds.value?.length) {
        const needles = selectedCompanyIds.value.map((x) => String(x).trim());
        list = list.filter((record) =>
            needles.some((needle) =>
                String(record.empCompanyId).includes(needle),
            ),
        );
    }
    if (selectedDepartmentIds.value?.length) {
        const set = new Set(selectedDepartmentIds.value);
        list = list.filter((record) => set.has(record.empDepartment));
    }
    if (selectedTeamIds.value?.length) {
        const set = new Set(selectedTeamIds.value);
        list = list.filter((record) => set.has(record.empTeam));
    }
    if (selectedPositionIds.value?.length) {
        const set = new Set(selectedPositionIds.value);
        list = list.filter((record) => set.has(record.empPosition));
    }
    return list;
});

/* ===== Sorting & Pagination ===== */
const sortedRows = computed(() => {
    const direction = sortOrder.value === "desc" ? -1 : 1;
    return [...filteredBase.value].sort((a, b) => {
        const valueA =
            sortKey.value === "fullName" ? a.empFullname : a[sortKey.value];
        const valueB =
            sortKey.value === "fullName" ? b.empFullname : b[sortKey.value];
        return (
            String(valueA || "").localeCompare(String(valueB || ""), "th") *
            direction
        );
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
                `/updateEmployeeAttendance/eveId/${eveId.value}/empId/${empId}`,
            );
            const index = rows.value.findIndex(
                (record) => record.empId === empId,
            );
            if (index !== -1)
                rows.value[index].empCheckinStatus = checked ? 1 : 0;
        } catch (error) {
            // ไม่แสดง log ใน Production
        }
    }
}

async function handleCheckAllOnPage(data) {
    const { pageKeys } = data;
    try {
        await axios.put(`/updateEmployeeAttendanceAll/eveId/${eveId.value}`, {
            employeeIds: pageKeys,
        });
        load(eveId.value);
    } catch (error) {
        // ไม่แสดง log ใน Production
    }
}

watch(
    rows,
    (list) => {
        selectedIds.value = list
            .filter((record) => Number(record.empCheckinStatus) === 1)
            .map((record) => record.empId);
    },
    { deep: true },
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
