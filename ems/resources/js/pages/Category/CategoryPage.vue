<template>
    <link rel="stylesheet"
  href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,0" />

  <!-- Toolbar -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Search -->
    <div class="flex w-full items-center gap-2 sm:max-w-xl">
      <div class="relative flex-1">
        <input
          v-model="searchInput"
          type="text"
          placeholder="Search Category / Created by / Delete by"
          @keyup.enter="onSearch"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
        />
      </div>

      <button
        class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="onSearch"
        aria-label="search"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
        </svg>
      </button>
    </div>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <CategorySort v-model="sortDir" />
      <button
        class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="openAdd"
      >
        <span class="text-lg leading-none">＋</span>
        <span>Add New</span>
      </button>
    </div>
  </div>

  <!-- ===== Data Table ===== -->
  <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
    <table class="min-w-full divide-y divide-gray-100">
      <thead class="bg-gray-50 text-gray-600">
        <tr class="text-left text-sm">
          <th class="px-6 py-3 w-14 font-semibold">#</th>
          <th class="px-6 py-3 font-semibold">Category</th>
          <th class="px-6 py-3 font-semibold">Created by</th>
          <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
          <th class="px-6 py-3 text-center w-20 font-semibold">Action</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, idx) in pagedRows"
          :key="row.id"
          class="text-sm text-gray-700 hover:bg-gray-50"
        >
          <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>
          <td class="px-6 py-3 truncate">{{ row.name }}</td>
          <td class="px-6 py-3">{{ row.createdBy }}</td>
          <td class="px-6 py-3">{{ formatDate(row.createdAt) }}</td>
          <td class="px-6 py-3 text-center flex justify-center gap-1">
            <!-- ปุ่ม Edit -->
            <button
              class="rounded-full p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50"
              @click="openEdit(row)"
              title="Edit"
            >
              <span class="material-symbols-outlined">edit</span>
            </button>

            <!-- ปุ่ม Delete -->
            <button
              class="rounded-full p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50"
              @click="remove(row.id)"
              title="Delete"
            >
              <span class="material-symbols-outlined">delete</span>
            </button>
          </td>
        </tr>

        <tr v-if="pagedRows.length === 0">
          <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
            ไม่พบข้อมูลที่ค้นหา
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- ===== Pagination ===== -->
  <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <span>แสดง</span>
      <select
        v-model.number="pageSize"
        class="rounded-xl border border-gray-200 px-2.5 py-1.5 outline-none"
      >
        <option v-for="n in [5,10,20,50]" :key="n" :value="n">{{ n }}</option>
      </select>
      <span>{{ visibleCountText }}</span>
    </div>

    <div class="flex items-center gap-2">
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === 1"
        @click="page--"
      >
        ก่อนหน้า
      </button>
      <span class="text-sm text-gray-600">หน้า {{ page }} / {{ totalPages || 1 }}</span>
      <button
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-40"
        :disabled="page === totalPages || totalPages === 0"
        @click="page++"
      >
        ถัดไป
      </button>
    </div>
  </div>

  <!-- ===== Create Modal ===== -->
  <CategoryCreate
    v-model:open="addOpen"
    :userName="userName"
    :duplicate="isDuplicate"
    @submit="createCategory"
  />

  <!-- ===== Edit Modal ===== -->
  <CategoryEdit
    v-model:open="editOpen"
    :category="editing"
    :is-duplicate="isDupForEdit"
    :formatDate="formatDate"
    @submit="updateCategory"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

import CategorySort from "@/components/Category/CategorySort.vue";
import CategoryCreate from "@/components/Category/CategoryCreate.vue";
import CategoryEdit from "@/components/Category/CategoryEdit.vue";

/* Axios Config */
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = true;

async function ensureCsrf() {
  try { await axios.get("/sanctum/csrf-cookie"); } catch(e){ console.error(e); }
}

type Row = { id: number; name: string; createdBy: string; createdAt: string | null; };

const rows = ref<Row[]>([]);
const searchInput = ref("");
const appliedQuery = ref("");
const page = ref(1);
const pageSize = ref(10);
const sortDir = ref<"asc" | "desc">("desc");
const addOpen = ref(false);
const editOpen = ref(false);
const editing = ref<Row | null>(null);
const newName = ref("");
const userName = ref("Admin");

/* Helpers */
function norm(s: unknown){ return String(s ?? "").trim().toLowerCase(); }
function toTime(iso?: string | null){ const t = iso ? new Date(iso).getTime() : NaN; return Number.isFinite(t) ? t : 0; }
function formatDate(iso?: string | null){
  if (!iso) return "-";
  const d = new Date(iso);
  if (isNaN(d.getTime())) return "-";
  return `${String(d.getDate()).padStart(2,"0")}/${String(d.getMonth()+1).padStart(2,"0")}/${d.getFullYear()}`;
}

/* Load Data */
async function loadCategories(){
  try {
    const res = await axios.get("/categories");
    const data = Array.isArray(res.data) ? res.data : res.data?.data || [];
    rows.value = data.map((c:any)=>({
      id: c.id,
      name: c.cat_name ?? c.emc_name ?? "",
      createdBy: c.created_by_name ?? c.created_by ?? "-",
      createdAt: c.cat_created_at ?? c.cat_create_at ?? c.created_at ?? null,
    }));
  } catch (e:any) {
    Swal.fire({ title:"โหลดข้อมูลไม่สำเร็จ", text:e?.message, icon:"error" });
  }
}
onMounted(loadCategories);

/* Derived */
const filtered = computed(()=> {
  const q = norm(appliedQuery.value);
  return !q ? rows.value.slice() : rows.value.filter(r=>norm(r.name).includes(q)||norm(r.createdBy).includes(q));
});
const sorted = computed(()=> {
  const dir = sortDir.value==="asc"?1:-1;
  return filtered.value.slice().sort((a,b)=>(toTime(a.createdAt)-toTime(b.createdAt))*dir);
});
const total = computed(()=>sorted.value.length);
const totalPages = computed(()=>Math.ceil(total.value/pageSize.value));
const startIndex = computed(()=> (page.value-1)*pageSize.value);
const endIndex = computed(()=> Math.min(startIndex.value+pageSize.value,total.value));
const pagedRows = computed(()=> sorted.value.slice(startIndex.value,endIndex.value));
const visibleCountText = computed(()=> total.value===0? "0 จาก 0 รายการ": `${endIndex.value} จาก ${total.value} รายการ`);

/* Search */
function onSearch(){ appliedQuery.value = searchInput.value.trim(); page.value=1; }

/* Add */
function openAdd(){ newName.value=""; addOpen.value=true; }
const isDuplicate = computed(()=> rows.value.some(r=>norm(r.name)===norm(newName.value)));
async function createCategory(nameFromModal:string){
  const name = nameFromModal.trim(); if(!name) return;
  if(rows.value.some(r=>norm(r.name)===norm(name))) return Swal.fire({title:"มีชื่อนี้อยู่แล้ว",icon:"warning"});
  await ensureCsrf();
  const res = await axios.post("/categories",{cat_name:name});
  const created = res.data?.data??res.data;
  rows.value.unshift({
    id:created.id,
    name:created.cat_name??name,
    createdBy:userName.value,
    createdAt:created.cat_created_at??created.created_at??null,
  });
  addOpen.value=false; Swal.fire({title:"เพิ่มสำเร็จ!",icon:"success"});
}

/* Edit */
function openEdit(row:Row){ editing.value={...row}; editOpen.value=true; }
function isDupForEdit(name:string,id?:number){ const n=norm(name); return rows.value.some(r=>norm(r.name)===n && r.id!==id); }
async function updateCategory(payload:{id:number; name:string;}){
  await ensureCsrf(); await axios.patch(`/categories/${payload.id}`,{cat_name:payload.name});
  const i=rows.value.findIndex(r=>r.id===payload.id); if(i!==-1) rows.value[i].name=payload.name;
  editOpen.value=false; Swal.fire({title:"บันทึกสำเร็จ!",icon:"success"});
}

/* Delete */
function remove(id:number){
  Swal.fire({title:"ลบหมวดหมู่?",icon:"warning",showCancelButton:true}).then(async(res)=>{
    if(!res.isConfirmed) return;
    const backup=[...rows.value]; rows.value=rows.value.filter(r=>r.id!==id);
    try{ await ensureCsrf(); await axios.delete(`/categories/${id}`); Swal.fire({title:"ลบสำเร็จ",icon:"success"});}
    catch{ rows.value=backup; Swal.fire({title:"ลบไม่สำเร็จ",icon:"error"}); }
  });
}
</script>

<style>
/* ✅ Material Symbols Styling */
.material-symbols-outlined {
  font-family: "Material Symbols Outlined";
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  display: inline-block;
  white-space: nowrap;
  direction: ltr;
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
.truncate {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
</style>
