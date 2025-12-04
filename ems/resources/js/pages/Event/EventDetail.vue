<!-- pages/EventDetail.vue -->
<template>
  <div class="p-6" @pointerdown.capture="onRootPointer">
    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <h2 class="text-3xl font-semibold">Details Event</h2>
    </div>

    <div class="grid grid-cols-1 gap-7 lg:grid-cols-3">
      <!-- Left -->
      <div class="space-y-6 lg:col-span-2">
        <!-- Row: Title + Category -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-xl font-medium text-neutral-800">Event Title</label>
            <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5 text-md font-medium">
              <input :value="form.title" disabled class="w-full bg-transparent text-neutral-400 outline-none" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xl font-medium text-neutral-800">Category</label>
            <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5 text-md font-medium">
              <!-- ใช้ computed เพื่อให้ขึ้นชื่อเสมอ (รองรับ soft-deleted) -->
              <input :value="categoryDisplay" disabled class="w-full bg-transparent text-neutral-400 outline-none text-md" />
              <span class="text-neutral-400">
                <Icon icon="mdi:chevron-down" class="h-6 w-6" />
              </span>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div>
          <label class="mb-1 block text-xl font-medium text-neutral-800">Event Details</label>
          <div class="flex w-full items-start gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
            <textarea :value="form.details" rows="4" disabled class="w-full resize-none bg-transparent text-neutral-400 outline-none text-md font-medium"></textarea>
          </div>
        </div>

        <!-- Date / Time / Duration -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-xl font-medium text-neutral-800">Date</label>
            <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
              <input :value="form.date" type="date" disabled class="w-full bg-transparent text-neutral-400 outline-none text-md font-medium" />
              <span class="text-neutral-400">
                <Icon icon="stash:data-date-solid" class="h-6 w-6" />
              </span>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xl font-medium text-neutral-800">Time</label>
            <div class="flex w-full items-center justify-between rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
              <div class="flex items-center gap-3 ">
                <span class="w-15 text-center text-neutral-400 font-medium text-md">
                  {{ toThaiTime(form.timeStart) }}
                </span>
                <span class="select-none text-slate-400">:</span>
                <span class="w-15 text-center text-neutral-400 font-medium text-md">
                  {{ toThaiTime(form.timeEnd) }}
                </span>
              </div>
              <span class="text-neutral-400">
                <Icon icon="mdi:clock-outline" class="h-6 w-6" />
              </span>
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xl font-medium text-neutral-800">Duration</label>
            <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
              <input :value="formatDuration(form.duration)" disabled class="w-full bg-transparent text-neutral-400 outline-none text-md font-medium" />
              <span class="text-slate-400">
                <Icon icon="lucide:clock-fading" class="h-6 w-6" />
              </span>
            </div>
          </div>
        </div>

        <!-- Location -->
        <div>
          <label class="mb-1 block text-xl font-medium text-neutral-800">Location</label>
          <div class="flex w-full items-center gap-2 rounded-2xl border border-neutral-400 bg-neutral-100 px-3 py-2.5">
            <input :value="form.location" disabled class="w-full bg-transparent text-neutral-400 outline-none text-md font-medium" />
          </div>
        </div>
      </div>

      <!-- Right: Attachments (view-only look) -->
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

    <!-- Guests -->
    <div class="mt-10">
      <h3 class="text-3xl font-semibold">Add Guest</h3>
      <h4 class="mb-1 block text-xl font-medium text-neutral-800">Search</h4>

      <!-- Search row -->
      <div class="mb-4 flex flex-wrap items-center gap-2">
        <input
          v-model.trim="searchInput"
          @keyup.enter="doSearch"
          class="min-w-[260px] flex-1 rounded-2xl border border-neutral-200 bg-white px-3 py-2.5 text-md font-medium text-neutral-400 placeholder:text-red-300 outline-none"
          placeholder="Search ID / Name / Nickname"
        />
        <button
          @click="doSearch"
          class="grid h-12 w-12 place-items-center rounded-full bg-rose-700 text-white hover:opacity-90 active:opacity-100"
          aria-label="search"
        >
          <Icon icon="mdi:magnify" class="h-10 w-10" />
        </button>

        <!-- Filters (compact custom dropdown pills) -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Company -->
          <div class="relative" data-dd @pointerdown.stop ref="ddCompanyWrap">
            <button
              type="button"
              @click="openWithSize('Company')"
              class="inline-flex items-center gap-1.5 rounded-2xl border px-3 py-2.5 text-md font-medium shadow-sm transition"
              :class="companySel ? 'border-rose-700 text-rose-700 bg-white' : 'border-neutral-400 text-neutral-800 bg-white hover:border-rose-300'"
            >
              <span>{{ companySelLabel }}</span>
              <Icon icon="mdi:chevron-down" class="h-6 w-6 transition-transform" :class="openCompany ? 'text-red-700 rotate-180' : 'text-rose-700'" />
            </button>

            <div
              v-if="openCompany"
              class="absolute z-20 mt-2 w-max rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-lg max-h-72 overflow-auto"
              :style="{ minWidth: dd.Company + 'px' }"
            >
              <button @click="resetFilter('company')" class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-rose-600 hover:text-white">Company</button>
              <button
                v-for="c in uniqueCompanies"
                :key="c.value"
                @click="pickFilter('company', c.value)"
                class="block w-full rounded-xl px-3 py-2 text-left text-sm"
                :class="c.value===companySel ? 'bg-rose-600 text-white' : 'hover:bg-rose-600 hover:text-white'"
              >
                {{ c.label }}
              </button>
            </div>
          </div>

          <!-- Department -->
          <div class="relative" data-dd @pointerdown.stop ref="ddDeptWrap">
            <button
              type="button"
              @click="openWithSize('Dept')"
              class="inline-flex items-center gap-1.5 rounded-2xl border px-3 py-2.5 text-md font-medium shadow-sm transition"
              :class="deptSel ? 'border-rose-700 text-rose-700 bg-white' : 'border-neutral-400 text-neutral-800 bg-white hover:border-rose-300'"
            >
              <span>{{ deptSelLabel }}</span>
              <Icon icon="mdi:chevron-down" class="h-6 w-6 transition-transform" :class="openDept ? 'text-red-700 rotate-180' : 'text-rose-700'" />
            </button>
            <div
              v-if="openDept"
              class="absolute z-20 mt-2 w-max rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-lg max-h-72 overflow-auto"
              :style="{ minWidth: dd.Dept + 'px' }"
            >
              <button @click="resetFilter('dept')" class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-rose-600 hover:text-white">Department</button>
              <button
                v-for="d in uniqueDepartments"
                :key="d"
                @click="pickFilter('dept', d)"
                class="block w-full rounded-xl px-3 py-2 text-left text-sm"
                :class="d===deptSel ? 'bg-rose-600 text-white' : 'hover:bg-rose-600 hover:text-white'"
              >
                {{ d }}
              </button>
            </div>
          </div>

          <!-- Team -->
          <div class="relative" data-dd @pointerdown.stop ref="ddTeamWrap">
            <button
              type="button"
              @click="openWithSize('Team')"
              class="inline-flex items-center gap-1.5 rounded-2xl border px-3 py-2.5 text-md font-medium shadow-sm transition"
              :class="teamSel ? 'border-rose-700 text-rose-700 bg-white' : 'border-neutral-400 text-neutral-800 bg-white hover:border-rose-300'"
            >
              <span>{{ teamSelLabel }}</span>
              <Icon icon="mdi:chevron-down" class="h-6 w-6 transition-transform" :class="openTeam ? 'text-red-700 rotate-180' : 'text-rose-700'" />
            </button>
            <div
              v-if="openTeam"
              class="absolute z-20 mt-2 w-max rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-lg max-h-72 overflow-auto"
              :style="{ minWidth: dd.Team + 'px' }"
            >
              <button @click="resetFilter('team')" class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-rose-600 hover:text-white">Team</button>
              <button
                v-for="t in uniqueTeams"
                :key="t"
                @click="pickFilter('team', t)"
                class="block w-full rounded-xl px-3 py-2 text-left text-sm"
                :class="t===teamSel ? 'bg-rose-600 text-white' : 'hover:bg-rose-600 hover:text-white'"
              >
                {{ t }}
              </button>
            </div>
          </div>

          <!-- Position -->
          <div class="relative" data-dd @pointerdown.stop ref="ddPosWrap">
            <button
              type="button"
              @click="openWithSize('Pos')"
              class="inline-flex items-center gap-1.5 rounded-2xl border px-3 py-2.5 text-md font-medium shadow-sm transition"
              :class="posSel ? 'border-rose-700 text-rose-700 bg-white' : 'border-neutral-400 text-neutral-800 bg-white hover:border-rose-300'"
            >
              <span>{{ posSelLabel }}</span>
              <Icon icon="mdi:chevron-down" class="h-6 w-6 transition-transform" :class="openPos ? 'text-red-700 rotate-180' : 'text-rose-700'" />
            </button>
            <div
              v-if="openPos"
              class="absolute z-20 mt-2 w-max rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-lg max-h-72 overflow-auto"
              :style="{ minWidth: dd.Pos + 'px' }"
            >
              <button @click="resetFilter('pos')" class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-rose-600 hover:text-white">Position</button>
              <button
                v-for="p in uniquePositions"
                :key="p"
                @click="pickFilter('pos', p)"
                class="block w-full rounded-xl px-3 py-2 text-left text-sm"
                :class="p===posSel ? 'bg-rose-600 text-white' : 'hover:bg-rose-600 hover:text-white'"
              >
                {{ p }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ใช้ DataTable -->
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
        <!-- หัวคอลัมน์ checkbox (read-only) -->
        <template #header-_select>
          <div class="w-10 text-left">
            <input type="checkbox" class="accent-rose-600 opacity-60" disabled />
          </div>
        </template>

        <!-- เซลล์ checkbox (read-only) -->
        <template #cell-_select="{ row }">
          <input
            type="checkbox"
            class="accent-rose-700"
            :checked="selectedIds.has(row.id)"
            disabled
          />
        </template>

        <!-- footer-info -->
        <template #footer-info="{ from, to, total }">
          <span>แสดง</span>
          <div class="relative inline-block">
            <select
              class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200"
              :value="pageSize"
              @change="pageSize = +$event.target.value"
            >
              <option v-for="opt in [10,20,30,50]" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </div>
          <span>{{ from }}-{{ to }} จาก {{ total }} รายการ</span>
        </template>

        <template #empty>No data</template>
      </DataTable>

      <!-- Back -->
      <div class="mt-6">
        <button class="rounded-2xl bg-neutral-400 px-5 py-2 text-white" @click="onBack">
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

axios.defaults.baseURL = "/api";
axios.defaults.withCredentials = true;
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

export default {
  name: "EventDetailsPretty",
  components: { Icon, DataTable },
  props: { id: { type: [String, Number], required: false } },
  data() {
    return {
      loading: false,
      error: "",
      files: [],
      Categories: [],        // list ทั้งหมด
      idToCat: {},           // map: id -> {id,name,inactive}

      rows: [],              // employees
      selectedIds: new Set(),

      // pagination (ใช้ร่วมกับ DataTable)
      page: 1,
      pageSize: 10,

      // event form (view-only)
      form: {
        title: "", categoryId: "", categoryName: "",
        details: "", date: "", timeStart: "", timeEnd: "",
        duration: "", location: "", status: "",
      },

      // search/filters
      searchInput: "",
      searchQuery: "",
      companySel: "",
      deptSel: "",
      teamSel: "",
      posSel: "",
      openCompany: false,
      openDept: false,
      openTeam: false,
      openPos: false,
      defaultPageSize: 10,
      openPageSize: false,
      dd: { Company: 160, Dept: 160, Team: 160, Pos: 160, PageSize: 160 },

      // คอลัมน์ DataTable
      guestsTableColumns: [
        { key: "_select",     label: "",           class: "w-10" },
        { key: "codeDisplay", label: "ID",         class: "text-left" },
        { key: "name",        label: "Name",       class: "text-left" },
        { key: "nick",        label: "Nickname",   class: "text-left" },
        { key: "department",  label: "Department", class: "text-left" },
        { key: "team",        label: "Team",       class: "text-left" },
        { key: "position",    label: "Position",   class: "text-left" },
      ],
    };
  },
  computed: {
    // ชื่อหมวดหมู่แบบ robust + รองรับ soft-deleted
    categoryDisplay() {
      // 1) API ให้ชื่อมาแล้ว
      if (this.form.categoryName) return String(this.form.categoryName);

      const id = this.form.categoryId;
      if (!id) return "";

      // 2) ค้นจากแผนที่
      const rec = this.idToCat[String(id)];

      // 3) เดาจากฟิลด์ nested
      const guessed =
        this.form.category?.name ??
        this.form.category?.category_name ??
        this.form.cat_name ??
        this.form.ctg_name ??
        "";
      if (guessed) return String(guessed);

      // 4) สุดท้าย แสดง #id
      return `#${id}`;
    },

    companySelLabel() {
      if (!this.companySel) return 'Company';
      const found = this.uniqueCompanies.find(c => c.value === this.companySel);
      return found ? found.label : this.companySel;
    },
    deptSelLabel() { return this.deptSel || 'Department'; },
    teamSelLabel() { return this.teamSel || 'Team'; },
    posSelLabel()  { return this.posSel  || 'Position'; },

    // master lists
    uniqueDepartments() {
      return [...new Set(this.rows.map(r => r.department).filter(Boolean))].sort();
    },
    uniqueTeams() {
      return [...new Set(this.rows.map(r => r.team).filter(Boolean))].sort();
    },
    uniquePositions() {
      return [...new Set(this.rows.map(r => r.position).filter(Boolean))].sort();
    },
    uniqueCompanies() {
      const vals = new Map();
      for (const r of this.rows) {
        const value = r.companyAbbr || r.companyId || r.companyName || "";
        if (!value) continue;
        const label = r.companyAbbr
          ? (r.companyName ? `${r.companyAbbr} — ${r.companyName}` : r.companyAbbr)
          : (r.companyId ? `${r.companyId} — ${r.companyName || ""}`.trim() : r.companyName);
        if (!vals.has(value)) vals.set(value, { value, label });
      }
      return Array.from(vals.values()).sort((a, b) => a.label.localeCompare(b.label));
    },

    // filter
    filteredRows() {
      const q = (this.searchQuery || "").toLowerCase();
      return this.rows.filter(r => {
        const hitQ =
          !q ||
          [r.codeDisplay, r.code, r.name, r.nick]
            .some(v => String(v || "").toLowerCase().includes(q));

        const hitCompany = !this.companySel ||
          this.companySel === r.companyAbbr ||
          this.companySel === r.companyId   ||
          this.companySel === r.companyName;

        const hitDept = !this.deptSel || r.department === this.deptSel;
        const hitTeam = !this.teamSel || r.team       === this.teamSel;
        const hitPos  = !this.posSel  || r.position   === this.posSel;

        return hitQ && hitCompany && hitDept && hitTeam && hitPos;
      });
    },

    // pagination
    pageCount() {
      return Math.max(1, Math.ceil(this.filteredRows.length / this.pageSize));
    },
    startIndex() {
      return (this.page - 1) * this.pageSize;
    },
    endIndex() {
      return Math.min(this.startIndex + this.pageSize, this.filteredRows.length);
    },
    pagedRows() {
      return this.filteredRows.slice(this.startIndex, this.endIndex);
    },
    rangeLabel() {
      if (this.filteredRows.length === 0) return "0 รายการ";
      return `${this.startIndex + 1}–${this.endIndex} จาก ${this.filteredRows.length} รายการ`;
    },
  },
  async created() {
    await this.fetchCategories();   // โหลดลิสต์ (พยายามรวม soft-deleted)
    await this.fetchEvent();        // ถ้าไม่พอจะ fallback ไปดึงเดี่ยว
    await this.fetchEmployees();
    await this.fetchGuestSelection();
  },
  watch: {
    "$route.params.id"(n, o) {
      if (n !== o) {
        this.fetchCategories();
        this.fetchEvent();
        this.fetchEmployees();
        this.fetchGuestSelection();
      }
    },
    pageSize() {
      this.page = 1;
    },
  },
  mounted() {
    document.addEventListener('click', this._onDoc);
  },
  beforeUnmount() {
    document.removeEventListener('click', this._onDoc);
  },
  methods: {
    /* -------------------- Categories -------------------- */
    _normCat(c, i) {
      const id = c.id ?? c.cat_id ?? c.category_id ?? c.categoryId ?? i + 1;
      const name = (
        c.name ?? c.cat_name ?? c.category_name ?? c.ctg_name ??
        c.name_th ?? c.name_en ?? c.title ?? c.cat_title ?? ''
      );
      // เดาสถานะ inactive จากฟิลด์ที่ใช้กันบ่อย
        const inactive = Boolean(
          c.cat_delete_status === 'inactive' || c.cat_deleted_at
           || c.deleted_at || c.is_deleted === 1 || c.active === 0 || c.status === 'inactive'
         );
      return { id: String(id), name: String(name), inactive, raw: c };
    },

    async fetchCategories() {
      try {
        // ปรับ params ให้ตรง backend ของคุณ เช่น ?withTrashed=1
        const r = await axios.get('/categoriesAll', { params: { withTrashed: 1, includeInactive: 1 } });
        const arr = Array.isArray(r.data) ? r.data : (r.data?.data ?? []);
        this.Categories = arr.map(this._normCat);
        // สร้าง map
        this.idToCat = {};
        for (const cat of this.Categories) this.idToCat[cat.id] = cat;
      } catch (e) {
        console.warn('fetchCategories failed', e);
        this.Categories = [];
        this.idToCat = {};
      }
    },

    // ดึงเดี่ยว (ใช้เมื่อ id ไม่อยู่ในลิสต์ เช่น soft-deleted แต่ backend ไม่คืนใน index)
    async ensureCategoryNameFor(id) {
      const key = String(id || '');
      if (!key || this.idToCat[key]) return;
      try {
        const r = await axios.get(`/categories/${key}`, { params: { withTrashed: 1 } });
        const raw = (r.data && r.data.data) ? r.data.data : r.data;
        const cat = this._normCat(raw, 0);
        this.idToCat[cat.id] = cat;
        if (!this.form.categoryName && String(this.form.categoryId) === cat.id) {
          this.form.categoryName = cat.name;
        }
      } catch (e) {
        console.warn('ensureCategoryNameFor failed for id=', key, e);
      }
    },

    /* -------------------- Event -------------------- */
    async fetchEvent() {
      try {
        this.loading = true;
        const id = this.id ?? this.$route?.params?.id;
        if (!id) { this.error = "ไม่พบรหัสอีเวนต์ในเส้นทาง (route)"; return; }

        const res = await axios.get(`/event/${id}`);
        let item = Array.isArray(res.data) ? res.data[0] : (res.data?.data ?? res.data);
        if (!item) { this.error = "ไม่พบข้อมูลอีเวนต์ใน response"; return; }

        this.form.title        = item.evn_title ?? item.title ?? "";
        this.form.categoryId   = item.evn_category_id ?? item.category_id ?? item.categoryId ?? item.cat_id ?? "";
        this.form.categoryName = (
          item.category_name ??
          item.cat_name ??
          item.ctg_name ??
          item.category?.name ??
          ""
        );
        this.form.details      = item.evn_description ?? item.details ?? "";
        this.form.date         = (item.evn_date ?? item.date ?? "").slice(0, 10);
        this.form.timeStart    = (item.evn_timestart ?? item.timeStart ?? "").slice(0, 5);
        this.form.timeEnd      = (item.evn_timeend ?? item.timeEnd ?? "").slice(0, 5);
        this.form.duration     = item.evn_duration ?? item.duration ?? "";
        this.form.location     = item.evn_location ?? item.location ?? "";
        this.form.status       = item.evn_status ?? item.status ?? "";

        this.files = item.evn_file === "have" ? [{ name: "Meeting information.pdf", size: 512000 }] : [];

        // Fallback: map จากลิสต์ถ้า API ไม่ส่งชื่อ
        if (!this.form.categoryName && this.form.categoryId && this.Categories.length) {
          const found = this.idToCat[String(this.form.categoryId)];
          if (found?.name) this.form.categoryName = found.name;
        }

        // ยังไม่มีชื่อ (มักเป็น soft-deleted) → ดึงเดี่ยว
        if (!this.form.categoryName && this.form.categoryId) {
          await this.ensureCategoryNameFor(this.form.categoryId);
        }
      } catch (e) {
        console.error(e);
        this.error = `โหลดข้อมูลล้มเหลว${e?.response?.status ? ` (status=${e.response.status})` : ""}`;
      } finally {
        this.loading = false;
      }
    },

    /* -------------------- Employees -------------------- */
    async fetchEmployees() {
      try {
        const r = await axios.get("get-employees");
        const prefixMap = { 1: "นาย", 2: "นาง", 3: "นางสาว" };
        const arr = Array.isArray(r.data) ? r.data : (r.data?.data || []);

        this.rows = arr.map((e, idx) => {
          const prefixText = e.emp_prefix ? (prefixMap[e.emp_prefix] || e.emp_prefix) : "";
          const fullName = [prefixText, e.emp_firstname, e.emp_lastname]
            .filter(Boolean).join(" ").replace(/\s+/g, " ").trim();

          const rawId = String(e.emp_id ?? "");
          const rawPrefixFromId = (rawId.match(/^[A-Za-z]+/) || [""])[0];
          const rawNumberFromId = rawId.replace(/^[A-Za-z]+/, "");

          const companyAbbr = (e.company_abbr || e.company_code || rawPrefixFromId || "").toUpperCase();
          const codePrefix  = companyAbbr || rawPrefixFromId || "";
          const codeNumber  = rawNumberFromId || String(e.emp_running || "");
          const codeDisplay = codePrefix ? `${codePrefix}${codeNumber}` : (rawId || codeNumber);

          return {
            id: e.id ?? idx + 1,
            code: rawId,
            codeDisplay,
            codePrefix,
            codeNumber,
            name: fullName,
            nick: e.emp_nickname ?? "",
            department: e.department_name || e.emp_department_id || "",
            team:       e.team_name       || e.emp_team_id       || "",
            position:   e.position_name   || e.emp_position_id   || "",
            companyAbbr:  companyAbbr,
            companyId:    e.company_id    || "",
            companyName:  e.company_name  || "",
          };
        });

        if (this.page > this.pageCount) this.page = this.pageCount;
      } catch (err) {
        console.error("fetchEmployees error", err);
        this.rows = [];
      }
    },

    /* -------------------- Connect (พนักงานที่เลือกใน event) -------------------- */
    async fetchGuestSelection() {
      try {
        const eventId = this.id ?? this.$route?.params?.id;
        if (!eventId) return;
        const r = await axios.get(`events/${eventId}/connects`);
        const ids = Array.isArray(r.data) ? r.data : (r.data?.employee_ids || []);
        this.selectedIds = new Set(ids.map(n => Number(n)));
      } catch (e) {
        console.error('fetchGuestSelection error', e);
        this.selectedIds = new Set();
      }
    },
    rowClassFn(row) {
      return this.selectedIds.has(row.id) ? 'row-selected' : '';
    },

    /* -------------------- Utils -------------------- */
    formatDuration(v) {
      if (v == null || v === "") return "";
      if (typeof v === "string" && /^\d{1,2}:\d{2}$/.test(v)) {
        const [hh, mm] = v.split(":").map(Number);
        return this._fmtHM(hh, mm);
      }
      const n = Number(v);
      if (!Number.isFinite(n)) return String(v);
      if (n >= 60) {
        const h = Math.floor(n / 60);
        const m = Math.round(n % 60);
        return this._fmtHM(h, m);
      }
      const h = Math.floor(n);
      const m = Math.round((n - h) * 60);
      return this._fmtHM(h, m);
    },
    _fmtHM(h, m) {
      const hh = h > 0 ? `${h} ${h === 1 ? "Hour" : "Hours"}` : "";
      const mm = m > 0 ? `${m} min` : "";
      return [hh, mm].filter(Boolean).join(" ") || "0 min";
    },
    prettySize(bytes) {
      if (!bytes && bytes !== 0) return "";
      const units = ["B","KB","MB","GB"]; let i = 0, val = bytes;
      while (val >= 1024 && i < units.length - 1) { val /= 1024; i++; }
      return `${val.toFixed(1)} ${units[i]}`;
    },
    toThaiTime(hhmm) {
      if (!hhmm) return "";
      const [hh, mm] = String(hhmm).split(":");
      if (Number.isNaN(+hh) || Number.isNaN(+mm)) return hhmm;
      const d = new Date(Date.UTC(1970, 0, 1, Number(hh), Number(mm), 0));
      const fmt = new Intl.DateTimeFormat("th-TH", {
        timeZone: "Asia/Bangkok", hour: "2-digit", minute: "2-digit", hour12: false,
      }).format(d);
      return `${fmt} น.`;
    },

    // --- pagination helper (เผื่อใช้ที่อื่น) ---
    goTo(p) {
      this.page = Math.min(Math.max(1, p), this.pageCount);
    },

    // --- UI ---
    onBack() {
      if (this.$router && this.$router.back) this.$router.back();
    },

    // --- Search trigger ---
    doSearch() {
      this.searchQuery = this.searchInput.trim();
      this.page = 1;
    },

    // --- Dropdown helpers (เมนูฟิลเตอร์) ---
    _onDoc(e) {
      const container = this.$el.querySelector('.mt-10'); // โซน Add Guest
      if (container && !container.contains(e.target)) {
        this.closeAllDropdowns();
      }
    },
    onRootPointer(e) {
      const insideDropdown = e.target.closest('[data-dd]');
      if (!insideDropdown) this.closeAllDropdowns();
    },
    openWithSize(what) {
      this.openCompany = this.openDept = this.openTeam = this.openPos = this.openPageSize = false;
      this.$nextTick(() => {
        const refMap = {
          Company: 'ddCompanyWrap', Dept: 'ddDeptWrap',
          Team: 'ddTeamWrap', Pos: 'ddPosWrap', PageSize: 'ddPageSizeWrap'
        };
        const el = this.$refs[refMap[what]];
        if (el) {
          const w = el.querySelector('button')?.offsetWidth || 160;
          this.dd[what] = Math.max(160, w);
        }
        if (what==='Company') this.openCompany = true;
        if (what==='Dept')    this.openDept    = true;
        if (what==='Team')    this.openTeam    = true;
        if (what==='Pos')     this.openPos     = true;
        if (what==='PageSize') this.openPageSize = true;
      });
    },
    pickFilter(type, value) {
      if (type==='company') this.companySel = (this.companySel===value ? '' : value);
      if (type==='dept')    this.deptSel    = (this.deptSel===value    ? '' : value);
      if (type==='team')    this.teamSel    = (this.teamSel===value    ? '' : value);
      if (type==='pos')     this.posSel     = (this.posSel===value     ? '' : value);
      this.closeAllDropdowns();
      this.page = 1;
    },
    resetFilter(type) {
      if (type==='company') this.companySel = '';
      if (type==='dept')    this.deptSel    = '';
      if (type==='team')    this.teamSel    = '';
      if (type==='pos')     this.posSel     = '';
      this.closeAllDropdowns();
      this.page = 1;
    },
    closeAllDropdowns() {
      this.openCompany = this.openDept = this.openTeam = this.openPos = this.openPageSize = false;
    },
  },
};
</script>

<style scoped>
/* เทาเข้มทั้งแถว (read-only selection) */
:deep(tr.row-selected) > * {
  background-color: #d4d4d4 !important;
}
:deep(tr.row-selected:hover) > * {
  background-color: #d4d4d4 !important;
}
:deep(tr.row-selected input[type="checkbox"]) {
  opacity: 1;
  cursor: not-allowed;
}
:deep(input[type="checkbox"][disabled]) {
  opacity: 1;
}
</style>

