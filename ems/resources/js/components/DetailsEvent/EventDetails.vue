<!-- /**
 * ชื่อไฟล์: EventDetails.vue
 * คำอธิบาย: Component สำหรับแสดงรายละเอียดกิจกรรม (Event Details) แบบ Read-only
 *           พร้อมแสดงรายชื่อ Guest ที่ได้รับเชิญ และไฟล์แนบของกิจกรรม
 * Input: id (รหัสกิจกรรม) จาก props
 * Output: หน้าแสดงรายละเอียดกิจกรรม ข้อมูล Guest และไฟล์แนบ
 * ชื่อผู้เขียน/แก้ไข: Raveroj Sonthi
 * วันที่จัดทำ/แก้ไข: 2026-03-1
 */ -->
<template>
    <div class="p-6" @pointerdown.capture="onRootPointer">
        <div class="mb-6 flex items-center gap-3">
            <h2 class="text-3xl font-semibold">Details Event</h2>
        </div>

        <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <BaseReadonlyField class="md:col-span-2" label="Event Title" :value="form.title" />

                    <BaseReadonlyField label="Category" :value="categoryDisplay" icon="iconamoon:arrow-down-2-light" />
                </div>

                <div>
                    <label class="mb-1 block text-xl font-medium text-neutral-800">Event Details</label>
                    <div
                        class="flex w-full items-start gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
                        <textarea :value="form.details" rows="4" disabled
                            class="w-full resize-none bg-transparent text-neutral-400 outline-none text-md font-medium"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <BaseReadonlyField label="Date" :value="formatDate(form.date)" icon="stash:data-date-solid" />

                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Time</label>
                        <div
                            class="flex w-full items-center justify-between rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="w-15 text-center text-neutral-400 font-medium text-md">{{
                                    toThaiTime(form.timeStart) }}</span>
                                <span class="select-none text-slate-400">:</span>
                                <span class="w-15 text-center text-neutral-400 font-medium text-md">{{
                                    toThaiTime(form.timeEnd) }}</span>
                            </div>
                            <span class="text-neutral-400">
                                <Icon icon="iconamoon:clock-light" class="h-6 w-6 text-neutral-400 shrink-0" />
                            </span>
                        </div>
                    </div>

                    <BaseReadonlyField label="Duration" :value="calculateDuration(
                        form.timeStart,
                        form.timeEnd,
                        form.duration
                    )" icon="mingcute:time-duration-line" />
                </div>

                <BaseReadonlyField label="Location" :value="form.location" />
            </div>

            <div>
                <label class="mb-1 block text-xl font-medium text-neutral-800">Upload Attachments</label>
                <p class="mb-2 text-xs text-neutral-400">
                    Drag and drop document to your support task
                </p>

                <div
                    class="flex min-h-[400px] flex-col gap-2 rounded-[20px] border border-dashed border-neutral-400 bg-neutral-100 p-4">
                    <div v-if="files.length === 0" class="grid flex-1 place-items-center text-neutral-400 text-md">
                        ไม่มีไฟล์แนบ
                    </div>
                    <div v-else class="flex flex-col gap-2">
                        <a v-for="(f, i) in files" :key="i" href="javascript:void(0)" @click.stop="openFile(f.url)"
                            class="flex h-[60px] w-full items-center gap-3 rounded-2xl border border-neutral-400 bg-white px-3 p-2.5 text-neutral-400 font-medium transition-colors cursor-pointer hover:bg-gray-50 no-underline">
                            <Icon icon="basil:file-solid" class="h-10 w-10 text-slate-700" />
                            <span class="truncate text-md text-neutral-400 hover:text-slate-700 flex-1 text-left"
                                :title="f.file_name || f.name">
                                {{ f.file_name || f.name }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-3xl font-semibold mb-4">Add Guest</h3>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex flex-1 items-center gap-2 min-w-[200px]">
                    <SearchBar v-model="searchInput" placeholder="Search Employee ID / Name / Nickname"
                        @search="doSearch">
                    </SearchBar>
                </div>
                <div class="mt-8 gap-2">
                    <EmployeeDropdown label="Company" :options="uniqueCompanies" v-model="companySel"
                        @update:modelValue="onFilterChange" class="px-2" />
                    <EmployeeDropdown label="Department" :options="uniqueDepartments" v-model="departmentSelect"
                        @update:modelValue="onFilterChange" class="px-2" />
                    <EmployeeDropdown label="Team" :options="uniqueTeams" v-model="teamSelect"
                        @update:modelValue="onFilterChange" class="px-2" />
                    <EmployeeDropdown label="Position" :options="uniquePositions" v-model="positionSelect"
                        @update:modelValue="onFilterChange" class="px-2" />
                </div>
            </div>

            <!-- แสดงจำนวน Guest ที่ถูกเลือก -->
            <div class="mt-4 flex items-center gap-2">
                <Icon icon="mdi:account-check" class="w-7 h-7 text-slate-600" />
                <span class="text-[16px] font-medium text-neutral-700">
                    Selected Guests :
                </span>
                <span class="text-[16px] font-semibold text-slate-700">
                    {{ selectedIds.size }}
                </span>
                <span class="text-[16px] font-medium text-neutral-500">
                    / {{ rows.length }} people
                </span>
            </div>

            <DataTable :rows="pagedRows" :columns="guestsTableColumns" :loading="loading"
                :total-items="filteredRows.length" v-model:page="page" v-model:pageSize="pageSize" row-key="id"
                :show-row-number="false" :selectable="false" :rowClass="rowClassFn" class="mt-2">
                <template #header-_select>
                    <div class="w-10 text-center">
                        <input type="checkbox" class="accent-rose-600 opacity-60 size-5 cursor-not-allowed" disabled
                            @click.prevent />
                    </div>
                </template>
                <template #cell-_select="{ row }">
                    <div @click.stop class="flex justify-center">
                        <input type="checkbox" class="accent-slate-700 size-5 cursor-not-allowed" disabled
                            :checked="selectedIds.has(row.id)" @click.prevent />
                    </div>
                </template>

                <template #cell-_index="{ row }">
                    <span class="text-neutral-500 font-medium">
                        {{ filteredRows.indexOf(row) + 1 }}
                    </span>
                </template>

                <template #footer-info="{ from, to, total }"> </template>
            </DataTable>

            <div class="mt-6">
                <button class="rounded-2xl bg-neutral-400 px-5 py-2 text-white hover:bg-neutral-500 transition"
                    @click="onBack">
                    ← Back
                </button>
            </div>
        </div>

        <div v-if="loading" class="mt-4 text-slate-500">กำลังโหลดข้อมูล…</div>
        <div v-if="error" class="mt-4 text-rose-600">{{ error }}</div>
    </div>
</template>

<script>
import axios from "axios";
import { Icon } from "@iconify/vue";
import DataTable from "@/components/DataTable.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import BaseReadonlyField from "@/components/DetailsEvent/BaseReadonlyField.vue";
import SearchBar from "../SearchBar.vue";

axios.defaults.withCredentials = true;

export default {
    name: "EventDetailCard",
    components: {
        Icon,
        DataTable,
        EmployeeDropdown,
        BaseReadonlyField,
        SearchBar,
    },
    props: { id: { type: [String, Number], required: true } },
    data() {
        return {
            loading: false,        // สถานะกำลังโหลดข้อมูล
            error: "",             // ข้อความ error ที่จะแสดงบนหน้า
            files: [],             // ไฟล์แนบของกิจกรรม
            categories: [],        // รายการหมวดหมู่กิจกรรมทั้งหมด
            idToCat: {},           // map รหัสหมวดหมู่ → ข้อมูลหมวดหมู่ สำหรับ lookup เร็ว
            rows: [],              // รายชื่อพนักงานทั้งหมดสำหรับตาราง Guest
            selectedIds: new Set(), // รหัสพนักงานที่เป็น Guest ของกิจกรรมนี้
            page: 1,               // หน้าปัจจุบันของ DataTable
            pageSize: 10,          // จำนวนแถวต่อหน้าของ DataTable
            form: {
                title: "",         // ชื่อกิจกรรม
                categoryId: "",    // รหัสหมวดหมู่กิจกรรม
                categoryName: "",  // ชื่อหมวดหมู่กิจกรรม (แสดงผล)
                details: "",       // คำอธิบายกิจกรรม
                date: "",          // วันที่จัดกิจกรรม (format: YYYY-MM-DD)
                timeStart: "",     // เวลาเริ่มกิจกรรม (format: HH:MM)
                timeEnd: "",       // เวลาสิ้นสุดกิจกรรม (format: HH:MM)
                duration: "",      // ระยะเวลากิจกรรม
                location: "",      // สถานที่จัดกิจกรรม
                status: "",        // สถานะกิจกรรม
            },
            searchInput: "",       // คำค้นหาชั่วคราว (ก่อนกด search)
            searchQuery: "",       // คำค้นหาที่ใช้งานจริงในการกรอง
            companySel: [],        // Company ที่เลือกจาก Filter
            departmentSelect: [],           // Department ที่เลือกจาก Filter
            teamSelect: [],           // Team ที่เลือกจาก Filter
            positionSelect: [],            // Position ที่เลือกจาก Filter

            // โครงคอลัมน์ของ DataTable Guest
            guestsTableColumns: [
                { key: "_select", label: "", class: "w-5 text-center" },
                { key: "_index", label: "#", class: "w-12 text-center" },
                { key: "codeDisplay", label: "Employee ID", class: "w-auto text-left" },
                { key: "name", label: "Name", class: "w-auto text-left" },
                { key: "nick", label: "Nickname", class: "w-auto text-left" },
                { key: "department", label: "Department", class: "w-auto text-left" },
                { key: "team", label: "Team", class: "w-auto text-left" },
                { key: "position", label: "Position", class: "text-left" },
            ],
        };
    },
    computed: {
        /**
         * ชื่อฟังก์ชัน: categoryDisplay
         * คำอธิบาย: แสดงชื่อหมวดหมู่กิจกรรม โดย fallback หลายระดับถ้าไม่เจอชื่อ
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        categoryDisplay() {
            if (this.form.categoryName) return String(this.form.categoryName);
            const id = this.form.categoryId;
            if (!id) return "";
            const rec = this.idToCat[String(id)];
            // fallback ไปใช้ชื่อจาก form โดยตรงถ้ามี
            const guessed = this.form.category?.name ?? this.form.cat_name ?? "";
            return guessed || (rec ? rec.name : `#${id}`);
        },

        /**
         * ชื่อฟังก์ชัน: uniqueDepartments
         * คำอธิบาย: ดึง Department ที่ไม่ซ้ำกันจากรายชื่อพนักงานทั้งหมด สำหรับ Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        uniqueDepartments() {
            return this.toOptions(this.rows.map((r) => r.department));
        },

        /**
         * ชื่อฟังก์ชัน: uniqueTeams
         * คำอธิบาย: ดึง Team ที่ไม่ซ้ำกันจากรายชื่อพนักงานทั้งหมด สำหรับ Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        uniqueTeams() {
            return this.toOptions(this.rows.map((r) => r.team));
        },

        /**
         * ชื่อฟังก์ชัน: uniquePositions
         * คำอธิบาย: ดึง Position ที่ไม่ซ้ำกันจากรายชื่อพนักงานทั้งหมด สำหรับ Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        uniquePositions() {
            return this.toOptions(this.rows.map((r) => r.position));
        },

        /**
         * ชื่อฟังก์ชัน: uniqueCompanies
         * คำอธิบาย: ดึง Company ที่ไม่ซ้ำกันจากรายชื่อพนักงานทั้งหมด สำหรับ Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        uniqueCompanies() {
            return this.toOptions(this.rows.map((r) => r.companyAbbr));
        },

        /**
         * ชื่อฟังก์ชัน: filteredRows
         * คำอธิบาย: กรองรายชื่อพนักงานตามคำค้นหาและ Filter ที่เลือก
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        filteredRows() {
            const q = (this.searchQuery || "").toLowerCase();
            return this.rows.filter((r) => {
                // กรองตามคำค้นหา (Employee ID, ชื่อ, nickname)
                const hitQ =
                    !q ||
                    [r.codeDisplay, r.code, r.name, r.nick].some((v) =>
                        String(v || "").toLowerCase().includes(q)
                    );
                // กรองตาม Filter แต่ละหมวด
                const hitCompany = this.companySel.length === 0 || this.companySel.includes(r.companyAbbr);
                const hitDept = this.departmentSelect.length === 0 || this.departmentSelect.includes(r.department);
                const hitTeam = this.teamSelect.length === 0 || this.teamSelect.includes(r.team);
                const hitPos = this.positionSelect.length === 0 || this.positionSelect.includes(r.position);
                return hitQ && hitCompany && hitDept && hitTeam && hitPos;
            });
        },

        /**
         * ชื่อฟังก์ชัน: pagedRows
         * คำอธิบาย: ดึงข้อมูลพนักงานเฉพาะหน้าปัจจุบันสำหรับ DataTable
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        pagedRows() {
            const start = (this.page - 1) * this.pageSize;
            return this.filteredRows.slice(start, start + this.pageSize);
        },
    },
    async created() {
        // ดึงข้อมูลทั้งหมดพร้อมกันเพื่อลดเวลาโหลด
        await Promise.all([
            this.fetchCategories(),
            this.fetchEvent(),
            this.fetchEmployees(),
            this.fetchGuestSelection(),
        ]);
    },
    methods: {
        /**
         * ชื่อฟังก์ชัน: openFile
         * คำอธิบาย: เปิดไฟล์แนบในแท็บใหม่ของ browser
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        openFile(url) {
            if (!url) return;
            window.open(url, "_blank");
        },

        /**
         * ชื่อฟังก์ชัน: toOptions
         * คำอธิบาย: แปลง array ของค่าดิบเป็น array ของ option object สำหรับ Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        toOptions(arr) {
            return [...new Set(arr.filter(Boolean))]
                .sort()
                .map((v) => ({ label: v, value: v }));
        },

        /**
         * ชื่อฟังก์ชัน: fetchCategories
         * คำอธิบาย: ดึงข้อมูลหมวดหมู่กิจกรรมทั้งหมดจาก API รวมถึงที่ถูกลบแล้ว (withTrashed)
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        async fetchCategories() {
            try {
                const r = await axios.get("/categoriesAll", {
                    params: { withTrashed: 1, includeInactive: 1 },
                });
                const arr = Array.isArray(r.data) ? r.data : r.data?.data ?? [];
                this.categories = arr.map(this.normalizeCat);
                // สร้าง map id → category สำหรับ lookup เร็วใน categoryDisplay
                this.idToCat = {};
                for (const cat of this.categories) {
                    this.idToCat[String(cat.id)] = cat;
                }
            } catch (e) {
                console.warn("fetchCategories failed", e);
            }
        },

        /**
         * ชื่อฟังก์ชัน: normalizeCat
         * คำอธิบาย: แปลงข้อมูล Category จาก API ให้เป็นรูปแบบมาตรฐาน {id, name}
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        normalizeCat(c, i) {
            const id = c.id ?? c.categoryId ?? i + 1;
            const name = c.name ?? c.cat_name ?? c.title ?? "";
            return { id: String(id), name: String(name) };
        },

        /**
         * ชื่อฟังก์ชัน: fetchEvent
         * คำอธิบาย: ดึงข้อมูลกิจกรรมจาก API แล้ว map ลงใน form และ files
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        async fetchEvent() {
            try {
                this.loading = true;
                if (!this.id) {
                    this.error = "ไม่พบรหัสอีเวนต์";
                    return;
                }
                const res = await axios.get(`/event/${this.id}`);
                const rawData = res.data;
                const item = rawData.event || rawData;

                // ดึงไฟล์แนบจาก response
                if (Array.isArray(rawData.files)) {
                    this.files = rawData.files;
                } else if (Array.isArray(item.files)) {
                    this.files = item.files;
                } else {
                    this.files = [];
                }

                if (item) {
                    this.form.title = item.evn_title ?? item.title ?? "";
                    this.form.categoryId = item.evn_category_id ?? item.category_id ?? "";
                    this.form.categoryName =
                        item.category?.cat_name ??
                        item.category_name ??
                        item.cat_name ??
                        item.category?.name ??
                        "";
                    this.form.details = item.evn_description ?? item.details ?? "";
                    // แปลง ISO datetime → YYYY-MM-DD สำหรับแสดงผล
                    this.form.date = (item.evn_date ?? item.date ?? "").slice(0, 10);
                    this.form.timeStart = (item.evn_timestart ?? item.timeStart ?? "").slice(0, 5);
                    this.form.timeEnd = (item.evn_timeend ?? item.timeEnd ?? "").slice(0, 5);
                    this.form.duration = item.evn_duration ?? item.duration ?? "";
                    this.form.location = item.evn_location ?? item.location ?? "";
                }
            } catch (e) {
                this.error = "โหลดข้อมูลไม่สำเร็จ";
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        /**
         * ชื่อฟังก์ชัน: fetchEmployees
         * คำอธิบาย: ดึงรายชื่อพนักงานทั้งหมดจาก API แล้ว map เป็นรูปแบบที่ใช้ใน DataTable
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        async fetchEmployees() {
            try {
                const r = await axios.get("get-employees");
                const arr = Array.isArray(r.data) ? r.data : r.data?.data || [];
                this.rows = arr.map((e, idx) => {
                    const rawId = String(e.emp_id || "").trim();
                    // คำนวณ Company Abbreviation จาก company_abbr หรือ prefix ของ emp_id
                    let abbr = (e.company_abbr || e.company_code || "").toUpperCase().trim();
                    if (!abbr) {
                        const match = rawId.match(/^([A-Za-z]+)/);
                        if (match) abbr = match[1].toUpperCase();
                    }
                    // ถ้า emp_id เป็นตัวเลขล้วน ให้นำ abbr นำหน้าเพื่อแสดงผล
                    let display = rawId;
                    const isNumericId = /^\d+$/.test(rawId);
                    if (isNumericId && abbr) {
                        display = `${abbr}${rawId}`;
                    }
                    return {
                        id: e.id ?? idx + 1,
                        code: rawId,
                        codeDisplay: display,
                        name: `${e.emp_firstname} ${e.emp_lastname}`,
                        nick: e.emp_nickname || "",
                        department: e.department_name || "",
                        team: e.team_name || "",
                        position: e.position_name || "",
                        companyAbbr: abbr,
                        companyId: e.company_id || "",
                    };
                });
            } catch (err) {
                this.rows = [];
            }
        },

        /**
         * ชื่อฟังก์ชัน: fetchGuestSelection
         * คำอธิบาย: ดึงรหัสพนักงานที่เป็น Guest ของกิจกรรมนี้จาก API เพื่อ highlight ในตาราง
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        async fetchGuestSelection() {
            try {
                if (!this.id) return;
                const r = await axios.get(`events/${this.id}/connects`);
                const ids = r.data?.employee_ids || r.data || [];
                // แปลงเป็น Set<number> สำหรับ lookup เร็ว
                this.selectedIds = new Set(ids.map((n) => Number(n)));
            } catch (e) {
                this.selectedIds = new Set();
            }
        },

        /**
         * ชื่อฟังก์ชัน: rowClassFn
         * คำอธิบาย: กำหนด CSS class ให้แถว Guest เพื่อ highlight พนักงานที่เป็น Guest
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        rowClassFn(row) {
            return this.selectedIds.has(row.id) ? "row-selected" : "";
        },

        /**
         * ชื่อฟังก์ชัน: calculateDuration
         * คำอธิบาย: คำนวณระยะเวลากิจกรรมจากเวลาเริ่มและเวลาสิ้นสุด
         *           ถ้าคำนวณไม่ได้จะใช้ค่า fallback จาก DB แทน
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        calculateDuration(start, end, fallbackDuration) {
            if (start && end) {
                const [startH, startM] = start.split(":").map(Number);
                const [endH, endM] = end.split(":").map(Number);
                if (!isNaN(startH) && !isNaN(startM) && !isNaN(endH) && !isNaN(endM)) {
                    const startTotal = startH * 60 + startM;
                    const endTotal = endH * 60 + endM;
                    let diff = endTotal - startTotal;
                    if (diff < 0) diff += 24 * 60; // รองรับกรณีข้ามเที่ยงคืน
                    const h = Math.floor(diff / 60);
                    const m = diff % 60;
                    let res = `${h} Hour`;
                    if (m > 0) res += ` ${m} Min`;
                    return res;
                }
            }
            return this.formatDurationFallback(fallbackDuration);
        },

        /**
         * ชื่อฟังก์ชัน: formatDurationFallback
         * คำอธิบาย: แปลงค่า duration จาก DB (ทศนิยมชั่วโมง) เป็นข้อความที่อ่านง่าย เช่น "2 Hour 30 Minutes"
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        formatDurationFallback(v) {
            const n = Number(v);
            if (!Number.isFinite(n)) return v;
            const h = Math.floor(n);
            const m = Math.round((n - h) * 60);
            let res = `${h} Hour`;
            if (m > 0) res += ` ${m} Minute${m > 1 ? "s" : ""}`;
            return res;
        },

        /**
         * ชื่อฟังก์ชัน: formatDate
         * คำอธิบาย: แปลงวันที่จาก format YYYY-MM-DD เป็น DD/MM/YYYY สำหรับแสดงผล
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        formatDate(dateStr) {
            if (!dateStr) return "";
            const [y, m, d] = dateStr.split("-");
            if (y && m && d) return `${d}/${m}/${y}`;
            return dateStr;
        },

        /**
         * ชื่อฟังก์ชัน: toThaiTime
         * คำอธิบาย: เพิ่ม "น." ต่อท้ายเวลาสำหรับแสดงผลแบบไทย เช่น "09:00 น."
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        toThaiTime(v) {
            return v ? `${v} น.` : "";
        },

        /**
         * ชื่อฟังก์ชัน: onRootPointer
         * คำอธิบาย: จัดการ pointer event ที่ root element สำหรับปิด dropdown เมื่อคลิกนอกพื้นที่
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        onRootPointer(e) {
            // ตรวจสอบว่าคลิกอยู่ภายใน dropdown หรือไม่
            const insideDropdown = e.target.closest("[data-dd]");
            if (!insideDropdown) {
                // ปิด dropdown ถ้าคลิกนอกพื้นที่
            }
        },

        /**
         * ชื่อฟังก์ชัน: onBack
         * คำอธิบาย: กลับไปหน้าก่อนหน้า โดยใช้ router.back() หรือ emit event ถ้าไม่มี router
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        onBack() {
            this.$router ? this.$router.back() : this.$emit("back");
        },

        /**
         * ชื่อฟังก์ชัน: doSearch
         * คำอธิบาย: Apply คำค้นหาจาก searchInput ไปใช้งานจริง และรีเซ็ตกลับหน้า 1
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        doSearch() {
            this.searchQuery = this.searchInput.trim();
            this.page = 1; // รีเซ็ตกลับหน้า 1 เมื่อค้นหาใหม่
        },

        /**
         * ชื่อฟังก์ชัน: onFilterChange
         * คำอธิบาย: รีเซ็ตกลับหน้า 1 เมื่อมีการเปลี่ยน Dropdown Filter
         * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
         * วันที่จัดทำ/แก้ไข: 2025-12-28
         */
        onFilterChange() {
            this.page = 1;
        },
    },
};
</script>

<style scoped>
/* highlight แถวที่เป็น Guest ด้วยสีเทา */
:deep(tr.row-selected)>* {
    background-color: #d4d4d4 !important;
}

:deep(tr.row-selected input[type="checkbox"]) {
    opacity: 1;
    cursor: not-allowed;
}
</style>
