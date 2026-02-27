<template>
    <div class="relative" ref="root">
        <!-- Display trigger -->
        <div class="relative group" @click="showDropdown = !showDropdown">
            <div
                :class="[
                    'flex items-center justify-between px-[20px] font-medium rounded-2xl cursor-pointer z-10 bg-white border transition h-[52px]',
                    modelValue ? 'text-neutral-800' : 'text-red-300',
                    hasError
                        ? 'border-red-500 bg-red-50'
                        : 'border-neutral-200 group-hover:border-rose-400',
                ]"
            >
                <span>{{ formattedDisplay || 'dd/mm/yy' }}</span>
                <svg
                    class="w-6 h-6 text-red-700"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                >
                    <path
                        fill="currentColor"
                        d="M8.5 14a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m0 3.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0"
                    />
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        d="M8 3.25a.75.75 0 0 1 .75.75v.75h6.5V4a.75.75 0 0 1 1.5 0v.758q.228.006.425.022c.38.03.736.098 1.073.27a2.75 2.75 0 0 1 1.202 1.202c.172.337.24.693.27 1.073c.03.365.03.81.03 1.345v7.66c0 .535 0 .98-.03 1.345c-.03.38-.098.736-.27 1.073a2.75 2.75 0 0 1-1.201 1.202c-.338.172-.694.24-1.074.27c-.365.03-.81.03-1.344.03H8.17c-.535 0-.98 0-1.345-.03c-.38-.03-.736-.098-1.073-.27a2.75 2.75 0 0 1-1.202-1.2c-.172-.338-.24-.694-.27-1.074c-.03-.365-.03-.81-.03-1.344V8.67c0-.535 0-.98.03-1.345c.03-.38.098-.736.27-1.073A2.75 2.75 0 0 1 5.752 5.05c.337-.172.693-.24 1.073-.27q.197-.016.425-.022V4A.75.75 0 0 1 8 3.25m10.25 7H5.75v6.05c0 .572 0 .957.025 1.252c.023.288.065.425.111.515c.12.236.311.427.547.547c.09.046.227.088.514.111c.296.024.68.025 1.253.025h7.6c.572 0 .957 0 1.252-.025c.288-.023.425-.065.515-.111a1.25 1.25 0 0 0 .547-.547c.046-.09.088-.227.111-.515c.024-.295.025-.68.025-1.252zM10.5 7a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>

        <!-- Calendar dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-[280px] bg-white rounded-[20px] shadow-lg border border-neutral-100 z-50 p-4 space-y-2"
                @click.stop
                ref="dropdown"
            >
                <!-- Month / Year selectors (disabled) -->
                <!-- <div class="flex items-center justify-center mb-4">
                    <div class="flex gap-2 relative">
                        <div class="relative">
                            <button
                                @click="toggleMonthDropdown"
                                class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-neutral-200 hover:bg-neutral-50"
                                type="button"
                            >
                                Month
                                <Icon icon="ic:round-navigate-next" width="25" height="25" class="rotate-90 text-red-700" />
                            </button>
                            <div
                                v-if="showMonthDropdown"
                                class="absolute z-20 w-36 bg-white border border-neutral-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide"
                            >
                                <div
                                    v-for="(month, index) in monthOptions"
                                    :key="index"
                                    @click="selectMonth(index)"
                                    :class="{ 'bg-red-500 text-white': currentCalendarMonth.month() === index }"
                                    class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center"
                                >
                                    {{ month }}
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <button
                                @click="toggleYearDropdown"
                                class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-neutral-200 hover:bg-neutral-50"
                                type="button"
                            >
                                Year
                                <Icon icon="ic:round-navigate-next" width="25" height="25" class="rotate-90 text-red-700" />
                            </button>
                            <div
                                v-if="showYearDropdown"
                                class="absolute z-20 w-36 bg-white border border-neutral-200 rounded-md shadow-lg h-32 overflow-y-scroll scrollbar-hide"
                            >
                                <div
                                    v-for="year in yearOptions"
                                    :key="year"
                                    @click="selectYear(year)"
                                    :class="{ 'bg-red-500 text-white': currentCalendarMonth.year() === year }"
                                    class="p-1 text-sm hover:bg-red-100 cursor-pointer text-center"
                                >
                                    {{ year }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Nav arrows + current month label -->
                <div class="flex items-center justify-between text-base font-medium mb-3 px-1">
                    <span class="font-normal text-lg">{{ currentCalendarMonth.format('MMM YYYY') }}</span>
                    <div class="flex items-center text-red-500 cursor-pointer p-1 rounded-full">
                        <Icon icon="ic:round-navigate-next" width="25" height="25" @click="prevMonth" class="rotate-180" />
                        <Icon icon="ic:round-navigate-next" width="25" height="25" @click="nextMonth" />
                    </div>
                </div>

                <!-- Calendar grid -->
                <table class="w-full text-center text-xs border-collapse">
                    <thead>
                        <tr class="text-neutral-500 font-medium">
                            <th v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="font-medium pb-2">{{ d }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(week, wi) in calendarWeeks" :key="wi">
                            <td
                                v-for="(day, di) in week"
                                :key="di"
                                class="p-0 align-middle"
                                @click="day.isCurrentMonth && !day.isDisabled ? pickDate(day.date) : null"
                            >
                                <div :class="dateCellClass(day)" class="w-full h-8 flex items-center justify-center transition duration-150">
                                    {{ day.day }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Clear button -->
                <div class="mt-4 flex justify-center">
                    <button
                        @click="clearDate"
                        type="button"
                        class="rounded-lg bg-neutral-100 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-200"
                    >
                        Clear
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import dayjs from 'dayjs';
import 'dayjs/locale/en';
import weekday from 'dayjs/plugin/weekday';
import weekOfYear from 'dayjs/plugin/weekOfYear';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { Icon } from '@iconify/vue';

dayjs.extend(weekday);
dayjs.extend(weekOfYear);
dayjs.extend(customParseFormat);
dayjs.locale('en');

export default {
    components: { Icon },
    props: {
        /** Selected date in YYYY-MM-DD format */
        modelValue: { type: String, default: '' },
        /** Minimum selectable date (YYYY-MM-DD) */
        min: { type: String, default: '' },
        /** Show error styling */
        hasError: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],

    data() {
        return {
            showDropdown: false,
            showMonthDropdown: false,
            showYearDropdown: false,
            currentCalendarMonth: this.modelValue ? dayjs(this.modelValue) : dayjs(),
        };
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    watch: {
        modelValue(val) {
            if (val) this.currentCalendarMonth = dayjs(val);
        },
        showDropdown(val) {
            if (val) {
                this.currentCalendarMonth = this.modelValue ? dayjs(this.modelValue) : dayjs();
            } else {
                this.showMonthDropdown = false;
                this.showYearDropdown = false;
            }
        },
    },

    computed: {
        formattedDisplay() {
            if (!this.modelValue) return '';
            const [y, m, d] = this.modelValue.split('-');
            return `${d}/${m}/${y.slice(-2)}`;
        },
        monthOptions() {
            return Array.from({ length: 12 }, (_, i) =>
                new Date(2000, i, 1).toLocaleString('en-US', { month: 'short' })
            );
        },
        yearOptions() {
            const cur = dayjs().year();
            const years = [];
            for (let i = cur - 10; i <= cur + 10; i++) years.push(i);
            return years.sort((a, b) => b - a);
        },
        minDayjs() {
            return this.min ? dayjs(this.min, 'YYYY-MM-DD') : null;
        },
        calendarWeeks() {
            const startOfMonth = this.currentCalendarMonth.startOf('month');
            const endOfMonth = this.currentCalendarMonth.endOf('month');
            const startOfWeek = startOfMonth.weekday(0);
            const endOfWeek = endOfMonth.weekday(6);

            const selectedDayjs = this.modelValue ? dayjs(this.modelValue, 'YYYY-MM-DD') : null;
            const today = dayjs().startOf('day');
            const calendar = [];
            let day = startOfWeek;

            while (day.isBefore(endOfWeek) || day.isSame(endOfWeek, 'day')) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    const dateString = day.format('YYYY-MM-DD');
                    const isCurrentMonth = day.isSame(this.currentCalendarMonth, 'month');
                    const isSelected = selectedDayjs && day.isSame(selectedDayjs, 'day');
                    const isToday = day.isSame(today, 'day');
                    const isDisabled = this.minDayjs ? day.isBefore(this.minDayjs, 'day') : false;

                    week.push({ day: day.date(), date: dateString, isCurrentMonth, isSelected, isToday, isDisabled });
                    day = day.add(1, 'day');
                }
                calendar.push(week);
            }
            return calendar;
        },
    },

    methods: {
        handleClickOutside(event) {
            if (!this.showDropdown) return;
            const root = this.$refs.root;
            if (root && !root.contains(event.target)) {
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
        selectMonth(index) {
            this.currentCalendarMonth = dayjs(this.currentCalendarMonth).month(index);
            this.showMonthDropdown = false;
        },
        selectYear(year) {
            this.currentCalendarMonth = dayjs(this.currentCalendarMonth).year(year);
            this.showYearDropdown = false;
        },
        prevMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.subtract(1, 'month');
        },
        nextMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.add(1, 'month');
        },
        pickDate(dateString) {
            this.$emit('update:modelValue', dateString);
            this.showDropdown = false;
        },
        clearDate() {
            this.$emit('update:modelValue', '');
            this.showDropdown = false;
        },
        dateCellClass(day) {
            let cls = 'text-xs ';

            if (day.isDisabled) {
                return cls + 'rounded-lg text-neutral-200 cursor-not-allowed';
            }

            cls += 'cursor-pointer ';

            if (day.isSelected) {
                cls += 'rounded-lg bg-red-500 text-white font-semibold';
            } else if (day.isToday) {
                cls += 'rounded-lg bg-blue-500 text-white font-bold';
            } else if (day.isCurrentMonth) {
                cls += 'rounded-lg text-neutral-800 hover:bg-neutral-100';
            } else {
                cls += 'rounded-lg text-neutral-300';
            }

            return cls;
        },
    },
};
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
