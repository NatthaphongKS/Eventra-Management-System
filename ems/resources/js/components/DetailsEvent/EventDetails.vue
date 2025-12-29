<!-- /**
 * ชื่อไฟล์: EventDetails.vue
 * คำอธิบาย: Component สำหรับแสดงรายละเอียดกิจกรรม (Event Details)
 * Http request: GET /event/:id
 * Input: id (รหัสกิจกรรม)
 * Output: JSON Object ของ Event พร้อมข้อมูลรายละเอียด
 * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
 * วันที่จัดทำ/แก้ไข: 2025-12-28
 */ -->
<template>
    <div class="p-6" @pointerdown.capture="onRootPointer">
        <div class="mb-6 flex items-center gap-3">
            <h2 class="text-3xl font-semibold">Details Event</h2>
        </div>

        <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <BaseReadonlyField label="Event Title" :value="form.title" />
                    <BaseReadonlyField label="Category" :value="categoryDisplay" icon="mdi:chevron-down" />
                </div>

                <div>
                    <label class="mb-1 block text-xl font-medium text-neutral-800">Event Details</label>
                    <div class="flex w-full items-start gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
                        <textarea :value="form.details" rows="4" disabled class="w-full resize-none bg-transparent text-neutral-400 outline-none text-md font-medium"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <BaseReadonlyField label="Date" :value="formatDate(form.date)" icon="stash:data-date-solid" />
                    
                    <div>
                        <label class="mb-1 block text-xl font-medium text-neutral-800">Time</label>
                        <div class="flex w-full items-center justify-between rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
                            <div class="flex items-center gap-3 ">
                                <span class="w-15 text-center text-neutral-400 font-medium text-md">{{ toThaiTime(form.timeStart) }}</span>
                                <span class="select-none text-slate-400">:</span>
                                <span class="w-15 text-center text-neutral-400 font-medium text-md">{{ toThaiTime(form.timeEnd) }}</span>
                            </div>
                            <span class="text-neutral-400"><Icon icon="mdi:clock-outline" class="h-6 w-6" /></span>
                        </div>
                    </div>

                    <BaseReadonlyField 
                        label="Duration" 
                        :value="calculateDuration(form.timeStart, form.timeEnd, form.duration)" 
                        icon="lucide:clock-fading" 
                    />
                </div>

                <BaseReadonlyField label="Location" :value="form.location" />
            </div>

            <div>
                <label class="mb-1 block text-xl font-medium text-neutral-800">Upload attachments</label>
                <p class="mb-2 text-xs text-neutral-400">Drag and drop document to your support task</p>

                <div class="flex min-h-[400px] flex-col gap-2 rounded-[20px] border border-dashed border-neutral-400 bg-neutral-100 p-4 ">
                    <div v-if="files.length === 0" class="grid flex-1 place-items-center text-neutral-400 text-md">ไม่มีไฟล์แนบ</div>
                    <div v-else class="flex flex-col gap-2">
                        <div v-for="(f, i) in files" :key="i" class="flex h-[60px] w-full items-center gap-3 rounded-2xl border border-neutral-400 bg-white px-3 p-2.5 text-neutral-400 font-medium">
                            <Icon icon="basil:file-solid" class="h-10 w-10" />
                            <span class="truncate text-md text-gray-400">{{ f.name }}</span>
                            <div class="ml-auto"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-3xl font-semibold mb-4">Add Guest</h3>
            
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <div class="flex flex-1 items-center gap-2 min-w-[200px]">
                    <input
                        v-model.trim="searchInput"
                        @keyup.enter="doSearch"
                        class="w-full rounded-2xl border border-neutral-200 bg-white px-3 py-2.5 text-md font-medium text-neutral-400 placeholder:text-red-300 outline-none"
                        placeholder="Search ID / Name / Nickname"
                    />
                    <button @click="doSearch" class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-rose-700 text-white hover:opacity-90 active:opacity-100">
                        <Icon icon="mdi:magnify" class="h-8 w-8" />
                    </button>
                </div>

                <BaseFilterDropdown label="Company" :options="uniqueCompanies" v-model="companySel" @update:modelValue="onFilterChange" />
                <BaseFilterDropdown label="Department" :options="uniqueDepartments" v-model="deptSel" @update:modelValue="onFilterChange" />
                <BaseFilterDropdown label="Team" :options="uniqueTeams" v-model="teamSel" @update:modelValue="onFilterChange" />
                <BaseFilterDropdown label="Position" :options="uniquePositions" v-model="posSel" @update:modelValue="onFilterChange" />
            </div>

            <DataTable
                :rows="pagedRows"
                :columns="guestsTableColumns"
                :loading="loading"
                :total-items="filteredRows.length"
                v-model:page="page"
                v-model:pageSize="pageSize"
                row-key="id"
                :show-row-number="true"
                :rowClass="rowClassFn"
                class="mt-2"
            >
                <template #header-_select>
                    <div class="w-10 text-left"><input type="checkbox" class="accent-rose-600 opacity-60" disabled /></div>
                </template>
                <template #cell-_select="{ row }">
                    <input type="checkbox" class="accent-rose-700" :checked="selectedIds.has(row.id)" disabled />
                </template>
                <template #footer-info="{ from, to, total }">
                    <span>แสดง</span>
                    <div class="relative inline-block">
                        <select class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200"
                            :value="pageSize" @change="pageSize = +$event.target.value">
                            <option v-for="opt in [10,20,30,50]" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" /></svg>
                    </div>
                    <span>{{ from }}-{{ to }} จาก {{ total }} รายการ</span>
                </template>
                <template #empty>No data</template>
            </DataTable>

            <div class="mt-6">
                <button class="rounded-2xl bg-neutral-400 px-5 py-2 text-white" @click="onBack">← Back</button>
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
import BaseFilterDropdown from "@/components/DetailsEvent/BaseFilterDropdown.vue";
import BaseReadonlyField from "@/components/DetailsEvent/BaseReadonlyField.vue";

axios.defaults.baseURL = "/api";
axios.defaults.withCredentials = true;

export default {
    name: "EventDetailCard",
    components: { Icon, DataTable, BaseFilterDropdown, BaseReadonlyField },
    props: { id: { type: [String, Number], required: true } },
    data() {
        return {
            loading: false, error: "", files: [], Categories: [], idToCat: {},
            rows: [], selectedIds: new Set(),
            page: 1, pageSize: 10,
            form: {
                title: "", categoryId: "", categoryName: "", details: "", 
                date: "", timeStart: "", timeEnd: "", duration: "", location: "", status: "",
            },
            searchInput: "", searchQuery: "",
            companySel: "", deptSel: "", teamSel: "", posSel: "",
            guestsTableColumns: [
                { key: "_select", label: "", class: "w-10" },
                { key: "codeDisplay", label: "ID", class: "text-left" },
                { key: "name", label: "Name", class: "text-left" },
                { key: "nick", label: "Nickname", class: "text-left" },
                { key: "department", label: "Department", class: "text-left" },
                { key: "team", label: "Team", class: "text-left" },
                { key: "position", label: "Position", class: "text-left" },
            ],
        };
    },
    computed: {
        categoryDisplay() { 
            if (this.form.categoryName) return String(this.form.categoryName);
            const id = this.form.categoryId;
            if (!id) return "";
            const rec = this.idToCat[String(id)];
            const guessed = this.form.category?.name ?? this.form.cat_name ?? "";
            return guessed || (rec ? rec.name : `#${id}`);
        },
        
        uniqueDepartments() { return [...new Set(this.rows.map(r => r.department).filter(Boolean))].sort(); },
        uniqueTeams() { return [...new Set(this.rows.map(r => r.team).filter(Boolean))].sort(); },
        uniquePositions() { return [...new Set(this.rows.map(r => r.position).filter(Boolean))].sort(); },
        uniqueCompanies() {
            const vals = new Set();
            for (const r of this.rows) {
                if (r.companyAbbr) vals.add(r.companyAbbr);
            }
            return Array.from(vals).sort().map(val => ({ label: val, value: val }));
        },
        filteredRows() {
            const q = (this.searchQuery || "").toLowerCase();
            return this.rows.filter(r => {
                const hitQ = !q || [r.codeDisplay, r.code, r.name, r.nick].some(v => String(v || "").toLowerCase().includes(q));
                const hitCompany = !this.companySel || r.companyAbbr === this.companySel;
                const hitDept = !this.deptSel || r.department === this.deptSel;
                const hitTeam = !this.teamSel || r.team === this.teamSel;
                const hitPos = !this.posSel || r.position === this.posSel;
                return hitQ && hitCompany && hitDept && hitTeam && hitPos;
            });
        },
        pagedRows() {
             const start = (this.page - 1) * this.pageSize;
             return this.filteredRows.slice(start, start + this.pageSize);
        }
    },
    async created() {
        await Promise.all([this.fetchCategories(), this.fetchEvent(), this.fetchEmployees(), this.fetchGuestSelection()]);
    },
    methods: {
        async fetchCategories() {
            try {
                const r = await axios.get('/categoriesAll', { params: { withTrashed: 1, includeInactive: 1 } });
                const arr = Array.isArray(r.data) ? r.data : (r.data?.data ?? []);
                this.Categories = arr.map(this._normCat);
                this.idToCat = {};
                for (const cat of this.Categories) this.idToCat[String(cat.id)] = cat;
            } catch (e) { console.warn('fetchCategories failed', e); }
        },
        _normCat(c, i) {
            const id = c.id ?? c.categoryId ?? i + 1;
            const name = c.name ?? c.cat_name ?? c.title ?? '';
            return { id: String(id), name: String(name) };
        },
        async fetchEvent() { 
             try {
                this.loading = true;
                if (!this.id) { this.error = "ไม่พบรหัสอีเวนต์"; return; }
                const res = await axios.get(`/event/${this.id}`);
                const item = Array.isArray(res.data) ? res.data[0] : (res.data?.data ?? res.data);
                if (item) {
                    this.form.title = item.evn_title ?? item.title ?? "";
                    this.form.categoryId = item.evn_category_id ?? item.category_id ?? "";
                    this.form.categoryName = item.category?.cat_name ?? item.category_name ?? item.cat_name ?? item.category?.name ?? "";
                    this.form.details = item.evn_description ?? item.details ?? "";
                    this.form.date = (item.evn_date ?? item.date ?? "").slice(0, 10);
                    this.form.timeStart = (item.evn_timestart ?? item.timeStart ?? "").slice(0, 5);
                    this.form.timeEnd = (item.evn_timeend ?? item.timeEnd ?? "").slice(0, 5);
                    this.form.duration = item.evn_duration ?? item.duration ?? "";
                    this.form.location = item.evn_location ?? item.location ?? "";
                }
             } catch(e) { this.error = "โหลดข้อมูลไม่สำเร็จ"; } finally { this.loading = false; }
        },
        async fetchEmployees() {
            try {
                const r = await axios.get("get-employees");
                const arr = Array.isArray(r.data) ? r.data : (r.data?.data || []);
                this.rows = arr.map((e, idx) => {
                    const rawId = String(e.emp_id || "").trim();
                    let abbr = (e.company_abbr || e.company_code || "").toUpperCase().trim();
                    if (!abbr) {
                        const match = rawId.match(/^([A-Za-z]+)/); 
                        if (match) abbr = match[1].toUpperCase();
                    }
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
                        companyId: e.company_id || ""
                    };
                });
            } catch (err) { this.rows = []; }
        },
        async fetchGuestSelection() {
            try {
                if (!this.id) return;
                const r = await axios.get(`events/${this.id}/connects`);
                const ids = r.data?.employee_ids || r.data || [];
                this.selectedIds = new Set(ids.map(n => Number(n)));
            } catch (e) { this.selectedIds = new Set(); }
        },
        rowClassFn(row) { return this.selectedIds.has(row.id) ? 'row-selected' : ''; },

        /**
         * ชื่อฟังก์ชัน: calculateDuration 
         * คำอธิบาย: คำนวณระยะเวลาจาก TimeStart/End เพื่อความแม่นยำ (แทนการใช้ duration จาก DB ที่อาจไม่ตรง)
         */
        calculateDuration(start, end, fallbackDuration) {
            // ถ้ามีเวลาเริ่มและจบครบถ้วน ให้คำนวณเองเลย
            if (start && end) {
                const [startH, startM] = start.split(':').map(Number);
                const [endH, endM] = end.split(':').map(Number);
                
                if (!isNaN(startH) && !isNaN(startM) && !isNaN(endH) && !isNaN(endM)) {
                    const startTotal = startH * 60 + startM;
                    const endTotal = endH * 60 + endM;
                    let diff = endTotal - startTotal;
                    
                    // กรณีข้ามวัน (เช่น เริ่ม 23:00 จบ 01:00)
                    if (diff < 0) diff += 24 * 60; 

                    const h = Math.floor(diff / 60);
                    const m = diff % 60;

                    let res = `${h} Hour`;
                    if (m > 0) {
                        res += ` ${m} Min${m > 1 ? '' : ''}`;
                    }
                    return res;
                }
            }
            
            // ถ้าไม่มีเวลา ให้ใช้ Logic เดิม (Fallback)
            return this.formatDurationFallback(fallbackDuration);
        },

        // Logic เดิมสำหรับแปลงตัวเลข Duration (ใช้เป็น Fallback)
        formatDurationFallback(v) {
            const n = Number(v);
            if (!Number.isFinite(n)) return v;
            const h = Math.floor(n); // สมมติว่า DB เก็บเป็น Hour
            const m = Math.round((n - h) * 60); // เศษทศนิยมเป็นนาที
            let res = `${h} Hour`;
            if (m > 0) res += ` ${m} Minute${m > 1 ? 's' : ''}`;
            return res;
        },

        formatDate(dateStr) {
            if (!dateStr) return "";
            const [y, m, d] = dateStr.split('-');
            if (y && m && d) return `${d}/${m}/${y}`;
            return dateStr;
        },

        toThaiTime(v) { return v ? `${v} น.` : ""; },
        onRootPointer(e) {
            const insideDropdown = e.target.closest('[data-dd]');
            if (!insideDropdown) { }
        },
        onBack() { this.$router ? this.$router.back() : this.$emit('back'); },
        doSearch() { this.searchQuery = this.searchInput.trim(); this.page = 1; },
        onFilterChange() { this.page = 1; }
    },
};
</script>

<style scoped>
:deep(tr.row-selected) > * { background-color: #d4d4d4 !important; }
:deep(tr.row-selected input[type="checkbox"]) { opacity: 1; cursor: not-allowed; }
</style>