<!-- pages/edit_event.vue -->
<template>
    <div class="text-neutral-800 font-semibold font-[Poppins] text-3xl mb-4">
        Edit Event
    </div>
    <div class="grid grid-cols-12 h-full gap-0">
        <div class="col-span-8">

            <!-- ช่องกรอกชื่ออีเวนต์ -->
            <div class="grid grid-cols-3">
                <div class="mt-6 md:grid md:grid-cols-[1fr_520px] md:gap-8 items-stretch">
                    <!-- v-model.trim="evn_title" = ผูกค่ากับตัวแปร evn_title ใน data() อันนึงเปลี่ยนค่าอีกอันก็จะเปลี่ยนตาม
         trim = ตัดช่องว่างหน้า/หลังอัตโนมัติ -->
                    <div >
                        <label class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                            for="eventTitle">Event
                            Title</label><br />
                        <input class="border border-[#A1A1A1] rounded-[20px] px-[20px]  disabled:bg-[#F5F5F5]"
                            type="text" v-model.trim="eventTitle" id="eventTitle" disabled />
                        <!-- <InputText :label="'Event Title'" :value="eventTitle" /> -->
                    </div>

                    <div>
                        <!-- เลือก Category -->
                        <label>Event Category</label><br />
                        <select class="border border-neutral-200 rounded-[20px] px-[20px]" v-model="eventCategoryId">
                            <!-- v-model ตรงนี้จะผูกค่ากับ evn_category_id โหลดครั้งแรกจะได้ค่าเก่า + ถ้าเลืกใหม่จะได้ค่าใหม่ เวลาส่งไป save ก็จะส่งเป็น id -->

                            <!-- ถ้าหมวดเดิมเป็น inactive แต่อยากแสดงไว้ -->
                            <option :value="eventCategoryId" hidden> <!-- เก็บ id ค่าเดิมไว้ -->
                                {{ eventCategoryName
                                }}<!-- แสดงตัวเลือกจากตัวแปร evn_category_name ที่ดึงมาจาก controller -->
                            </option>

                            <option v-for="cat in selectCategory" :value="cat.id">
                                {{ cat.cat_name }}
                                <!-- cat เก็บข้อมูลในช่อง array ของ select_category ทุกรอบที่วน {id= 1 name=ประชุม ...}
        cat.id เอา cat ที่เก็บข้อมูลในช่อง array แล้วดึงค่ามาแค่ name เพื่อเอาไว้แสดงค่าที่ให้เลือก -->
                            </option>
                        </select><br />
                    </div>
                </div>
            </div>
            <div>
                <!-- ช่องกรอกรายละเอียดอีเวนต์ -->
                <!-- v-model.trim="evn_description" = ผูกค่ากับตัวแปร evn_description อันนึงเปลี่ยนค่าอีกอันก็จะเปลี่ยนตาม-->
                <label>Event Description</label><br />
                <textarea style="border: 2px solid black;" v-model.trim="eventDescription"></textarea><br>
            </div>

            <div class="grid grid-cols-3">
                <div>
                    <!-- วันที่ -->
                    <label>Date</label><br>
                    <input type="date" v-model="eventDate">
                </div>

                <div>
                    <label>Time</label>

                    <input type="time" v-model="eventTimeStart"></input>
                    <!-- v-model จะผูกกับค่า 2 ที่คือ 1ตอนโหลดหน้า ค่ามนี้จะโหลดเอาค่าที่ส่งมาจาก controller ผ่าน method fetchData
        2 ตอนเลือกค่า ใน Input ค่าก็จะเปลี่ยนไปตามที่เราเลือกแล้วส่งไปคำนวณ-->

                    <label> : </label>
                    <input type="time" v-model="eventTimeEnd"></input>
                </div>
                <div>
                    <!-- ส่วนแสดงช่วงเวลา -->
                    <label>duration</label><br>
                    <input disabled v-model="eventDuration"></input><br>
                    <!-- ผูกกับ evn_duration คำนวณค่าเสร็จแล้วก็จะมาแสดงตรงนี้ -->
                </div>
            </div>
            <!-- ส่วนแสดงสถานที่ -->
            <label>Location</label><br>
            <input type="text" v-model="eventLocation"></input>
        </div>
        <!-- Upload attachments -->
        <div class="col-span-4" style="margin: 20px" >
            <label>Upload attachments</label>

            <!-- ไฟล์เดิม -->
            <div v-if="filesExisting.length > 0" style="margin-bottom:8px">
                <!-- จะโชว์ก็ต่อเมื่อ length > 0 กันไม่มีข้อมูล-->
                <p style="margin:4px 0 8px; opacity:.8">ไฟล์เดิม</p>
                <ul style="list-style:none; padding:0; margin:0">
                    <!-- v for ตรงนี้ร้างตัวแปร oldFile ขึ้นมาเพื่อวนเก็บขอมูลใน array ของ filesExisting ในแต่ละรอบวน -->
                    <li v-for="oldFile in filesExisting" :key="oldFile.id"
                        style="display:flex; gap:10px; align-items:center; padding:6px 0;">
                        <a :href="oldFile.url" target="_blank" rel="noopener">{{ oldFile.file_name }}</a>
                        <!-- ส่วนใส่ชื่อไฟล์ แล้วทำเป็นเหมือนลิ้งค์ให้กดดู โดย href จะเอา link ที่ถูกเพิ่มโดย .map ใน controller แล้วส่งมาที่หน้าบ้าน -->

                        <span style="opacity:.7; font-size:12px">({{ prettySize(oldFile.file_size) }})</span>
                        <!-- เรียกใช้ prettysize ใน script จะแปลงจาก byte -> kbถ้า ≥ 1kB → MB ถ้า ≥ 1MB -->

                        <!-- ส่วนเอาออกจาก UI click แล้วจะเรียก removeExisting แล้วส่ง(oldFile.id) ไปด้วย-->
                        <button type="button" @click="removeExisting(oldFile.id)"
                            style="border:0; background:#eee; border-radius:6px; padding:4px 8px; cursor:pointer">
                            ✕
                        </button>
                    </li>
                </ul>
            </div>

            <!-- โซนอัปโหลดไฟล์ใหม่ -->
            <div class="dropzone" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop" :class="{ dragging }"
                style="border:1px dashed #bbb; padding:12px; border-radius:8px;">
                <!-- มีการเรียก ondrop เมื่อมีไฟล์ลากมาวางในช่อง -->
                <!-- @dragover.prevent="dragging = true" → เวลา ลากไฟล์มาทับ บล็อกนี้ → ตั้งค่า dragging = true (ตัวแปรที่สร้างไว้ใน script) + prevent กัน event ค่า default ของเบราว์เซอร์ -->
                <!-- @dragleave.prevent="dragging = false" → เวลา ลากไฟล์ออกจากบล็อกนี้ → ตั้งค่า dragging = false -->
                <!-- :class="{ dragging }" → ถ้า dragging = true จะเพิ่ม class CSS dragging ให้อัตโนมัติ (เช่นเปลี่ยนพื้นหลัง, border ฯลฯ) -->
                <p>Choose a file or drag & drop it here</p>
                <p class="muted">pdf, txt, docx, jpeg, xlsx – Up to 50MB</p>
                <button type="button" @click="pickFiles">Browse files</button>
                <!-- ส่วนนี้จะเป็นปุ่มเพิ่มไฟล์ ถ้ากด click เพิ่มไฟล์จะไปเรียก method  pickFiles -->
                <input ref="fileInput" type="file" multiple class="hidden-file"
                    accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" style="display:none" />
                <!-- ref="fileInput" → ให้ Vue อ้างถึง element นี้จากโค้ด JS ส่วน pickFiles (this.$refs.fileInput)

            type="file" → ช่องเลือกสำหรับไฟล์

            multiple → เลือกได้หลายไฟล์ในครั้งเดียว ได้ข้อมูลมาเป็น !!! filelist เหมือน array แต่ไม่ใช่ เลยต้องไปแปลงเป็น array ใน method addfile

            accept="..." → จำกัดชนิดไฟล์ที่เลือกได้

            @change="onPick" → เวลาเลือกไฟล์ → เรียก method onPick เพื่อเก็บไฟล์เข้า state (filesNew)

            style="display:none" → ซ่อน input นี้ไม่ให้ผู้ใช้เห็น (กดปุ่ม Browse files ข้างบนแทน) -->

            </div>

            <!-- รายการไฟล์ใหม่ -->
            <ul v-if="filesNew.length > 0" class="file-list">
                <!-- ไม่ render อะไรเลยถ้า  filesNew ไม่มีข้อมูลด้านใน -->

                <li v-for="(newFile, index) in filesNew" :key="index">
                    <!-- วนลูปใน array filesNew
                รอบแรก newFile = ไฟล์ที่ index 0, รอบสอง = ไฟล์ที่ index 1 …
                index = ตำแหน่งไฟล์ใน array -->

                    {{ newFile.name }} ({{ prettySize(newFile.size) }})
                    <button type="button" @click="removeFile(index)">✕</button>
                </li>
            </ul>
        </div>
</div>
        <!-- ===== Add Guest (table) ===== -->
        <h3 style="margin-top: 24px">Add Guest</h3>

        <div class="guest-toolbar" style="display:flex;gap:10px;align-items:center;margin:14px 0;">

            <!-- ช่อง serach -->
            <input v-model.trim="searchDraft" placeholder="Search..."
                style="flex:1;padding:8px 12px;border:1px solid #ddd;border-radius:999px;" />

            <!-- ช่อง dropdown โชว์ข้อมูล department-->
            <select v-model="filtersDraft.department">
                <option value="">Department</option>
                <option v-for="department in departments" :key="department" :value="department">{{ department }}
                </option>
            </select>

            <!-- ช่อง dropdown โชว์ข้อมูล Team-->
            <select v-model="filtersDraft.team">
                <option value="">Team</option>
                <option v-for="team in teams" :key="team" :value="team">{{ team }}</option>
            </select>

            <!-- ช่อง dropdown โชว์ข้อมูล Position-->
            <select v-model="filtersDraft.position">
                <option value="">Position</option>
                <option v-for="position in positions" :key="position" :value="position">{{ position }}</option>
            </select>
            <button type="button" @click="applySearch">Search</button>
            <!-- ปุ่ม search เรียก Method applySearch -->
            <button type="button" @click="resetSearch">Clear</button>
            <!-- ปุ่ม clear search เรียก Method resetSearch -->
        </div>

    <!-- ช่อง table โชว์ข้อมูล พนักงาน-->
    <div class="table-wrap" style="border:1px solid #eee;border-radius:12px;overflow:hidden;background:#fff;">
        <table class="guest-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#fafafa">
                    <th style="width:40px;padding:10px;border-bottom:1px solid #f1f1f1">

                        <!-- ปุ่มเลือกทั้งหมด -->
                        <input type="checkbox" :checked="allCheckedOnPage" @change="toggleAllOnPage" />
                        <!-- ถ้ากด check จะเรียก computed allCheckedOnPage
                         แล้วถ้ามีการเปลี่ยนที่ติ๊ก จะเรียก method toggleAllOnPage
                         เป็นการ ผูกค่า checked ของ checkbox กับ computed property allCheckedOnPage
                        เวลา Vue render กล่อง checkbox มันจะถามว่า
                        allCheckedOnPage ตอนนี้คืนค่า true หรือ false ?-->

                    </th>
                    <th style="width:60px;padding:10px;border-bottom:1px solid #f1f1f1">#</th>
                    <th style="width:120px;padding:10px;border-bottom:1px solid #f1f1f1">ID</th>
                    <th style="padding:10px;border-bottom:1px solid #f1f1f1">Name</th>
                    <th style="width:120px;padding:10px;border-bottom:1px solid #f1f1f1">Nickname</th>
                    <th style="padding:10px;border-bottom:1px solid #f1f1f1">Department</th>
                    <th style="padding:10px;border-bottom:1px solid #f1f1f1">Team</th>
                    <th style="padding:10px;border-bottom:1px solid #f1f1f1">Position</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(employee, index) in pagedEmployees" :key="employee.id"
                    :style="lockedIds.has(employee.id) ? 'opacity:.6' : ''">
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">
                        <input type="checkbox" :value="employee.id" :checked="selectedIds.has(employee.id)"
                            :disabled="lockedIds.has(employee.id)" @change="toggleOne(employee.id, $event)" />
                    </td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ (page - 1) * perPage + index + 1 }}</td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.emp_id }}</td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.emp_firstname }} {{
                        employee.emp_lastname }}
                    </td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.nickname || '-' }}</td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.department || '-' }}</td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.team || '-' }}</td>
                    <td style="padding:10px;border-bottom:1px solid #f1f1f1">{{ employee.position || '-' }}</td>
                </tr>

                <tr v-if="!loadingEmployees && pagedEmployees.length === 0">
                    <td colspan="8" style="padding:14px;text-align:center;color:#777">ไม่พบข้อมูล</td>
                </tr>
                <tr v-if="loadingEmployees">
                    <td colspan="8" style="padding:14px;text-align:center;color:#777">กำลังโหลด...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;">
        <div>
            แสดง
            <select v-model.number="perPage">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
            </select>
            รายการต่อหน้า
        </div>
        <div class="pager">
            <button :disabled="page === 1" @click="page--">‹</button>
            <span>{{ page }} / {{ totalPages || 1 }}</span>
            <button :disabled="page === totalPages || totalPages === 0" @click="page++">›</button>
        </div>
    </div>

    <button @click="saveEvent">
        บันทึกการแก้ไข
    </button>


</template>

<script>
import axios from 'axios';

import { Component } from 'react';
export default {
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
            filters: { department: '', team: '', position: '' }, //เก็บค่าที่เลือก จาก dropdown
            filtersDraft: { department: '', team: '', position: '' }, //เก็บค่าที่เลือก จาก dropdown ก่อนกด search

            departments: [],
            teams: [],
            positions: [],

            selectedIds: new Set(),//เก็บไอดีที่ถูกติ๊ก
            lockedIds: new Set(), //เก็บข้อมูลคนที่เคยถูกเลือกไว้แล้ว
            page: 1,
            perPage: 10,

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
        }, pickFiles() { this.$refs.fileInput?.click?.() },
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
            this.filesExisting = this.filesExisting.filter(file => file.id === id)
            this.filesDeleted.push(id) //เจอแล้วก็เพิ่มข้อมูล Id ใส่ตัวแปร fileDeleted
        },
        //ส่วนแปลง ขนาดไฟล์
        prettySize(byte) { const mb = byte / (1024 * 1024); return mb >= 1 ? `${mb.toFixed(2)} MB` : `${(byte / 1024).toFixed(0)} KB` },
        //byte / (1024 * 1024); return mb >= 1 ? ถ้าไฟล์มีขนาด ≥ 1 MB → แสดงเป็น MB ถ้าไฟล์มีขนาด < 1 MB → แสดงเป็น KB
        //mb.toFixed(2) = ปัดทศนิยม 2 ตำแหน่ง
        //${(byte / 1024).toFixed(0)} KB ถ้าไฟล์เล็กกว่า 1 MB → จะแปลงเป็น KB แทน

        applySearch() { this.search = this.searchDraft; this.filters = { ...this.filtersDraft }; this.page = 1 },
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

        async saveEvent() {
            if (!this.eventTitle?.trim()) return alert('กรอกชื่ออีเวนต์ก่อน')
            try {
                this.saving = true
                const id = this.$route.params.id
                const formData = new FormData()

                formData.append('id', id)
                formData.append('evn_title', this.eventTitle.trim())

                // ฟิลด์อื่นๆ (ส่งเฉพาะที่ใช้งานอยู่)
                if (this.eventCategoryId) formData.append('evn_category_id', this.eventCategoryId)
                if (this.eventDescription !== undefined) formData.append('evn_description', this.eventDescription ?? '')
                if (this.eventDate) formData.append('evn_date', this.eventDate)
                if (this.eventTimeStart) formData.append('evn_timestart', this.eventTimeStart)
                if (this.eventTimeEnd) formData.append('evn_timeend', this.eventTimeEnd)
                if (this.eventLocation) formData.append('evn_location', this.eventLocation)

                // duration เป็น “นาที”
                formData.append('evn_duration', String(this.eventDurationMinutes || 0))

                // ไฟล์
                this.filesNew.forEach(file => formData.append('attachments[]', file, file.name))
                this.filesDeleted.forEach(fileId => formData.append('delete_file_ids[]', fileId))

                // แขก (รวมแขกเดิม)
                Array.from(this.selectedIds).forEach(employeeId => formData.append('employee_ids[]', employeeId))

                const res = await axios.post('/edit-event', formData, {
                    headers: { 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' }
                })

                alert(res.data?.message || 'อัปเดตสำเร็จ')
                this.filesExisting = res.data?.files || []
                this.filesNew = []
                this.filesDeleted = []
            } catch (err) {
                console.error(err)
                alert(err.response?.data?.message || 'อัปเดตไม่สำเร็จ')
            } finally {
                this.saving = false
            }
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
            this.eventDuration = `${hour}h${min}m`; // ใช้สำหรับ “แสดงผล” ชั่วโมง h นาที m -> 2h50m
        },
    },
    computed: {
        filteredEmployees() {
            const searchData = (this.search || '').toLowerCase() //ถ้า this.search ไม่มีค่า → ใช้ '' (สตริงว่าง) แทน + เปลี่ยนให้เป็นตัวพิมพ์เล็กทั้งหมด
            return this.employees.filter(employee => {
                //วนลูปทุก employee () ที่อยู่ใน array this.employees
                // filter จะคืนพนักงานที่ตรงเงื่อนไขเท่านั้น
                const matchText = !searchData || `${employee.emp_id} ${employee.emp_firstname} ${employee.emp_lastname} ${employee.nickname || ''}`.toLowerCase().includes(searchData)
                const matchDept = !this.filters.department || employee.department === this.filters.department
                const matchTeam = !this.filters.team || employee.team === this.filters.team
                const matchPos = !this.filters.position || employee.position === this.filters.position
                return matchText && matchDept && matchTeam && matchPos
            })
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
