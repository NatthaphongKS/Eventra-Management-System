<template>
    <div class="relative">
        <button @click="showDropdown = !showDropdown" type="button"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xl text-neutral-800 transition-colors duration-200 font-[Poppins] font-size-[20px]"
            ref="dateButton">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M8.5 14a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m0 3.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0" />
                <path fill="currentColor" fill-rule="evenodd"
                    d="M8 3.25a.75.75 0 0 1 .75.75v.75h6.5V4a.75.75 0 0 1 1.5 0v.758q.228.006.425.022c.38.03.736.098 1.073.27a2.75 2.75 0 0 1 1.202 1.202c.172.337.24.693.27 1.073c.03.365.03.81.03 1.345v7.66c0 .535 0 .98-.03 1.345c-.03.38-.098.736-.27 1.073a2.75 2.75 0 0 1-1.201 1.202c-.338.172-.694.24-1.074.27c-.365.03-.81.03-1.344.03H8.17c-.535 0-.98 0-1.345-.03c-.38-.03-.736-.098-1.073-.27a2.75 2.75 0 0 1-1.202-1.2c-.172-.338-.24-.694-.27-1.074c-.03-.365-.03-.81-.03-1.344V8.67c0-.535 0-.98.03-1.345c.03-.38.098-.736.27-1.073A2.75 2.75 0 0 1 5.752 5.05c.337-.172.693-.24 1.073-.27q.197-.016.425-.022V4A.75.75 0 0 1 8 3.25m10.25 7H5.75v6.05c0 .572 0 .957.025 1.252c.023.288.065.425.111.515c.12.236.311.427.547.547c.09.046.227.088.514.111c.296.024.68.025 1.253.025h7.6c.572 0 .957 0 1.252-.025c.288-.023.425-.065.515-.111a1.25 1.25 0 0 0 .547-.547c.046-.09.088-.227.111-.515c.024-.295.025-.68.025-1.252zM10.5 7a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z"
                    clip-rule="evenodd" />
            </svg>
            <span>Date</span>
        </button>

        <div v-if="showDropdown"
            class="absolute z-10 mt-2 w-80 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none p-4"
            @click.stop ref="dateDropdown">

            <div class="flex items-center justify-between mb-4">
                <div class="flex gap-2 relative">
                    <div class="relative">
                        <button @click="toggleMonthDropdown"
                            class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200 hover:bg-gray-50"
                            type="button">
                            Month
                            <svg class="w-4 h-4 inline ml-1 transition-transform duration-200"
                                :class="{ 'rotate-180': showMonthDropdown }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div v-if="showMonthDropdown"
                            class="absolute z-20 w-36 bg-white border border-gray-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide">
                            <div v-for="(month, index) in monthOptions" :key="index" @click="selectMonth(index)"
                                :class="{ 'bg-red-500 text-white': currentCalendarMonth.month() === index }"
                                class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center">
                                {{ month }}
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="toggleYearDropdown"
                            class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200 hover:bg-gray-50"
                            type="button">
                            Year
                            <svg class="w-4 h-4 inline ml-1 transition-transform duration-200"
                                :class="{ 'rotate-180': showYearDropdown }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div v-if="showYearDropdown"
                            class="absolute z-20 w-36 bg-white border border-gray-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide">
                            <div v-for="year in yearOptions" :key="year" @click="selectYear(year)"
                                :class="{ 'bg-red-500 text-white': currentCalendarMonth.year() === year }"
                                class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center">
                                {{ year }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-base font-medium mb-3 px-1">
                <span class="font-normal text-lg">{{ currentCalendarMonth.format('MMM YYYY') }}</span>
                <div>
                    <span @click="prevMonth"
                        class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block mr-1">&lt;</span>
                    <span @click="nextMonth"
                        class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block">&gt;</span>
                </div>
            </div>

            <table class="w-full text-center text-xs border-collapse">
                <thead>
                    <tr class="text-gray-500 font-medium">
                        <th v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="day"
                            class="font-medium pb-2">{{ day }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
                        <td v-for="(day, dayIndex) in week" :key="dayIndex" class="p-0 align-middle"
                            @click="day.isCurrentMonth ? selectDate(day.date) : null">
                            <div :class="dateCellClass(day)" class="w-full h-8 flex items-center justify-center transition duration-150">
                                {{ day.day }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 flex justify-center">
                <button @click="clearDateRange"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                    Clear
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import dayjs from "dayjs";
import 'dayjs/locale/en';
import weekday from 'dayjs/plugin/weekday';
import weekOfYear from 'dayjs/plugin/weekOfYear';
import isBetween from 'dayjs/plugin/isBetween';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(weekday);
dayjs.extend(weekOfYear);
dayjs.extend(isBetween);
dayjs.extend(customParseFormat);

dayjs.locale('en');

export default {
    props: {
        modelValue: {
            type: Object,
            default: () => ({ start: null, end: null })
        }
    },
    emits: ['update:modelValue'],

    data() {
        return {
            showDropdown: false,
            showMonthDropdown: false,
            showYearDropdown: false,
            selectedRange: this.modelValue,
            currentCalendarMonth: dayjs(),
            isSelectingStart: true,
        };
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    watch: {
        modelValue(newVal) {
            this.selectedRange = newVal;
        },
        selectedRange: {
            handler(newVal) {
                this.$emit('update:modelValue', newVal);
            },
            deep: true
        },
        showDropdown(newVal) {
            if (newVal) {
                if (this.selectedRange.start) {
                    this.currentCalendarMonth = dayjs(this.selectedRange.start);
                } else {
                    this.currentCalendarMonth = dayjs();
                }
            } else {
                this.showMonthDropdown = false;
                this.showYearDropdown = false;
            }
        }
    },

    computed: {
        monthOptions() {
            return Array.from({ length: 12 }, (v, i) => {
                const date = new Date(2000, i, 1);
                return date.toLocaleString('en-US', { month: 'short' });
            });
        },
        yearOptions() {
            const currentYear = dayjs().year();
            const years = [];
            for (let i = currentYear - 10; i <= currentYear + 10; i++) {
                years.push(i);
            }
            return years.sort((a, b) => b - a);
        },

        calendarWeeks() {
            const startOfMonth = this.currentCalendarMonth.startOf('month');
            const endOfMonth = this.currentCalendarMonth.endOf('month');
            const startOfWeek = startOfMonth.weekday(0);
            const endOfWeek = endOfMonth.weekday(6);

            const calendar = [];
            let day = startOfWeek;

            const startDayjs = this.selectedRange.start ? dayjs(this.selectedRange.start, 'YYYY-MM-DD') : null;
            const endDayjs = this.selectedRange.end ? dayjs(this.selectedRange.end, 'YYYY-MM-DD') : null;
            const today = dayjs().startOf('day');

            while (day.isBefore(endOfWeek) || day.isSame(endOfWeek, 'day')) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    const dateString = day.format('YYYY-MM-DD');
                    const isCurrentMonth = day.isSame(this.currentCalendarMonth, 'month');

                    let isInRange = false;
                    if (startDayjs && endDayjs) {
                        isInRange = day.isBetween(startDayjs, endDayjs, 'day', '()');
                    }

                    const isRangeStart = startDayjs && day.isSame(startDayjs, 'day');
                    const isRangeEnd = endDayjs && day.isSame(endDayjs, 'day');
                    const isToday = day.isSame(today, 'day');

                    week.push({
                        day: day.date(),
                        date: dateString,
                        isCurrentMonth,
                        isRangeStart,
                        isRangeEnd,
                        isInRange,
                        isToday,
                    });
                    day = day.add(1, 'day');
                }
                calendar.push(week);
            }
            return calendar;
        }
    },

    methods: {
        handleClickOutside(event) {
            if (!this.showDropdown) return;
            const button = this.$refs.dateButton;
            const dropdown = this.$refs.dateDropdown;
            if (button && !button.contains(event.target) && dropdown && !dropdown.contains(event.target)) {
                this.showDropdown = false;
            }
        },
        toggleMonthDropdown() { this.showMonthDropdown = !this.showMonthDropdown; this.showYearDropdown = false; },
        toggleYearDropdown() { this.showYearDropdown = !this.showYearDropdown; this.showMonthDropdown = false; },
        selectMonth(index) { this.currentCalendarMonth = dayjs(this.currentCalendarMonth).month(index); this.showMonthDropdown = false; },
        selectYear(year) { this.currentCalendarMonth = dayjs(this.currentCalendarMonth).year(year); this.showYearDropdown = false; },
        prevMonth() { this.currentCalendarMonth = this.currentCalendarMonth.subtract(1, 'month'); },
        nextMonth() { this.currentCalendarMonth = this.currentCalendarMonth.add(1, 'month'); },

        clearDateRange() {
            this.selectedRange.start = null;
            this.selectedRange.end = null;
            this.isSelectingStart = true;
            this.showDropdown = false;
        },

        selectDate(dateString) {
            const currentStart = this.selectedRange.start;
            const dayjsDate = dayjs(dateString, 'YYYY-MM-DD');

            if (this.isSelectingStart || !currentStart || (currentStart && this.selectedRange.end)) {
                // เลือกวันเริ่ม
                this.selectedRange.start = dateString;
                this.selectedRange.end = null;
                this.isSelectingStart = false;
            } else {
                // เลือกวันจบ -> ไม่ปิด Dropdown (ตามที่ขอ)
                const dayjsStart = dayjs(currentStart, 'YYYY-MM-DD');
                if (dayjsDate.isBefore(dayjsStart, 'day')) {
                    this.selectedRange.start = dateString;
                    this.selectedRange.end = currentStart;
                } else {
                    this.selectedRange.end = dateString;
                }
                this.isSelectingStart = true;
                // this.showDropdown = false; // เอาออก เพื่อไม่ให้ปิดอัตโนมัติ
            }
            this.currentCalendarMonth = dayjsDate;
        },

        dateCellClass(day) {
            let classes = 'cursor-pointer text-xs ';

            if (day.isRangeStart && day.isRangeEnd) {
                classes += ' rounded-lg';
            } else if (day.isRangeStart) {
                classes += ' rounded-l-lg';
            } else if (day.isRangeEnd) {
                classes += ' rounded-r-lg';
            } else if (day.isInRange) {
                classes += ' rounded-none';
            } else {
                classes += ' rounded-lg';
            }

            if (day.isToday) {
                classes += ' bg-blue-500 text-white font-bold';
            }
            else if (day.isRangeStart || day.isRangeEnd) {
                classes += ' bg-red-500 text-white font-semibold';
            }
            else if (day.isInRange) {
                classes += ' bg-red-100 text-red-700';
            }
            else if (day.isCurrentMonth) {
                classes += ' text-gray-800 hover:bg-gray-100';
            }
            else {
                classes += ' text-gray-300';
            }

            return classes;
        },
    },
};
</script>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
