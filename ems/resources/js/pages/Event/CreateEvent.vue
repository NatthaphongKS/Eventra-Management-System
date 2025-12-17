<template>
    <div class="min-h-screen bg-white pb-20">
        <div
            class="text-neutral-800 font-semibold font-[Poppins] text-3xl mb-4 pt-6 px-6"
        >
            Create Event
        </div>

        <div class="grid grid-cols-12 h-full gap-0 px-6">
            <div class="col-span-8">
                <div class="grid">
                    <div
                        class="mt-6 md:grid md:grid-cols-[3fr_200px] md:gap-8 items-stretch"
                    >
                        <div>
                            <label
                                class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                            >
                                Event Title
                                <span class="text-red-600">*</span> </label
                            ><br />
                            <InputPill
                                v-model="eventTitle"
                                placeholder="Enter event title"
                                class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                            />
                        </div>
                        <div>
                            <label
                                class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                            >
                                Event Category
                                <span class="text-red-600">*</span> </label
                            ><br />
                            <div class="relative">
                                <select
                                    v-model="eventCategoryId"
                                    class="appearance-none border border-neutral-200 rounded-[20px] px-[20px] w-full h-[52px] font-[Poppins] text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition bg-white"
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
                                    <svg
                                        class="h-4 w-4 fill-current"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                    >
                        Event Description </label
                    ><br />
                    <textarea
                        v-model.trim="eventDescription"
                        placeholder="Enter description..."
                        class="border border-neutral-200 w-full h-[165px] rounded-2xl p-5 font-[Poppins] text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition resize-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div>
                        <label
                            class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                        >
                            Date <span class="text-red-600">*</span> </label
                        ><br />
                        <input
                            type="date"
                            v-model="eventDate"
                            class="border border-neutral-200 w-full h-[52px] rounded-2xl px-[20px] font-[Poppins] text-neutral-800 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition uppercase-date cursor-pointer"
                            @click="$event.target.showPicker()"
                        />
                    </div>

                    <div>
                        <label
                            class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                        >
                            Time <span class="text-red-600">*</span>
                        </label>
                        <div
                            class="flex h-[52px] w-full items-center gap-2 rounded-xl border border-neutral-200 px-3 shadow-sm bg-white focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400 transition"
                        >
                            <input
                                type="time"
                                v-model="eventTimeStart"
                                step="300"
                                class="time-input flex-1 bg-transparent text-[15px] font-medium text-neutral-800 outline-none text-center cursor-pointer caret-transparent"
                                @click="$event.target.showPicker()"
                                @keydown.prevent
                            />

                            <span class="text-[18px] font-bold text-red-600"
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
                                icon="iconamoon:clock-light"
                                class="w-15 h-15 text-rose-400 m-0 pointer-events-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                            >Duration</label
                        >
                        <div
                            class="flex h-[52px] w-full items-center gap-3 rounded-xl border border-neutral-200 px-4 shadow-sm bg-[#F5F5F5]"
                        >
                            <input
                                class="w-full h-full bg-transparent font-[Poppins] text-neutral-600 outline-none"
                                disabled
                                v-model="eventDurationDisplay"
                            />
                            <Icon
                                icon="iconamoon:clock-light"
                                class="w-6 h-6 text-neutral-400"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
                    >
                        Location <span class="text-red-600">*</span> </label
                    ><br />
                    <InputPill
                        v-model="eventLocation"
                        placeholder="Enter location"
                        class="w-full h-[52px] font-medium font-[Poppins] text-[20px] text-neutral-800 border border-neutral-200 rounded-[20px] px-5 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-300 transition"
                    />
                </div>
            </div>

            <div class="col-span-4 pl-8 pt-6">
                <h3 class="text-[17px] font-semibold text-neutral-800 mb-2">
                    Upload attachments
                </h3>
                <p class="text-sm text-neutral-500 mb-4">
                    Drag and drop document to your support task
                </p>

                <div
                    class="group relative rounded-2xl border-2 border-dashed border-rose-300 bg-rose-50 p-6 transition-all hover:border-rose-400"
                    :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="onDrop"
                >
                    <div v-if="filesNew.length > 0" class="mb-4 space-y-2">
                        <div
                            v-for="(item, index) in filesNew"
                            :key="index"
                            class="w-full flex items-center justify-between rounded-2xl bg-white border border-neutral-200 px-4 py-3 shadow-sm"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-md bg-red-600"
                                >
                                    <Icon
                                        icon="mdi:file"
                                        class="h-5 w-5 text-white"
                                    />
                                </div>
                                <div class="truncate">
                                    <span
                                        class="block truncate text-[14px] font-medium text-neutral-800"
                                    >
                                        {{ item.name }}
                                    </span>
                                    <span class="text-xs text-neutral-500">
                                        {{ prettySize(item.size) }}
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 hover:text-red-600 hover:bg-neutral-100 transition"
                                @click="removeFile(index)"
                                aria-label="Remove file"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-center justify-center text-center min-h-[200px]"
                    >
                        <Icon
                            icon="entypo:upload-to-cloud"
                            class="w-16 h-16 mb-3 text-rose-400"
                        />
                        <p class="text-[15px] font-medium text-neutral-800">
                            Choose a file or drag &amp; drop it here
                        </p>
                        <p class="mt-1 text-sm text-neutral-600">
                            pdf, txt, docx, jpeg, xlsx
                        </p>
                    </div>

                    <div class="flex justify-center mt-6">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-[12px] border border-rose-500 px-4 py-1.5 text-sm text-rose-700 hover:bg-rose-50 active:bg-rose-100 transition"
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

        <div class="px-6 mt-8">
            <h3
                class="text-neutral-800 font-semibold font-[Poppins] text-[15px] mb-4"
            >
                Add Guest
            </h3>

            <div
                class="grid gap-3 mb-4 md:grid-cols-[1fr,165px,165px,165px,165px] items-center"
            >
                <SearchBar
                    v-model="searchDraft"
                    class="w-full"
                    placeholder="Search ID / Name"
                    @search="applySearch"
                />

                <select
                    v-model="filtersDraft.empId"
                    @change="applySearch"
                    class="filter-select w-full border border-neutral-300 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 outline-none"
                >
                    <option value="">Company ID</option>
                    <option v-for="id in empIdOptions" :key="id" :value="id">
                        {{ id }}
                    </option>
                </select>

                <select
                    v-model="filtersDraft.department"
                    @change="applySearch"
                    class="filter-select w-full border border-neutral-300 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 outline-none"
                >
                    <option value="">Department</option>
                    <option v-for="d in departments" :key="d" :value="d">
                        {{ d }}
                    </option>
                </select>

                <select
                    v-model="filtersDraft.team"
                    @change="applySearch"
                    class="filter-select w-full border border-neutral-300 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 outline-none"
                >
                    <option value="">Team</option>
                    <option v-for="t in teams" :key="t" :value="t">
                        {{ t }}
                    </option>
                </select>

                <select
                    v-model="filtersDraft.position"
                    @change="applySearch"
                    class="filter-select w-full border border-neutral-300 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-rose-300 outline-none"
                >
                    <option value="">Position</option>
                    <option v-for="p in positions" :key="p" :value="p">
                        {{ p }}
                    </option>
                </select>
            </div>

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
                <template #empty>ไม่พบข้อมูลพนักงาน</template>
            </DataTable>
        </div>

        <div class="mt-8 px-6 w-full flex justify-between items-center">
            <CancelButton size="md" :disabled="saving" @click="onCancel">
                Cancel
            </CancelButton>

            <button
                type="button"
                @click="saveEvent"
                :disabled="saving"
                class="inline-flex items-center justify-center gap-2 rounded-[20px] px-6 py-2 bg-green-600 text-white font-semibold hover:bg-green-700 h-[45px] min-w-[140px] transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                <span>{{ saving ? "Saving..." : "Create" }}</span>
            </button>
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
import SearchBar from "@/components/SearchBar.vue";
import { Icon } from "@iconify/vue";
import DataTable from "@/components/DataTable.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";

export default {
    components: {
        InputPill,
        Icon,
        SearchBar,
        DataTable,
        CancelButton,
        ModalAlert,
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
            departments: [],
            teams: [],
            positions: [],

            // Files
            filesNew: [],
            dragging: false,

            // Guests & Table
            employees: [],
            loadingEmployees: false,
            search: "",
            searchDraft: "",
            filters: { department: "", team: "", position: "", empId: "" },
            filtersDraft: { department: "", team: "", position: "", empId: "" },
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
        empIdOptions() {
            return [
                ...new Set(this.employees.map((e) => e.emp_id).filter(Boolean)),
            ].sort();
        },
        filteredEmployees() {
            const searchData = (this.search || "").toLowerCase();
            return this.employees.filter((employee) => {
                const matchText =
                    !searchData ||
                    `${employee.emp_id} ${employee.emp_firstname} ${employee.emp_lastname} ${employee.nickname}`
                        .toLowerCase()
                        .includes(searchData);
                const matchDept =
                    !this.filters.department ||
                    employee.department === this.filters.department;
                const matchTeam =
                    !this.filters.team || employee.team === this.filters.team;
                const matchPos =
                    !this.filters.position ||
                    employee.position === this.filters.position;
                const matchEmpId =
                    !this.filters.empId ||
                    employee.emp_id === this.filters.empId;

                return (
                    matchText &&
                    matchDept &&
                    matchTeam &&
                    matchPos &&
                    matchEmpId
                );
            });
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
        search() {
            this.page = 1;
        },
        filters: {
            deep: true,
            handler() {
                this.page = 1;
            },
        },
        perPage() {
            this.page = 1;
        },
    },
    mounted() {
        this.fetchInfo();
    },
    methods: {
        // โหลดข้อมูล Categories และ Employees
        async fetchInfo() {
            try {
                this.loadingEmployees = true;
                const res = await axios.get("/event-info"); // ใช้ endpoint นี้เพื่อดึง metadata ทั้งหมด
                const data = res.data || {};

                this.selectCategory = data.categories || [];
                this.departments = (data.departments || []).map(
                    (d) => d.dpm_name
                );
                this.teams = (data.teams || []).map((t) => t.tm_name);
                this.positions = (data.positions || []).map((p) => p.pst_name);

                this.employees = (data.employees || []).map((e) => ({
                    id: e.id,
                    emp_id: e.emp_id || "",
                    emp_firstname: e.emp_firstname || "",
                    emp_lastname: e.emp_lastname || "",
                    nickname: e.emp_nickname || "",
                    department: e.department_name || "",
                    team: e.team_name || "",
                    position: e.position_name || "",
                }));
            } catch (err) {
                console.error("Error fetching info:", err);
            } finally {
                this.loadingEmployees = false;
            }
        },

        // Calculation
        calDuration() {
            if (!this.eventTimeStart || !this.eventTimeEnd) {
                this.eventDurationDisplay = "";
                this.eventDurationMinutes = 0;
                return;
            }
            const [sh, sm] = this.eventTimeStart.split(":").map(Number);
            const [eh, em] = this.eventTimeEnd.split(":").map(Number);
            let diff = eh * 60 + em - (sh * 60 + sm);
            if (diff < 0) diff += 24 * 60; // ข้ามวัน

            this.eventDurationMinutes = Math.max(0, diff);
            const h = Math.floor(diff / 60);
            const m = diff % 60;

            if (m === 0) this.eventDurationDisplay = `${h} Hour`;
            else if (h === 0) this.eventDurationDisplay = `${m} Min`;
            else this.eventDurationDisplay = `${h} Hour ${m} Min`;
        },

        // Files
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

        // Table Action
        onUpdateSelected(ids) {
            this.selectedIds = new Set(ids);
        },
        applySearch() {
            this.search = this.searchDraft;
            this.filters = { ...this.filtersDraft };
            this.page = 1;
        },

        // Action
        onCancel() {
            if (this.filesNew.length || this.eventTitle) {
                if (!confirm("Discard unsaved changes?")) return;
            }
            this.$router.back();
        },

        async saveEvent() {
            // Validation basic
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
                        // Mapping ให้ตรงกับ EventController::store
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
                        ); // ส่งเป็นนาที
                        formData.append("event_location", this.eventLocation);

                        // Files
                        this.filesNew.forEach((f) =>
                            formData.append("attachments[]", f)
                        );

                        // Guests
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
                ...cfg,
            };
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
.filter-select {
    background-color: white;
}
/* ซ่อนเคอร์เซอร์สำหรับช่องเวลา */
.caret-transparent {
    caret-color: transparent;
}
</style>
