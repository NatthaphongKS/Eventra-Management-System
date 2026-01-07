<template>
    <div class="font-[Poppins] pb-20" @pointerdown.capture="onRootPointer">
        <div class="mb-8 flex items-center gap-3">
            <h2 class="text-2xl font-semibold text-neutral-800">
                Create Event
            </h2>
        </div>

        <div class="grid grid-cols-12 gap-8 border-b border-neutral-100 pb-10 mb-10">
            <div class="col-span-12 lg:col-span-8">
                <h3 class="text-xl font-semibold text-neutral-800 mb-6">Event Details</h3>

                <div class="grid md:grid-cols-[1fr_240px] gap-6 items-start">
                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                            Event Title <span class="text-red-600">*</span>
                        </label>
                        <InputPill
                            v-model="eventTitle"
                            placeholder="Enter event title"
                            :class="[
                                'w-full h-[52px] font-medium text-[16px] text-neutral-800 border rounded-[20px] px-5 focus:outline-none transition',
                                'placeholder:text-red-300',
                                errors.eventTitle ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                            ]"
                            @input="errors.eventTitle = false"
                        />
                        <p v-if="errors.eventTitle" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                    </div>

                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                            Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select
                                v-model="eventCategoryId"
                                :class="[
                                    'appearance-none border rounded-[20px] px-[20px] w-full h-[52px] font-medium focus:outline-none transition bg-white cursor-pointer',
                                    eventCategoryId ? 'text-neutral-800' : 'text-red-300',
                                    errors.eventCategoryId ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                                ]"
                                @change="errors.eventCategoryId = false"
                            >
                                <option value="" disabled selected>Select</option>
                                <option v-for="cat in selectCategory" :key="cat.id" :value="cat.id" class="text-neutral-800">
                                    {{ cat.cat_name }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <Icon icon="mdi:chevron-down" class="h-6 w-6 text-red-300" />
                            </div>
                        </div>
                        <p v-if="errors.eventCategoryId" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                        Event Description <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        v-model.trim="eventDescription"
                        placeholder="Write some description... (255 words)"
                        :class="[
                            'w-full h-[160px] rounded-2xl p-5 font-medium text-neutral-800 focus:outline-none transition resize-none border',
                            'placeholder:text-red-300',
                            errors.eventDescription ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                        ]"
                        @input="errors.eventDescription = false"
                    ></textarea>
                    <p v-if="errors.eventDescription" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Date <span class="text-red-600">*</span></label>
                        <div class="relative group">
                            <div :class="['absolute inset-0 flex items-center justify-between px-[20px] font-medium rounded-2xl pointer-events-none z-10 bg-white border transition', eventDate ? 'text-neutral-800' : 'text-red-300', errors.eventDate ? 'border-red-500 bg-red-50' : 'border-neutral-200 group-focus-within:border-rose-400']">
                                <span>{{ formattedDateDisplay || "dd/mm/yy" }}</span>
                                <svg class="w-6 h-6 text-red-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M8.5 14a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m0 3.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0"/>
                                    <path fill="currentColor" fill-rule="evenodd" d="M8 3.25a.75.75 0 0 1 .75.75v.75h6.5V4a.75.75 0 0 1 1.5 0v.758q.228.006.425.022c.38.03.736.098 1.073.27a2.75 2.75 0 0 1 1.202 1.202c.172.337.24.693.27 1.073c.03.365.03.81.03 1.345v7.66c0 .535 0 .98-.03 1.345c-.03.38-.098.736-.27 1.073a2.75 2.75 0 0 1-1.201 1.202c-.338.172-.694.24-1.074.27c-.365.03-.81.03-1.344.03H8.17c-.535 0-.98 0-1.345-.03c-.38-.03-.736-.098-1.073-.27a2.75 2.75 0 0 1-1.202-1.2c-.172-.338-.24-.694-.27-1.074c-.03-.365-.03-.81-.03-1.344V8.67c0-.535 0-.98.03-1.345c.03-.38.098-.736.27-1.073A2.75 2.75 0 0 1 5.752 5.05c.337-.172.693-.24 1.073-.27q.197-.016.425-.022V4A.75.75 0 0 1 8 3.25m10.25 7H5.75v6.05c0 .572 0 .957.025 1.252c.023.288.065.425.111.515c.12.236.311.427.547.547c.09.046.227.088.514.111c.296.024.68.025 1.253.025h7.6c.572 0 .957 0 1.252-.025c.288-.023.425-.065.515-.111a1.25 1.25 0 0 0 .547-.547c.046-.09.088-.227.111-.515c.024-.295.025-.68.025-1.252zM10.5 7a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <input
                                type="date"
                                v-model="eventDate"
                                :min="minDate"
                                class="relative w-full h-[52px] rounded-2xl px-[20px] opacity-0 z-20 cursor-pointer"
                                @click="$event.target.showPicker()"
                                @change="errors.eventDate = false"
                            />
                        </div>
                        <p v-if="errors.eventDate" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                    </div>

                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Time <span class="text-red-600">*</span></label>
                        <div :class="['flex h-[52px] w-full items-center rounded-2xl border px-2 shadow-sm bg-white transition', errors.eventTime ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400']">
                            <div class="relative flex-1 flex items-center justify-center h-full overflow-hidden">
                                <span v-if="!eventTimeStart" class="absolute pointer-events-none text-red-300 text-[15px] font-medium z-10">Start</span>
                                <input
                                    type="time"
                                    v-model="eventTimeStart"
                                    step="300"
                                    :class="['time-input w-full bg-transparent text-[15px] font-medium outline-none text-center cursor-pointer caret-transparent z-20', eventTimeStart ? 'text-neutral-800' : 'text-transparent']"
                                    @click="$event.target.showPicker()"
                                    @change="errors.eventTime = false"
                                    @keydown.prevent
                                />
                            </div>
                            <span class="flex-none text-[16px] font-bold text-red-300 px-1">:</span>
                            <div class="relative flex-1 flex items-center justify-center h-full overflow-hidden">
                                <span v-if="!eventTimeEnd" class="absolute pointer-events-none text-red-300 text-[15px] font-medium z-10">End</span>
                                <input
                                    type="time"
                                    v-model="eventTimeEnd"
                                    step="300"
                                    :class="['time-input w-full bg-transparent text-[15px] font-medium outline-none text-center cursor-pointer caret-transparent z-20', eventTimeEnd ? 'text-neutral-800' : 'text-transparent']"
                                    @click="$event.target.showPicker()"
                                    @change="errors.eventTime = false"
                                    @keydown.prevent
                                />
                            </div>
                            <Icon icon="mdi:clock-outline" class="flex-none w-5 h-5 text-red-700 mr-2 pointer-events-none" />
                        </div>
                        <p v-if="errors.eventTime" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                    </div>

                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Duration</label>
                        <div class="relative flex h-[52px] w-full items-center justify-between rounded-2xl border border-neutral-200 px-5 shadow-sm bg-[#F9FAFB]">
                            <div :class="['font-medium text-[16px] z-10', eventDurationDisplay ? 'text-neutral-800' : 'text-neutral-400']">
                                {{ eventDurationDisplay || "Auto Fill" }}
                            </div>
                            <div v-if="!eventDurationDisplay" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="text-neutral-400 font-medium text-[16px] pl-10">Hour</span>
                            </div>
                            <div class="flex items-center z-10">
                                <Icon icon="lucide:clock-fading" class="w-6 h-6 text-neutral-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Location <span class="text-red-600">*</span></label>
                    <InputPill
                        v-model="eventLocation"
                        placeholder="Location/Building/Room Name"
                        :class="[
                            'w-full h-[52px] font-medium text-[16px] text-neutral-800 border rounded-[20px] px-5 focus:outline-none transition',
                            'placeholder:text-red-300',
                            errors.eventLocation ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                        ]"
                        @input="errors.eventLocation = false"
                    />
                    <p v-if="errors.eventLocation" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4">
                <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Upload attachments</label>
                <div class="group relative flex flex-col min-h-[300px] rounded-[24px] border-2 border-dashed border-rose-200 bg-rose-50/50 p-5 transition-all hover:border-rose-400" :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
                    <div v-if="filesNew.length > 0" class="flex flex-col gap-2 mb-4">
                        <div v-for="(item, index) in filesNew" :key="index" class="w-full flex items-center justify-between rounded-2xl bg-white border border-rose-100 px-3 py-2.5 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <Icon icon="basil:file-solid" class="h-6 w-6 text-rose-600" />
                                <div class="truncate">
                                    <span class="block truncate text-sm font-medium text-neutral-800">{{ item.name }}</span>
                                    <span class="text-xs text-rose-500">{{ prettySize(item.size) }}</span>
                                </div>
                            </div>
                            <button type="button" @click="removeFile(index)"><Icon icon="mdi:close" class="h-4 w-4 text-neutral-400" /></button>
                        </div>
                    </div>
                    <div v-else class="flex flex-1 flex-col items-center justify-center text-center">
                        <Icon icon="entypo:upload-to-cloud" class="h-8 w-8 text-rose-500 mb-2" />
                        <p class="text-sm font-medium text-neutral-800">Choose a file or drag & drop it here</p>
                    </div>
                    <div class="mt-auto flex justify-center pt-4">
                        <button type="button" class="rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 shadow-sm" @click="pickFiles">Browse files</button>
                    </div>
                    <input ref="fileInput" type="file" multiple class="hidden" accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
                </div>
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-3xl font-semibold text-neutral-800 mb-4">Add Guest</h3>

            <h4 class="text-xl font-medium text-neutral-800 mb-3">Search</h4>
            <div class="flex flex-wrap items-center gap-4 w-full">
                <div class="flex items-center gap-2 flex-1 min-w-[320px]">
                    <div class="relative w-full">
                        <input
                            v-model="searchRaw"
                            type="text"
                            placeholder="Search ID / Name / Nickname"
                            class="w-full h-[48px] rounded-[30px] border border-neutral-200 px-6 text-[15px] text-neutral-800 placeholder:text-rose-300 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200 transition bg-white"
                            @keyup.enter="performSearch"
                        />
                    </div>
                    <button
                        type="button"
                        class="flex-none grid place-items-center w-[48px] h-[48px] rounded-full bg-[#B91C1C] text-white hover:bg-red-800 transition shadow-sm"
                        @click="performSearch"
                    >
                        <Icon icon="ic:baseline-search" class="w-6 h-6" />
                    </button>
                </div>

                <div class="flex flex-row flex-wrap items-center gap-2">
                    <EmployeeDropdown label="Company ID" v-model="selectedCompanyIds" :options="companyIdOptions" />
                    <EmployeeDropdown label="Department" v-model="selectedDepartmentIds" :options="departmentOptions" />
                    <EmployeeDropdown label="Team" v-model="selectedTeamIds" :options="teamOptions" />
                    <EmployeeDropdown label="Position" v-model="selectedPositionIds" :options="positionOptions" />
                </div>
            </div>

            <div class="mt-8">
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
                        {{ (row.emp_firstname || "") + " " + (row.emp_lastname || "") }}
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
                <button
                    type="button"
                    @click="onCancel"
                    :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#C10008] text-white font-semibold hover:bg-red-700 w-[140px] h-[48px] transition shadow-sm"
                >
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white rotate-45" />
                    <span>Cancel</span>
                </button>
            </div>

            <div class="flex-none">
                <button
                    type="button"
                    @click="saveEvent"
                    :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#00A73D] text-white font-semibold hover:bg-green-700 w-[140px] h-[48px] transition shadow-sm"
                >
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                    <span>Create</span>
                </button>
            </div>
        </div>

        <ModalAlert
            v-model:open="showConfirmCreate"
            title="Confirm Creation"
            message="Are you sure you want to create this event?"
            type="confirm"
            :showCancel="true"
            @confirm="executeCreateEvent"
        />

        <ModalAlert
            v-model:open="showSuccessAlert"
            title="Success"
            message="New event has been created."
            type="success"
            :showCancel="false"
            @confirm="onSuccessConfirm"
        />

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

export default {
    components: { InputPill, Icon, DataTable, EmployeeDropdown, CancelButton, ModalAlert },
    data() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        return {
            eventTitle: "", eventCategoryId: "", eventDescription: "", eventDate: "", eventTimeStart: "", eventTimeEnd: "",
            eventDurationDisplay: "", eventDurationMinutes: 0, eventLocation: "",
            minDate: `${year}-${month}-${day}`,
            errors: { eventTitle: false, eventCategoryId: false, eventDescription: false, eventDate: false, eventTime: false, eventLocation: false },
            selectCategory: [], filesNew: [], dragging: false,
            employees: [], loadingEmployees: false,
            search: "",    // Filter value
            searchRaw: "", // V-model input value
            selectedCompanyIds: [], selectedDepartmentIds: [], selectedTeamIds: [], selectedPositionIds: [],
            companyIdOptions: [], departmentOptions: [], teamOptions: [], positionOptions: [],
            selectedIds: new Set(), page: 1, perPage: 10, saving: false,
            showConfirmCreate: false, showSuccessAlert: false,
        };
    },
    computed: {
        formattedDateDisplay() {
            if (!this.eventDate) return "";
            const [y, m, d] = this.eventDate.split("-");
            return `${d}/${m}/${y.slice(-2)}`;
        },
        columns() {
            return [
                { key: "emp_id", label: "ID", class: "text-left min-w-[100px]" },
                { key: "fullname", label: "Name", class: "text-left min-w-[200px]" },
                { key: "nickname", label: "Nickname", class: "text-left min-w-[100px]" },
                { key: "department", label: "Department", class: "text-left min-w-[180px]" },
                { key: "team", label: "Team", class: "text-left min-w-[140px]" },
                { key: "position", label: "Position", class: "text-left min-w-[240px]" }
            ];
        },
        filteredEmployees() {
            const q = (this.search || "").toLowerCase().trim();
            let list = this.employees;
            if (q) {
                list = list.filter(e =>
                    (e.emp_firstname || "").toLowerCase().includes(q) ||
                    (e.emp_id || "").toLowerCase().includes(q) ||
                    (e.nickname || "").toLowerCase().includes(q)
                );
            }
            if (this.selectedCompanyIds.length) list = list.filter(r => this.selectedCompanyIds.includes(r.companyId));
            if (this.selectedDepartmentIds.length) list = list.filter(r => this.selectedDepartmentIds.includes(r.department));
            if (this.selectedTeamIds.length) list = list.filter(r => this.selectedTeamIds.includes(r.team));
            if (this.selectedPositionIds.length) list = list.filter(r => this.selectedPositionIds.includes(r.position));
            return list;
        },
        pagedEmployees() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredEmployees.slice(start, start + this.perPage);
        },
        selectedIdsArr: { get() { return Array.from(this.selectedIds); }, set(arr) { this.selectedIds = new Set(arr); } }
    },
    watch: {
        eventTimeStart() { this.calDuration(); },
        eventTimeEnd() { this.calDuration(); }
    },
    mounted() { this.fetchInfo(); },
    methods: {
        performSearch() {
            this.search = this.searchRaw;
            this.page = 1;
        },
        calDuration() {
            if (!this.eventTimeStart || !this.eventTimeEnd) { this.eventDurationDisplay = ""; return; }
            const [sh, sm] = this.eventTimeStart.split(":").map(Number);
            const [eh, em] = this.eventTimeEnd.split(":").map(Number);
            let diff = (eh * 60 + em) - (sh * 60 + sm);
            if (diff < 0) diff += 24 * 60;
            this.eventDurationMinutes = diff;
            const h = Math.floor(diff / 60); const m = diff % 60;
            this.eventDurationDisplay = h > 0 ? `${h} Hour ${m} Min` : `${m} Min`;
        },
        saveEvent() {
            this.errors.eventTitle = !this.eventTitle;
            this.errors.eventCategoryId = !this.eventCategoryId;
            this.errors.eventDescription = !this.eventDescription;
            this.errors.eventDate = !this.eventDate;
            this.errors.eventTime = (!this.eventTimeStart || !this.eventTimeEnd);
            this.errors.eventLocation = !this.eventLocation;
            if (Object.values(this.errors).some(v => v)) return;
            this.showConfirmCreate = true;
        },
        async executeCreateEvent() {
            this.showConfirmCreate = false;
            this.saving = true;
            try {
                const formData = new FormData();
                formData.append("event_title", this.eventTitle.trim());
                formData.append("event_category_id", this.eventCategoryId);
                formData.append("event_description", this.eventDescription);
                formData.append("event_date", this.eventDate);
                formData.append("event_timestart", this.eventTimeStart);
                formData.append("event_timeend", this.eventTimeEnd);
                formData.append("event_duration", this.eventDurationMinutes);
                formData.append("event_location", this.eventLocation);
                this.filesNew.forEach(f => formData.append("attachments[]", f));
                this.selectedIds.forEach(id => formData.append("employee_ids[]", id));
                await axios.post("/event-save", formData, { headers: { "Content-Type": "multipart/form-data" } });
                this.showSuccessAlert = true;
            } catch (err) {
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
        async fetchInfo() {
            try {
                this.loadingEmployees = true;
                const res = await axios.get("/event-info");
                this.selectCategory = res.data?.categories || [];
                this.employees = (res.data?.employees || []).map(e => {
                    const rawId = String(e.emp_id || "").trim();
                    const prefixMatch = rawId.match(/^[A-Za-z]+/);
                    const companyPrefix = prefixMatch ? prefixMatch[0].toUpperCase() : "";
                    return {
                        id: e.id,
                        emp_id: e.emp_id || "",
                        emp_firstname: e.emp_firstname || "",
                        emp_lastname: e.emp_lastname || "",
                        nickname: e.emp_nickname || "",
                        department: e.department_name || "",
                        companyId: companyPrefix || e.company_id || "",
                        team: e.team_name || "",
                        position: e.position_name || ""
                    };
                });
                this.buildFilterOptions();
            } catch (err) { console.error(err); } finally { this.loadingEmployees = false; }
        },
        buildFilterOptions() {
            const toOpt = (arr) => [...new Set(arr.filter(Boolean))].sort().map(v => ({ label: v, value: v }));
            this.companyIdOptions = toOpt(this.employees.map(r => r.companyId));
            this.departmentOptions = toOpt(this.employees.map(r => r.department));
            this.teamOptions = toOpt(this.employees.map(r => r.team));
            this.positionOptions = toOpt(this.employees.map(r => r.position));
        },
        pickFiles() { this.$refs.fileInput.click(); },
        onPick(e) { this.addFiles([...e.target.files]); e.target.value = ""; },
        onDrop(e) { this.dragging = false; this.addFiles([...e.dataTransfer.files]); },
        addFiles(files) { files.forEach(f => { if (f.size <= 50 * 1024 * 1024) this.filesNew.push(f); }); },
        removeFile(idx) { this.filesNew.splice(idx, 1); },
        prettySize(byte) { return (byte / (1024 * 1024)).toFixed(2) + " MB"; },
        onUpdateSelected(ids) { this.selectedIds = new Set(ids); },
        onCancel() { this.$router.back(); },
        onRootPointer() {}
    }
};
</script>

<style scoped>
input[type="time"]::-webkit-datetime-edit,
input[type="time"]::-webkit-datetime-edit-fields-wrapper,
input[type="time"]::-webkit-datetime-edit-hour-field,
input[type="time"]::-webkit-datetime-edit-minute-field,
input[type="time"]::-webkit-datetime-edit-text {
    color: inherit;
    padding: 0;
}
input[type="time"]::-webkit-datetime-edit-hour-field:focus,
input[type="time"]::-webkit-datetime-edit-minute-field:focus {
    background-color: transparent !important;
    color: inherit;
    outline: none;
}
input[type="time"]::selection {
    background: transparent;
}
input[type="time"]::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute; left: 0; top: 0; width: 100%; height: 100%; margin: 0; padding: 0; cursor: pointer; opacity: 0;
}
.caret-transparent {
    caret-color: transparent;
}
</style>
