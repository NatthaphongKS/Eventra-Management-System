<!--
 * ชื่อไฟล์: CreateEvent.vue
 * คำอธิบาย: หน้าจอสำหรับสร้างกิจกรรมใหม่ (Add New Event) รวมถึงการอัปโหลดไฟล์แนบและจัดการรายชื่อแขกรับเชิญ
 * Input: ข้อมูลกิจกรรมจากฟอร์ม, ไฟล์แนบ, รายชื่อพนักงานที่เลือก
 * Output: บันทึกข้อมูลกิจกรรมลงฐานข้อมูลผ่าน API /event-save
 * ชื่อผู้เขียน/แก้ไข: ชิตดนัย รัตนเทียนทอง
 * วันที่จัดทำ/แก้ไข: 6 มีนาคม 2569
-->
<template>
    <div class="font-[Poppins] pb-20" @pointerdown.capture="onRootPointer">
        <div class="mb-8 flex items-center gap-3">
            <h2 class="text-3xl font-semibold text-neutral-800">
                Add New Event
            </h2>
        </div>

        <div class="grid grid-cols-12 gap-8 border-neutral-100 mb-6">
            <div class="col-span-12 lg:col-span-8">
                <div class="grid md:grid-cols-[1fr_240px] gap-6 items-start mb-4">
                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">
                            Event Title <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <InputPill v-model="eventTitle" placeholder="Name this event" :class="[
                                'w-full font-medium text-md text-neutral-800 border rounded-2xl px-3 py-2.5 focus:outline-none transition',
                                'placeholder:text-red-300',
                                errors.eventTitle
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                            ]" @input="errors.eventTitle = false" />
                            <p v-if="errors.eventTitle"
                                class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                                Required Field
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">
                            Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select v-model="eventCategoryId" :class="[
                                'appearance-none border rounded-2xl px-3 py-2.5 w-full font-medium text-md focus:outline-none transition bg-white cursor-pointer',
                                eventCategoryId
                                    ? 'text-neutral-800'
                                    : 'text-red-300',
                                errors.eventCategoryId
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                            ]" @change="errors.eventCategoryId = false">
                                <option value="" disabled selected>
                                    Choose Category
                                </option>
                                <option v-for="cat in selectCategory" :key="cat.id" :value="cat.id"
                                    class="text-neutral-800">
                                    {{ cat.cat_name }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <Icon icon="mdi:chevron-down" class="h-6 w-6 text-red-300" />
                            </div>
                            <p v-if="errors.eventCategoryId"
                                class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                                Required Select
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="mb-1 block text-xl font-medium text-neutral-800">
                        Event Description <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <textarea v-model.trim="eventDescription" placeholder="Write some description... (255 words)"
                            :class="[
                                'w-full h-[160px] rounded-2xl px-3 py-2.5 font-medium text-md text-neutral-800 focus:outline-none transition resize-none border',
                                'placeholder:text-red-300',
                                errors.eventDescription
                                    ? 'border-red-500 '
                                    : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                            ]" @input="errors.eventDescription = false"></textarea>
                        <p v-if="errors.eventDescription"
                            class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                            Required Field
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pb-2">
                    <div class="relative">
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Date <span
                                class="text-red-600">*</span></label>
                        <EventSingleDatePicker v-model="eventDate" :min="minDate" :has-error="errors.eventDate"
                            @update:modelValue="errors.eventDate = false" />
                        <p v-if="errors.eventDate" class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                            Required Date
                        </p>
                    </div>

                    <div class="relative">
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Time <span
                                class="text-red-600">*</span></label>
                        <div :class="[
                            'flex w-full items-center rounded-2xl border px-3 py-2.5 shadow-sm bg-white transition text-md font-medium',
                            errors.eventTime
                                ? 'border-red-500 bg-red-50'
                                : 'border-neutral-200 focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400',
                        ]">
                            <!-- Start trigger -->
                            <div class="relative flex-1 flex items-center justify-center">
                                <button type="button" class="tp-trigger"
                                    :class="(pickerStartHour || pickerStartMin) ? 'text-neutral-800' : 'text-red-300'"
                                    @click.stop="togglePanel('start')">
                                    {{ (!pickerStartHour && !pickerStartMin) ? 'Start' : (pickerStartHour || '--') + ':'
                                        + (pickerStartMin || '--') }}
                                </button>
                                <!-- Start panel -->
                                <div v-if="showStartPanel" class="tp-panel" @pointerdown.stop @click.stop>
                                    <div class="tp-col" ref="startHourCol">
                                        <div class="tp-col-header">Hour</div>
                                        <div v-for="h in hourOptions" :key="'sh' + h"
                                            :class="['tp-item', { 'tp-active': pickerStartHour === h }]"
                                            :ref="pickerStartHour === h ? 'startHourActive' : undefined"
                                            @pointerdown.stop="selectStartHour(h)">{{ h }}</div>
                                    </div>
                                    <div class="tp-col" ref="startMinCol">
                                        <div class="tp-col-header">Min</div>
                                        <div v-for="m in minuteOptions" :key="'sm' + m"
                                            :class="['tp-item', { 'tp-active': pickerStartMin === m }]"
                                            :ref="pickerStartMin === m ? 'startMinActive' : undefined"
                                            @pointerdown.stop="selectStartMin(m)">{{ m }}</div>
                                    </div>
                                </div>
                            </div>

                            <span class="flex-none text-md font-bold text-red-300 px-1">-</span>

                            <!-- End trigger -->
                            <div class="relative flex-1 flex items-center justify-center">
                                <button type="button" class="tp-trigger"
                                    :class="(pickerEndHour || pickerEndMin) ? 'text-neutral-800' : 'text-red-300'"
                                    @click.stop="togglePanel('end')">
                                    {{ (!pickerEndHour && !pickerEndMin) ? 'End' : (pickerEndHour || '--') + ':' +
                                        (pickerEndMin || '--') }}
                                </button>
                                <!-- End panel -->
                                <div v-if="showEndPanel" class="tp-panel" @pointerdown.stop @click.stop>
                                    <div class="tp-col" ref="endHourCol">
                                        <div class="tp-col-header">Hour</div>
                                        <div v-for="h in hourOptions" :key="'eh' + h"
                                            :class="['tp-item', { 'tp-active': pickerEndHour === h }]"
                                            :ref="pickerEndHour === h ? 'endHourActive' : undefined"
                                            @pointerdown.stop="selectEndHour(h)">{{ h }}</div>
                                    </div>
                                    <div class="tp-col" ref="endMinCol">
                                        <div class="tp-col-header">Min</div>
                                        <div v-for="m in minuteOptions" :key="'em' + m"
                                            :class="['tp-item', { 'tp-active': pickerEndMin === m }]"
                                            :ref="pickerEndMin === m ? 'endMinActive' : undefined"
                                            @pointerdown.stop="selectEndMin(m)">{{ m }}</div>
                                    </div>
                                </div>
                            </div>

                            <Icon icon="mdi:clock-outline"
                                class="flex-none w-5 h-5 text-red-700 mr-1 pointer-events-none" />
                        </div>
                        <p v-if="errors.eventTime" class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                            {{ timeErrorMessage || 'Required Time' }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Duration</label>
                        <div
                            class="flex w-full items-center gap-3 rounded-2xl border border-neutral-200 px-3 py-2.5 shadow-sm bg-[#F9FAFB] text-md font-medium">
                            <input
                                class="w-full bg-transparent font-medium text-neutral-600 outline-none border-0 placeholder:text-neutral-400 text-md"
                                disabled v-model="eventDurationDisplay" placeholder="Auto fill   Hour" />
                            <Icon icon="lucide:clock-fading" class="w-6 h-6 text-neutral-400" />
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="mb-1 block text-xl font-medium text-neutral-800">
                        Location <span class="text-red-600">*</span>
                    </label>

                    <div class="relative">
                        <InputPill v-model="eventLocation" placeholder="Location/Building/Room Name" :class="[
                            'w-full font-medium text-md text-neutral-800 border rounded-2xl px-3 py-2.5 focus:outline-none transition',
                            'placeholder:text-red-300',
                            errors.eventLocation
                                ? 'border-red-500 bg-red-50'
                                : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                        ]" @input="errors.eventLocation = false" />
                        <p v-if="errors.eventLocation"
                            class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">
                            Required Field
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-span-4 m-5">
                <h3 class="text-[17px] font-semibold text-neutral-800">
                    Upload attachments
                </h3>
                <p class="text-sm text-neutral-800 mb-2">
                    Drag and drop document to your support task
                </p>

                <div class="group relative rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50 p-6 transition-all"
                    :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }" @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
                    <div v-if="filesNew.length > 0" class="mb-4 space-y-2">
                        <div v-for="(item, index) in filesNew" :key="index"
                            class="w-full flex items-center justify-between rounded-2xl bg-white border border-neutral-200 px-4 py-3 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-md">
                                    <Icon icon="basil:file-solid" class="h-10 w-10 text-rose-600" />
                                </div>

                                <div class="truncate">
                                    <span class="truncate text-[16px] text-neutral-800 block">{{ item.name }}</span>
                                    <span class="text-xs text-rose-500">{{
                                        prettySize(item.size)
                                    }}</span>
                                </div>
                            </div>

                            <button type="button"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-100"
                                @click="removeFile(index)" aria-label="Remove file">
                                ✕
                            </button>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center text-center min-h-[260px]">
                        <Icon icon="ep:upload-filled" class="w-40 h-28 mb-3 text-rose-300" />
                        <p class="text-[16px] font-medium text-neutral-800">
                            Choose a file or drag &amp; drop it here
                        </p>
                        <p class="mt-1 text-sm text-neutral-800">
                            pdf, txt, docx, jpeg, xlsx, png
                        </p>
                    </div>

                    <div class="flex justify-center mt-1 mb-12">
                        <button type="button"
                            class="inline-flex items-center rounded-[12px] border bg-white border-rose-500 px-2 py-1 text-neutral-800 hover:bg-rose-50 active:bg-rose-100"
                            @click="pickFiles">
                            <span class="text-sm font-medium">Browse files</span>
                        </button>
                    </div>

                    <input ref="fileInput" type="file" multiple class="hidden"
                        accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
                </div>
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-3xl font-semibold text-neutral-800 mb-4">
                Add Guest
            </h3>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex flex-1 items-center gap-2 min-w-[200px]">
                    <SearchBar v-model="searchRaw" placeholder="Search ID / Name / Nickname" @search="performSearch" />
                </div>

                <div class="mt-8 gap-2 flex flex-row flex-wrap items-center">
                    <EmployeeDropdown label="Company" v-model="selectedCompanyIds" :options="companyIdOptions"
                        class="px-2" />
                    <EmployeeDropdown label="Department" v-model="selectedDepartmentIds" :options="departmentOptions"
                        class="px-2" />
                    <EmployeeDropdown label="Team" v-model="selectedTeamIds" :options="teamOptions" class="px-2" />
                    <EmployeeDropdown label="Position" v-model="selectedPositionIds" :options="positionOptions"
                        class="px-2" />
                </div>
            </div>

            <!-- แสดงจำนวนคนที่ถูกเลือก -->
            <div class="mt-4 flex items-center gap-2">
                <Icon icon="mdi:account-check" class="w-7 h-7 text-red-600" />
                <span class="text-[16px] font-medium text-neutral-700">
                    Selected Guests :
                </span>
                <span class="text-[16px] font-semibold text-red-600">
                    {{ selectedIdsForSubmit.length }}
                </span>
                <span class="text-[16px] font-medium text-neutral-500">
                    / {{ employees.length }} people
                </span>
            </div>

            <div class="mt-8">
                <DataTable :rows="pagedEmployees" :columns="columns" :loading="loadingEmployees"
                    :totalItems="filteredEmployees.length" v-model:page="page" v-model:pageSize="perPage"
                    :pageSizeOptions="[10, 25, 50]" :selectable="true" :showRowNumber="true" rowKey="id"
                    :modelValue="selectedIdsArr" @update:modelValue="onUpdateSelected">
                    <template #cell-fullname="{ row }">
                        {{
                            (row.emp_firstname || "") +
                            " " +
                            (row.emp_lastname || "")
                        }}
                    </template>
                    <template #empty>
                        <div class="py-8 text-center text-neutral-400">
                            ไม่พบข้อมูลพนักงาน
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <div class="mt-10 w-full flex flex-row justify-between items-center border-t border-neutral-100 pt-8">
            <div class="flex-none">
                <button type="button" @click="onCancel" :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#C10008] text-white font-semibold hover:bg-red-700 w-[140px] h-[48px] transition shadow-sm">
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white rotate-45" />
                    <span>Cancel</span>
                </button>
            </div>

            <div class="flex-none">
                <button type="button" @click="saveEvent" :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#00A73D] text-white font-semibold hover:bg-green-700 w-[140px] h-[48px] transition shadow-sm">
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                    <span>Create</span>
                </button>
            </div>
        </div>

        <ModalAlert v-model:open="showConfirmCreate" title="Confirm Creation"
            message="Are you sure you want to create this event?" type="confirm" :showCancel="true"
            @confirm="executeCreateEvent" />

        <ModalAlert v-model:open="progressAlert.open" type="progress" title="Saving..."
            message="Please wait while we save your changes." :progress="progressAlert.progress"
            :total="selectedIdsForSubmit.length" :showCancel="false" />

        <ModalAlert v-model:open="showSuccessAlert" title="Success" message="New event has been created." type="success"
            :showCancel="false" @confirm="onSuccessConfirm" />

        <ModalAlert v-model:open="fileTypeError" title="ERROR!" message="Unsupported file type. Please try again."
            type="error" :showCancel="false" />

        <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
            :showCancel="alert.showCancel" :okText="alert.okText" :cancelText="alert.cancelText"
            @confirm="alert.onConfirm?.()" @cancel="alert.onCancel?.()" />
    </div>
</template>

<script>
import axios from "axios";
import InputPill from "@/components/Input/InputPill.vue";
import { Icon } from "@iconify/vue";
import DataTable from "@/components/DataTable.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import EventSingleDatePicker from "@/components/IndexEvent/EventSingleDatePicker.vue";
import SearchBar from "@/components/SearchBar.vue";

export default {
    components: {
        InputPill,
        Icon,
        DataTable,
        EmployeeDropdown,
        CancelButton,
        ModalAlert,
        EventSingleDatePicker,
        SearchBar,
    },
    data() {
        // ตัวแปรเก็บข้อมูลพื้นฐานของกิจกรรม
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");

        return {
            eventTitle: "",
            eventCategoryId: "",
            eventDescription: "",
            eventDate: "",
            eventTimeStart: "",
            eventTimeEnd: "",
            showStartPanel: false,
            showEndPanel: false,
            pickerStartHour: "",
            pickerStartMin: "",
            pickerEndHour: "",
            pickerEndMin: "",
            eventDurationDisplay: "",
            eventDurationMinutes: 0,
            eventLocation: "",
            minDate: `${year}-${month}-${day}`,
            errors: {
                eventTitle: false,
                eventCategoryId: false,
                eventDescription: false,
                eventDate: false,
                eventTime: false,
                eventLocation: false,
            },
            timeErrorMessage: '',
            selectCategory: [],
            filesNew: [],
            dragging: false,
            fileTypeError: false,
            employees: [],
            loadingEmployees: false,
            search: "", // Filter value
            searchRaw: "", // V-model input value
            selectedCompanyIds: [],
            selectedDepartmentIds: [],
            selectedTeamIds: [],
            selectedPositionIds: [],
            companyIdOptions: [],
            departmentOptions: [],
            teamOptions: [],
            positionOptions: [],
            selectedIds: new Set(),
            page: 1,
            perPage: 10,
            saving: false,
            showConfirmCreate: false,
            showSuccessAlert: false,
            alert: {
                open: false,
                type: 'confirm',
                title: '',
                message: '',
                showCancel: false,
                okText: 'OK',
                cancelText: 'Cancel',
                onConfirm: null,
                onCancel: null,
            },
            progressAlert: { open: false, progress: 0, total: 100 },
        };
    },
    computed: {
        hourOptions() {
            return Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'));
        },
        minuteOptions() {
            return Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'));
        },
        formattedDateDisplay() {
            if (!this.eventDate) return "";
            const [y, m, d] = this.eventDate.split("-");
            return `${d}/${m}/${y.slice(-2)}`;
        },
        columns() {
            return [
                {
                    key: "emp_id",
                    label: "ID",
                    class: "text-left min-w-[100px]",
                },
                {
                    key: "fullname",
                    label: "Name",
                    class: "text-left min-w-[200px]",
                },
                {
                    key: "nickname",
                    label: "Nickname",
                    class: "text-left min-w-[100px]",
                },
                {
                    key: "department",
                    label: "Department",
                    class: "text-left min-w-[180px]",
                },
                {
                    key: "team",
                    label: "Team",
                    class: "text-left min-w-[140px]",
                },
                {
                    key: "position",
                    label: "Position",
                    class: "text-left min-w-[240px]",
                },
            ];
        },
        filteredEmployees() {
            const q = (this.search || "").toLowerCase().trim();
            let list = this.employees;
            if (q) {
                list = list.filter(
                    (e) =>
                        (e.emp_firstname || "").toLowerCase().includes(q) ||
                        (e.emp_id || "").toLowerCase().includes(q) ||
                        (e.nickname || "").toLowerCase().includes(q)
                );
            }
            if (this.selectedCompanyIds.length)
                list = list.filter((r) =>
                    this.selectedCompanyIds.includes(r.companyId)
                );
            if (this.selectedDepartmentIds.length)
                list = list.filter((r) =>
                    this.selectedDepartmentIds.includes(r.department)
                );
            if (this.selectedTeamIds.length)
                list = list.filter((r) =>
                    this.selectedTeamIds.includes(r.team)
                );
            if (this.selectedPositionIds.length)
                list = list.filter((r) =>
                    this.selectedPositionIds.includes(r.position)
                );
            return list;
        },
        pagedEmployees() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredEmployees.slice(start, start + this.perPage);
        },
        selectedIdsArr: {
            get() {
                return Array.from(this.selectedIds);
            },
            set(arr) {
                this.selectedIds = new Set(arr);
            },
        },
        selectedIdsForSubmit() {
            return Array.from(this.selectedIds);
        },
    },
    watch: {
        eventTimeStart() {
            this.calDuration();
        },
        eventTimeEnd() {
            this.calDuration();
        },
    },
    mounted() {
        this.fetchInfo();
    },
    methods: {
        // ฟังก์ชันสำหรับค้นหาข้อมูลพนักงาน
        performSearch() {
            this.search = this.searchRaw;
            this.page = 1;
        },
        togglePanel(which) {
            if (which === 'start') {
                this.showEndPanel = false;
                this.showStartPanel = !this.showStartPanel;
                if (this.showStartPanel) this.$nextTick(() => this.scrollPanelToActive('start'));
            } else {
                this.showStartPanel = false;
                this.showEndPanel = !this.showEndPanel;
                if (this.showEndPanel) this.$nextTick(() => this.scrollPanelToActive('end'));
            }
        },
        scrollPanelToActive(which) {
            const scroll = (refArr, container) => {
                const el = Array.isArray(refArr) ? refArr[0]?.$el || refArr[0] : refArr?.$el || refArr;
                if (el && container) container.scrollTop = el.offsetTop - container.offsetTop - 40;
            };
            if (which === 'start') {
                scroll(this.$refs.startHourActive, this.$refs.startHourCol);
                scroll(this.$refs.startMinActive, this.$refs.startMinCol);
            } else {
                scroll(this.$refs.endHourActive, this.$refs.endHourCol);
                scroll(this.$refs.endMinActive, this.$refs.endMinCol);
            }
        },
        selectStartHour(h) {
            this.pickerStartHour = h;
            this.syncStartTime();
        },
        selectStartMin(m) {
            this.pickerStartMin = m;
            this.syncStartTime();
        },
        syncStartTime() {
            if (this.pickerStartHour !== '' && this.pickerStartMin !== '') {
                this.eventTimeStart = `${this.pickerStartHour}:${this.pickerStartMin}`;
                this.errors.eventTime = false;
                this.calDuration();
            }
        },
        selectEndHour(h) {
            this.pickerEndHour = h;
            this.syncEndTime();
        },
        selectEndMin(m) {
            this.pickerEndMin = m;
            this.syncEndTime();
        },
        syncEndTime() {
            if (this.pickerEndHour !== '' && this.pickerEndMin !== '') {
                this.eventTimeEnd = `${this.pickerEndHour}:${this.pickerEndMin}`;
                this.errors.eventTime = false;
                this.calDuration();
            }
        },
        closePickers() {
            this.showStartPanel = false;
            this.showEndPanel = false;
        },
        // ฟังก์ชันสำหรับคำนวณระยะเวลาของกิจกรรม (Duration)
        calDuration() {
            if (!this.eventTimeStart || !this.eventTimeEnd) {
                this.eventDurationDisplay = "";
                this.timeErrorMessage = '';
                return;
            }
            const [sh, sm] = this.eventTimeStart.split(":").map(Number);
            const [eh, em] = this.eventTimeEnd.split(":").map(Number);
            const startMin = sh * 60 + sm;
            const endMin = eh * 60 + em;
            // if end is before or equal start -> invalid (do not auto-wrap to next day)
            if (endMin <= startMin) {
                this.eventDurationDisplay = "";
                this.eventDurationMinutes = 0;
                this.errors.eventTime = true;
                this.timeErrorMessage = 'End time must be after start time';
                return;
            }
            let diff = endMin - startMin;
            this.eventDurationMinutes = diff;
            const h = Math.floor(diff / 60);
            const m = diff % 60;
            this.eventDurationDisplay =
                h > 0 ? `${h} Hour ${m} Min` : `${m} Min`;
            this.errors.eventTime = false;
            this.timeErrorMessage = '';
        },
        // ฟังก์ชันตรวจสอบความถูกต้องของข้อมูล (Validation) ก่อนแสดง Modal ยืนยัน
        saveEvent() {
            this.errors.eventTitle = !this.eventTitle;
            this.errors.eventCategoryId = !this.eventCategoryId;
            this.errors.eventDescription = !this.eventDescription;
            this.errors.eventDate = !this.eventDate;
            this.errors.eventTime = !this.eventTimeStart || !this.eventTimeEnd;
            // Additional validation: end must be after start
            if (!this.errors.eventTime) {
                const [sh, sm] = this.eventTimeStart.split(":").map(Number);
                const [eh, em] = this.eventTimeEnd.split(":").map(Number);
                const startMin = sh * 60 + sm;
                const endMin = eh * 60 + em;
                if (endMin <= startMin) {
                    this.errors.eventTime = true;
                    this.timeErrorMessage = 'End time must be after start time';
                } else {
                    this.timeErrorMessage = '';
                }
            }
            this.errors.eventLocation = !this.eventLocation;
            if (Object.values(this.errors).some((v) => v)) return;
            this.showConfirmCreate = true;
        },
        // ฟังก์ชันสำหรับส่งข้อมูลกิจกรรมและไฟล์แนบไปยัง Server
        async executeCreateEvent() {
            this.showConfirmCreate = false;
            this.saving = true;

            //  เปิด progress modal
            this.progressAlert = { open: true, progress: 0, total: 100 };

            // จำลอง progress 0 → 80 ระหว่างรอ API
            let fakeProgress = 0;
            const progressInterval = setInterval(() => {
                if (fakeProgress < 80) {
                    fakeProgress += Math.floor(Math.random() * 10) + 5;
                    if (fakeProgress > 80) fakeProgress = 80;
                    this.progressAlert.progress = fakeProgress;
                }
            }, 300);

            try {
                const formData = new FormData();
                // จัดเตรียมข้อมูลในรูปแบบ Multipart Form Data
                formData.append("event_title", this.eventTitle.trim());
                formData.append("event_category_id", this.eventCategoryId);
                formData.append("event_description", this.eventDescription);
                formData.append("event_date", this.eventDate);
                formData.append("event_timestart", this.eventTimeStart);
                formData.append("event_timeend", this.eventTimeEnd);
                formData.append("event_duration", this.eventDurationMinutes);
                formData.append("event_location", this.eventLocation);
                this.filesNew.forEach((f) =>
                    formData.append("attachments[]", f)
                );
                this.selectedIds.forEach((id) =>
                    formData.append("employee_ids[]", id)
                );
                await axios.post("/event-save", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                //  API เสร็จแล้ว  progress 100 แล้วปิด
                clearInterval(progressInterval);
                this.progressAlert.progress = 100;

                await new Promise(r => setTimeout(r, 600)); // รอให้ bar เต็มก่อนปิด
                this.progressAlert.open = false;

                this.showSuccessAlert = true;
            } catch (err) {
                //  ปิด progress ก่อนแสดง error
                clearInterval(progressInterval);
                this.progressAlert.open = false;

                console.error(err);
                alert("Failed to create event.");
            } finally {
                this.saving = false;
            }
        },
        onSuccessConfirm() {
            this.showSuccessAlert = false;
            this.$router.push("/event");
        },
        // ฟังก์ชันดึงข้อมูลเบื้องต้น (หมวดหมู่และรายชื่อพนักงาน) จาก Server
        async fetchInfo() {
            try {
                this.loadingEmployees = true;
                const res = await axios.get("/event-info");
                this.selectCategory = res.data?.categories || [];
                this.employees = (res.data?.employees || []).map((e) => {
                    const rawId = String(e.emp_id || "").trim();
                    const prefixMatch = rawId.match(/^[A-Za-z]+/);
                    const companyPrefix = prefixMatch
                        ? prefixMatch[0].toUpperCase()
                        : "";
                    return {
                        id: e.id,
                        emp_id: e.emp_id || "",
                        emp_firstname: e.emp_firstname || "",
                        emp_lastname: e.emp_lastname || "",
                        nickname: e.emp_nickname || "",
                        department: e.department_name || "",
                        companyId: companyPrefix || e.company_id || "",
                        team: e.team_name || "",
                        position: e.position_name || "",
                    };
                });
                this.buildFilterOptions();
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingEmployees = false;
            }
        },
        buildFilterOptions() {
            const toOpt = (arr) =>
                [...new Set(arr.filter(Boolean))]
                    .sort()
                    .map((v) => ({ label: v, value: v }));
            this.companyIdOptions = toOpt(
                this.employees.map((r) => r.companyId)
            );
            this.departmentOptions = toOpt(
                this.employees.map((r) => r.department)
            );
            this.teamOptions = toOpt(this.employees.map((r) => r.team));
            this.positionOptions = toOpt(this.employees.map((r) => r.position));
        },
        pickFiles() {
            this.$refs.fileInput.click();
        },
        onPick(e) {
            this.addFiles([...e.target.files]);
            e.target.value = "";
        },
        onDrop(e) {
            this.dragging = false;
            this.addFiles([...e.dataTransfer.files]);
        },
        // ฟังก์ชันจัดการการอัปโหลดไฟล์
        addFiles(files) {
            const allowed = ['pdf', 'txt', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
            let hasInvalid = false;
            files.forEach((f) => {
                const ext = f.name.split('.').pop().toLowerCase();
                if (!allowed.includes(ext)) {
                    hasInvalid = true;
                    return;
                }
                if (f.size <= 50 * 1024 * 1024) this.filesNew.push(f);
            });
            this.fileTypeError = hasInvalid;
        },
        removeFile(idx) {
            this.filesNew.splice(idx, 1);
        },
        prettySize(byte) {
            return (byte / (1024 * 1024)).toFixed(2) + " MB";
        },
        onUpdateSelected(ids) {
            this.selectedIds = new Set(ids);
        },
        onCancel() {
            if (!this.isFormDirty()) {
                this.$router.back();
                return;
            }
            this.alert = {
                open: true,
                type: 'confirm',
                title: 'DO YOU WANT TO LEAVE THIS CHANGE?',
                message: 'Your changes will be lost.',
                showCancel: true,
                okText: 'Ok',
                cancelText: 'Cancel',
                onConfirm: () => {
                    this.alert.open = false;
                    this.$router.back();
                },
                onCancel: () => {
                    this.alert.open = false;
                },
            };
        },
        isFormDirty() {
            if (this.eventTitle) return true;
            if (this.eventCategoryId) return true;
            if (this.eventDescription) return true;
            if (this.eventDate) return true;
            if (this.eventTimeStart || this.eventTimeEnd) return true;
            if (this.eventLocation) return true;
            if (this.filesNew.length > 0) return true;
            if (this.selectedIds.size > 0) return true;
            return false;
        },
        onRootPointer(e) {
            if (e.target.closest('.tp-panel') || e.target.closest('.tp-trigger')) return;
            this.closePickers();
        },
    },
};
</script>

<style scoped>
/* Time trigger button */
.tp-trigger {
    width: 100%;
    /* removed height: 44px to align with other input fields */
    padding: 0;
    font-size: 15px;
    font-weight: 600;
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    outline: none;
    text-align: center;
}

.tp-trigger:hover {
    background: #fff1f2;
}

/* Two-column panel */
.tp-panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%);
    z-index: 50;
    display: flex;
    background: white;
    border: 1px solid #e5e5e5;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
    overflow: hidden;
    width: 160px;
}

.tp-col {
    flex: 1;
    max-height: 220px;
    overflow-y: auto;
    scroll-behavior: smooth;
}

.tp-col+.tp-col {
    border-left: 1px solid #f0f0f0;
}

.tp-col-header {
    position: sticky;
    top: 0;
    background: white;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: #9ca3af;
    padding: 6px 0;
    border-bottom: 1px solid #f0f0f0;
    z-index: 1;
}

.tp-item {
    text-align: center;
    padding: 6px 0;
    font-size: 14px;
    font-weight: 500;
    color: #525252;
    cursor: pointer;
    transition: background .1s;
}

.tp-item:hover {
    background: #fff1f2;
}

.tp-active {
    background: #be123c !important;
    color: white !important;
    font-weight: 600;
}

.tp-col::-webkit-scrollbar {
    width: 4px;
}

.tp-col::-webkit-scrollbar-thumb {
    background: #e5e5e5;
    border-radius: 4px;
}

.tp-col::-webkit-scrollbar-track {
    background: transparent;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    cursor: pointer;
    opacity: 0;
}

.caret-transparent {
    caret-color: transparent;
}
</style>
