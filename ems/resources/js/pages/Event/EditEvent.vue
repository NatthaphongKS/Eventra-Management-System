<!-- /**
 * ชื่อไฟล์: EditEvent.vue
 * คำอธิบาย: หน้าแก้ไขข้อมูลกิจกรรม รองรับการอัปโหลดไฟล์และเลือก Guest
 * Input: id (รหัสกิจกรรม) จาก route params
 * Output: แบบฟอร์มแก้ไขกิจกรรม ส่งข้อมูลผ่าน POST /edit-event
 * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
 * วันที่จัดทำ/แก้ไข: 2026-03-1
 */ -->
<template>
    <div class="text-neutral-800 font-semibold font-[Poppins] text-3xl mb-4">
        Edit Event
    </div>
    <div class="grid grid-cols-12 h-full gap-0">
        <div class="col-span-8">

            <!-- ช่องกรอกชื่ออีเวนต์ -->
            <div class="grid ">
                <div class="mt-6 md:grid md:grid-cols-[3fr_200px] md:gap-8 items-stretch">
                    <!-- v-model.trim="evn_title" = ผูกค่ากับตัวแปร evn_title ใน data() อันนึงเปลี่ยนค่าอีกอันก็จะเปลี่ยนตาม
                     trim = ตัดช่องว่างหน้า/หลังอัตโนมัติ -->
                    <div>
                        <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px] mb-4 ml-1">
                            Event Title <span class="text-red-500">*</span>
                        </label><br />
                        <InputPill v-model="eventTitle"
                            class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5"
                            :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventTitle }" />

                        <p v-if="submitted && formErrors.eventTitle" class="mt-1 text-xs text-red-600 font-medium">
                            Required field
                        </p>
                    </div>


                    <!-- ช่องเลือกประเภท event-->
                    <div>
                        <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">
                            Event Category <span class="text-red-500">*</span>
                        </label><br />
                        <div class="relative w-full">
                            <select
                                class="appearance-none border border-neutral-200 rounded-[20px] px-[20px] w-full h-[52px] focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition bg-white"
                                v-model="eventCategoryId"
                                :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventCategoryId }">

                                <option :value="eventCategoryId" hidden>
                                    {{ eventCategoryName }}
                                </option>

                                <option v-for="cat in selectCategory" :value="cat.id">
                                    {{ cat.cat_name }}
                                </option>
                            </select>

                            <Icon icon="iconamoon:arrow-down-2-light"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 text-red-600 pointer-events-none" />
                        </div>

                        <p v-if="submitted && formErrors.eventCategoryId" class="mt-1 text-xs text-red-600 font-medium">
                            Required Select
                        </p>
                    </div>
                </div>
            </div>

            <!-- ช่องกรอกคำอธิบายอีเวนต์ -->
            <div class="mt-4">
                <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">
                    Event Description <span class="text-red-500">*</span>
                </label><br />
                <textarea
                    class="border border-neutral-200 w-full h-[165px] rounded-2xl focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition px-5 py-4"
                    v-model.trim="eventDescription" placeholder="Write some description... (255 words)"
                    :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventDescription }"></textarea>

                <p v-if="submitted && formErrors.eventDescription" class="mt-1 text-xs text-red-600 font-medium">
                    Required field
                </p>
            </div>

            <div class="grid grid-cols-3 mt-4 gap-4">

                <!-- ช่องกรอกวัน -->
                <div class="">
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">
                        Date <span class="text-red-500">*</span>
                    </label><br>
                    <div class="relative w-full">
                        <input class="border border-neutral-200 w-full h-[52px] rounded-2xl
                        focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition
                        px-5 py-4
                        [&::-webkit-calendar-picker-indicator]:hidden
                        [&::-webkit-inner-spin-button]:hidden
                        [&::-webkit-clear-button]:hidden" type="date" v-model="eventDate" :min="minDate"
                            :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventDate }"
                            onclick="this.showPicker()">


                        <Icon icon="stash:data-date-solid"
                            class="ml-20 w-7 h-[30px] text-red-700 shrink-0 absolute right-5 top-1/2 -translate-y-1/2  pointer-events-none" />
                    </div>
                    <p v-if="submitted && formErrors.eventDate" class="mt-1 text-xs text-red-600 font-medium">
                        'Required date'
                    </p>
                </div>

                <!-- ช่องกรอกเวลา -->
                <div class="">
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">
                        Time <span class="text-red-500">*</span>
                    </label>
                    <div class="flex h-[52px] w-full items-center gap-1 rounded-2xl border border-neutral-200 shadow-sm px-5 py-4"
                        :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && (formErrors.eventTimeStart || formErrors.eventTimeEnd) }">
                        <!-- Time Start -->
                        <div class="flex items-center justify-center">
                            <input type="time" v-model="eventTimeStart" step="300"
                                class="time-input w-auto bg-transparent text-[16px]  font-medium text-neutral-800 outline-none text-center"
                                @click="$event.target.showPicker()" />
                            <span class="text-[16px]  font-medium text-neutral-800 ml-2"></span>

                        </div>

                        <span class="mx-1 text-[18px] font-bold text-red-600">:</span>
                        <!-- Time End -->
                        <div class="flex items-center justify-center">
                            <input type="time" v-model="eventTimeEnd" step="300"
                                class="time-input w-auto bg-transparent text-[16px]  font-medium text-neutral-800 outline-none text-center"
                                @click="$event.target.showPicker()" />
                            <span class="text-[16px]  font-medium text-neutral-800 ml-2"></span>
                        </div>
                        <div>
                            <span class="text-red-700">
                                <Icon icon="iconamoon:clock-light" class="h-6 w-6" />
                            </span>
                        </div>
                    </div>

                    <p v-if="submitted && (formErrors.eventTimeStart || formErrors.eventTimeEnd)"
                        class="mt-1 text-xs text-red-600 font-medium">
                        {{ formErrors.timeMsg || 'Require Time' }}
                    </p>

                </div>


                <!-- ช่องกรอกแสดงช่วงเวลา -->
                <div>
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">Duration</label>
                    <div class="flex h-[52px] w-full items-center gap-3 rounded-xl  px-4 shadow-sm bg-[#F5F5F5]">
                        <input class=" w-full h-[52px] bg-transparent outline-none text-neutral-500" disabled
                            v-model="eventDuration" placeholder="Auto fill Hour"></input>
                        <Icon icon="mingcute:time-duration-line" class="w-7 h-7  text-neutral-400" />
                    </div>
                </div>
            </div>

            <!-- ช่องกรอกสถานที่-->
            <div class="mt-4">
                <label class="text-neutral-800 font-semibold font-[Poppins] text-[16px]  mb-4 ml-1">
                    Location <span class="text-red-500">*</span>
                </label><br>
                <InputPill v-model="eventLocation" class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800
             border border-neutral-200 rounded-[20px] px-5"
                    :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventLocation }" />

                <p v-if="submitted && formErrors.eventLocation" class="mt-1 text-xs text-red-600 font-medium">
                    Required field
                </p>
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
                    <p class="text-[16px]  font-medium text-neutral-800">Choose a file or drag &amp; drop it here</p>
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
        <h3 class="text-3xl font-semibold">Add Guest</h3>

        <div class="mt-4 flex flex-col gap-3">
            <div class="flex flex-wrap items-center gap-3 w-full">
                <div class="flex-1 min-w-[260px]">
                    <SearchBar v-model="search" placeholder="Search ID / Name / Nickname" @search="() => (page = 1)"
                        class="" />
                </div>

                <div class="flex flex-row flex-wrap items-center gap-2 mt-8">
                    <EmployeeDropdown label="Company ID" v-model="selectedCompanyIds" :options="companyIdOptions" />
                    <EmployeeDropdown label="Department" v-model="selectedDepartmentIds" :options="departmentOptions" />
                    <EmployeeDropdown label="Team" v-model="selectedTeamIds" :options="teamOptions" />
                    <EmployeeDropdown label="Position" v-model="selectedPositionIds" :options="positionOptions" />
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

    <!-- ปุ่มยกเลิก / ยืนยัน -->

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

export default {
    components: { InputPill, Icon, SearchBar, DropdownPill, DataTable, CancelButton, ModalAlert, EmployeeDropdown },
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
            eventDuration: 0,         // ระยะเวลากิจกรรม (แสดงผล เช่น "2 Hour 30 Min")
            eventLocation: '',        // สถานที่จัดกิจกรรม
            saving: false,            // สถานะกำลังบันทึกข้อมูล (ป้องกัน submit ซ้ำ)
            eventDurationMinutes: 0,  // ระยะเวลากิจกรรมในหน่วยนาที (ส่งไป Backend)

            // --- Validation ---
            formErrors: {},   // เก็บ field ที่ validation ไม่ผ่าน
            submitted: false, // true = กดปุ่ม submit แล้ว (เริ่มแสดง error)

            // --- ไฟล์แนบ ---
            filesExisting: [], // ไฟล์แนบเดิมที่มีอยู่ใน DB
            filesNew: [],      // ไฟล์แนบใหม่ที่ผู้ใช้เพิ่ม
            filesDeleted: [],  // รหัสไฟล์เดิมที่ผู้ใช้ลบออก
            dragging: false,   // สถานะกำลัง drag ไฟล์เข้า drop zone

            // --- ตารางพนักงาน และ Filter ---
            employees: [],          // รายชื่อพนักงานทั้งหมด
            loadingEmployees: false, // สถานะกำลังโหลดข้อมูลพนักงาน
            search: '',             // คำค้นหาพนักงาน
            searchDraft: '',        // คำค้นหาชั่วคราว (ก่อน apply)

            // ค่าที่เลือกจาก Dropdown Filter
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

                // Map Guest ID เดิมเข้า Set เพื่อติ๊ก checkbox และล็อกไม่ให้แก้ไข
                const guestsMapped = (payload?.guestIds ?? []).map(id => parseInt(id))
                this.selectedIds = new Set(guestsMapped)
                this.lockedIds = new Set(guestsMapped)

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

        /**
         * ชื่อฟังก์ชัน: addFiles
         * คำอธิบาย: ตรวจสอบและเพิ่มไฟล์เข้า filesNew โดยเช็คประเภทและขนาดไฟล์
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        addFiles(list) {
            const MAX_MB = 50 // ขนาดไฟล์สูงสุดที่อนุญาต (MB)
            const ALLOWED_TYPES = [ // ประเภทไฟล์ที่อนุญาต
                "application/pdf",
                "text/plain",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "image/jpeg",
                "image/png",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/vnd.ms-excel",
            ]
            const errors = []

            list.forEach(file => {
                if (file.size > MAX_MB * 1024 * 1024) {
                    errors.push(`${file.name}: ไฟล์เกิน ${MAX_MB}MB`)
                } else if (!ALLOWED_TYPES.includes(file.type)) {
                    errors.push(`${file.name}: ประเภทไฟล์ไม่รองรับ`)
                } else {
                    this.filesNew.push(file)
                }
            })

            if (errors.length) alert(errors.join('\n'))
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
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        validateForm() {
            const errors = {}

            if (!this.eventTitle?.trim()) errors.eventTitle = true
            if (!this.eventCategoryId) errors.eventCategoryId = true
            if (!this.eventDescription?.trim()) errors.eventDescription = true
            if (!this.eventDate) errors.eventDate = true
            if (!this.eventTimeStart) errors.eventTimeStart = true
            if (!this.eventTimeEnd) errors.eventTimeEnd = true
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

        /**
         * ชื่อฟังก์ชัน: submitForm
         * คำอธิบาย: ส่งข้อมูลกิจกรรมที่แก้ไขไปยัง Backend พร้อม flag การส่งอีเมล
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        async submitForm(sendMail = true) {
            try {
                this.saving = true

                const id = this.$route.params.id
                const formData = new FormData()

                // ข้อมูลพื้นฐานของกิจกรรม
                formData.append('id', id)
                formData.append('evn_title', this.eventTitle?.trim() || '')
                if (this.eventCategoryId) formData.append('evn_category_id', String(this.eventCategoryId))
                formData.append('evn_description', this.eventDescription ?? '')
                formData.append('evn_date', this.eventDate)
                formData.append('evn_timestart', this.eventTimeStart)
                formData.append('evn_timeend', this.eventTimeEnd)
                formData.append('evn_location', this.eventLocation)
                formData.append('evn_duration', String(this.eventDurationMinutes || 0))
                formData.append('send_mail', sendMail ? '1' : '0') // flag ส่งอีเมล

                // ไฟล์แนบใหม่
                if (this.filesNew.length > 0) {
                    this.filesNew.forEach((file) => formData.append('attachments[]', file))
                }

                // รหัสไฟล์เดิมที่ต้องการลบ
                if (this.filesDeleted.length > 0) {
                    this.filesDeleted.forEach((fileId) => formData.append('delete_file_ids[]', fileId))
                }

                // รหัสพนักงานที่เป็น Guest (รวม Guest เดิมที่ล็อกไว้)
                this.selectedIdsForSubmit.forEach(empId => formData.append('employee_ids[]', empId))

                const res = await axios.post('/edit-event', formData, {
                    headers: { 'Accept': 'application/json' },
                })

                // แสดงผลตามสถานะที่ได้รับจาก Backend
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
         * ชื่อฟังก์ชัน: calDuration
         * คำอธิบาย: คำนวณระยะเวลากิจกรรมจากเวลาเริ่มและเวลาสิ้นสุด
         * ชื่อผู้เขียน/แก้ไข: RAVEROJ SONTHI
         * วันที่จัดทำ/แก้ไข: 2026-03-1
         */
        calDuration() {
            const [startHour, startMinute] = (this.eventTimeStart || '0:0').split(':').map(Number)
            const [endHour, endMinute] = (this.eventTimeEnd || '0:0').split(':').map(Number)

            const totalStartMinutes = startHour * 60 + startMinute // แปลงเวลาเริ่มเป็นนาที
            const totalEndMinutes = endHour * 60 + endMinute       // แปลงเวลาสิ้นสุดเป็นนาที
            let diff = totalEndMinutes - totalStartMinutes

            if (diff < 0) diff += 24 * 60 // รองรับกรณีข้ามเที่ยงคืน

            this.eventDurationMinutes = Math.max(0, diff)

            // แสดงผลในรูปแบบ "X Hour Y Min"
            const hours = Math.floor(diff / 60)
            const minutes = diff % 60

            if (minutes === 0) {
                this.eventDuration = `${hours} Hour`
            } else if (hours === 0) {
                this.eventDuration = `${minutes} Min`
            } else {
                this.eventDuration = `${hours} Hour ${minutes} Min`
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

            // มีการเปลี่ยนแปลง → แสดง Alert ยืนยันก่อนออก
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
    },

    computed: {
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
/* ทำให้ input type="time" ดู “เรียบ” และกลืนกับกล่องพิล */
.time-input::-webkit-calendar-picker-indicator {
    /* opacity: 0; */
    display: none;
}

/* ซ่อนตัวบอก AM/PM */
/* .time-input::-webkit-datetime-edit-ampm-field {
    display: none;
} */

/* ซ่อนปุ่มปฏิทินเดิมของ Chrome/Safari */
</style>
