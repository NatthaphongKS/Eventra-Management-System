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
                        :style="{ height: HEADER_HEIGHT + 'px' }">
                        <div class="flex items-center justify-end pr-2 border-r border-neutral-200"
                            :style="{ width: DEPT_COL_WIDTH + 'px', flexShrink: 0 }">
                            <span style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; text-align: right;">
                                Department
                            </span>
                        </div>
                        <div class="flex items-center pl-2 border-r border-neutral-200"
                            :style="{ width: TEAM_COL_WIDTH + 'px', flexShrink: 0 }">
                            <span style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">
                                Team
                            </span>
                        </div>
                        <div class="flex items-center justify-center flex-1">
                            <span style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em;">
                                People
                            </span>
                        </div>
                    </div>

                <div :style="{ height: dynamicChartHeight + 'px' }" class="relative flex">

                    <!-- ============================================================ -->
                    <!-- Custom Y-Axis: Department (left) + Team (right)             -->
                    <!-- ============================================================ -->
                    <div class="flex-shrink-0 flex" :style="{ width: Y_AXIS_WIDTH + 'px' }">

                        <!-- Department column -->
                        <div class="flex flex-col border-r border-neutral-200"
                            :style="{ width: DEPT_COL_WIDTH + 'px', paddingTop: chartTopPadding + 'px', paddingBottom: chartBottomPadding + 'px' }">
                            <template v-for="(group, gIdx) in groupedData" :key="'dept-' + gIdx">
                                <!--
                                    เส้นขีดแบ่ง department:
                                    - ระหว่าง dept: border-b-2 สีเข้ม (เส้นหนา)
                                    - แถวสุดท้ายไม่มีเส้น (last group)
                                -->
                                <div class="flex items-center justify-end pr-2 relative"
                                    :style="{
                                        height: (group.teams.length * ROW_HEIGHT) + 'px',
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
                            :style="{ width: TEAM_COL_WIDTH + 'px', paddingTop: chartTopPadding + 'px', paddingBottom: chartBottomPadding + 'px' }">
                            <template v-for="(group, gIdx) in groupedData" :key="'tg-' + gIdx">
                                <div v-for="(team, tIdx) in group.teams" :key="'team-' + team.name"
                                    class="flex items-center pl-2"
                                    :style="{
                                        height: ROW_HEIGHT + 'px',
                                        /* เส้นบางแบ่งระหว่างทีมในกลุ่มเดียวกัน */
                                        borderBottom: tIdx < group.teams.length - 1
                                            ? '1px dashed #e5e7eb'
                                            /* เส้นหนาแบ่งระหว่าง department (ยกเว้นกลุ่มสุดท้าย) */
                                            : gIdx < groupedData.length - 1
                                                ? '2px solid #d1d5db'
                                                : 'none'
                                    }">
                                    <span style="font-size: 11px; color: #374151; line-height: 1.3; word-break: break-word;">
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
const Y_AXIS_WIDTH = 190;   // ความกว้างรวมของ custom Y-axis (dept + team column)
const DEPT_COL_WIDTH = 95;  // ความกว้าง column Department
const TEAM_COL_WIDTH = 95;  // ความกว้าง column Team
const ROW_HEIGHT = 90;      // ความสูงต่อ 1 แถว (ต้องตรงกับ barThickness + spacing ใน Chart.js)
const HEADER_HEIGHT = 36;   // ความสูงของ header row (Department / Team / People)

export default {
    name: "GraphEventParticipationChartJS",
    props: {
        eventId: { type: [Number, String], required: true },
        data: { type: Object, default: () => ({ departments: [], teams: [] }) },
        options: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            openDropdown: null,
            originalDepartments: [],
            originalTeams: [],
            selectedDepartments: [],
            selectedTeams: [],
            filteredData: [],
            chartInstance: null,

            // expose constants to template
            Y_AXIS_WIDTH,
            DEPT_COL_WIDTH,
            TEAM_COL_WIDTH,
            ROW_HEIGHT,
            HEADER_HEIGHT,

            // padding ที่ chart.js ใส่ให้ plot area (top/bottom)
            // จะถูก sync จาก afterFit callback
            chartTopPadding: 10,
            chartBottomPadding: 10,
        };
    },
    computed: {
        departmentOptions() {
            return this.originalDepartments.map(d => d.name);
        },
        isAllDeptSelected() {
            return this.departmentOptions.length > 0 &&
                this.selectedDepartments.length === this.departmentOptions.length;
        },
        isAllTeamSelected() {
            const activeTeams = this.originalTeams
                .filter(t => !this.isTeamDisabled(t.department))
                .map(t => t.name);
            return activeTeams.length > 0 && activeTeams.every(n => this.selectedTeams.includes(n));
        },
        dynamicChartHeight() {
            // คำนวณความสูงให้พอดีกับจำนวนแถวเป๊ะๆ + รวม Padding บนล่างที่ Chart.js ใช้
            return (this.filteredData.length * ROW_HEIGHT) + this.chartTopPadding + this.chartBottomPadding;
        },
        // จัดกลุ่ม filteredData ตาม department สำหรับ render HTML overlay
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
        if (this.chartInstance) {
            this.chartInstance.destroy();
        }
    },
    methods: {
        isTeamDisabled(dept) {
            return !this.selectedDepartments.includes(dept);
        },
        toggleDropdown(type) {
            this.openDropdown = this.openDropdown === type ? null : type;
        },
        toggleSelection(type, value) {
            if (type === 'department') {
                const idx = this.selectedDepartments.indexOf(value);
                if (idx > -1) this.selectedDepartments.splice(idx, 1);
                else this.selectedDepartments.push(value);
            } else {
                const idx = this.selectedTeams.indexOf(value);
                if (idx > -1) this.selectedTeams.splice(idx, 1);
                else this.selectedTeams.push(value);
                this.updateDisplayData();
            }
        },
        toggleAll(type) {
            if (type === 'department') {
                this.selectedDepartments = this.isAllDeptSelected ? [] : [...this.departmentOptions];
            } else {
                const activeTeams = this.originalTeams
                    .filter(t => !this.isTeamDisabled(t.department))
                    .map(t => t.name);
                this.selectedTeams = this.isAllTeamSelected ? [] : activeTeams;
                this.updateDisplayData();
            }
        },
        updateDisplayData() {
            let filtered = this.originalTeams.filter(t => this.selectedTeams.includes(t.name));
            filtered.sort((a, b) => a.department.localeCompare(b.department));
            this.filteredData = filtered;
        },
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
                                self.chartTopPadding = area.top;
                                self.chartBottomPadding = chart.height - area.bottom;
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#fff',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                title: (tooltipItems) => {
                                    const idx = tooltipItems[0].dataIndex;
                                    const team = self.filteredData[idx];
                                    return team ? `${team.name} (${team.department})` : '';
                                },
                                label: (context) => {
                                    const val = context.raw || 0;
                                    const total = context.chart.data.datasets.reduce(
                                        (sum, dataset) => sum + dataset.data[context.dataIndex], 0
                                    );
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.dataset.label}: ${val} (${pct}%)`;
                                },
                                afterBody: (tooltipItems) => {
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const total = tooltipItems[0].chart.data.datasets.reduce(
                                        (sum, dataset) => sum + dataset.data[dataIndex], 0
                                    );
                                    return `\nTotal: ${total}`;
                                }
                            }
                        },
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
