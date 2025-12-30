<template>
    <div class="relative">
        <button
            @click="showDropdown = !showDropdown"
            type="button"
            class="inline-flex h-11 items-center rounded-lg bg-white px-4 font-semibold text-gray-700 focus:outline-none text-sm whitespace-nowrap"
            ref="dateButton"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5" />
            </svg>
            {{ dateRangeText }}
        </button>

        <div v-if="showDropdown"
             class="absolute z-10 mt-2 w-80 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none p-4"
             @click.stop
             ref="dateDropdown"
        >
            <div class="flex items-center justify-between mb-4">
                <div class="flex gap-2 relative">

                    <div class="relative">
                        <button @click="toggleMonthDropdown"
                            class="text-sm font-semibold text-red-500 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200"
                            type="button"
                        >
                            Mount
                            <svg class="w-4 h-4 inline ml-1 transition-transform duration-200" :class="{'rotate-180': showMonthDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div v-if="showMonthDropdown" class="absolute z-20 w-36 bg-white border border-gray-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide">
                            <div v-for="(month, index) in monthOptions" :key="index"
                                @click="selectMonth(index)"
                                :class="{'bg-red-500 text-white': currentCalendarMonth.month() === index}"
                                class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center"
                            >
                                {{ month }}
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="toggleYearDropdown"
                            class="text-sm font-semibold text-red-500 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200"
                            type="button"
                        >
                            Year
                            <svg class="w-4 h-4 inline ml-1 transition-transform duration-200" :class="{'rotate-180': showYearDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                         <div v-if="showYearDropdown" class="absolute z-20 w-36 bg-white border border-gray-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide">
                            <div v-for="year in yearOptions" :key="year"
                                @click="selectYear(year)"
                                :class="{'bg-red-500 text-white': currentCalendarMonth.year() === year}"
                                class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center"
                            >
                                {{ year }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-base font-medium mb-3 px-1">
                <span class="font-normal text-lg">{{ currentCalendarMonth.format('MMM YYYY') }}</span>
                <div>
                    <span @click="prevMonth" class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block mr-1">&lt;</span>
                    <span @click="nextMonth" class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block">&gt;</span>
                </div>
            </div>

            <table class="w-full text-center text-xs">
                <thead>
                    <tr class="text-gray-500 font-medium">
                        <th v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="day" class="font-medium pb-2">{{ day }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
                        <td v-for="(day, dayIndex) in week" :key="dayIndex"
                            @click="day.isCurrentMonth ? selectDate(day.date) : null"
                            :class="dateCellClass(day)"
                        >
                            {{ day.day }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4 flex justify-between">
                <button
                    @click="clearDateRange"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                >
                    Clear Filter
                </button>
                <button
                    @click="showDropdown = false"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                >
                    Done
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
            if (!newVal) {
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

        dateRangeText() {
            const start = this.selectedRange.start;
            const end = this.selectedRange.end;

            const format = (dateString) => {
                if (!dateString) return 'N/A';
                try {
                    return dayjs(dateString, 'YYYY-MM-DD').format('DD/MM/YYYY');
                } catch {
                    return 'N/A';
                }
            };

            if (!start && !end) {
                return "Date";
            }

            if (start && !end) {
                return format(start);
            }

            if (start && end) {
                 return `${format(start)} – ${format(end)}`;
            }

            return "Date";
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

                    week.push({
                        day: day.date(),
                        date: dateString,
                        isCurrentMonth,
                        isRangeStart,
                        isRangeEnd,
                        isInRange,
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

        toggleMonthDropdown() {
            this.showMonthDropdown = !this.showMonthDropdown;
            this.showYearDropdown = false;
        },
        toggleYearDropdown() {
            this.showYearDropdown = !this.showYearDropdown;
            this.showMonthDropdown = false;
        },

        selectMonth(monthIndex) {
             this.currentCalendarMonth = dayjs(this.currentCalendarMonth).month(monthIndex);
             this.showMonthDropdown = false;
        },
        selectYear(year) {
             this.currentCalendarMonth = dayjs(this.currentCalendarMonth).year(year);
             this.showYearDropdown = false;
        },

        selectDate(dateString) {
            const currentStart = this.selectedRange.start;

            const dayjsDate = dayjs(dateString, 'YYYY-MM-DD');

            if (this.isSelectingStart || !currentStart || (currentStart && this.selectedRange.end)) {
                // 1. เลือก Start ใหม่
                this.selectedRange.start = dateString;
                this.selectedRange.end = null;
                this.isSelectingStart = false;

            } else {
                // 2. เลือก End
                const dayjsStart = dayjs(currentStart, 'YYYY-MM-DD');

                if (dayjsDate.isBefore(dayjsStart, 'day')) {
                    // ถ้าเลือก End ก่อน Start ให้สลับ
                    this.selectedRange.start = dateString;
                    this.selectedRange.end = currentStart;
                } else {
                    // เลือก End ปกติ
                    this.selectedRange.end = dateString;
                }

                this.isSelectingStart = true;
                this.showDropdown = false;
            }

            this.currentCalendarMonth = dayjsDate;
        },

        // 🎯 LOGIC: Start Date และ End Date เป็นสีแดงเข้ม (ลบสีฟ้าออกทั้งหมด)
        dateCellClass(day) {
            let classes = 'p-1 h-8 w-8 rounded-lg cursor-pointer transition duration-150 text-xs';

            if (!day.isCurrentMonth) {
                classes += ' text-gray-400';
            }

            const isFullRange = this.selectedRange.start && this.selectedRange.end;

            // 1. RED (เข้ม): Start Date หรือ End Date
            if (day.isRangeStart || day.isRangeEnd) {
                classes += ' bg-red-500 text-white font-semibold';

                if (day.isRangeStart && day.isRangeEnd) {
                    // วันเดียว
                    classes += ' rounded-lg';
                } else if (day.isRangeStart) {
                    // จุดเริ่มต้น
                    classes += ' rounded-l-lg rounded-r-none';
                } else if (day.isRangeEnd) {
                    // จุดสิ้นสุด
                    classes += ' rounded-r-lg rounded-l-none';
                }
            }
            // 2. LIGHT RED: In Range
            else if (day.isInRange) {
                classes += ' bg-red-100 text-red-700 rounded-none';
            }

            // 3. วันที่ปกติ (Hover)
            else if (day.isCurrentMonth) {
                classes += ' hover:bg-gray-100 text-gray-800';
            }

            return classes;
        },

        prevMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.subtract(1, 'month');
        },
        nextMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.add(1, 'month');
        },


        clearDateRange() {
            this.selectedRange.start = null;
            this.selectedRange.end = null;
            this.isSelectingStart = true;
            this.showDropdown = false;
        },
    },
};
</script>

<style>
/* Optional: ซ่อน Scrollbar สำหรับ Dropdown ที่เป็น Selectors */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;  /* Chrome, Safari, Opera */
}
</style>