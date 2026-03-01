<!-- /**
 * ชื่อไฟล์: GraphEventParticipation.vue
 * คำอธิบาย: Component แสดงกราฟแท่งแนวนอน (Horizontal Bar Chart) สำหรับสถิติการตอบรับเข้าร่วมกิจกรรม
 *           แบ่งตาม Department และ Team พร้อม Filter แบบ Multi-select
 * Input: eventId (รหัสกิจกรรม), data (ข้อมูล departments และ teams พร้อมสถิติ), options (ตัวเลือกเพิ่มเติม)
 * Output: กราฟแท่งแสดง Accepted / Declined / Pending ของแต่ละ Team
 * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
 * วันที่จัดทำ/แก้ไข: 2026-03-01
 */ -->

<template>
    <div class="w-full rounded-[20px] flex flex-col bg-white p-8 shadow-[0_10px_25px_-12px_rgba(0,0,0,0.25)]">

        <div class="relative z-50 mb-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 class="mb-3 text-left font-semibold text-neutral-700 text-2xl">
                    Event Participation Graph
                </h3>
                <div class="flex gap-6 text-[14px] text-neutral-400 font-medium">
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-4 border border-neutral-300 rounded bg-[#00a73d]"></span>
                        Accepted
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-4 border border-neutral-300 rounded bg-[#c10008]"></span>
                        Declined
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-4 w-4 border border-neutral-300 rounded bg-[#0084d1]"></span>
                        Pending
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative min-w-[200px]">
                    <button @click="toggleDropdown('department')"
                        class="flex w-full h-[48px] items-center justify-between gap-4 rounded-[24px] border border-neutral-300 bg-white px-5 py-3 text-[18px] font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors">
                        Department
                        <svg class="h-5 w-5 transition-transform"
                            :class="openDropdown === 'department' ? 'rotate-180 text-[#c10008]' : 'text-neutral-500'"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="openDropdown === 'department'"
                        class="absolute right-0 mt-2 w-[240px] rounded-[16px] border border-neutral-200 bg-white p-4 shadow-[0_4px_20px_rgba(0,0,0,0.1)] max-h-[300px] overflow-y-auto z-50">
                        <div @click="toggleAll('department')" class="flex items-center gap-3 cursor-pointer mb-3 group">
                            <div class="w-5 h-5 flex-shrink-0 rounded-[4px] border flex items-center justify-center transition-colors"
                                :class="isAllDeptSelected ? 'bg-[#c10008] border-[#c10008]' : 'bg-white border-neutral-400 group-hover:border-[#c10008]'">
                                <svg v-if="isAllDeptSelected" class="w-3.5 h-3.5 text-white" fill="none"
                                    stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[16px] font-medium text-neutral-800">Filter All</span>
                        </div>
                        <div v-for="dept in departmentOptions" :key="dept" @click="toggleSelection('department', dept)"
                            class="flex items-center gap-3 cursor-pointer mb-3 last:mb-0 group">
                            <div class="w-5 h-5 flex-shrink-0 rounded-[4px] border flex items-center justify-center transition-colors"
                                :class="selectedDepartments.includes(dept) ? 'bg-[#c10008] border-[#c10008]' : 'bg-white border-neutral-400 group-hover:border-[#c10008]'">
                                <svg v-if="selectedDepartments.includes(dept)" class="w-3.5 h-3.5 text-white"
                                    fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[16px] text-neutral-700">{{ dept }}</span>
                        </div>
                    </div>
                </div>

                <div class="relative min-w-[200px]">
                    <button @click="toggleDropdown('team')"
                        class="flex w-full h-[48px] items-center justify-between gap-4 rounded-[24px] border border-neutral-300 bg-white px-5 py-3 text-[18px] font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors">
                        Team
                        <svg class="h-5 w-5 transition-transform"
                            :class="openDropdown === 'team' ? 'rotate-180 text-[#c10008]' : 'text-neutral-500'"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-if="openDropdown === 'team'"
                        class="absolute right-0 mt-2 w-[240px] rounded-[16px] border border-neutral-200 bg-white p-4 shadow-[0_4px_20px_rgba(0,0,0,0.1)] max-h-[300px] overflow-y-auto z-50">
                        <div @click="toggleAll('team')" class="flex items-center gap-3 cursor-pointer mb-3 group">
                            <div class="w-5 h-5 flex-shrink-0 rounded-[4px] border flex items-center justify-center transition-colors"
                                :class="isAllTeamSelected ? 'bg-[#c10008] border-[#c10008]' : 'bg-white border-neutral-400 group-hover:border-[#c10008]'">
                                <svg v-if="isAllTeamSelected" class="w-3.5 h-3.5 text-white" fill="none"
                                    stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[16px] font-medium text-neutral-800">Filter All</span>
                        </div>
                        <div v-for="team in originalTeams" :key="team.name"
                            @click="!isTeamDisabled(team.department) && toggleSelection('team', team.name)"
                            class="flex items-center gap-3 mb-3 last:mb-0 transition-all"
                            :class="isTeamDisabled(team.department) ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer group'">
                            <div class="w-5 h-5 flex-shrink-0 rounded-[4px] border flex items-center justify-center transition-colors"
                                :class="[
                                    isTeamDisabled(team.department) ? 'bg-gray-100 border-gray-300' :
                                        selectedTeams.includes(team.name) ? 'bg-[#c10008] border-[#c10008]' : 'bg-white border-neutral-400 group-hover:border-[#c10008]'
                                ]">
                                <svg v-if="selectedTeams.includes(team.name)" class="w-3.5 h-3.5 text-white" fill="none"
                                    stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[16px]"
                                :class="isTeamDisabled(team.department) ? 'text-neutral-400' : 'text-neutral-700'">
                                {{ team.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Area -->
        <div class="w-full relative" style="height: 360px;">
            <div v-if="filteredData.length === 0"
                class="absolute inset-0 flex items-center justify-center text-neutral-400 text-[16px]">
                No data to display
            </div>

            <div v-else class="w-full h-full overflow-y-auto pr-2" ref="scrollContainer">

                <!-- ============================================================ -->
                <!-- Sticky Header: column titles for Y-axis + X-axis            -->
                <!-- ============================================================ -->
                <div class="flex flex-shrink-0 sticky top-0 bg-white z-10 border-b-2 border-neutral-200"
                    :style="{ height: headerHeight + 'px' }">
                    <div class="flex items-center justify-end pr-2 border-r border-neutral-200"
                        :style="{ width: deptColWidth + 'px', flexShrink: 0 }">
                        <span
                            style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; text-align: right;">
                            Department
                        </span>
                    </div>
                    <div class="flex items-center pl-2 border-r border-neutral-200"
                        :style="{ width: teamColWidth + 'px', flexShrink: 0 }">
                        <span
                            style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">
                            Team
                        </span>
                    </div>
                    <div class="flex items-center justify-center flex-1">
                        <span
                            style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">
                            People
                        </span>
                    </div>
                </div>

                <div :style="{ height: dynamicChartHeight + 'px' }" class="relative flex">

                    <!-- ============================================================ -->
                    <!-- Custom Y-Axis: Department (left) + Team (right)             -->
                    <!-- ============================================================ -->
                    <div class="flex-shrink-0 flex" :style="{ width: yAxisWidth + 'px' }">

                        <!-- Department column -->
                        <div class="flex flex-col border-r border-neutral-200"
                            :style="{ width: deptColWidth + 'px', paddingTop: chartTopPadding + 'px', paddingBottom: chartBottomPadding + 'px' }">
                            <template v-for="(group, gIdx) in groupedData" :key="'dept-' + gIdx">
                                <!--
                                    เส้นขีดแบ่ง department:
                                    - ระหว่าง dept: border-b-2 สีเข้ม (เส้นหนา)
                                    - แถวสุดท้ายไม่มีเส้น (last group)
                                -->
                                <div class="flex items-center justify-end pr-2 relative" :style="{
                                    height: (group.teams.length * rowHeight) + 'px',
                                    borderBottom: gIdx < groupedData.length - 1 ? '2px solid #d1d5db' : 'none'
                                }">
                                    <span
                                        style="font-size: 11px; font-weight: 600; color: #374151; line-height: 1.3; text-align: right; word-break: break-word;">
                                        {{ group.department }}
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Team column -->
                        <div class="flex flex-col border-r border-neutral-200"
                            :style="{ width: teamColWidth + 'px', paddingTop: chartTopPadding + 'px', paddingBottom: chartBottomPadding + 'px' }">
                            <template v-for="(group, gIdx) in groupedData" :key="'tg-' + gIdx">
                                <div v-for="(team, tIdx) in group.teams" :key="'team-' + team.name"
                                    class="flex items-center pl-2" :style="{
                                        height: rowHeight + 'px',
                                        /* เส้นบางแบ่งระหว่างทีมในกลุ่มเดียวกัน */
                                        borderBottom: tIdx < group.teams.length - 1
                                            ? '1px dashed #e5e7eb'
                                            /* เส้นหนาแบ่งระหว่าง department (ยกเว้นกลุ่มสุดท้าย) */
                                            : gIdx < groupedData.length - 1
                                                ? '2px solid #d1d5db'
                                                : 'none'
                                    }">
                                    <span
                                        style="font-size: 11px; color: #374151; line-height: 1.3; word-break: break-word;">
                                        {{ team.name }}
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Canvas wrapper (takes remaining width) -->
                    <div class="flex-1 h-full">
                        <canvas ref="chartCanvas"></canvas>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels);

// ค่าคงที่ที่ใช้ sync ระหว่าง HTML overlay กับ Chart.js
const yAxisWidth = 190;    // ความกว้างรวมของ custom Y-axis (dept + team column)
const deptColWidth = 95;   // ความกว้าง column Department
const teamColWidth = 95;   // ความกว้าง column Team
const rowHeight = 90;      // ความสูงต่อ 1 แถว (ต้องตรงกับ barThickness + spacing ใน Chart.js)
const headerHeight = 36;   // ความสูงของ header row (Department / Team / People)

export default {
    name: "GraphEventParticipationChartJS",
    props: {
        eventId: { type: [Number, String], required: true },
        data: { type: Object, default: () => ({ departments: [], teams: [] }) },
        options: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            openDropdown: null,        // ชื่อ dropdown ที่เปิดอยู่ ('department' | 'team' | null)
            originalDepartments: [],   // ข้อมูล Department ทั้งหมดที่รับมาจาก props
            originalTeams: [],         // ข้อมูล Team ทั้งหมดที่รับมาจาก props
            selectedDepartments: [],   // Department ที่ถูกเลือกจาก Filter
            selectedTeams: [],         // Team ที่ถูกเลือกจาก Filter
            filteredData: [],          // ข้อมูล Team ที่ผ่าน Filter แล้ว ใช้ render Chart และ HTML overlay
            chartInstance: null,       // instance ของ Chart.js (เก็บไว้เพื่อ destroy ก่อน re-render)

            // expose constants ให้ template ใช้งานได้
            yAxisWidth,
            deptColWidth,
            teamColWidth,
            rowHeight,
            headerHeight,

            // padding บน-ล่างของ plot area ที่ Chart.js คำนวณให้
            // sync มาจาก onComplete callback เพื่อให้ HTML overlay ตรงกับ Chart
            chartTopPadding: 10,
            chartBottomPadding: 10,
        };
    },
    computed: {
        /**
    * ชื่อฟังก์ชัน: departmentOptions
    * คำอธิบาย: แปลง originalDepartments เป็น array ของชื่อ Department สำหรับแสดงใน dropdown
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        departmentOptions() {
            return this.originalDepartments.map(d => d.name);
        },
        /**
    * ชื่อฟังก์ชัน: isAllDeptSelected
    * คำอธิบาย: ตรวจสอบว่า Department ทั้งหมดถูกเลือกครบไหม สำหรับ กดfilterทั้งหมด "Filter All"
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        isAllDeptSelected() {
            return this.departmentOptions.length > 0 &&
                this.selectedDepartments.length === this.departmentOptions.length;
        },

        /**
     * ชื่อฟังก์ชัน: isAllTeamSelected
     * คำอธิบาย: ตรวจสอบว่า Team ที่ active ทั้งหมดถูกเลือกครบไหม สำหรับ กดfilterทั้งหมด "Filter All"
     * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
     * วันที่จัดทำ/แก้ไข: 2026-03-01
     */
        isAllTeamSelected() {
            const activeTeams = this.originalTeams
                .filter(t => !this.isTeamDisabled(t.department))
                .map(t => t.name);
            return activeTeams.length > 0 && activeTeams.every(n => this.selectedTeams.includes(n));
        },
        /**
     * ชื่อฟังก์ชัน: dynamicChartHeight
     * คำอธิบาย: คำนวณความสูงของ Chart ให้พอดีกับจำนวนแถวที่แสดงอยู่
     * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
     * วันที่จัดทำ/แก้ไข: 2026-03-01
     */
        dynamicChartHeight() {
            // คำนวณความสูงให้พอดีกับจำนวนแถวเป๊ะๆ + รวม padding บนล่างที่ Chart.js ใช้
            return (this.filteredData.length * rowHeight) + this.chartTopPadding + this.chartBottomPadding;
        },

        /**
     * ชื่อฟังก์ชัน: groupedData
     * คำอธิบาย: จัดกลุ่ม filteredData ตาม Department สำหรับ  ฝั่ง Label แกน Y ให้แสดง ทีมที่อยู่ใน department เดียวกันอยู่ด้วยกัน
     * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
     * วันที่จัดทำ/แก้ไข: 2026-03-01
     */
        groupedData() {
            const groups = [];
            let currentDept = null;
            for (const team of this.filteredData) {
                if (team.department !== currentDept) {
                    groups.push({ department: team.department, teams: [team] });
                    currentDept = team.department;
                } else {
                    groups[groups.length - 1].teams.push(team);
                }
            }
            return groups;
        },
    },
    watch: {
        data: {
            immediate: true,
            deep: true,
            handler(newData) {
                if (newData) {
                    this.originalDepartments = newData.departments ? [...newData.departments] : [];
                    this.originalTeams = newData.teams ? [...newData.teams] : [];
                    this.selectedDepartments = this.originalDepartments.map(d => d.name);
                    this.selectedTeams = this.originalTeams
                        .filter(t => this.selectedDepartments.includes(t.department))
                        .map(t => t.name);
                    this.updateDisplayData();
                }
            },
        },
        selectedDepartments: {
            handler(newDepts, oldDepts) {
                const addedDepts = newDepts.filter(d => !oldDepts.includes(d));
                if (addedDepts.length > 0) {
                    const toAdd = this.originalTeams
                        .filter(t => addedDepts.includes(t.department))
                        .map(t => t.name)
                        .filter(n => !this.selectedTeams.includes(n));
                    this.selectedTeams = [...this.selectedTeams, ...toAdd];
                }
                this.selectedTeams = this.selectedTeams.filter(name => {
                    const t = this.originalTeams.find(t => t.name === name);
                    return t && newDepts.includes(t.department);
                });
                this.updateDisplayData();
            },
            deep: true
        },
        filteredData: {
            handler() {
                this.$nextTick(() => this.renderChart());
            },
            deep: true
        }
    },
    beforeUnmount() {
        // destroy chart instance เพื่อป้องกัน memory leak
        if (this.chartInstance) {
            this.chartInstance.destroy();
        }
    },
    methods: {
        /**
    * ชื่อฟังก์ชัน: isTeamDisabled
    * คำอธิบาย: ตรวจสอบว่า Department ของ Team นั้นถูก deselect อยู่ไหม
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        isTeamDisabled(dept) {
            // ตรวจสอบว่า Department ของ Team นั้นถูก deselect อยู่หรือไม่
            return !this.selectedDepartments.includes(dept);
        },

        /**
    * ชื่อฟังก์ชัน: toggleDropdown
    * คำอธิบาย: เปิด/ปิด dropdown ถ้าคลิก dropdown เดิมซ้ำจะปิด
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        toggleDropdown(type) {
            // toggle เปิด/ปิด dropdown — ถ้าคลิก dropdown เดิมให้ปิด
            this.openDropdown = this.openDropdown === type ? null : type;
        },

        /**
    * ชื่อฟังก์ชัน: toggleSelection
    * คำอธิบาย: เพิ่ม/ลบ Department หรือ Team ออกจากรายการที่ถูกเลือก
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        toggleSelection(type, value) {
            if (type === 'department') {
                const idx = this.selectedDepartments.indexOf(value);
                // ถ้ามีอยู่แล้วให้เอาออก ถ้ายังไม่มีให้เพิ่ม
                if (idx > -1) this.selectedDepartments.splice(idx, 1);
                else this.selectedDepartments.push(value);
            } else {
                const idx = this.selectedTeams.indexOf(value);
                if (idx > -1) this.selectedTeams.splice(idx, 1);
                else this.selectedTeams.push(value);
                this.updateDisplayData();
            }
        },
        /**
    * ชื่อฟังก์ชัน: toggleAll
    * คำอธิบาย: เลือกทั้งหมด หรือยกเลิกทั้งหมด สำหรับ Department หรือ Team
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        toggleAll(type) {
            if (type === 'department') {
                // ถ้าเลือกครบทุก dept อยู่แล้ว ให้ ยกเลิกทั้งหมด, ถ้ายังไม่ครบ ให้ เลือกทั้งหมด
                this.selectedDepartments = this.isAllDeptSelected ? [] : [...this.departmentOptions];
            } else {
                // เฉพาะ Team ที่ Department ยังถูกเลือกอยู่เท่านั้น
                const activeTeams = this.originalTeams
                    .filter(t => !this.isTeamDisabled(t.department))
                    .map(t => t.name);
                this.selectedTeams = this.isAllTeamSelected ? [] : activeTeams;
                this.updateDisplayData();
            }
        },
        /**
    * ชื่อฟังก์ชัน: updateDisplayData
    * คำอธิบาย: กรองและเรียงลำดับข้อมูล Team ที่ถูกเลือกเพื่อใช้ render Chart
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        updateDisplayData() {
            // กรองเฉพาะ Team ที่ถูกเลือก แล้ว sort ตาม Department เพื่อให้ groupedData ถูก
            let filtered = this.originalTeams.filter(t => this.selectedTeams.includes(t.name));
            filtered.sort((a, b) => a.department.localeCompare(b.department));
            this.filteredData = filtered;
        },

        /**
    * ชื่อฟังก์ชัน: renderChart
    * คำอธิบาย: สร้าง Bar Chart ด้วย Chart.js
    *          และ Custom Plugin สำหรับแสดงตัวเลขผลรวมต่อท้ายบาร์
    * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
    * วันที่จัดทำ/แก้ไข: 2026-03-01
    */
        renderChart() {
            if (this.filteredData.length === 0) {
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                    this.chartInstance = null;
                }
                return;
            }

            const ctx = this.$refs.chartCanvas;
            if (!ctx) return;

            // ซ่อน labels ของ Y-axis เพราะเราใช้ HTML overlay แทน
            const labels = this.filteredData.map(() => '');
            const acceptedData = this.filteredData.map(d => d.attending || 0);
            const declinedData = this.filteredData.map(d => d.notAttending || 0);
            const pendingData = this.filteredData.map(d => d.pending || 0);

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            const self = this;

            // 1. สร้าง Custom Plugin สำหรับวาดตัวเลขผลรวมต่อท้ายบาร์
            const totalLabelsPlugin = {
                id: 'totalLabels',
                afterDatasetsDraw(chart) {
                    const ctx = chart.ctx;
                    const datasets = chart.data.datasets;

                    ctx.save();
                    // กำหนดรูปแบบตัวอักษรของผลรวม
                    ctx.font = 'bold 16px "Inter", sans-serif';
                    ctx.fillStyle = '#4b5563'; // สีเทาเข้ม
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';

                    const count = datasets[0].data.length;
                    for (let i = 0; i < count; i++) {
                        let total = 0;
                        let maxX = 0;
                        let y = null;

                        // วนลูปหาผลรวมและหาจุดขอบขวาสุดของบาร์
                        for (let j = 0; j < datasets.length; j++) {
                            const val = Number(datasets[j].data[i]) || 0;
                            total += val;

                            const meta = chart.getDatasetMeta(j);
                            if (!meta.hidden && meta.data[i]) {
                                const bar = meta.data[i];
                                // หาพิกัด x ที่ขวาสุดเพื่อใช้วาดตัวเลขต่อท้าย
                                if (bar.x > maxX) {
                                    maxX = bar.x;
                                    y = bar.y;
                                }
                            }
                        }

                        // ถ้ายอดรวมมากกว่า 0 ให้วาดตัวเลขต่อท้าย (เว้นระยะ x ออกไปอีก 10px)
                        if (total > 0 && y !== null) {
                            ctx.fillText(total, maxX + 10, y);
                        }
                    }
                    ctx.restore();
                }
            };

            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Accepted',
                            data: acceptedData,
                            backgroundColor: '#00a73d',
                            barThickness: 24,
                        },
                        {
                            label: 'Declined',
                            data: declinedData,
                            backgroundColor: '#c10008',
                            barThickness: 24,
                        },
                        {
                            label: 'Pending',
                            data: pendingData,
                            backgroundColor: '#0084d1',
                            barThickness: 24,
                            borderRadius: { topRight: 4, bottomRight: 4 },
                        }
                    ]
                },
                plugins: [totalLabelsPlugin], // 2. นำ Custom Plugin มาใส่ที่นี่
                options: {
                    layout: {
                        padding: { right: 40 } // 3. เพิ่ม padding ขวา เพื่อไม่ให้ตัวเลขรวมโดน Canvas ตัดขอบ
                    },
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        onComplete() {
                            const chart = self.chartInstance;
                            if (chart) {
                                const area = chart.chartArea;
                                // sync padding จาก Chart.js มาให้ HTML overlay ตรงกัน
                                self.chartTopPadding = area.top;
                                self.chartBottomPadding = chart.height - area.bottom;
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                       
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 11 },
                            formatter: (value) => value > 0 ? value : '',
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                color: '#e5e7eb',
                                borderDash: [5, 5],
                            },
                            border: { display: false },
                        },
                        y: {
                            stacked: true,
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                display: false,
                                padding: 0,
                            },
                            afterFit(scale) {
                                scale.width = 4;
                            }
                        }
                    }
                }
            });
        }
    }
};
</script>

<style scoped>
/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 10px;
}
</style>
