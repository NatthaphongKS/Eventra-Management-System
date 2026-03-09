<!-- /**
 * ชื่อไฟล์: EditEvent.vue
 * คำอธิบาย: หน้าแก้ไขข้อมูลกิจกรรม รองรับการอัปโหลดไฟล์และเลือก Guest
 * Input: id (รหัสกิจกรรม) จาก route params
 * Output: แบบฟอร์มแก้ไขกิจกรรม ส่งข้อมูลผ่าน POST /edit-event
 * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
 * วันที่จัดทำ/แก้ไข: 2026-03-03
 */ -->

<!-- pages/edit_event.vue -->
<template>
    <div class="font-[Poppins] pb-20" @pointerdown.capture="onRootPointer">
        <div class="text-neutral-800 font-semibold font-[Poppins] text-3xl mb-4">
            Edit Event
        </div>
        <div class="grid grid-cols-12 h-full gap-0">
            <div class="col-span-8">

                <!-- ช่องกรอกชื่ออีเวนต์ -->
                <div class="grid md:grid-cols-[1fr_240px] gap-6 items-start mb-6 mt-6">
                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">
                            Event Title <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <InputPill v-model="eventTitle" :class="[
                                'w-full font-medium text-md text-neutral-800 border rounded-2xl px-3 py-2.5 focus:outline-none transition',
                                'placeholder:text-red-300',
                                submitted && formErrors.eventTitle ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                            ]" />
                            <p v-if="submitted && formErrors.eventTitle"
                                class="absolute -bottom-5 left-1 text-xs text-red-600 font-medium">
                                Required field
                            </p>
                        </div>
                    </div>

                    <!-- ช่องเลือกประเภท event-->
                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select :class="[
                                'appearance-none border rounded-2xl px-3 py-2.5 w-full font-medium text-md focus:outline-none transition bg-white cursor-pointer',
                                eventCategoryId ? 'text-neutral-800' : 'text-red-300',
                                submitted && formErrors.eventCategoryId ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                            ]" v-model="eventCategoryId">
                                <option :value="initialForm.eventCategoryId" hidden>
                                    {{ eventCategoryName }}
                                </option>

                                <option v-for="cat in selectCategory" :value="cat.id" class="text-neutral-800">
                                    {{ cat.cat_name }}
                                </option>
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <Icon icon="mdi:chevron-down" class="h-6 w-6 text-red-300" />
                            </div>
                            <p v-if="submitted && formErrors.eventCategoryId"
                                class="absolute -bottom-5 left-1 text-xs text-red-600 font-medium">
                                Required Select
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ช่องกรอกคำอธิบายอีเวนต์ -->
                <div class="mb-6">
                    <label class="mb-1 block text-xl font-medium text-neutral-800">
                        Event Description <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <textarea :class="[
                            'w-full h-[160px] rounded-2xl px-3 py-2.5 font-medium text-md text-neutral-800 focus:outline-none transition resize-none border',
                            'placeholder:text-red-300',
                            submitted && formErrors.eventDescription ? 'border-red-500 ' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                        ]" v-model.trim="eventDescription" placeholder="Write some description... (255 words)">
                        </textarea>
                        <p v-if="submitted && formErrors.eventDescription"
                            class="absolute -bottom-5 left-1 text-xs text-red-600 font-medium">
                            Required field
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pb-2">

                    <!-- ช่องกรอกวันที่ -->
                    <div class="relative">
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Date <span
                                class="text-red-600">*</span></label>
                        <EventSingleDatePicker v-model="eventDate" :min="minDate"
                            :has-error="submitted && formErrors.eventDate"
                            @update:modelValue="formErrors.eventDate = false" />
                        <p v-if="submitted && formErrors.eventDate"
                            class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">Required Date</p>
                    </div>

                    <!-- ช่องกรอกเวลา -->
                    <div class="relative">
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Time <span
                                class="text-red-600">*</span></label>
                        <div :class="[
                            'flex w-full items-center rounded-2xl border px-3 py-2.5 shadow-sm bg-white transition',
                            submitted && formErrors.eventTime
                                ? 'border-red-500 bg-red-50'
                                : 'border-neutral-200 focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400',
                        ]">
                            <!-- ปุ่มเลือกเวลาเริ่มต้น -->
                            <div class="relative flex-1 flex items-center justify-center">
                                <button type="button" class="tp-trigger"
                                    :class="(pickerStartHour || pickerStartMin) ? 'text-neutral-800' : 'text-red-300'"
                                    @click.stop="togglePanel('start')">
                                    {{ (!pickerStartHour && !pickerStartMin) ? 'Start' : (pickerStartHour || '--') + ':'
                                        + (pickerStartMin || '--') }}
                                </button>
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

                            <span class="flex-none text-[16px] font-bold text-red-300 px-1">-</span>
                            <!-- ปุ่มเลือกเวลาสิ้นสุด -->
                            <div class="relative flex-1 flex items-center justify-center">
                                <button type="button" class="tp-trigger"
                                    :class="(pickerEndHour || pickerEndMin) ? 'text-neutral-800' : 'text-red-300'"
                                    @click.stop="togglePanel('end')">
                                    {{ (!pickerEndHour && !pickerEndMin) ? 'End' : (pickerEndHour || '--') + ':' +
                                        (pickerEndMin || '--') }}
                                </button>
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
                        <!-- ข้อความ error แสดงใต้ช่องเวลา -->
                        <p v-if="formErrors.eventTime"
                            class="absolute -bottom-5 left-1 text-red-500 text-xs font-medium">{{ timeErrorMessage ||
                                'Required Time' }}</p>
                    </div>

                    <!-- ช่องแสดงผลระยะเวลาที่คำนวณได้ -->
                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Duration</label>
                        <div
                            class="flex w-full items-center gap-3 rounded-2xl border border-neutral-200 px-3 py-2.5 shadow-sm bg-[#F5F5F5]">
                            <input class="w-full bg-transparent outline-none text-neutral-500 font-medium text-md"
                                disabled v-model="eventDuration" placeholder="Auto fill Hour" />
                            <Icon icon="mingcute:time-duration-line" class="w-6 h-6 text-neutral-400" />
                        </div>
                    </div>
                </div>

                <!-- ช่องกรอกสถานที่-->
                <div class="mb-6">
                    <label class="mb-1 block text-xl font-medium text-neutral-800">
                        Location <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <InputPill v-model="eventLocation" :class="[
                            'w-full font-medium text-md text-neutral-800 border rounded-2xl px-3 py-2.5 focus:outline-none transition',
                            'placeholder:text-red-300',
                            submitted && formErrors.eventLocation ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                        ]" />

                        <p v-if="submitted && formErrors.eventLocation"
                            class="absolute -bottom-5 left-1 text-xs text-red-600 font-medium">
                            Required field
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload attachments -->
            <div class="col-span-4 m-5">
                <h3 class="text-[17px] font-semibold text-neutral-800">Upload attachments</h3>
                <p class="text-sm text-neutral-800 mb-2">Drag and drop document to your support task</p>

                <!-- Drop zone -->
                <div class="group relative rounded-2xl border-2 border-dashed border-red-700 bg-red-100 p-6 transition-all"
                    :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }" @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
                    <!-- รายการไฟล์ (เดิม + ใหม่) เต็มความกว้าง เรียงลงมา -->
                    <div v-if="hasAnyFiles" class="mb-4 space-y-2">
                        <div v-for="item in uploadItems" :key="item.key"
                            class="w-full flex items-center justify-between rounded-2xl bg-white border border-neutral-200 px-4 py-3 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-md ">
                                    <Icon icon="basil:file-solid" class="h-10 w-10 text-red-700" />
                                </div>

                                <!-- ไฟล์เดิมเป็นลิงก์, ไฟล์ใหม่เป็นข้อความ -->
                                <template v-if="item.kind === 'existing'">
                                    <a :href="item.url" target="_blank" rel="noopener"
                                        class="truncate text-[16px]  text-red-700 hover:underline">
                                        {{ item.name }}
                                    </a>
                                    <!-- <span class="ml-2 shrink-0 text-xs text-neutral-500">({{ prettySize(item.size)
                                }})</span> -->
                                </template>
                                <template v-else>
                                    <span class="truncate text-[16px]  text-neutral-800">{{ item.name }}</span>
                                    <!-- <span class="ml-2 shrink-0 text-xs text-neutral-500">({{ prettySize(item.size)
                                }})</span> -->
                                </template>
                            </div>

                            <button type="button"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-100"
                                @click="item.kind === 'existing' ? removeExisting(item.id) : removeFile(item.index)"
                                aria-label="Remove file" title="Remove">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- เมฆ + ข้อความ: โชว์เฉพาะตอน “ยังไม่มีไฟล์เลย” -->
                    <div v-else class="flex flex-col items-center justify-center text-center min-h-[260px]">
                        <Icon icon="ep:upload-filled" class="w-40 h-28 mb-3 text-red-300" />
                        <p class="text-[16px]  font-medium text-neutral-800">Choose a file or drag &amp; drop it here
                        </p>
                        <p class="mt-1 text-sm text-neutral-800">pdf, txt, docx, jpeg, xlsx, png</p>
                    </div>

                    <!-- ปุ่ม Browse: อยู่ล่างกลางเสมอ -->
                    <div class="flex justify-center mt-1 mb-12">
                        <button type="button"
                            class="inline-flex items-center rounded-[10px] border  bg-white border-rose-500 px-2 py-1  text-neutral-800 hover:bg-rose-50 active:bg-rose-100"
                            @click="pickFiles">
                            <span class="text-sm font-medium">Browse files</span>
                        </button>
                    </div>

                    <!-- error (ถ้ามี) -->
                    <!-- <p v-if="uploadError" class="mt-2 text-xs text-red-600 text-center">{{ uploadError }}</p> -->

                    <!-- input file (ซ่อน) -->
                    <input ref="fileInput" type="file" multiple class="hidden"
                        accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-3xl font-semibold text-neutral-800 mb-4">Add Guest</h3>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex flex-1 items-center gap-2 min-w-[200px]">
                    <SearchBar v-model="search" placeholder="Search ID / Name / Nickname" @search="() => (page = 1)" />
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

        <div class="mt-6">
            <DataTable :rows="pagedEmployees" :columns="columns" :loading="loadingEmployees"
                :totalItems="filteredEmployees.length" v-model:page="page" v-model:pageSize="perPage"
                :pageSizeOptions="[10, 25, 50]" :selectable="true" :showRowNumber="true" rowKey="id"
                :modelValue="selectedIdsArr" @update:modelValue="onUpdateSelected" :rowClass="rowClass"
                :isRowDisabled="(row) => lockedIds.has(row.id)">
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

    <!-- แถบปุ่ม -->
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
                <span>Confirm</span>
            </button>
        </div>
    </div>
    <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
        :showCancel="alert.showCancel" :okText="alert.okText" :cancelText="alert.cancelText" @confirm="alert.onConfirm"
        @cancel="alert.onCancel" />

    <ModalAlert v-model:open="fileTypeError" title="ERROR!" message="Unsupported file type. Please try again."
        type="error" :showCancel="false" />

    <ModalAlert v-model:open="progressAlert.open" type="progress" title="Saving..."
        message="Please wait while we save your changes." :progress="progressAlert.progress"
        :total="selectedIdsForSubmit.length" :showCancel="false" />

</template>

<!-- /**
 * ชื่อไฟล์: EditEvent.vue
 * คำอธิบาย: หน้าแก้ไขข้อมูลกิจกรรม (Edit Event) สำหรับแก้ไขข้อมูล Event ที่มีอยู่แล้ว
 *           รองรับการอัปโหลดไฟล์แนบ การเลือก Guest และการส่งอีเมลแจ้งเตือน
 * Input: id (รหัสกิจกรรม) จาก route params, ข้อมูลกิจกรรมจาก API GET /edit-event/{id}
 * Output: แบบฟอร์มแก้ไขกิจกรรม ส่งข้อมูลผ่าน POST /edit-event
 * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
 * วันที่จัดทำ/แก้ไข: 2026-02-15
 */ -->

<script>
import axios from 'axios';
import InputPill from '@/components/Input/InputPill.vue';
import SearchBar from "@/components/SearchBar.vue";
import { Icon } from '@iconify/vue'
import DropdownPill from '@/components/Input/DropdownPill.vue'
import DataTable from '@/components/DataTable.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
import ModalAlert from '@/components/Alert/ModalAlert.vue'
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import EventSingleDatePicker from "@/components/IndexEvent/EventSingleDatePicker.vue";

export default {
    components: { InputPill, Icon, SearchBar, DropdownPill, DataTable, CancelButton, ModalAlert, EmployeeDropdown, EventSingleDatePicker },
    data() {
        return {
            // --- ข้อมูลฟอร์ม ---
            eventTitle: '',           // ชื่อกิจกรรม
            eventCategoryName: '',    // ชื่อหมวดหมู่กิจกรรม (แสดงผล)
            eventCategoryId: '',      // รหัสหมวดหมู่กิจกรรม (ส่งไป Backend)
            selectCategory: [],       // รายการหมวดหมู่สำหรับ dropdown
            eventDescription: '',     // คำอธิบายกิจกรรม
            eventDate: '',            // วันที่จัดกิจกรรม (format: YYYY-MM-DD)
            eventTimeStart: '',       // เวลาเริ่มกิจกรรม (format: HH:MM)
            eventTimeEnd: '',         // เวลาสิ้นสุดกิจกรรม (format: HH:MM)
            showStartPanel: false,    // แสดง/ซ่อน time picker ของเวลาเริ่ม
            showEndPanel: false,      // แสดง/ซ่อน time picker ของเวลาสิ้นสุด
            pickerStartHour: '',      // ชั่วโมงที่เลือกใน picker (เริ่ม)
            pickerStartMin: '',       // นาทีที่เลือกใน picker (เริ่ม)
            pickerEndHour: '',        // ชั่วโมงที่เลือกใน picker (สิ้นสุด)
            pickerEndMin: '',         // นาทีที่เลือกใน picker (สิ้นสุด)
            eventDuration: 0,         // ระยะเวลากิจกรรม (แสดงผล เช่น "2 Hour 30 Min")
            eventLocation: '',        // สถานที่จัดกิจกรรม
            saving: false,            // สถานะกำลังบันทึกข้อมูล (ป้องกัน submit ซ้ำ)
            eventDurationMinutes: 0,  // ระยะเวลากิจกรรมในหน่วยนาที (ส่งไป Backend)

            // --- Validation ---
            formErrors: {},           // เก็บ field ที่ validation ไม่ผ่าน
            submitted: false,         // true = กดปุ่ม submit แล้ว (เริ่มแสดง error)
            timeErrorMessage: '',     // ข้อความ error เฉพาะของช่วงเวลา (end ต้องหลัง start)

            // --- ไฟล์แนบ ---
            filesExisting: [],        // ไฟล์แนบเดิมที่มีอยู่ใน DB
            filesNew: [],             // ไฟล์แนบใหม่ที่ผู้ใช้เพิ่ม
            filesDeleted: [],         // รหัสไฟล์เดิมที่ผู้ใช้ลบออก
            dragging: false,          // สถานะกำลัง drag ไฟล์เข้า drop zone
            fileTypeError: false,     // แจ้งเตือนเมื่อไฟล์ที่อัปโหลดไม่ถูกประเภท

            // --- Table & Filter Data ---
            employees: [],
            loadingEmployees: false,
            search: '',
            searchDraft: '',

            // เพิ่มตัวแปรให้ครบตามที่ HTMLเรียกใช้ (v-model)
            selectedCompanyIds: [],
            selectedDepartmentIds: [],
            selectedTeamIds: [],
            selectedPositionIds: [],

            // ตัวเลือกสำหรับ Dropdown Filter
            companyIdOptions: [],
            departmentOptions: [],
            teamOptions: [],
            positionOptions: [],

            // --- การเลือก Guest ---
            selectedIds: new Set(), // รหัสพนักงานที่ถูกเลือกเป็น Guest (ใหม่)
            lockedIds: new Set(),   // รหัสพนักงานที่เป็น Guest เดิม (แก้ไขไม่ได้)

            // --- Pagination ---
            page: 1,      // หน้าปัจจุบัน
            perPage: 10,  // จำนวนแถวต่อหน้า

            // --- Alert Config ---
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

            // เก็บค่าเดิมตอนโหลดหน้า สำหรับเช็คว่ามีการเปลี่ยนแปลงไหม
            initialForm: {},

            // --- Progress Alert ---
            progressAlert: {
                open: false,
                progress: 0,
                total: 0,
            },
        };

    },
    methods: {
        /**
         * ชื่อฟังก์ชัน: fetchData
         * คำอธิบาย: ดึงข้อมูลกิจกรรมจาก API มาแสดงในฟอร์ม รวมถึงข้อมูลพนักงานและ Guest เดิม
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        async fetchData() {
            try {
                // ดึงข้อมูลกิจกรรมและหมวดหมู่พร้อมกัน
                const [eventResponse, categoryResponse] = await Promise.all([
                    axios.get(`/edit-event/${this.$route.params.id}`),
                    axios.get('/categories')
                ])

                const payload = eventResponse.data
                const data = payload?.event ?? {}
                const categories = categoryResponse.data?.data ?? []

                // Map ข้อมูลกิจกรรมลงในตัวแปร
                this.eventTitle = data?.evn_title ?? ''
                this.eventCategoryId = data?.evn_category_id ?? ''
                this.eventCategoryName = data?.cat_name ?? ''
                this.eventDescription = data?.evn_description ?? ''
                this.eventDate = data.evn_date?.split("T")[0] ?? '' // แปลง ISO datetime → YYYY-MM-DD
                this.eventTimeStart = data?.evn_timestart ?? ''
                this.eventTimeEnd = data?.evn_timeend ?? ''
                this.eventLocation = data?.evn_location ?? ''
                this.selectCategory = categories
                this.filesExisting = payload?.files ?? []

                // Sync picker state from loaded times
                if (this.eventTimeStart) {
                    const [h, m] = this.eventTimeStart.split(':')
                    this.pickerStartHour = String(h).padStart(2, '0')
                    this.pickerStartMin = String(m).padStart(2, '0')
                }
                if (this.eventTimeEnd) {
                    const [h, m] = this.eventTimeEnd.split(':')
                    this.pickerEndHour = String(h).padStart(2, '0')
                    this.pickerEndMin = String(m).padStart(2, '0')
                }

                // Map Guest ID เดิมเข้า Set เพื่อติ๊ก checkbox และล็อกไม่ให้แก้ไข
                const guestsMapped = (payload?.guestIds ?? []).map(id => parseInt(id))
                this.selectedIds = new Set(guestsMapped)
                this.lockedIds = new Set(guestsMapped)

                // ไฟล์เดิม
                this.filesExisting = payload?.files ?? [] // เก็บข้อมูล files ที่ส่งมาจาก controller

                // โหลดข้อมูลพนักงานสำหรับตาราง
                this.loadingEmployees = true
                const employeeInfo = await axios.get('/event-info')
                const employeeData = employeeInfo.data || {}

                // Map ข้อมูลพนักงานพร้อมคำนวณ Company Abbreviation จาก emp_id
                this.employees = (employeeData.employees || []).map(employee => {
                    const rawId = String(employee.emp_id || employee.code || "")
                    const rawPrefixFromId = (rawId.match(/^[A-Za-z]+/) || [""])[0]
                    const companyAbbr = (rawPrefixFromId || "").toUpperCase()

                    return {
                        id: employee.id,
                        emp_id: rawId,
                        emp_firstname: employee.emp_firstname || employee.first_name || '',
                        emp_lastname: employee.emp_lastname || employee.last_name || '',
                        fullname: `${employee.emp_firstname || ''} ${employee.emp_lastname || ''}`,
                        nickname: employee.emp_nickname || '',
                        department: employee.department_name || '',
                        team: employee.team_name || '',
                        position: employee.position_name || '',
                        companyAbbr: companyAbbr,
                        companyId: employee.company_id || companyAbbr || "",
                    }
                })

                this.buildFilterOptions()
                this.loadingEmployees = false

            } catch (err) {
                console.error('fetchData error:', err)
                this.eventTitle = '(โหลดข้อมูลไม่สำเร็จ)'
            }

            // เก็บค่าเดิมไว้เทียบตอนกด Cancel
            this.initialForm = {
                eventTitle: this.eventTitle,
                eventCategoryId: this.eventCategoryId,
                eventDescription: this.eventDescription,
                eventDate: this.eventDate,
                eventTimeStart: this.eventTimeStart,
                eventTimeEnd: this.eventTimeEnd,
                eventLocation: this.eventLocation,
                selectedIds: new Set([...this.selectedIds]),
                filesExisting: JSON.parse(JSON.stringify(this.filesExisting)),
                filesNew: [...this.filesNew],
            }
        },

        /**
         * ชื่อฟังก์ชัน: isFormChanged
         * คำอธิบาย: เช็คว่าข้อมูลในฟอร์มมีการเปลี่ยนแปลงจากค่าเดิมหรือไม่
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        isFormChanged() {
            if (this.eventTitle !== this.initialForm.eventTitle) return true
            if (this.eventCategoryId !== this.initialForm.eventCategoryId) return true
            if (this.eventDescription !== this.initialForm.eventDescription) return true
            if (this.eventDate !== this.initialForm.eventDate) return true
            if (this.eventTimeStart !== this.initialForm.eventTimeStart) return true
            if (this.eventTimeEnd !== this.initialForm.eventTimeEnd) return true
            if (this.eventLocation !== this.initialForm.eventLocation) return true

            // เช็ค Guest list ว่าเปลี่ยนไหม
            if (this.selectedIds.size !== this.initialForm.selectedIds.size) return true
            for (let id of this.selectedIds) {
                if (!this.initialForm.selectedIds.has(id)) return true
            }

            // เช็ค Files ว่าเปลี่ยนไหม
            if (this.filesExisting.length !== this.initialForm.filesExisting.length) return true
            if (this.filesNew.length !== this.initialForm.filesNew.length) return true

            return false
        },
        /**
        * ชื่อฟังก์ชัน: isOnlyCategoryChanged
        * คำอธิบาย: ตรวจสอบว่ามีการแก้ไข "เฉพาะ" Category เพียงอย่างเดียวหรือไม่
        */
        isOnlyCategoryChanged() {
            // เช็คว่า Category เปลี่ยนแปลงหรือไม่
            const isCategoryChanged = this.eventCategoryId !== this.initialForm.eventCategoryId;

            // เช็คว่าฟิลด์อื่นๆ มีการเปลี่ยนแปลงหรือไม่
            let isOtherChanged = false;

            if (this.eventTitle !== this.initialForm.eventTitle) isOtherChanged = true;
            if (this.eventDescription !== this.initialForm.eventDescription) isOtherChanged = true;
            if (this.eventDate !== this.initialForm.eventDate) isOtherChanged = true;
            if (this.eventTimeStart !== this.initialForm.eventTimeStart) isOtherChanged = true;
            if (this.eventTimeEnd !== this.initialForm.eventTimeEnd) isOtherChanged = true;
            if (this.eventLocation !== this.initialForm.eventLocation) isOtherChanged = true;

            // เช็ค Guest list ว่าเปลี่ยนไหม
            if (this.selectedIds.size !== this.initialForm.selectedIds.size) isOtherChanged = true;
            for (let id of this.selectedIds) {
                if (!this.initialForm.selectedIds.has(id)) isOtherChanged = true;
            }

            // เช็ค Files ว่าเปลี่ยนไหม
            if (this.filesExisting.length !== this.initialForm.filesExisting.length) isOtherChanged = true;
            if (this.filesNew.length !== this.initialForm.filesNew.length) isOtherChanged = true;

            // จะเป็น true ก็ต่อเมื่อ Category เปลี่ยนแปลง และฟิลด์อื่นๆ "ไม่" เปลี่ยนแปลง
            return isCategoryChanged && !isOtherChanged;
        },

        /**
         * ชื่อฟังก์ชัน: toOptions
         * คำอธิบาย: แปลง array ของค่าดิบเป็น array ของ option object สำหรับ Dropdown
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        toOptions(arr) {
            const uniqueValues = [...new Set(arr.filter(Boolean))].sort()
            return uniqueValues.map((v) => ({ label: v, value: v }))
        },

        /**
         * ชื่อฟังก์ชัน: buildFilterOptions
         * คำอธิบาย: สร้างตัวเลือก Dropdown Filter (Company, Department, Team, Position) จากข้อมูลพนักงาน
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        buildFilterOptions() {
            this.companyIdOptions = this.toOptions(this.employees.map((r) => r.companyId))
            this.departmentOptions = this.toOptions(this.employees.map((r) => r.department))
            this.teamOptions = this.toOptions(this.employees.map((r) => r.team))
            this.positionOptions = this.toOptions(this.employees.map((r) => r.position))
        },

        /**
         * ชื่อฟังก์ชัน: rowClass
         * คำอธิบาย: กำหนด CSS class ให้แถวที่ล็อก (Guest เดิม) เพื่อแสดงว่าแก้ไขไม่ได้
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        rowClass(row) {
            if (this.lockedIds.has(row.id)) {
                return 'pointer-events-none !bg-neutral-300 select-none'
            }
            return ''
        },

        /**
         * ชื่อฟังก์ชัน: onUpdateSelected
         * คำอธิบาย: รับค่าจาก DataTable เมื่อมีการเช็ค/ยกเลิกเช็ค checkbox โดยกรอง lockedIds ออก
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        onUpdateSelected(nextArr) {
            // กรอง lockedIds ออก เพื่อไม่ให้ Guest เดิมถูกลบออกจาก selectedIds
            const filteredIds = nextArr.filter(id => !this.lockedIds.has(id))
            this.selectedIds = new Set(filteredIds)
        },

        /**
         * ชื่อฟังก์ชัน: pickFiles
         * คำอธิบาย: จำลองการคลิกที่ input file ที่ซ่อนอยู่ เพื่อเปิด File Picker ของระบบปฏิบัติการ
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        pickFiles() {
            this.$refs.fileInput?.click?.()
        },

        /**
         * ชื่อฟังก์ชัน: onPick
         * คำอธิบาย: รับไฟล์จาก input file เมื่อผู้ใช้เลือกไฟล์ผ่าน File Picker
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        onPick(file) {
            this.addFiles([...file.target.files])
            file.target.value = '' // reset input เพื่อให้เลือกไฟล์เดิมซ้ำได้
        },

        /**
         * ชื่อฟังก์ชัน: onDrop
         * คำอธิบาย: รับไฟล์จากการ drag & drop เข้า drop zone
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        onDrop(event) {
            this.dragging = false
            this.addFiles([...event.dataTransfer.files])
        },
        onPick(file) { this.addFiles([...file.target.files]); file.target.value = '' },
        onDrop(event) { this.dragging = false; this.addFiles([...event.dataTransfer.files]) },

        /**
         * ชื่อฟังก์ชัน: addFiles
         * คำอธิบาย: ตรวจสอบและเพิ่มไฟล์เข้า filesNew โดยเช็คประเภทและขนาดไฟล์
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-01
         */
        addFiles(list) {
            const MAX_MB = 50
            const ALLOWED_TYPES = [
                "application/pdf",
                "text/plain",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "image/jpeg",
                "image/png",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/vnd.ms-excel",
            ]
            let hasError = false

            list.forEach(file => {
                if (file.size > MAX_MB * 1024 * 1024) {
                    hasError = true
                } else if (!ALLOWED_TYPES.includes(file.type)) {
                    hasError = true
                } else {
                    this.filesNew.push(file)
                }
            })

            this.fileTypeError = hasError  // → เด้ง ModalAlert อัตโนมัติ
        },

        /**
         * ชื่อฟังก์ชัน: removeFile
         * คำอธิบาย: ลบไฟล์ใหม่ (ที่ยังไม่ได้บันทึก) ออกจาก filesNew
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        removeFile(index) {
            this.filesNew.splice(index, 1)
        },

        /**
         * ชื่อฟังก์ชัน: removeExisting
         * คำอธิบาย: ลบไฟล์เดิม (ที่อยู่ใน DB) ออกจากรายการแสดงผล และบันทึกรหัสไว้ส่งไป Backend
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        removeExisting(id) {
            this.filesExisting = this.filesExisting.filter(file => file.id !== id)
            this.filesDeleted.push(id) // เก็บ id ไว้ส่งไปให้ Backend ลบออกจาก DB
        },

        /**
         * ชื่อฟังก์ชัน: prettySize
         * คำอธิบาย: แปลงขนาดไฟล์จาก bytes เป็น KB หรือ MB
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        prettySize(byte) {
            const mb = byte / (1024 * 1024)
            return mb >= 1 ? `${mb.toFixed(2)} MB` : `${(byte / 1024).toFixed(0)} KB`
        },

        /**
         * ชื่อฟังก์ชัน: resetSearch
         * คำอธิบาย: รีเซ็ตค่าการค้นหาและ Filter ทั้งหมดกลับเป็นค่าเริ่มต้น
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        resetSearch() {
            this.search = ''
            this.searchDraft = ''
            this.selectedCompanyIds = []
            this.selectedDepartmentIds = []
            this.selectedTeamIds = []
            this.selectedPositionIds = []
            this.page = 1
        },
        /**
         * ชื่อฟังก์ชัน: validateForm
         * คำอธิบาย: ตรวจสอบความถูกต้องของข้อมูลในฟอร์มก่อนบันทึก
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-01
         */
        validateForm() {
            const errors = {}
            if (!this.eventTitle?.trim()) errors.eventTitle = true
            if (!this.eventCategoryId) errors.eventCategoryId = true
            if (!this.eventDescription?.trim()) errors.eventDescription = true
            if (!this.eventDate) errors.eventDate = true

            if (!this.eventTimeStart) errors.eventTimeStart = true
            if (!this.eventTimeEnd) errors.eventTimeEnd = true

            // ตรวจเพิ่มเติมเมื่อกรอกครบทั้งคู่
            if (this.eventTimeStart && this.eventTimeEnd) {
                const [sh, sm] = this.eventTimeStart.split(':').map(Number)
                const [eh, em] = this.eventTimeEnd.split(':').map(Number)
                if ((eh * 60 + em) <= (sh * 60 + sm)) {
                    errors.eventTimeEnd = true
                    this.timeErrorMessage = 'End time must be after start time'
                } else {
                    this.timeErrorMessage = ''
                }
            }

            if (!this.eventLocation?.trim()) errors.eventLocation = true

            this.formErrors = errors
            return Object.keys(errors).length === 0
        },

        /**
         * ชื่อฟังก์ชัน: saveEvent
         * คำอธิบาย: ตรวจสอบฟอร์มแล้วแสดง Alert ยืนยันการแก้ไข และถามว่าต้องการส่งอีเมลหรือไม่
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        async saveEvent() {
            this.submitted = true

            if (!this.validateForm()) return // หยุดถ้า validate ไม่ผ่าน
            if (!this.isFormChanged()) {
                this.openAlert({
                    type: 'error',
                    title: 'NO CHANGES MADE',
                    message: 'No changes detected in the form.',
                    showCancel: true,
                    cancelText: 'No, Go Back',

                })
                return
            }
            if (this.isOnlyCategoryChanged()) {
                // ข้ามแจ้งเตือน Are you sure? แล้วมาถามเรื่องอีเมลเลย
                this.openAlert({
                    type: 'confirm',
                    title: 'ARE YOU SURE TO EDIT?',
                    message: 'Are you sure you want to change this?',
                    showCancel: true,
                    okText: 'OK',
                    cancelText: 'Cancel',
                    onConfirm: () => this.submitForm(true),
                })
                return; // สั่ง return เพื่อไม่ให้โค้ดรันลงไปเจอ Alert ปกติด้านล่าง
            }
            // Alert ขั้นที่ 1: ยืนยันการแก้ไข
            this.openAlert({
                type: 'confirm',
                title: 'ARE YOU SURE TO EDIT?',
                message: 'Are you sure you want to change this?',
                showCancel: true,
                okText: 'OK',
                cancelText: 'Cancel',
                onConfirm: () => {
                    // Alert ขั้นที่ 2: ถามว่าต้องการส่งอีเมลแจ้งเตือนหรือไม่
                    this.openAlert({
                        type: 'confirm',
                        title: 'SEND EMAIL NOTIFICATION?',
                        message: 'Do you want to send an email notification to guests?',
                        showCancel: true,
                        okText: 'Yes, Send Email',
                        cancelText: 'No, Skip Email',
                        onConfirm: () => this.submitForm(true),  // ส่งอีเมล
                        onCancel: () => this.submitForm(false),  // ไม่ส่งอีเมล
                    })
                },
            })
        },

        // คำนวณระยะเวลา (Duration)
        // - อ่านค่า `eventTimeStart` และ `eventTimeEnd` (รูปแบบ HH:mm)
        // - แปลงเป็นนาทีรวม (hour * 60 + minute) แล้วหาผลต่าง (end - start)
        // - ไม่อนุญาตให้เวลาจบก่อนหรือเท่ากับเวลาเริ่ม (no next-day wrap). ถ้าเกิดขึ้น จะตั้ง error และข้อความ
        // - เก็บผลลัพธ์เป็น `eventDurationMinutes` (ตัวเลขนาที) และ `eventDuration` (ข้อความอ่านง่าย)
        calDuration() {
            // If either time missing, clear duration and message
            if (!this.eventTimeStart || !this.eventTimeEnd) {
                this.eventDurationMinutes = 0;
                this.eventDuration = '';
                this.timeErrorMessage = '';
                return;
            }

            const [startHour, startMinute] = this.eventTimeStart.split(':').map(Number);
            const [endHour, endMinute] = this.eventTimeEnd.split(':').map(Number);

            const startMin = startHour * 60 + startMinute;
            const endMin = endHour * 60 + endMinute;

            // Enforce end > start (no automatic next-day wrap)
            if (endMin <= startMin) {
                this.eventDurationMinutes = 0;
                this.eventDuration = '';
                this.formErrors.eventTime = true;
                this.timeErrorMessage = 'End time must be after start time';
                return;


            }

            const diff = endMin - startMin;
            this.eventDurationMinutes = diff;
            const hour = Math.floor(diff / 60);
            const min = diff % 60;
            if (hour === 0) this.eventDuration = `${min} Min`;
            else if (min === 0) this.eventDuration = `${hour} Hour`;
            else this.eventDuration = `${hour} Hour ${min} Min`;

            this.formErrors.eventTime = false;
            this.timeErrorMessage = '';
        },
        /**
         * ชื่อฟังก์ชัน: submitForm
         * คำอธิบาย: ส่งข้อมูลกิจกรรมที่แก้ไขไปยัง Backend พร้อม flag การส่งอีเมล
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-01
         */
        async submitForm(sendMail = true) {
            try {
                this.saving = true

                //  เปิด progress modal
                this.progressAlert = { open: true, progress: 0, total: 100 }

                // จำลอง progress 0 → 80 ระหว่างรอ API
                let fakeProgress = 0
                const progressInterval = setInterval(() => {
                    if (fakeProgress < 80) {
                        fakeProgress += Math.floor(Math.random() * 10) + 5
                        if (fakeProgress > 80) fakeProgress = 80
                        this.progressAlert.progress = fakeProgress
                    }
                }, 300)

                const id = this.$route.params.id
                const formData = new FormData()

                formData.append('id', id)
                formData.append('evn_title', this.eventTitle?.trim() || '')
                if (this.eventCategoryId) formData.append('evn_category_id', String(this.eventCategoryId))
                formData.append('evn_description', this.eventDescription ?? '')
                formData.append('evn_date', this.eventDate)
                formData.append('evn_timestart', this.eventTimeStart)
                formData.append('evn_timeend', this.eventTimeEnd)
                formData.append('evn_location', this.eventLocation)
                formData.append('evn_duration', String(this.eventDurationMinutes || 0))
                formData.append('send_mail', sendMail ? '1' : '0')

                if (this.filesNew.length > 0) {
                    this.filesNew.forEach((file) => formData.append('attachments[]', file))
                }
                if (this.filesDeleted.length > 0) {
                    this.filesDeleted.forEach((fileId) => formData.append('delete_file_ids[]', fileId))
                }

                this.selectedIdsForSubmit.forEach(empId => formData.append('employee_ids[]', empId))

                const res = await axios.post('/edit-event', formData, {
                    headers: { 'Accept': 'application/json' },
                })

                //  API เสร็จแล้ว  progress 100 แล้วปิด
                clearInterval(progressInterval)
                this.progressAlert.progress = 100

                await new Promise(r => setTimeout(r, 600)) // รอให้ bar เต็มก่อนปิด
                this.progressAlert.open = false

                if (res.data.mail_warning) {
                    this.openAlert({
                        type: 'warning',
                        title: 'บันทึกสำเร็จ (แต่ส่งเมลไม่ได้)',
                        message: 'ข้อมูลถูกบันทึกแล้ว แต่ระบบส่งอีเมลขัดข้อง: ' + res.data.mail_warning,
                        okText: 'OK',
                        onConfirm: () => this.$router.back(),
                    })
                } else {
                    this.openAlert({
                        type: 'success',
                        title: 'EDIT SUCCESS!',
                        message: sendMail
                            ? 'This event has been successfully edited and email notification sent.'
                            : 'This event has been successfully edited (no email sent).',
                        okText: 'OK',
                        onConfirm: () => this.$router.back(),
                    })
                }

            } catch (err) {
                //  ปิด progress ก่อนแสดง error
                this.progressAlert.open = false
                this.openAlert({
                    type: 'error',
                    title: 'EDIT FAILED!',
                    message: err.response?.data?.message || 'An error occurred.',
                })
            } finally {
                this.saving = false
            }
        },

        /**
         * ชื่อฟังก์ชัน: onCancel
         * คำอธิบาย: จัดการการกดปุ่ม Cancel โดยเช็คว่ามีการเปลี่ยนแปลงข้อมูลไหม
         *           ถ้าไม่มีการเปลี่ยนแปลงจะกลับหน้าก่อนหน้า
         *           ถ้ามีการเปลี่ยนแปลงจะแสดง Alert ยืนยันก่อน
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        onCancel() {
            if (!this.isFormChanged()) {
                this.$router.back()
                return
            }

            // มีการเปลี่ยนแปลง แสดง Alert ยืนยันก่อนออก
            this.alert = {
                open: true,
                type: 'confirm',
                title: 'DO YOU WANT TO LEAVE THIS CHANGE?',
                message: 'Your changes will be lost.',
                showCancel: true,
                okText: 'Ok',
                cancelText: 'Cancel',
                onConfirm: () => {
                    this.alert.open = false
                    this.$router.back()
                },
                onCancel: () => {
                    this.alert.open = false
                }
            }
        },

        /**
         * ชื่อฟังก์ชัน: openAlert
         * คำอธิบาย: เปิด Modal Alert โดยปิด Alert เดิมก่อน แล้วค่อยเปิดใหม่ใน nextTick
         *           เพื่อให้ Vue re-render Modal ถูก
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        openAlert(cfg = {}) {
            // ปิด Alert เดิมก่อน แล้วค่อยเปิดใหม่ใน nextTick เพื่อให้ Vue re-render ไม่งั้นตอนทำ alert 2 อัน จะมีปัญหา
            this.alert.open = false
            this.alert.onConfirm = null
            this.alert.onCancel = null

            this.$nextTick(() => {
                Object.assign(this.alert, {
                    open: true,
                    type: 'success',
                    title: '',
                    message: '',
                    showCancel: false,
                    okText: 'OK',
                    cancelText: 'Cancel',
                    onConfirm: null,
                    onCancel: null,
                }, cfg)
            })
        },
        togglePanel(which) {
            // Toggle the hour/min picker panels for start/end time.
            // - When opening start panel, ensure end panel is closed and vice versa.
            // - After opening, call `scrollPanelToActive()` to scroll to the currently selected value.
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
            // When both hour and minute are selected in the start picker,
            // update `eventTimeStart` (string HH:mm), clear the time error and recalc duration.
            if (this.pickerStartHour !== '' && this.pickerStartMin !== '') {
                this.eventTimeStart = `${this.pickerStartHour}:${this.pickerStartMin}`;
                this.formErrors.eventTime = false;
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
            // When both hour and minute are selected in the end picker,
            // update `eventTimeEnd` (string HH:mm), clear the time error and recalc duration.
            if (this.pickerEndHour !== '' && this.pickerEndMin !== '') {
                this.eventTimeEnd = `${this.pickerEndHour}:${this.pickerEndMin}`;
                this.formErrors.eventTime = false;
                this.calDuration();
            }
        },
        closePickers() {
            this.showStartPanel = false;
            this.showEndPanel = false;
        },
        onRootPointer(e) {
            if (e.target.closest('.tp-panel') || e.target.closest('.tp-trigger')) return;
            this.closePickers();
        },
    },

    computed: {
        hourOptions() {
            return Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0'));
        },
        minuteOptions() {
            return Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'));
        },

        // --- Filtering Logic (Adapted from EventCheckIn) ---
        /**
         * ชื่อฟังก์ชัน: filteredEmployees
         * คำอธิบาย: กรองรายชื่อพนักงานตามคำค้นหาและ Filter ที่เลือก
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        filteredEmployees() {
            const searchQuery = (this.search || "").toLowerCase().trim()
            let result = this.employees

            // กรองตามคำค้นหา (emp_id, ชื่อ, นามสกุล, nickname)
            if (searchQuery) {
                result = result.filter((employee) =>
                    [String(employee.emp_id), employee.emp_firstname, employee.emp_lastname, employee.nickname]
                        .some((field) => field?.toLowerCase().includes(searchQuery))
                )
            }

            // กรองตาม Company
            if (this.selectedCompanyIds?.length) {
                const companyNeedles = this.selectedCompanyIds.map((x) => String(x).trim()).filter(Boolean)
                result = result.filter((r) => {
                    const companyIdStr = String(r.companyId || r.companyAbbr || "").trim()
                    return companyNeedles.some((needle) => companyIdStr.includes(needle))
                })
            }

            // กรองตาม Department
            if (this.selectedDepartmentIds?.length) {
                const departmentSet = new Set(this.selectedDepartmentIds)
                result = result.filter((r) => departmentSet.has(r.department))
            }

            // กรองตาม Team
            if (this.selectedTeamIds?.length) {
                const teamSet = new Set(this.selectedTeamIds)
                result = result.filter((r) => teamSet.has(r.team))
            }

            // กรองตาม Position
            if (this.selectedPositionIds?.length) {
                const positionSet = new Set(this.selectedPositionIds)
                result = result.filter((r) => positionSet.has(r.position))
            }

            return result
        },

        /**
         * ชื่อฟังก์ชัน: isValidTimeLogic
         * คำอธิบาย: ตรวจสอบว่าเวลาสิ้นสุดมากกว่าเวลาเริ่มหรือไม่
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        isValidTimeLogic() {
            if (!this.eventTimeStart || !this.eventTimeEnd) return true // ยังไม่กรอกเวลา ถือว่าผ่านก่อน

            const [startHour, startMinute] = (this.eventTimeStart || '0:0').split(':').map(Number)
            const [endHour, endMinute] = (this.eventTimeEnd || '0:0').split(':').map(Number)
            const totalStartMinutes = startHour * 60 + startMinute
            const totalEndMinutes = endHour * 60 + endMinute

            return totalEndMinutes > totalStartMinutes // เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม
        },

        // โครงคอลัมน์ของ DataTable
        columns() {
            return [
                { key: 'emp_id', label: 'Employee ID', sortable: false, class: 'min-w-[120px] text-left' },
                { key: 'fullname', label: 'Name', sortable: false, class: 'min-w-[120px] text-left' },
                { key: 'nickname', label: 'Nickname', sortable: false, class: 'min-w-[120px] text-left' },
                { key: 'department', label: 'Department', sortable: false, class: 'min-w-[120px] text-left' },
                { key: 'team', label: 'Team', sortable: false, class: 'min-w-[120px] text-left' },
                { key: 'position', label: 'Position', sortable: false, class: 'min-w-[120px] text-left' },
            ]
        },

        // เช็คว่ามีไฟล์แนบอยู่หรือไม่ (ทั้งไฟล์เดิมและไฟล์ใหม่)
        hasAnyFiles() {
            return (this.filesExisting?.length || 0) + (this.filesNew?.length || 0) > 0
        },

        // รวมไฟล์เดิมและไฟล์ใหม่เป็น array เดียวสำหรับแสดงผล
        uploadItems() {
            const existingItems = (this.filesExisting || []).map(f => ({
                key: `old-${f.id}`,
                kind: 'existing',
                id: f.id,
                name: f.file_name,
                url: f.url,
                size: f.file_size ?? 0,
            }))
            const newItems = (this.filesNew || []).map((f, i) => ({
                key: `new-${i}`,
                kind: 'new',
                index: i,
                name: f.name,
                size: f.size ?? 0,
            }))
            return [...existingItems, ...newItems] // ไฟล์เดิมขึ้นก่อน ตามด้วยไฟล์ใหม่
        },

        // รวม Guest เดิมที่ล็อก + Guest ใหม่ที่เลือก สำหรับส่งไปยัง Backend
        selectedIdsForSubmit() {
            return Array.from(new Set([...this.lockedIds, ...this.selectedIds]))
        },

        // computed สำหรับ v-model ของ DataTable (ต้องคง lockedIds ไว้เสมอ)
        selectedIdsArr: {
            get() {
                return Array.from(new Set([...this.lockedIds, ...this.selectedIds]))
            },
            set(arr) {
                // เก็บเฉพาะที่ไม่ใช่ lockedIds ใน selectedIds
                const nonLockedIds = arr.filter(id => !this.lockedIds.has(id))
                this.selectedIds = new Set(nonLockedIds)
            }
        },

        // คำนวณจำนวนหน้าทั้งหมดสำหรับ Pagination
        totalPages() {
            return Math.ceil(this.filteredEmployees.length / this.perPage)
        },

        // ดึงข้อมูลพนักงานเฉพาะหน้าปัจจุบัน
        pagedEmployees() {
            const startIndex = (this.page - 1) * this.perPage
            return this.filteredEmployees.slice(startIndex, startIndex + this.perPage)
        },

        // เช็คว่าพนักงานทุกคนในหน้าปัจจุบันถูกเลือกหมดแล้วหรือยัง (ยกเว้น lockedIds)
        allCheckedOnPage() {
            if (this.pagedEmployees.length === 0) return false
            const unlockedEmployees = this.pagedEmployees.filter(employee => !this.lockedIds.has(employee.id))
            return unlockedEmployees.length > 0 && unlockedEmployees.every(employee => this.selectedIds.has(employee.id))
        },

        /**
         * ชื่อฟังก์ชัน: minDate
         * คำอธิบาย: คำนวณวันที่ปัจจุบันในรูปแบบ YYYY-MM-DD สำหรับกำหนดค่าต่ำสุดของ date input
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        minDate() {
            const today = new Date()
            const year = today.getFullYear()
            const month = String(today.getMonth() + 1).padStart(2, '0')
            const day = String(today.getDate()).padStart(2, '0')
            return `${year}-${month}-${day}`
        },
    },

    watch: {
        eventTimeStart: 'calDuration', // คำนวณ duration ใหม่เมื่อเวลาเริ่มเปลี่ยน
        eventTimeEnd: 'calDuration',   // คำนวณ duration ใหม่เมื่อเวลาสิ้นสุดเปลี่ยน
        search() { this.page = 1 },   // รีเซ็ตหน้าเมื่อคำค้นหาเปลี่ยน

        // รีเซ็ตหน้าเมื่อ Filter เปลี่ยน
        selectedCompanyIds() { this.page = 1 },
        selectedDepartmentIds() { this.page = 1 },
        selectedTeamIds() { this.page = 1 },
        selectedPositionIds() { this.page = 1 },
        perPage() { this.page = 1 },
    },

    mounted() {
        this.fetchData() // โหลดข้อมูลทันทีที่ component ถูก mount
    },
}
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
</style>
