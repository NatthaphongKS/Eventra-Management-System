<template>
    <!-- การ์ดหลัก -->
    <section class="p-0">
        <div
            class="w-full max-w-screen-2xl mx-auto bg-white rounded-2xl shadow border border-gray-200 p-5 md:p-6"
        >
            <!-- =================== Toolbar =================== -->
            <div class="flex items-center gap-3 mb-4 overflow-visible">
                <!-- Search (ต้องกดแว่น หรือกด Enter เพื่อ Apply) -->
                <input
                    v-model.trim="searchInput"
                    placeholder="Search..."
                    @keyup.enter="applySearchAndFilters"
                    class="flex-1 h-10 px-4 rounded-full border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-500"
                />
                <button
                    class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center hover:bg-rose-700"
                    @click="applySearchAndFilters"
                    aria-label="Search"
                    title="ค้นหา/ใช้ฟิลเตอร์ (คลิกหรือกด Enter)"
                >
                    <MagnifyingGlassIcon class="w-5 h-5" />
                </button>

                <!-- Filter (เป็นการ์ดแบบเลือกค่าอย่างเดียว ไม่มีช่องค้นหาภายใน) -->
                <div class="relative z-50">
                    <button
                        type="button"
                        @click="toggleFilter"
                        aria-label="Filter"
                        :aria-expanded="showFilter"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100"
                        :class="{ 'bg-gray-100': showFilter }"
                    >
                        <svg
                            class="w-5 h-5"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            viewBox="0 0 24 24"
                        >
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="6" y1="12" x2="16" y2="12" />
                            <line x1="8" y1="17" x2="12" y2="17" />
                        </svg>
                        <span class="hidden sm:inline">Filter</span>
                        <span
                            v-if="hasActiveFilters"
                            class="w-2 h-2 bg-rose-600 rounded-full"
                        ></span>
                    </button>

                    <!-- Filter Card -->
                    <div
                        v-if="showFilter"
                        class="absolute top-full right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                        @click.stop
                    >
                        <div class="p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800">
                                    Filter
                                </h3>
                                <button
                                    @click="clearStageFilters"
                                    class="text-xs text-rose-600 hover:text-rose-800"
                                >
                                    Clear
                                </button>
                            </div>

                            <!-- ID -->
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >ID</label
                                >
                                <div class="relative">
                                    <button
                                        type="button"
                                        class="w-full h-9 px-3 rounded-md border border-rose-300 bg-white text-left flex items-center justify-between"
                                        @click="toggleSelect('id')"
                                    >
                                        <span class="truncate">{{
                                            filtersStage.id
                                        }}</span>
                                        <span class="ml-2 text-gray-400"
                                            >▾</span
                                        >
                                    </button>
                                    <div
                                        v-if="openSelect === 'id'"
                                        class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
                                    >
                                        <ul class="max-h-56 overflow-auto py-1">
                                            <li
                                                v-for="opt in filteredIdOptions"
                                                :key="'id-' + opt"
                                                @click="chooseStage('id', opt)"
                                                class="px-3 py-2 cursor-pointer hover:bg-rose-50"
                                            >
                                                {{ opt }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Department -->
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Department</label
                                >
                                <div class="relative">
                                    <button
                                        type="button"
                                        class="w-full h-9 px-3 rounded-md border border-rose-300 bg-white text-left flex items-center justify-between"
                                        @click="toggleSelect('department')"
                                    >
                                        <span class="truncate">{{
                                            filtersStage.department
                                        }}</span>
                                        <span class="ml-2 text-gray-400"
                                            >▾</span
                                        >
                                    </button>
                                    <div
                                        v-if="openSelect === 'department'"
                                        class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
                                    >
                                        <ul class="max-h-56 overflow-auto py-1">
                                            <li
                                                v-for="opt in filteredDeptOptions"
                                                :key="'dept-' + opt"
                                                @click="
                                                    chooseStage(
                                                        'department',
                                                        opt
                                                    )
                                                "
                                                class="px-3 py-2 cursor-pointer hover:bg-rose-50"
                                            >
                                                {{ opt }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Team -->
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Team</label
                                >
                                <div class="relative">
                                    <button
                                        type="button"
                                        class="w-full h-9 px-3 rounded-md border border-rose-300 bg-white text-left flex items-center justify-between"
                                        @click="toggleSelect('team')"
                                    >
                                        <span class="truncate">{{
                                            filtersStage.team
                                        }}</span>
                                        <span class="ml-2 text-gray-400"
                                            >▾</span
                                        >
                                    </button>
                                    <div
                                        v-if="openSelect === 'team'"
                                        class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
                                    >
                                        <ul class="max-h-56 overflow-auto py-1">
                                            <li
                                                v-for="opt in filteredTeamOptions"
                                                :key="'team-' + opt"
                                                @click="
                                                    chooseStage('team', opt)
                                                "
                                                class="px-3 py-2 cursor-pointer hover:bg-rose-50"
                                            >
                                                {{ opt }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Position -->
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Position</label
                                >
                                <div class="relative">
                                    <button
                                        type="button"
                                        class="w-full h-9 px-3 rounded-md border border-rose-300 bg-white text-left flex items-center justify-between"
                                        @click="toggleSelect('position')"
                                    >
                                        <span class="truncate">{{
                                            filtersStage.position
                                        }}</span>
                                        <span class="ml-2 text-gray-400"
                                            >▾</span
                                        >
                                    </button>
                                    <div
                                        v-if="openSelect === 'position'"
                                        class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
                                    >
                                        <ul class="max-h-56 overflow-auto py-1">
                                            <li
                                                v-for="opt in filteredPositionOptions"
                                                :key="'pos-' + opt"
                                                @click="
                                                    chooseStage('position', opt)
                                                "
                                                class="px-3 py-2 cursor-pointer hover:bg-rose-50"
                                            >
                                                {{ opt }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- ปุ่ม Apply ฟิลเตอร์ (เลือกใช้ปุ่มแว่น/Enter แทน) -->
                        </div>
                    </div>
                </div>

                <!-- Sort (อัตโนมัติ + กดซ้ำเพื่อยกเลิก) -->
                <div class="relative z-50">
                    <button
                        type="button"
                        @click="toggleSort"
                        aria-label="Sort"
                        :aria-expanded="showSort"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100"
                        :class="{ 'bg-gray-100': showSort }"
                    >
                        <ArrowsUpDownIcon class="w-5 h-5" />
                        <span class="hidden sm:inline">Sort</span>
                    </button>

                    <div
                        v-if="showSort"
                        class="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                        @click.stop
                    >
                        <div class="p-2">
                            <div
                                class="mb-2 px-2 text-sm font-semibold text-gray-700"
                            >
                                Sort
                            </div>

                            <!-- ตัวเลือกเรียง -->
                            <button
                                v-for="option in sortOptions"
                                :key="option.key + '-' + option.order"
                                @click="toggleSortOption(option)"
                                class="w-full flex items-center gap-3 px-2 py-2 text-sm text-left hover:bg-gray-50 rounded"
                                :class="{
                                    'bg-rose-50 text-rose-700':
                                        isActiveSort(option),
                                }"
                            >
                                <span
                                    class="w-4 h-4 flex items-center justify-center"
                                >
                                    <span
                                        v-if="isActiveSort(option)"
                                        class="text-rose-600"
                                    ></span>
                                </span>
                                <span>{{ option.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add New -->
                <router-link
                    to="/add-employee"
                    class="ml-auto inline-flex items-center h-10 px-4 rounded-full bg-rose-600 text-white hover:bg-rose-700 whitespace-nowrap z-0"
                >
                    + Add New
                </router-link>
            </div>

            <!-- =================== Chips ฟิลเตอร์ที่ถูก Apply แล้ว =================== -->
            <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-4">
                <div
                    v-if="filters.id !== 'all'"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full"
                >
                    ID: {{ filters.id }}
                    <button
                        @click="removeFilter('id')"
                        class="text-rose-600 hover:text-rose-800"
                    >
                        ✕
                    </button>
                </div>
                <div
                    v-if="filters.department !== 'all'"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-rose-100 text-rose-800 text-xs rounded-full"
                >
                    {{ filters.department }}
                    <button
                        @click="removeFilter('department')"
                        class="text-rose-600 hover:text-rose-800"
                    >
                        ✕
                    </button>
                </div>
                <div
                    v-if="filters.team !== 'all'"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                >
                    {{ filters.team }}
                    <button
                        @click="removeFilter('team')"
                        class="text-blue-600 hover:text-blue-800"
                    >
                        ✕
                    </button>
                </div>
                <div
                    v-if="filters.position !== 'all'"
                    class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
                >
                    {{ filters.position }}
                    <button
                        @click="removeFilter('position')"
                        class="text-green-600 hover:text-green-800"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- =================== Table =================== -->
            <div class="overflow-auto">
                <table class="w-full table-fixed">
                    <colgroup>
                        <col class="w-12" />
                        <col class="w-24" />
                        <col class="w-[20%]" />
                        <col class="w-28" />
                        <col class="w-24" />
                        <col class="w-32" />
                        <col class="w-24" />
                        <col class="w-32" />
                        <col class="w-36" />
                    </colgroup>

                    <thead class="bg-gray-50">
                        <tr class="text-left">
                            <th
                                class="px-2.5 py-2 font-semibold text-[13px] text-center"
                            >
                                #
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                ID
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Name
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Nickname
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Phone
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Department
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Team
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Position
                            </th>
                            <th class="px-2.5 py-2 font-semibold text-[13px]">
                                Date Add (D/M/Y)
                            </th>
                        </tr>
                    </thead>

                    <!-- เนื้อหา 15px -->
                    <tbody class="text-[15px]">
                        <tr
                            v-for="(emp, i) in paged"
                            :key="emp.id ?? emp.emp_id ?? i"
                            class="border-b border-gray-200 last:border-0 hover:bg-rose-50"
                        >
                            <td class="px-2.5 py-2 text-center">
                                {{ (page - 1) * pageSize + i + 1 }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.emp_id || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                <span class="block truncate">
                                    {{
                                        (emp.emp_prefix
                                            ? emp.emp_prefix + " "
                                            : "") +
                                        (emp.emp_firstname || "") +
                                        " " +
                                        (emp.emp_lastname || "")
                                    }}
                                </span>
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.emp_nickname || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.phone || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.department_name || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.team_name || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{ emp.position_name || "N/A" }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                {{
                                    emp.created_at
                                        ? new Date(
                                              emp.created_at
                                          ).toLocaleDateString("en-GB")
                                        : "N/A"
                                }}
                            </td>
                            <td class="px-2.5 py-2">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <button
                                        @click="editEmployee(emp.id)"
                                        aria-label="Edit"
                                        class="p-1.5 rounded-lg hover:bg-gray-100"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon
                                            class="w-4 h-4 text-gray-600"
                                        />
                                    </button>
                                    <button
                                        @click="requestDelete(emp)"
                                        aria-label="Delete"
                                        class="p-1.5 rounded-lg hover:bg-rose-50"
                                        title="Delete"
                                    >
                                        <TrashIcon
                                            class="w-4 h-4 text-rose-600"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="paged.length === 0">
                            <td
                                :colspan="10"
                                class="px-3 py-6 text-center text-gray-500"
                            >
                                {{
                                    filtered.length === 0 && hasActiveFilters
                                        ? "No employees match the selected filters"
                                        : "No data found"
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- =================== Pagination (custom dropdown เล็ก) =================== -->
            <div class="flex flex-wrap items-center gap-3 pt-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-700">แสดง</span>

                    <div ref="pageSizeWrap" class="relative inline-block">
                        <button
                            type="button"
                            @click="togglePageSize()"
                            class="relative h-9 min-w-[72px] px-4 pr-9 rounded-full border-2 border-[#D40E11] bg-white text-[14px] font-medium text-black text-left select-none focus:outline-none focus:ring-2 focus:ring-[#D40E11]/20"
                            :aria-expanded="openPageSize"
                            aria-haspopup="listbox"
                        >
                            {{ pageSize }}
                            <svg
                                class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 fill-[#D40E11] transition-transform"
                                :class="openPageSize ? 'rotate-180' : ''"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.16l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.39a.75.75 0 0 1 .02-1.18z"
                                />
                            </svg>
                        </button>

                        <div
                            v-if="openPageSize"
                            class="absolute z-50 mt-1.5 w-full rounded-2xl border-2 border-[#D40E11] bg-white shadow-lg overflow-hidden"
                            role="listbox"
                        >
                            <ul class="py-1">
                                <li v-for="s in [10, 25, 50, 100]" :key="s">
                                    <button
                                        type="button"
                                        @click="choosePageSize(s)"
                                        class="w-full text-left px-3 py-1.5 text-[14px] hover:bg-rose-50"
                                        :class="
                                            s === pageSize
                                                ? 'text-[#D40E11] font-semibold bg-rose-50/50'
                                                : 'text-black'
                                        "
                                        role="option"
                                        :aria-selected="s === pageSize"
                                    >
                                        {{ s }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <span class="ml-2 text-[11px] text-gray-500">
                        {{ page }}-{{ totalPages }} จาก
                        {{ sorted.length }} รายการ
                    </span>
                </div>

                <!-- ปุ่มเปลี่ยนหน้า -->
                <div class="flex-1 flex justify-center">
                    <div class="flex items-center gap-2">
                        <button
                            class="w-10 h-10 flex items-center justify-center rounded-full text-2xl leading-none text-rose-800 hover:text-rose-900 disabled:opacity-45 disabled:hover:text-rose-800"
                            @click="prevPage"
                            :disabled="page === 1"
                            aria-label="Previous page"
                        >
                            ◄
                        </button>

                        <button
                            v-for="(p, idx) in pagesToShow"
                            :key="idx"
                            :disabled="p === '…'"
                            @click="p !== '…' && goToPage(p)"
                            :aria-current="p === page ? 'page' : null"
                            class="w-9 h-9 px-3 rounded-full border transition disabled:cursor-default select-none"
                            :class="[
                                p === '…'
                                    ? 'border-transparent bg-transparent text-gray-400 cursor-default'
                                    : 'border-rose-600',
                                p !== page && p !== '…'
                                    ? 'bg-white text-rose-600 hover:bg-rose-50 hover:text-rose-700'
                                    : '',
                                p === page ? 'bg-rose-600 text-white' : '',
                            ]"
                        >
                            {{ p }}
                        </button>

                        <button
                            class="w-10 h-10 flex items-center justify-center rounded-full text-2xl leading-none text-rose-800 hover:text-rose-900 disabled:opacity-45 disabled:hover:text-rose-800"
                            @click="nextPage"
                            :disabled="page === totalPages || totalPages === 0"
                            aria-label="Next page"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- คลิกนอกเพื่อปิด Filter/Sort -->
        <div
            v-if="showFilter || showSort"
            @click="
                showFilter = false;
                showSort = false;
                openSelect = null;
            "
            class="fixed inset-0 z-40"
        ></div>

        <!-- Confirm Delete Modal -->
        <teleport to="body">
            <div
                v-if="confirmOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
                    @click="cancelDelete"
                ></div>
                <div
                    class="relative w-[360px] max-w-[90vw] bg-white rounded-2xl shadow-xl p-6 text-center"
                >
                    <div
                        class="mx-auto w-14 h-14 rounded-full bg-yellow-400 grid place-items-center shadow-inner"
                    >
                        <QuestionMarkCircleIcon class="w-10 h-10 text-white" />
                    </div>
                    <h3 class="mt-4 font-semibold">ARE YOU SURE TO DELETE?</h3>
                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        This will be deleted permanently.<br />Are you sure?
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <button
                            type="button"
                            @click="cancelDelete"
                            class="w-28 h-10 rounded-lg bg-rose-600 text-white hover:bg-rose-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="confirmDelete"
                            class="w-28 h-10 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            autofocus
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Success Modal -->
        <teleport to="body">
            <div
                v-if="successOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center"
            >
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
                    @click="closeSuccess"
                ></div>
                <div
                    class="relative w-[440px] max-w-[92vw] bg-white rounded-2xl shadow-xl p-8 text-center"
                >
                    <div
                        class="mx-auto w-16 h-16 rounded-full bg-emerald-500 grid place-items-center shadow-inner"
                    >
                        <CheckCircleIcon class="w-10 h-10 text-white" />
                    </div>
                    <h3
                        class="mt-5 text-xl font-extrabold tracking-wide text-gray-800"
                    >
                        DELETE SUCCESS!
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        We have deleted a new employee.
                    </p>
                    <div class="mt-6">
                        <button
                            type="button"
                            @click="closeSuccess"
                            class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            autofocus
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </section>
</template>

<script>
import axios from "axios";
import { inject } from "vue";
import {
    MagnifyingGlassIcon,
    ArrowsUpDownIcon,
    PencilSquareIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";
import {
    QuestionMarkCircleIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
    name: "EmployeesPage",
    components: {
        MagnifyingGlassIcon,
        ArrowsUpDownIcon,
        PencilSquareIcon,
        TrashIcon,
        QuestionMarkCircleIcon,
        CheckCircleIcon,
    },

    data() {
        return {
            employees: [],
            // ค้นหาแบบ Stage (searchInput) กับค่าที่ถูก Apply แล้ว (search)
            searchInput: "",
            search: "",

            // ฟิลเตอร์แบบ Stage กับค่าที่ถูก Apply แล้ว
            filtersStage: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },
            filters: {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            },

            // Sort (อัตโนมัติ)
            sortBy: null,
            sortOrder: null,

            // UI state
            showFilter: false,
            showSort: false,
            openSelect: null, // 'id' | 'department' | 'team' | 'position' | null

            // Modal
            confirmOpen: false,
            successOpen: false,
            deleting: null,

            // Paging
            page: 1,
            pageSize: 10,
            openPageSize: false,
        };
    },

    async created() {
        await this.fetchEmployees(); // เปิดหน้ามาแสดง "ทั้งหมด" ก่อน
    },

    setup() {
        const setLayoutBlur = inject("setLayoutBlur", () => {});
        return { setLayoutBlur };
    },

    mounted() {
        document.addEventListener("click", this.onClickOutsidePageSize);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onClickOutsidePageSize);
    },

    computed: {
        // ทำข้อมูลให้ปลอดภัย
        normalized() {
            return this.employees.map((e) => ({
                ...e,
                emp_id: e.emp_id ?? "",
                emp_prefix: e.emp_prefix ?? "",
                emp_firstname: e.emp_firstname ?? "",
                emp_lastname: e.emp_lastname ?? "",
                emp_nickname: e.emp_nickname ?? "",
                position_name: e.position_name ?? "",
                // แก้ให้ดึงจาก emp_email/emp_phone
                email: e.emp_email ?? "",
                phone: e.emp_phone ?? "",
                department_name: e.department_name ?? "",
                team_name: e.team_name ?? "",
                created_at: e.created_at ?? "",
            }));
        },

        // Unique options (ไม่รวม 'all' ในลิสต์ให้เลือก)
        uniqueIds() {
            const ids = this.normalized.map((e) => e.emp_id).filter(Boolean);
            return [...new Set(ids)].sort((a, b) =>
                String(a).localeCompare(String(b), "en", { numeric: true })
            );
        },
        uniqueDepartments() {
            const arr = this.normalized
                .map((e) => e.department_name)
                .filter((v) => v && v !== "N/A");
            return [...new Set(arr)].sort((a, b) => a.localeCompare(b, "th"));
        },
        uniqueTeams() {
            const arr = this.normalized
                .map((e) => e.team_name)
                .filter((v) => v && v !== "N/A");
            return [...new Set(arr)].sort((a, b) => a.localeCompare(b, "th"));
        },
        uniquePositions() {
            const arr = this.normalized
                .map((e) => e.position_name)
                .filter((v) => v && v !== "N/A");
            return [...new Set(arr)].sort((a, b) => a.localeCompare(b, "th"));
        },

        // ใช้เป็นแหล่งข้อมูลในเมนู (ตั้งชื่อเดิม filtered* เพื่อไม่ต้องแก้ template อื่น)
        filteredIdOptions() {
            return this.uniqueIds;
        },
        filteredDeptOptions() {
            return this.uniqueDepartments;
        },
        filteredTeamOptions() {
            return this.uniqueTeams;
        },
        filteredPositionOptions() {
            return this.uniquePositions;
        },

        // ตัวเลือก sort
        sortOptions() {
            return [
                { key: "name", order: "asc", label: "ชื่อพนักงาน A–Z" },
                { key: "name", order: "desc", label: "ชื่อพนักงาน Z–A" },
                { key: "department", order: "asc", label: "ชื่อแผนก A–Z" },
                { key: "department", order: "desc", label: "ชื่อแผนก Z–A" },
                { key: "team", order: "asc", label: "ชื่อทีม A–Z" },
                { key: "team", order: "desc", label: "ชื่อทีม Z–A" },
                { key: "position", order: "asc", label: "ชื่อตำแหน่ง A–Z" },
                { key: "position", order: "desc", label: "ชื่อตำแหน่ง Z–A" },
                { key: "id", order: "asc", label: "รหัสพนักงาน น้อย–มาก" },
                { key: "id", order: "desc", label: "รหัสพนักงาน มาก–น้อย" },
            ];
        },

        // มีฟิลเตอร์ถูก Apply อยู่ไหม (ใช้ค่าที่ apply แล้ว)
        hasActiveFilters() {
            const f = this.filters;
            return (
                f.id !== "all" ||
                f.department !== "all" ||
                f.team !== "all" ||
                f.position !== "all"
            );
        },

        // ค้นหา + ฟิลเตอร์ (ใช้ค่า apply แล้ว: search, filters)
        filtered() {
            let result = this.normalized;

            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter((e) =>
                    `${e.emp_id} ${e.emp_prefix} ${e.emp_firstname} ${e.emp_lastname} ${e.emp_nickname} ${e.position_name} ${e.email} ${e.phone} ${e.department_name} ${e.team_name}`
                        .toLowerCase()
                        .includes(q)
                );
            }

            if (this.filters.id !== "all")
                result = result.filter((e) => e.emp_id === this.filters.id);
            if (this.filters.department !== "all")
                result = result.filter(
                    (e) => e.department_name === this.filters.department
                );
            if (this.filters.team !== "all")
                result = result.filter(
                    (e) => e.team_name === this.filters.team
                );
            if (this.filters.position !== "all")
                result = result.filter(
                    (e) => e.position_name === this.filters.position
                );

            return result;
        },

        // เรียงลำดับ (อัตโนมัติ)
        sorted() {
            const result = [...this.filtered];
            if (!this.sortBy || !this.sortOrder) return result;

            if (this.sortBy === "name") {
                result.sort((a, b) => {
                    const A = `${a.emp_firstname} ${a.emp_lastname}`.trim();
                    const B = `${b.emp_firstname} ${b.emp_lastname}`.trim();
                    return this.sortOrder === "asc"
                        ? A.localeCompare(B, "th")
                        : B.localeCompare(A, "th");
                });
            } else if (this.sortBy === "id") {
                const cmp = (x, y) =>
                    String(x ?? "").localeCompare(String(y ?? ""), "en", {
                        numeric: true,
                    });
                result.sort((a, b) =>
                    this.sortOrder === "asc"
                        ? cmp(a.emp_id, b.emp_id)
                        : -cmp(a.emp_id, b.emp_id)
                );
            } else if (this.sortBy === "department") {
                result.sort((a, b) =>
                    this.sortOrder === "asc"
                        ? (a.department_name ?? "").localeCompare(
                              b.department_name ?? "",
                              "th"
                          )
                        : (b.department_name ?? "").localeCompare(
                              a.department_name ?? "",
                              "th"
                          )
                );
            } else if (this.sortBy === "position") {
                result.sort((a, b) =>
                    this.sortOrder === "asc"
                        ? (a.position_name ?? "").localeCompare(
                              b.position_name ?? "",
                              "th"
                          )
                        : (b.position_name ?? "").localeCompare(
                              a.position_name ?? "",
                              "th"
                          )
                );
            } else if (this.sortBy === "team") {
                result.sort((a, b) =>
                    this.sortOrder === "asc"
                        ? (a.team_name ?? "").localeCompare(
                              b.team_name ?? "",
                              "th"
                          )
                        : (b.team_name ?? "").localeCompare(
                              a.team_name ?? "",
                              "th"
                          )
                );
            }

            return result;
        },

        // ตัดหน้า
        totalPages() {
            return Math.ceil(this.sorted.length / this.pageSize);
        },
        paged() {
            const start = (this.page - 1) * this.pageSize;
            return this.sorted.slice(start, start + this.pageSize);
        },

        // เลขหน้าแบบมี …
        pagesToShow() {
            const total = this.totalPages;
            const cur = this.page;
            if (total <= 7)
                return Array.from({ length: total }, (_, i) => i + 1);

            const pages = [];
            const left = Math.max(3, cur - 1);
            const right = Math.min(total - 2, cur + 1);

            pages.push(1, 2);
            if (left > 3) pages.push("…");
            for (let p = left; p <= right; p++) pages.push(p);
            if (right < total - 2) pages.push("…");
            pages.push(total - 1, total);

            return pages.filter((v, i) => pages.indexOf(v) === i);
        },
    },

    methods: {
        /* ---------- Data ---------- */
        async fetchEmployees() {
            try {
                const res = await axios.get("/get-employees");
                this.employees = Array.isArray(res.data)
                    ? res.data
                    : res.data?.data || [];
            } catch (err) {
                console.error("Error fetching employees", err);
            }
        },

        /* ---------- Apply (ค้นหา + ฟิลเตอร์) เฉพาะเมื่อกดแว่น/Enter ---------- */
        applySearchAndFilters() {
            this.search = this.searchInput;
            this.filters = { ...this.filtersStage };
            this.page = 1;
            this.showFilter = false;
            this.openSelect = null;
        },

        /* ---------- Filter UI ---------- */
        toggleFilter() {
            this.showFilter = !this.showFilter;
            if (this.showFilter) {
                this.showSort = false;
                this.openSelect = null;
            }
        },
        toggleSelect(name) {
            this.openSelect = this.openSelect === name ? null : name;
        },
        chooseStage(field, value) {
            this.filtersStage[field] = value;
            this.openSelect = null; // ไม่มีช่องค้นหาในการ์ดแล้ว
            // ยังไม่ apply จนกว่าจะกดแว่น/Enter
        },
        clearStageFilters() {
            this.filtersStage = {
                id: "all",
                department: "all",
                team: "all",
                position: "all",
            };
            this.openSelect = null;
        },
        // ลบ filter ที่ถูก apply (ทำงานทันที)
        removeFilter(type) {
            if (["id", "department", "team", "position"].includes(type)) {
                this.filters[type] = "all";
                this.filtersStage[type] = "all"; // sync ให้ UI ตรงกัน
                this.page = 1;
            }
        },

        /* ---------- Sort (อัตโนมัติ + กดซ้ำเพื่อยกเลิก) ---------- */
        toggleSort() {
            this.showSort = !this.showSort;
            if (this.showSort) {
                this.showFilter = false;
                this.openSelect = null;
            }
        },
        isActiveSort(option) {
            return (
                this.sortBy === option.key && this.sortOrder === option.order
            );
        },
        toggleSortOption(option) {
            if (this.isActiveSort(option)) {
                this.sortBy = null;
                this.sortOrder = null;
            } else {
                this.sortBy = option.key;
                this.sortOrder = option.order;
            }
            this.showSort = false;
            this.page = 1;
        },
        clearSort() {
            this.sortBy = null;
            this.sortOrder = null;
            this.showSort = false;
            this.page = 1;
        },

        /* ---------- Paging ---------- */
        prevPage() {
            if (this.page > 1) this.page--;
        },
        nextPage() {
            if (this.page < this.totalPages) this.page++;
        },
        goToPage(n) {
            if (typeof n === "number" && n >= 1 && n <= this.totalPages)
                this.page = n;
        },

        /* ---------- Page size dropdown ---------- */
        togglePageSize() {
            this.openPageSize = !this.openPageSize;
        },
        choosePageSize(s) {
            this.pageSize = s;
            this.openPageSize = false;
            this.page = 1;
        },
        onClickOutsidePageSize(e) {
            const el = this.$refs.pageSizeWrap;
            if (this.openPageSize && el && !el.contains(e.target))
                this.openPageSize = false;
        },

        /* ---------- Actions ---------- */
        editEmployee(id) {
            if (!id) return;
            this.$router.push(`/edit-employee/${id}`);
        },
        requestDelete(emp) {
            this.deleting = emp;
            this.confirmOpen = true;
            this.setLayoutBlur(true);
        },
        cancelDelete() {
            this.confirmOpen = false;
            this.deleting = null;
            this.setLayoutBlur(false);
        },
        async confirmDelete() {
            if (!this.deleting) return;
            try {
                await axios.delete(`/employees/${this.deleting.id}`);
                await this.fetchEmployees();
                this.confirmOpen = false;
                this.successOpen = true;
                this.setLayoutBlur(true);
            } catch (err) {
                console.error("Error deleting employee", err);
                this.cancelDelete();
            } finally {
                this.deleting = null;
            }
        },
        closeSuccess() {
            this.successOpen = false;
            this.setLayoutBlur(false);
        },
    },
};
</script>
