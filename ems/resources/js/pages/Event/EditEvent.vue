<!-- pages/edit_event.vue -->
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
                        <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
                            Event Title <span class="text-red-500">*</span>
                        </label><br />
                        <InputPill v-model="eventTitle" class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5"
                            :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventTitle }" />

                        <p v-if="submitted && formErrors.eventTitle" class="mt-1 text-xs text-red-600 font-medium">
                            Required field
                        </p>
                    </div>
                    <div>
                        <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
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

            <div class="mt-4">
                <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
                    Event Description <span class="text-red-500">*</span>
                </label><br />
                <textarea
                    class="border border-neutral-200 w-full h-[165px] rounded-2xl focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition px-5 py-4"
                    v-model.trim="eventDescription"
                    placeholder="Write some description... (255 words)"
                    :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventDescription }"></textarea>

                <p v-if="submitted && formErrors.eventDescription" class="mt-1 text-xs text-red-600 font-medium">
                    Required field
                </p>
            </div>

            <div class="grid grid-cols-3 mt-4 gap-4">
                <div class="">
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
                        Date <span class="text-red-500">*</span>
                    </label><br>
                    <input class="border border-neutral-200 w-full h-[52px] rounded-2xl
                        focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition
                        px-5 py-4" type="date" v-model="eventDate"
                        :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventDate }">

                    <p v-if="submitted && formErrors.eventDate" class="mt-1 text-xs text-red-600 font-medium">
                        Required date
                    </p>
                </div>
                <div class="">
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
                        Time <span class="text-red-500">*</span>
                    </label>
                    <div class="flex h-[52px] w-full items-center gap-1 rounded-2xl border border-neutral-200 shadow-sm px-5 py-4"
                        :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && (formErrors.eventTimeStart || formErrors.eventTimeEnd) }">
                    <!-- Time Start -->
                    <div class="flex items-center justify-center">
                        <input
                            type="time"
                            v-model="eventTimeStart"
                            step="300"
                            class="time-input w-auto bg-transparent text-[15px] font-medium text-neutral-800 outline-none text-center"
                            @click="$event.target.showPicker()"
                        />
                        <span class="text-[15px] font-medium text-neutral-800 ml-2"></span>
                    </div>

                    <span class="mx-1 text-[18px] font-bold text-red-600">:</span>
                    <!-- Time End -->
                    <div class="flex items-center justify-center">
                        <input
                            type="time"
                            v-model="eventTimeEnd"
                            step="300"
                            class="time-input w-auto bg-transparent text-[15px] font-medium text-neutral-800 outline-none text-center"
                            @click="$event.target.showPicker()"
                        />
                        <span class="text-[15px] font-medium text-neutral-800 ml-2"></span>
                    </div>

                        <Icon icon="iconamoon:clock-light" class="ml-20 w-6 h-6 text-rose-400 shrink-0 ml-2" />

                    </div>

                    <p v-if="submitted && (formErrors.eventTimeStart || formErrors.eventTimeEnd)" class="mt-1 text-xs text-red-600 font-medium">
                        {{ formErrors.timeMsg || 'Require Time' }}
                    </p>

                </div>

                <div>
                    <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">Duration</label>
                    <div
                        class="flex h-[52px] w-full items-center gap-3 rounded-xl border border-neutral-200 px-4 shadow-sm bg-[#F5F5F5]">
                        <input class=" w-full h-[52px] bg-transparent outline-none text-neutral-500" disabled v-model="eventDuration" placeholder="Auto fill Hour"></input>
                        <Icon icon="iconamoon:clock-light" class="w-6 h-6  text-neutral-400" />
                        </div>
                </div>
            </div>
            <div class="mt-4">
                <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4">
                    Location <span class="text-red-500">*</span>
                </label><br>
                <InputPill v-model="eventLocation" class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800
             border border-neutral-200 rounded-[20px] px-5"
             :class="{ '!border-red-500 !ring-1 !ring-red-500': submitted && formErrors.eventLocation }"/>

                <p v-if="submitted && formErrors.eventLocation" class="mt-1 text-xs text-red-600 font-medium">
                    Required field
                </p>
            </div>

        </div>
        <!-- Upload attachments -->
        <div class="col-span-4 m-5">
            <h3 class="text-[17px] font-semibold text-neutral-800">Upload attachments</h3>
            <p class="text-sm text-neutral-500 mb-2">Drag and drop document to your support task</p>

            <!-- ▼ Drop zone -->
            <div class="group relative rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50 p-6 transition-all hover:border-rose-400"
                :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }" @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
                <!-- รายการไฟล์ (เดิม + ใหม่) เต็มความกว้าง เรียงลงมา -->
                <div v-if="hasAnyFiles" class="mb-4 space-y-2">
                    <div v-for="item in uploadItems" :key="item.key"
                        class="w-full flex items-center justify-between rounded-2xl bg-white border border-neutral-200 px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md ">
                                <Icon icon="basil:file-solid" class="h-10 w-10 text-rose-600" />
                            </div>

                            <!-- ไฟล์เดิมเป็นลิงก์, ไฟล์ใหม่เป็นข้อความ -->
                            <template v-if="item.kind === 'existing'">
                                <a :href="item.url" target="_blank" rel="noopener"
                                    class="truncate text-[15px] text-rose-700 hover:underline">
                                    {{ item.name }}
                                </a>
                                <span class="ml-2 shrink-0 text-xs text-neutral-500">({{ prettySize(item.size)
                                    }})</span>
                            </template>
                            <template v-else>
                                <span class="truncate text-[15px] text-neutral-800">{{ item.name }}</span>
                                <span class="ml-2 shrink-0 text-xs text-neutral-500">({{ prettySize(item.size)
                                    }})</span>
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
                    <Icon icon="entypo:upload-to-cloud" class="w-16 h-16 mb-3 text-rose-400" />
                    <p class="text-[15px] font-medium text-neutral-800">Choose a file or drag &amp; drop it here</p>
                    <p class="mt-1 text-sm text-neutral-600">pdf, txt, docx, jpeg, xlsx</p>
                </div>

                <!-- ปุ่ม Browse: อยู่ล่างกลางเสมอ -->
                <div class="flex justify-center mt-6">
                    <button type="button"
                        class="inline-flex items-center rounded-[12px] border bg-white border-rose-500 px-4 py-1.5 text-sm text-rose-700 hover:bg-rose-50 active:bg-rose-100"
                        @click="pickFiles">
                        Browse files
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
    <h3 class="text-neutral-800 font-semibold font-[Poppins] text-3xl m-4">Add Guest</h3>

    <div class="guest-toolbar grid gap-3 mt-3 mb-3 md:grid-cols-[1fr,165px,165px,165px,165px] items-center">
        <!-- Search -->
        <div>
            <SearchBar v-model="searchDraft" class="w-full" placeholder="Search Employee ID / Name / Nickname"
                @search="applySearch" />
        </div>

        <!-- Company ID -->
        <div class="mt-5 relative">
            <select v-model="filtersDraft.empId" @change="applySearch"
                class="appearance-none w-full border border-neutral-200 rounded-2xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 outline-none bg-white">
                <option value="">Company ID</option>
                <option v-for="team in empIdOptions" :key="team" :value="team">{{ team }}</option>
            </select>

            <Icon icon="iconamoon:arrow-down-2-light"
                  class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-red-500 pointer-events-none" />
        </div>

        <!-- Department -->
        <div class="mt-5 relative">
            <select v-model="filtersDraft.department" @change="applySearch"
                class="appearance-none w-full border border-neutral-200 rounded-2xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 outline-none bg-white">
                <option value="">department</option>
                <option v-for="team in departments" :key="team" :value="team">{{ team }}</option>
            </select>

            <Icon icon="iconamoon:arrow-down-2-light"
                  class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-red-500 pointer-events-none" />
        </div>

        <!-- Team -->
        <div class="mt-5 relative">
            <select v-model="filtersDraft.team" @change="applySearch"
                class="appearance-none w-full border border-neutral-200 rounded-2xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 outline-none bg-white">
                <option value="">Team</option>
                <option v-for="team in teams" :key="team" :value="team">{{ team }}</option>
            </select>

            <Icon icon="iconamoon:arrow-down-2-light"
                  class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-red-500 pointer-events-none" />
        </div>

        <!-- Position -->
        <div class="mt-5 relative">
            <select v-model="filtersDraft.position" @change="applySearch"
                class="appearance-none w-full border border-neutral-200 rounded-2xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 focus:border-rose-400 outline-none bg-white">
                <option value="">Position</option>
                <option v-for="position in positions" :key="position" :value="position">{{ position }}</option>
            </select>

            <Icon icon="iconamoon:arrow-down-2-light"
                  class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-red-500 pointer-events-none" />
        </div>
    </div>


    <DataTable :rows="pagedEmployees" :columns="columns" :loading="loadingEmployees"
        :totalItems="filteredEmployees.length" v-model:page="page" v-model:pageSize="perPage"
        :pageSizeOptions="[10, 25, 50]" :selectable="true" :showRowNumber="true" rowKey="id" :rowClass="rowClass"
        :modelValue="selectedIdsArr" @update:modelValue="onUpdateSelected">
        <!-- ชื่อเต็ม -->
        <template #cell-fullname="{ row }">
            {{ (row.emp_firstname || '') + ' ' + (row.emp_lastname || '') }}
        </template>

        <!-- ข้อความตอนว่าง -->
        <template #empty>ไม่พบข้อมูล</template>
    </DataTable>


    <!-- ปุ่มยกเลิก / ยืนยัน -->

    <!-- แถบปุ่ม -->
    <div class="mt-6 w-full flex justify-between items-center">
        <!-- ปุ่มยกเลิก (ซ้าย) -->
        <div>
            <CancelButton size="md" :disabled="saving" @click="onCancel">
                Cancel
            </CancelButton>
        </div>
        <div>
            <!-- ปุ่มบันทึก (ขวา) -->
            <button type="button" @click="saveEvent" :disabled="saving" class="inline-flex items-center justify-center gap-2
         rounded-[20px] px-4 py-2 bg-green-600 text-white font-semibold
         hover:bg-green-700 w-[140px] h-[45px] transition">
                <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                <span>บันทึก</span>
            </button>

        </div>
    </div>

    <ModalAlert v-model:open="alert.open" :type="alert.type" :title="alert.title" :message="alert.message"
        :showCancel="alert.showCancel" :okText="alert.okText" :cancelText="alert.cancelText" @confirm="alert.onConfirm"
        @cancel="alert.onCancel" />

</template>

<script>
import axios from 'axios';
import InputPill from '@/components/Input/InputPill.vue';
import SearchBar from "@/components/SearchBar.vue";
import { Icon } from '@iconify/vue'
import DropdownPill from '@/components/Input/DropdownPill.vue'
import DataTable from '@/components/DataTable.vue'
import CancelButton from '@/components/Button/CancelButton.vue'
import ModalAlert from '@/components/Alert/ModalAlert.vue'


export default {
    components: { InputPill, Icon, SearchBar, DropdownPill, DataTable, CancelButton, ModalAlert },
    data() { // เก็บ state ของฟอร์มไว้ใน component
        return {

            eventTitle: '',        // ตัวแปรสำหรับ input "Event Title" เอาไว้เก็บค่าตอน controller ส่งค่ามา
            eventCategoryName: '', // ตัวแปรสำหรับ select "Event Category" ที่ส่งมาจาก table อื่น
            eventCategoryId: '',     // <-- ใช้ค่านี้ส่งไป backend ตอนกด save
            selectCategory: [], //เก็บข้อมูล catagory ที่มีทั้งหมด เลยเก็บเป็น array
            eventDescription: '',
            eventDate: '',
            eventTimeStart: '',
            eventTimeEnd: '',
            eventDuration: 0,
            eventLocation: '',
            saving: false,
            eventDurationMinutes: 0, // นาทีจริงที่จะส่งไป backend

            // Validation state
            formErrors: {},
            submitted: false,

            // ⬇️ state สำหรับไฟล์โ
            filesExisting: [],    // ตัวแปรรับข้อมูล [{id,file_name,url,file_size,...}] จาก backend
            filesNew: [],         // File objects เลือกอัปโหลดเพิ่ม
            filesDeleted: [],     // เก็บ id ไฟล์เดิมที่ผู้ใช้ลบ
            dragging: false,

            // ===== สำหรับตาราง Guest =====
            employees: [],
            loadingEmployees: false,

            search: '',
            searchDraft: '', //เก็บค่าที่ผู้ใช้ป้อน ก่อนกด search
            filters: { department: '', team: '', position: '', empId: '' }, //เก็บค่าที่เลือก จาก dropdown
            filtersDraft: { department: '', team: '', position: '', empId: '' }, //เก็บค่าที่เลือก จาก dropdown ก่อนกด search

            departments: [],
            teams: [],
            positions: [],

            selectedIds: new Set(),//เก็บไอดีที่ถูกติ๊ก
            lockedIds: new Set(), //เก็บข้อมูลคนที่เคยถูกเลือกไว้แล้ว
            page: 1,
            perPage: 10,

            alert: {
                open: false,
                type: 'confirm', // success | error | warning | confirm
                title: '',
                message: '',
                showCancel: false,
                okText: 'OK',
                cancelText: 'Cancel',
                onConfirm: null,
                onCancel: null,
            },

        };
    },
    methods: {
        // ฟังก์ชันดึงข้อมูลจาก backend มาแสดงในฟอร์ม
        async fetchData() {
            try {
                // เรียก API GET /edit-event/{id} โดย {id} เอามาจาก route param
                const evn_response = await axios.get(`/edit-event/${this.$route.params.id}`) //evn_response รับค่าข้อมูล json เรียก fuction edit-event บน route
                // console.log(evn_response) //ข้อมูล json

                const payload = evn_response.data      // สร้างตัวแปร Payload อีก 1 ตัวมาเพื่อมาเก็บข้อมูลเฉพาะ data
                const data = payload?.event ?? {}      // data เป็นตัวที่เก็บจาก payload อีกทีแล้วเพิ่มเงื่อนไขกัน null

                const response = await axios.get('/categories')
                const categories = response.data?.data ?? []

                this.eventCategoryId = data?.evn_category_id ?? ''   //เก็บ
                //เอาข้อมูลจาก controller ที่ส่งมา มาเก็บในตัวแปรแต่ละตัวใน data()
                // เอาข้อมูลที่ได้มา map ลงในตัวแปรที่ bind กับ input/textarea
                this.eventTitle = data?.evn_title ?? '' // ถ้า data หรือ data.evn_title เป็น undefined ให้ใช้ '' แทน
                this.eventDescription = data?.evn_description ?? ''
                this.eventCategoryName = data?.cat_name ?? ''
                this.eventDate = data.evn_date.split("T")[0]; //เอาข้อมูลวันมาที่ได้มาแปลง format เป็น "yyyy-MM-dd".ก่อนส่งไปแสดงในช่องกรอก
                //spit(T) คือแยกข้อมูลเป็น array 2 ช่อง จะได้ ["2023-08-01", "00:00:00.000000Z"] จากแบบ "2023-08-01T00:00:00.000000Z".split("T")

                this.eventTimeStart = data?.evn_timestart ?? ''
                this.eventTimeEnd = data?.evn_timeend ?? ''
                this.eventLocation = data?.evn_location ?? ''
                this.selectCategory = categories

                // ⬇️ ไฟล์เดิม
                this.filesExisting = payload?.files ?? [] //เก็บข้อมูล files ที่ส่งมาจาก controller
                // files": [
                // {
                //   "id": 1,
                //   "file_name": "example.pdf",
                //   "file_path": "events/1.pdf",
                //   "file_size": 158047,
                //   "url": "http:....pdf"
                // },


                // 1) โหลด metadata สำหรับพนักงาน/ฟิลเตอร์
                this.loadingEmployees = true
                const info = await axios.get('/event-info') //ฌรียก controller /event-info เพื่อรับข้อมูลพนักงานมาแสดง
                const employeeData = info.data || {}
                this.employees = (employeeData.employees || []).map(employee => ({
                    id: employee.id,
                    emp_id: employee.emp_id || employee.code || '',
                    emp_firstname: employee.emp_firstname || employee.first_name || '',
                    emp_lastname: employee.emp_lastname || employee.last_name || '',
                    nickname: employee.emp_nickname || '',
                    department: employee.department_name || '',
                    team: employee.team_name || '',
                    position: employee.position_name || '',
                }))
                this.positions = (employeeData.positions || []).map(data => data.pst_name)
                this.departments = (employeeData.departments || []).map(data => data.dpm_name)
                this.teams = (employeeData.teams || []).map(data => data.tm_name)
                this.loadingEmployees = false

                // 2) รับ guest_ids จาก endpoint edit-event/{id}
                const guestIds = payload?.guest_ids || []
                this.lockedIds = new Set(guestIds)     // แขกเดิม => ล็อก
                this.selectedIds = new Set(guestIds)   // ติ๊กไว้ตั้งต้น


            } catch (err) {
                // ถ้า error ให้แจ้งใน console + set ค่า
                console.error(err)
                this.eventTitle = '(โหลดข้อมูลไม่สำเร็จ)'
            }
        },
        // ซีดแถวที่ล็อกไว้
        rowClass(row) {
            // เช็คว่าถ้าเป็น id ที่ถูกล็อก (เชิญไปแล้ว)
            if (this.lockedIds.has(row.id)) {
                // opacity-60: ทำให้สีจางลง
                // pointer-events-none: ปิดการคลิกทุกอย่างในแถวนั้น (รวมถึง checkbox)
                // bg-gray-50: ถมสีพื้นหลังให้ดูว่าเป็นสถานะ disabled
                return 'opacity-60 pointer-events-none bg-gray-50 select-none'
            }
            return ''
        },

        // รับค่าจาก DataTable เวลาเช็ค/ยกเลิกเช็ค
        onUpdateSelected(nextArr) {
            const filtered = nextArr.filter(id => !this.lockedIds.has(id))
            this.selectedIds = new Set(filtered)
        },
        pickFiles() { this.$refs.fileInput?.click?.() },
        //<input ref="fileInput" ... style="display:none" /> → ช่อง input hidden ถูกซ่อนตลอด ในส่วน input ใต้ browsefile

        // พอผู้ใช้กดปุ่ม "Browse files" → เรียก pickFiles()

        // this.$refs.fileInput.click() → จำลองการ "คลิก" ที่ input แบบซ่อน

        // Browser จะเด้ง File Picker (หน้าต่างเลือกไฟล์ของระบบปฏิบัติการ) ขึ้นมาให้ผู้ใช้เลือกไฟล์

        // พอเลือกเสร็จ → trigger event @change="onPick" → เรียกฟังก์ชัน onPick(file) มารับไฟล์ต่อเลย


        onPick(file) { this.addFiles([...file.target.files]); file.target.value = '' },
        // พอรับไฟล์แล้ว ([...file.target.files]) จะแปลงไฟล์จากที่เป็น filelist เป็น array ก่อนส่งให้ add files เพราะ arary ใช้คำสั่งได้เยอะกว่า

        onDrop(event) { this.dragging = false; this.addFiles([...event.dataTransfer.files]) },
        //ใช้เปลี่ยนสถานะ dragging (ที่ถูก set true ตอน @dragover) เอาไว้ใช้กับ css ตอนตกแต่ง

        //this.addFiles([...event.dataTransfer.files])
        // ส่ง array ไฟล์ไปให้ method addFiles()
        // [...event.dataTransfer.files] ใช้ spread operator ... แปลง FileList → array ของไฟล์จริง (File[])

        //flow
        //ผู้ใช้ลากไฟล์มาวาง → trigger @drop="onDrop"
        // onDrop ดึงไฟล์ทั้งหมดออกมา → แปลงเป็น array → ส่งไปตรวจสอบที่ addFiles
        // ถ้าไฟล์ผ่านเงื่อนไข → ถูกเพิ่มใน filesNew → แสดงใน < ul v -for= "newFile in filesNew" >

        addFiles(list) {  //รับไฟล์เข้ามาในชื่อ list
            const MAX_MB = 50
            const ALLOW = [
                "application/pdf", "text/plain", "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "image/jpeg", "image/png",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/vnd.ms-excel",
            ]
            const errs = []
            list.forEach(file => { //เอาไฟล์ที่รับมาเข้าเงื่อนไขเช็คว่ขนาดกิน หรือ ไฟล์ตรงประเภทไหม
                if (file.size > MAX_MB * 1024 * 1024) errs.push(`${file.name}: ไฟล์เกิน ${MAX_MB}MB`)
                else if (!ALLOW.includes(file.type)) errs.push(`${file.name}: ประเภทไฟล์ไม่รองรับ`)
                else this.filesNew.push(file) //ถ้าไม่ก็เพิ่มไฟล์เข้าตัวแปร filesNew ที่เป็น array
            })
            if (errs.length) alert(errs.join('\n')) //ถ้าไม่ผ่าน แสดง alert
        },

        removeFile(index) { this.filesNew.splice(index, 1) },

        removeExisting(id) { //รับ id ของไฟล์ที่จะลบมา แล้ว  filter(file => file.id === id) คือ วนลูป หา id ในข้อมูลarray ของfilesExisting
            this.filesExisting = this.filesExisting.filter(file => file.id !== id)
            this.filesDeleted.push(id) //เจอแล้วก็เพิ่มข้อมูล Id ใส่ตัวแปร fileDeleted
        },
        //ส่วนแปลง ขนาดไฟล์
        prettySize(byte) { const mb = byte / (1024 * 1024); return mb >= 1 ? `${mb.toFixed(2)} MB` : `${(byte / 1024).toFixed(0)} KB` },
        //byte / (1024 * 1024); return mb >= 1 ? ถ้าไฟล์มีขนาด ≥ 1 MB → แสดงเป็น MB ถ้าไฟล์มีขนาด < 1 MB → แสดงเป็น KB
        //mb.toFixed(2) = ปัดทศนิยม 2 ตำแหน่ง
        //${(byte / 1024).toFixed(0)} KB ถ้าไฟล์เล็กกว่า 1 MB → จะแปลงเป็น KB แทน

        applySearch() {
            this.search = this.searchDraft
            this.filters = { ...this.filtersDraft }
            this.page = 1
        },
        // this.search = this.searchDraft;           เอาค่าที่พิมพ์ไว้ใน input (searchDraft) → ไปใส่ตัวแปร search
        // this.filters = { ...this.filtersDraft }; เอาค่า department/team/position ที่เลือกชั่วคราว → ไปใส่ filters
        //  this.page = 1;                          รีเซ็ต pagination กลับไปหน้าแรก

        resetSearch() { //reset ค่าที่ search มา
            this.searchDraft = ''
            this.filtersDraft = { department: '', team: '', position: '' }
            this.search = ''
            this.filters = { department: '', team: '', position: '' }
            this.page = 1
        },

        toggleOne(id, event) {

            // 1. ถ้า id นี้อยู่ใน lockedIds (แขกที่ล็อกไว้แก้ไม่ได้)
            if (this.lockedIds.has(id)) { event?.preventDefault?.(); return }// ยกเลิก event checkbox ไม่ให้ติ๊กได้
            const selected = new Set(this.selectedIds) // 2. สร้าง Set ใหม่จาก selectedIds (รายชื่อที่ถูกเลือกอยู่)

            // 3. ถ้า checkbox ติ๊กอยู่ → เพิ่ม id เข้าไป
            //    ถ้าเอาติ๊กออก → ลบ id ออก
            if (event.target.checked) selected.add(id);
            else selected.delete(id)
            // 4. อัปเดตตัวแปร selectedIds ด้วย Set ที่เก็บข้อมูลคนที่โดนเลือกใหม่
            this.selectedIds = selected
        },

        toggleAllOnPage(event) {
            const tick = event.target.checked // true = ติ๊กทั้งหมด, false = เอาติ๊กออกทั้งหมด
            const select = new Set(this.selectedIds) // สร้าง Set ใหม่จาก selectedIds (รายชื่อที่ถูกเลือกอยู่)
            // วนจนครบจำนวนพนักงานที่โชว์อยู่ในหน้าปัจจุบัน
            this.pagedEmployees.forEach(employee => {

                // ถ้าเป็น โดนเลือกไปแล้ว → ข้าม
                if (this.lockedIds.has(employee.id)) return

                // ถ้า tick = true → add id
                // ถ้า tick = false → remove id
                if (tick) select.add(employee.id); else select.delete(employee.id)
            })

            // 4. อัปเดตตัวแปร selectedIds ด้วย Set ที่เก็บข้อมูลคนที่โดนเลือกใหม่
            this.selectedIds = select
        },

        // [Earth (Suphanut) 2025-12-06] Validate form
        validateForm() {
            const errors = {};

                if (!this.eventTitle?.trim()) errors.eventTitle = true;
                if (!this.eventCategoryId) errors.eventCategoryId = true;
                if (!this.eventDescription?.trim()) errors.eventDescription = true;
                if (!this.eventDate) errors.eventDate = true;

                // Check Required
                if (!this.eventTimeStart) errors.eventTimeStart = true;
                if (!this.eventTimeEnd) errors.eventTimeEnd = true;
                if (!this.eventLocation?.trim()) errors.eventLocation = true;

                // [Earth (Suphanut) 2025-12-06] Logic Check: Time
                // ถ้ามีการกรอกเวลาครบทั้งคู่ แต่ Logic ไม่ผ่าน (End <= Start)
                if (this.eventTimeStart && this.eventTimeEnd && !this.isValidTimeLogic) {
                    // ให้ขึ้นตัวแดงทั้งคู่ หรือแค่ตัวจบก็ได้ (ในที่นี้ให้แดงที่กรอบใหญ่ตาม Template)
                    errors.eventTimeEnd = true;
                    // เพิ่ม message พิเศษสำหรับเคสนี้ (Template จะดึงไปแสดง)
                    errors.timeMsg = 'End time must be after Start time';
                }

                this.formErrors = errors;
                return Object.keys(errors).length === 0;
        },

        async saveEvent() {
            this.submitted = true;
            // [Earth (Suphanut) 2025-12-06] Check validate without alert
            if (!this.validateForm()) {
                // ไม่ต้องทำอะไร ปล่อยให้หน้าจอโชว์สีแดงตาม state
                return;
            }

            this.openAlert({
                type: 'confirm',
                title: 'ARE YOU SURE TO EDIT?',
                message: 'Are you sure you want to change this?',
                showCancel: true,
                okText: 'OK',
                cancelText: 'Cancel',
                onConfirm: async () => {
                    try {
                        this.saving = true

                        const id = this.$route.params.id
                        const formData = new FormData()
                        formData.append('id', id)
                        formData.append('evn_title', this.eventTitle?.trim() || '')

                        if (this.eventCategoryId)
                            formData.append('evn_category_id', String(this.eventCategoryId))

                        formData.append('evn_description', this.eventDescription ?? '')
                        formData.append('evn_date', this.eventDate)
                        formData.append('evn_timestart', this.eventTimeStart)
                        formData.append('evn_timeend', this.eventTimeEnd)
                        formData.append('evn_location', this.eventLocation)
                        formData.append('evn_duration', String(this.eventDurationMinutes || 0))

                        // ✅ ไฟล์ใหม่ (ที่ลาก/เลือกมา)
                        if (this.filesNew.length > 0) {
                            this.filesNew.forEach((file) => {
                                formData.append('attachments[]', file)
                            })
                        }

                        // ✅ ไฟล์เดิมที่ถูกลบ
                        if (this.filesDeleted.length > 0) {
                            this.filesDeleted.forEach((id) => {
                                formData.append('deleted_ids[]', id)
                            })
                        }

                        // ✅ Guest ที่เลือก (optional)
                        // แขก (รวมแขกเดิมที่ล็อก)
                        this.selectedIdsForSubmit.forEach(empId =>
                            formData.append('employee_ids[]', empId)
                        );


                        const res = await axios.post('/edit-event', formData, {
                            headers: { 'Accept': 'application/json' },
                        })

                        this.openAlert({
                            type: 'success',
                            title: 'EDIT SUCCESS!',
                            message: 'This event has been successfully edited.',
                            okText: 'OK',
                            onConfirm: () => this.$router.back(),
                        })
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
            })
        },


        calDuration() {
            const [startHour, startMinute] = (this.eventTimeStart || '0:0').split(':').map(Number); //แยกเวลาตรงส่วน : เพื่อแยก ชั่วโมงกับ นาที
            // startHour เก็บ ชั่วโมง startMinute เก็บนาที
            //เอาแต่ละ element ใน array ไปผ่านฟังก์ชัน Number() เพื่อแปลงจาก string → number :  ["09", "30"].map(Number) → [9, 30]
            const [endHour, endMinute] = (this.eventTimeEnd || '0:0').split(':').map(Number);

            let sumStartMin = startHour * 60 + startMinute; //แปลงแล้วรวมเวลาเป็นนาที
            let sumEndMin = endHour * 60 + endMinute;
            let diff = sumEndMin - sumStartMin;// เอานาทีที่รวมกับชั่วโมงแล้วทั้ง 2 ช่วงมาลบกัน
            if (diff < 0) diff += 24 * 60; // รองรับข้ามเที่ยงคืน ถ้าลบ แล้วได้ค่า ติดลบให้ diff เพิ่มไป 24 ชม แบบนาที


            this.eventDurationMinutes = Math.max(0, diff); //กัน bug เพื่อ diff ที่เข้ามาตรงนี้ติดลบ จะได้ค่า 0 แทน

            // ส่วนโชว์ ใน input :
            const hour = Math.floor(diff / 60), //hour เก็บชม ที่แปลง นาที จากdiff เศษปัดลง
                min = diff % 60;  //min เก็บนาที เอาเศษ
            this.eventDuration = `${hour} Hour ${min} Min`; // ใช้สำหรับ “แสดงผล” ชั่วโมง h นาที m -> 2h50m
            // เช็คว่า ถ้าไม่มีนาที หรือ ชั่วโมง ให้แสดงแค่ค่าเดียว
            if (min === 0) {
                this.eventDuration = `${hour} Hour`;
            } else if (hour === 0) {
                this.eventDuration = `${min} Min`;
            }
        },
        onCancel() {
            if (this.saving || this.filesNew.length) {
                if (!confirm('ยกเลิกและละทิ้งการแก้ไขทั้งหมด?')) return
            }
            this.$router?.back?.()  // หรือ this.$router.push('/events')
        },
        openAlert(cfg = {}) {
            // รีเซ็ต handler เก่า
            this.alert.onConfirm = null
            this.alert.onCancel = null

            // รวมค่าที่ส่งเข้ามากับค่า default
            Object.assign(this.alert, {
                open: true,
                type: 'success',
                title: '',
                message: '',
                showCancel: false,
                okText: 'OK',
                cancelText: 'Cancel',
            }, cfg)
        },
    },
    computed: {
        // ใน computed: { ... }
        isValidTimeLogic() {
            // แปลงเวลาเป็นตัวเลข (ชั่วโมง * 60 + นาที)
            const [startHour, startMinute] = (this.eventTimeStart || '0:0').split(':').map(Number);
            const [endHour, endMinute] = (this.eventTimeEnd || '0:0').split(':').map(Number);

            const sumStartMin = startHour * 60 + startMinute;
            const sumEndMin = endHour * 60 + endMinute;

            // ถ้ายังไม่ได้กรอกเวลา (หรือเป็น 00:00 ทั้งคู่ตอนโหลด) ให้ถือว่า true ไปก่อน (เดี๋ยวไปติด validate required แทน)
            if (!this.eventTimeStart || !this.eventTimeEnd) return true;

            // [Earth (Suphanut) 2025-12-06] แก้ไข Logic: ตัดการบวก 24 ชม. ออก เพื่อบังคับว่าเวลาจบต้องมากกว่าเวลาเริ่ม
            // เช็คว่า เวลาจบ ต้องมากกว่า เวลาเริ่ม ( > ) หรือ มากกว่าเท่ากับ ( >= ) แล้วแต่ requirement (ปกติ Event ควร >)
            return sumEndMin > sumStartMin;
        },
        // โครงคอลัมน์ของ DataTable
        columns() {
            return [
                { key: 'emp_id', label: 'ID', sortable: false, class: 'min-w-[120px]' },
                { key: 'fullname', label: 'Name', sortable: false }, // เรนเดอร์ผ่าน slot
                { key: 'nickname', label: 'Nickname', sortable: false, class: 'min-w-[120px]' },
                { key: 'department', label: 'Department', sortable: false },
                { key: 'team', label: 'Team', sortable: false },
                { key: 'position', label: 'Position', sortable: false },
            ]
        },

        // DataTable ต้องใช้ array; ของเดิมเก็บเป็น Set
        selectedIdsArr: {
            get() { return Array.from(this.selectedIds) },
            set(arr) {
                // ป้องกันเลือกคนที่ถูกล็อก
                this.selectedIds = new Set(arr.filter(id => !this.lockedIds.has(id)))
            }
        },
        empIdOptions() {
            // ได้เป็น array ของ string เช่น ["E001","E002",...]
            return [...new Set(this.employees.map(e => e.emp_id).filter(Boolean))];
        },
        // กรองด้วย empId เพิ่ม
        filteredEmployees() {
            const searchData = (this.search || '').toLowerCase()
            return this.employees.filter(employee => {
                const matchText = !searchData || `${employee.emp_id} ${employee.emp_firstname} ${employee.emp_lastname} ${employee.nickname || ''}`.toLowerCase().includes(searchData)
                const matchDept = !this.filters.department || employee.department === this.filters.department
                const matchTeam = !this.filters.team || employee.team === this.filters.team
                const matchPos = !this.filters.position || employee.position === this.filters.position

                // ถ้าเลือก empId ในดรอปดาวน์ให้บังคับตรง emp_id
                const matchEmpId = !this.filters.empId || employee.emp_id === this.filters.empId

                return matchText && matchDept && matchTeam && matchPos && matchEmpId
            })
        },
        hasAnyFiles() {
            return (this.filesExisting?.length || 0) + (this.filesNew?.length || 0) > 0
        },
        uploadItems() {
            const existing = (this.filesExisting || []).map(f => ({
                key: `old-${f.id}`,
                kind: 'existing',
                id: f.id,
                name: f.file_name,
                url: f.url,
                size: f.file_size ?? 0,
            }))
            const news = (this.filesNew || []).map((f, i) => ({
                key: `new-${i}`,
                kind: 'new',
                index: i,
                name: f.name,
                size: f.size ?? 0,
            }))
            // ให้ไฟล์เดิมขึ้นก่อน แล้วต่อด้วยไฟล์ใหม่
            return [...existing, ...news]
        },
        // ใช้ตัวนี้ตอนส่งจริง: รวมแขกเดิมที่ล็อก + แขกใหม่ที่เลือก
        selectedIdsForSubmit() {
            return Array.from(new Set([...this.lockedIds, ...this.selectedIds]));
        },

        // v-model ที่ bind กับ DataTable ต้อง “คง” แขกที่ล็อกไว้เสมอ
        selectedIdsArr: {
            get() {
                // ให้ DataTable เห็นว่าเช็ค (รวมล็อกด้วย) เพื่อแสดง checkbox เป็นติ๊ก
                return Array.from(new Set([...this.lockedIds, ...this.selectedIds]));
            },
            set(arr) {
                // เก็บเฉพาะที่ “ไม่ใช่ล็อก” ลง selectedIds, และบวก lockedIds กลับเข้าไปเสมอ
                const nonLocked = arr.filter(id => !this.lockedIds.has(id));
                this.selectedIds = new Set(nonLocked);
            }
        },

        totalPages() {
            return Math.ceil(this.filteredEmployees.length / this.perPage)
            // this.filteredEmployees.length = จำนวนพนักงานที่เหลือหลังกรอง search/filter แล้ว

            // this.perPage = จำนวนแถวต่อหน้า (เช่น 10, 25, 50)

            // Math.ceil() = ปัดเศษขึ้น → เผื่อพนักงานไม่ลงตัวกับจำนวนต่อหน้า
            //Ex. มี 47 คน, perPage = 10 → 47 / 10 = 4.7 → ปัดขึ้น = 5 หน้า จะแสดงว่ามี 5 หน้า
        },
        pagedEmployees() {
            const start = (this.page - 1) * this.perPage
            return this.filteredEmployees.slice(start, start + this.perPage)

            //คำนวณ index เริ่มต้นของพนักงานในหน้านี้ → (this.page - 1) * this.perPage

            //ใช้.slice(start, start + this.perPage) ดึงเฉพาะพนักงานของหน้านั้นออกมา

            // Ex page = 1, perPage = 10 → slice(0, 10) → เอาคนที่ index 0–9 แสดงคนที่จะอยู่ในแต่ละหน้า

        },
        allCheckedOnPage() {
            if (this.pagedEmployees.length === 0) return false
            const unlocked = this.pagedEmployees.filter(employee => !this.lockedIds.has(employee.id))
            return unlocked.length > 0 && unlocked.every(employee => this.selectedIds.has(employee.id))

            //ใช้เช็คว่า checkbox “ติ๊กทั้งหมด” บนหน้านี้ ควรถูกติ๊กหรือไม่
            // ถ้าไม่มีพนักงาน (length === 0) → return false
            // unlocked = พนักงานที่ ไม่ได้ถูกล็อก (lockedIds)
            // เงื่อนไขสุดท้าย:
            // unlocked.length > 0 → ต้องมีพนักงานให้เลือก
            // unlocked.every(...) → ทุกคนในหน้านี้ต้องอยู่ใน selectedIds (คือถูกเลือกแล้ว)

            // ตัวอย่าง หน้านี้มี 10 คน แต่เลือกไว้ครบ 10 → return true

            //หน้านี้มี 10 คน แต่เลือกไว้ 8 → return false
            //เพื่อถ้าติ๊กหมด เป็น true จะเอาค่าไปบอกให้ checkboxall จะติ๊กด้วย
        },
    },

    watch: {
        eventTimeStart: 'calDuration',//เรียก calDuration ไม่ว่าค่าจะเปลี่ยนจากการส่งมาผ่าน controller หรือ คนใช้เลือกเปลี่ยนเองตอนเลือก Input
        eventTimeEnd: 'calDuration',// ใช้เพราะว่าต้องการคำนวณ duration ทุกครั้งที่มีการส่งข้อมูลมาจาก controller เวลาโหลดข้อมูลเก่าด้วย

        search() { this.page = 1 },
        filters: { deep: true, handler() { this.page = 1 } },
        perPage() { this.page = 1 },
    },
    // ใช้เพื่อโหลดข้อมูลทันทีที่เปิดหน้า edit_event.vue
    mounted() {
        this.fetchData(); // เรียกฟังก์ชัน fetchData() เมื่อ component(layout.vue) ถูก mount
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
