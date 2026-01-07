<template>
    <div class="font-[Poppins]" @pointerdown.capture="onRootPointer">
        <div class="mb-8 flex items-center gap-3">
            <h2 class="text-2xl font-semibold text-neutral-800">
                Create Event
            </h2>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-8">
                <div class="grid md:grid-cols-[1fr_240px] gap-6 items-start">
                    <div>
                        <label
                            class="block text-neutral-800 font-semibold text-[15px] mb-2"
                        >
                            Event Title <span class="text-red-600">*</span>
                        </label>
                        <InputPill
                            v-model="eventTitle"
                            placeholder="Enter event title"
                            class="w-full h-[52px] font-medium text-[16px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-neutral-800 font-semibold text-[15px] mb-2"
                        >
                            Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select
                                v-model="eventCategoryId"
                                class="appearance-none border border-neutral-200 rounded-[20px] px-[20px] w-full h-[52px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition bg-white cursor-pointer"
                            >
                                <option value="" disabled selected>
                                    Select
                                </option>
                                <option
                                    v-for="cat in selectCategory"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.cat_name }}
                                </option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-neutral-500"
                            >
                                <Icon
                                    icon="mdi:chevron-down"
                                    class="h-6 w-6 text-neutral-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        class="block text-neutral-800 font-semibold text-[15px] mb-2"
                    >
                        Event Description
                    </label>
                    <textarea
                        v-model.trim="eventDescription"
                        placeholder="Enter description..."
                        class="border border-neutral-200 w-full h-[160px] rounded-2xl p-5 font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition resize-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label
                            class="block text-neutral-800 font-semibold text-[15px] mb-2"
                        >
                            Date <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                v-model="eventDate"
                                class="border border-neutral-200 w-full h-[52px] rounded-2xl px-[20px] font-medium text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition uppercase-date cursor-pointer"
                                @click="$event.target.showPicker()"
                            />
                            <!-- <Icon icon="stash:data-date-solid" class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-neutral-400 pointer-events-none" /> -->
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-neutral-800 font-semibold text-[15px] mb-2"
                        >
                            Time <span class="text-red-600">*</span>
                        </label>
                        <div
                            class="flex h-[52px] w-full items-center gap-1 rounded-2xl border border-neutral-200 px-3 shadow-sm bg-white focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400 transition"
                        >
                            <input
                                type="time"
                                v-model="eventTimeStart"
                                step="300"
                                class="time-input flex-1 bg-transparent text-[15px] font-medium text-neutral-800 outline-none text-center cursor-pointer caret-transparent"
                                @click="$event.target.showPicker()"
                                @keydown.prevent
                            />

                            <span class="text-[16px] font-bold text-neutral-400"
                                >:</span
                            >

                            <input
                                type="time"
                                v-model="eventTimeEnd"
                                step="300"
                                class="time-input flex-1 bg-transparent text-[15px] font-medium text-neutral-800 outline-none text-center cursor-pointer caret-transparent"
                                @click="$event.target.showPicker()"
                                @keydown.prevent
                            />

                            <Icon
                                icon="mdi:clock-outline"
                                class="w-6 h-6 text-neutral-400 ml-1 pointer-events-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-neutral-800 font-semibold text-[15px] mb-2 "
                            >Duration</label
                        >
                        <div
                            class="flex h-[52px] w-full items-center gap-3 rounded-2xl border border-neutral-200 px-4 shadow-sm bg-[#F9FAFB]"
                        >
                            <input
                                class="w-full h-full bg-transparent font-medium text-neutral-600 outline-none border-0"
                                disabled
                                v-model="eventDurationDisplay"
                            />
                            <Icon
                                icon="lucide:clock-fading"
                                class="w-6 h-6 text-neutral-400"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        class="block text-neutral-800 font-semibold text-[15px] mb-2"
                    >
                        Location <span class="text-red-600">*</span>
                    </label>
                    <InputPill
                        v-model="eventLocation"
                        placeholder="Enter location"
                        class="w-full h-[52px] font-medium text-[16px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                    />
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4">
                <label
                    class="block text-neutral-800 font-semibold text-[15px] mb-2"
                >
                    Upload attachments
                </label>
                <p class="text-xs text-neutral-500 mb-3">
                    Drag and drop document to your support task
                </p>

                <div
                    class="group relative flex flex-col min-h-[300px] rounded-[24px] border-2 border-dashed border-rose-200 bg-rose-50/50 p-5 transition-all hover:border-rose-400 hover:bg-rose-50"
                    :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="onDrop"
                >
                    <div
                        v-if="filesNew.length > 0"
                        class="flex flex-col gap-2 mb-4"
                    >
                        <div
                            v-for="(item, index) in filesNew"
                            :key="index"
                            class="w-full flex items-center justify-between rounded-2xl bg-white border border-rose-100 px-3 py-2.5 shadow-sm"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 text-rose-600"
                                >
                                    <Icon
                                        icon="basil:file-solid"
                                        class="h-6 w-6"
                                    />
                                </div>
                                <div class="truncate">
                                    <span
                                        class="block truncate text-sm font-medium text-neutral-800"
                                    >
                                        {{ item.name }}
                                    </span>
                                    <span class="text-xs text-rose-500">
                                        {{ prettySize(item.size) }}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="grid h-7 w-7 place-items-center rounded-full text-neutral-400 hover:text-red-600 hover:bg-rose-50 transition"
                                @click="removeFile(index)"
                                aria-label="Remove file"
                            >
                                <Icon icon="mdi:close" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-1 flex-col items-center justify-center text-center"
                    >
                        <div
                            class="mb-3 grid h-16 w-16 place-items-center rounded-full bg-rose-100 text-rose-500"
                        >
                            <Icon
                                icon="entypo:upload-to-cloud"
                                class="h-8 w-8"
                            />
                        </div>
                        <p class="text-sm font-medium text-neutral-800">
                            Choose a file or drag &amp; drop it here
                        </p>
                        <p class="mt-1 text-xs text-neutral-500">
                            pdf, txt, docx, jpeg, xlsx
                        </p>
                    </div>

                    <div class="mt-auto flex justify-center pt-4">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-50 hover:border-rose-300 transition"
                            @click="pickFiles"
                        >
                            Browse files
                        </button>
                    </div>
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                        @change="onPick"
                    />
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-3xl font-semibold">Add Guest</h3>

            <div class="mt-4 flex flex-col gap-3">
                <div class="flex flex-wrap items-center gap-3 w-full">
                    <div class="flex-1 min-w-[260px]">
                        <SearchBar
                            v-model="search"
                            placeholder="Search ID / Name / Nickname"
                            @search="() => (page = 1)"
                            class=""
                        />
                    </div>

                    <div
                        class="flex flex-row flex-wrap items-center gap-2 mt-8"
                    >
                        <EmployeeDropdown
                            label="Company ID"
                            v-model="selectedCompanyIds"
                            :options="companyIdOptions"
                        />
                        <EmployeeDropdown
                            label="Department"
                            v-model="selectedDepartmentIds"
                            :options="departmentOptions"
                        />
                        <EmployeeDropdown
                            label="Team"
                            v-model="selectedTeamIds"
                            :options="teamOptions"
                        />
                        <EmployeeDropdown
                            label="Position"
                            v-model="selectedPositionIds"
                            :options="positionOptions"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <DataTable
                    :rows="pagedEmployees"
                    :columns="columns"
                    :loading="loadingEmployees"
                    :totalItems="filteredEmployees.length"
                    v-model:page="page"
                    v-model:pageSize="perPage"
                    :pageSizeOptions="[10, 25, 50]"
                    :selectable="true"
                    :showRowNumber="true"
                    rowKey="id"
                    :modelValue="selectedIdsArr"
                    @update:modelValue="onUpdateSelected"
                >
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

        <div class="mt-6 w-full flex justify-between items-center">
            <!-- ปุ่มยกเลิก (ซ้าย) -->
            <div>
                <CancelButton size="md" :disabled="saving" @click="onCancel">
                    Cancel
                </CancelButton>
            </div>
            <div>
                <!-- ปุ่มบันทึก (ขวา) -->
                <button
                    type="button"
                    @click="saveEvent"
                    :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 py-2 bg-[#00A73D] text-white font-semibold hover:bg-green-700 w-[140px] h-[45px] transition"
                >
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                    <span>Create</span>
                </button>
            </div>
        </div>

        <ModalAlert
            v-model:open="alert.open"
            :type="alert.type"
            :title="alert.title"
            :message="alert.message"
            :showCancel="alert.showCancel"
            :okText="alert.okText"
            :cancelText="alert.cancelText"
            @confirm="alert.onConfirm"
            @cancel="alert.onCancel"
        />
    </div>
</template>

<script>
import axios from "axios";
import InputPill from "@/components/Input/InputPill.vue";
import { Icon } from "@iconify/vue";
import DataTable from "@/components/DataTable.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
// Import Components แบบ EventCheckIn
import SearchBar from "@/components/SearchBar.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import CancelButton from '@/components/Button/CancelButton.vue'

export default {
    components: {
        InputPill,
        Icon,
        DataTable,
        ModalAlert,
        SearchBar,
        EmployeeDropdown,
        CancelButton,
    },
    data() {
        return {
            // Form Data
            eventTitle: "",
            eventCategoryId: "",
            eventDescription: "",
            eventDate: "",
            eventTimeStart: "",
            eventTimeEnd: "",
            eventDurationDisplay: "",
            eventDurationMinutes: 0,
            eventLocation: "",

            // Metadata
            selectCategory: [],

            // Files
            filesNew: [],
            dragging: false,

            // Guests & Table
            employees: [],
            loadingEmployees: false,

            // Search & Filter (New System)
            search: "",
            selectedCompanyIds: [],
            selectedDepartmentIds: [],
            selectedTeamIds: [],
            selectedPositionIds: [],

            // Option Arrays
            companyIdOptions: [],
            departmentOptions: [],
            teamOptions: [],
            positionOptions: [],

            selectedIds: new Set(),
            page: 1,
            perPage: 10,

            // System
            saving: false,
            alert: {
                open: false,
                type: "confirm",
                title: "",
                message: "",
                showCancel: false,
                onConfirm: null,
            },
        };
    },
    computed: {
        columns() {
            return [
                {
                    key: "emp_id",
                    label: "ID",
                    sortable: false,
                    class: "min-w-[100px]",
                },
                { key: "fullname", label: "Name", sortable: false },
                { key: "nickname", label: "Nickname", sortable: false },
                { key: "department", label: "Department", sortable: false },
                { key: "team", label: "Team", sortable: false },
                { key: "position", label: "Position", sortable: false },
            ];
        },

        // --- Filtering Logic (Adapted from EventCheckIn) ---
        filteredEmployees() {
            const q = (this.search || "").toLowerCase().trim();
            let list = this.employees;

            // Search Filter
            if (q) {
                list = list.filter((e) =>
                    [
                        String(e.emp_id),
                        e.emp_firstname,
                        e.emp_lastname,
                        e.nickname,
                    ].some((f) => f?.toLowerCase().includes(q))
                );
            }

            // Company Filter
            if (this.selectedCompanyIds?.length) {
                const needles = this.selectedCompanyIds
                    .map((x) => String(x).trim())
                    .filter(Boolean);
                list = list.filter((r) => {
                    // เช็คทั้ง companyId และ companyAbbr ถ้ามี
                    const idStr = String(
                        r.companyId || r.companyAbbr || ""
                    ).trim();
                    return needles.some((n) => idStr.includes(n));
                });
            }

            // Department Filter
            if (this.selectedDepartmentIds?.length) {
                const set = new Set(this.selectedDepartmentIds);
                list = list.filter((r) => set.has(r.department));
            }

            // Team Filter
            if (this.selectedTeamIds?.length) {
                const set = new Set(this.selectedTeamIds);
                list = list.filter((r) => set.has(r.team));
            }

            // Position Filter
            if (this.selectedPositionIds?.length) {
                const set = new Set(this.selectedPositionIds);
                list = list.filter((r) => set.has(r.position));
            }

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
    },
    watch: {
        eventTimeStart: "calDuration",
        eventTimeEnd: "calDuration",
        // Reset page when filters change
        search() {
            this.page = 1;
        },
        selectedCompanyIds() {
            this.page = 1;
        },
        selectedDepartmentIds() {
            this.page = 1;
        },
        selectedTeamIds() {
            this.page = 1;
        },
        selectedPositionIds() {
            this.page = 1;
        },
        perPage() {
            this.page = 1;
        },
    },
    mounted() {
        this.fetchInfo();
    },
    methods: {
        async fetchInfo() {
            try {
                this.loadingEmployees = true;
                const res = await axios.get("/event-info");
                const data = res.data || {};

                this.selectCategory = data.categories || [];

                this.employees = (data.employees || []).map((e) => {
                    const rawId = String(e.emp_id || "");
                    const rawPrefixFromId = (rawId.match(/^[A-Za-z]+/) || [
                        "",
                    ])[0];
                    const companyAbbr = (rawPrefixFromId || "").toUpperCase();

                    return {
                        id: e.id,
                        emp_id: e.emp_id || "",
                        emp_firstname: e.emp_firstname || "",
                        emp_lastname: e.emp_lastname || "",
                        nickname: e.emp_nickname || "",
                        department: e.department_name || "",
                        team: e.team_name || "",
                        position: e.position_name || "",
                        companyAbbr: companyAbbr,
                        companyId: e.company_id || companyAbbr || "", // ใช้สำหรับ filter
                    };
                });

                // สร้างตัวเลือกสำหรับ Dropdowns
                this.buildFilterOptions();
            } catch (err) {
                console.error("Error fetching info:", err);
            } finally {
                this.loadingEmployees = false;
            }
        },

        // --- Helper for Options ---
        toOptions(arr) {
            const uniq = [...new Set(arr.filter(Boolean))].sort();
            return uniq.map((v) => ({ label: v, value: v }));
        },

        buildFilterOptions() {
            // Company: ใช้ companyId หรือ Abbr เป็นตัวเลือก
            this.companyIdOptions = this.toOptions(
                this.employees.map((r) => r.companyId)
            );

            // Other Filters
            this.departmentOptions = this.toOptions(
                this.employees.map((r) => r.department)
            );
            this.teamOptions = this.toOptions(
                this.employees.map((r) => r.team)
            );
            this.positionOptions = this.toOptions(
                this.employees.map((r) => r.position)
            );
        },

        // --- Core Functions (No changes needed) ---
        calDuration() {
            if (!this.eventTimeStart || !this.eventTimeEnd) {
                this.eventDurationDisplay = "";
                this.eventDurationMinutes = 0;
                return;
            }
            const [sh, sm] = this.eventTimeStart.split(":").map(Number);
            const [eh, em] = this.eventTimeEnd.split(":").map(Number);
            let diff = eh * 60 + em - (sh * 60 + sm);
            if (diff < 0) diff += 24 * 60;

            this.eventDurationMinutes = Math.max(0, diff);
            const h = Math.floor(diff / 60);
            const m = diff % 60;

            if (m === 0) this.eventDurationDisplay = `${h} Hour`;
            else if (h === 0) this.eventDurationDisplay = `${m} Min`;
            else this.eventDurationDisplay = `${h} Hour ${m} Min`;
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
        addFiles(files) {
            const MAX_MB = 50;
            const ALLOW = [
                "application/pdf",
                "text/plain",
                "application/msword",
                "image/jpeg",
                "image/png",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/vnd.ms-excel",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            ];
            const errs = [];
            files.forEach((f) => {
                if (f.size > MAX_MB * 1024 * 1024)
                    errs.push(`${f.name}: File too large (> ${MAX_MB}MB)`);
                else if (!ALLOW.includes(f.type))
                    errs.push(`${f.name}: Invalid file type`);
                else this.filesNew.push(f);
            });
            if (errs.length) alert(errs.join("\n"));
        },
        removeFile(idx) {
            this.filesNew.splice(idx, 1);
        },
        prettySize(byte) {
            const mb = byte / (1024 * 1024);
            return mb >= 1
                ? `${mb.toFixed(2)} MB`
                : `${(byte / 1024).toFixed(0)} KB`;
        },
        onUpdateSelected(ids) {
            this.selectedIds = new Set(ids);
        },
        onCancel() {
            if (this.filesNew.length || this.eventTitle) {
                if (!confirm("Discard unsaved changes?")) return;
            }
            this.$router.back();
        },
        async saveEvent() {
            if (
                !this.eventTitle ||
                !this.eventCategoryId ||
                !this.eventDate ||
                !this.eventTimeStart ||
                !this.eventTimeEnd ||
                !this.eventLocation
            ) {
                this.openAlert({
                    type: "warning",
                    title: "Missing Info",
                    message: "Please fill in all required fields (*).",
                });
                return;
            }
            this.openAlert({
                type: "confirm",
                title: "CONFIRM CREATION",
                message: "Are you sure you want to create this event?",
                showCancel: true,
                onConfirm: async () => {
                    this.saving = true;
                    try {
                        const formData = new FormData();
                        formData.append("event_title", this.eventTitle.trim());
                        formData.append(
                            "event_category_id",
                            this.eventCategoryId
                        );
                        formData.append(
                            "event_description",
                            this.eventDescription || ""
                        );
                        formData.append("event_date", this.eventDate);
                        formData.append("event_timestart", this.eventTimeStart);
                        formData.append("event_timeend", this.eventTimeEnd);
                        formData.append(
                            "event_duration",
                            this.eventDurationMinutes
                        );
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

                        this.openAlert({
                            type: "success",
                            title: "CREATE SUCCESS!",
                            message: "New event has been created.",
                            onConfirm: () => this.$router.push("/event"),
                        });
                    } catch (err) {
                        console.error(err);
                        const msg =
                            err.response?.data?.message ||
                            "Failed to create event.";
                        this.openAlert({
                            type: "error",
                            title: "ERROR",
                            message: msg,
                        });
                    } finally {
                        this.saving = false;
                    }
                },
            });
        },
        openAlert(cfg) {
            this.alert = {
                ...this.alert,
                open: true,
                showCancel: false,
                okText: "OK",
                // [เพิ่ม] ถ้าไม่ได้ส่ง onConfirm มา ให้ใส่ฟังก์ชันปิด Modal เป็นค่าเริ่มต้น
                onConfirm: () => {
                    this.alert.open = false;
                },
                ...cfg,
            };
        },
        // สำหรับปิด dropdown ในกรณีคลิกที่อื่น (ถ้า component ไม่ได้จัดการเอง)
        onRootPointer(e) {
            // อาจจะไม่จำเป็นต้องใช้แล้วถ้าใช้ EmployeeDropdown component ที่จัดการตัวเองได้ดี
            // แต่ใส่ไว้เผื่อกรณีที่ยังต้องใช้
        },
    },
};
</script>

<style scoped>
.time-input::-webkit-calendar-picker-indicator {
    opacity: 0;
}
.uppercase-date {
    text-transform: uppercase;
}
.caret-transparent {
    caret-color: transparent;
}
</style>
