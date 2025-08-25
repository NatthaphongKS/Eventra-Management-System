<template>

  <!-- Toolbar -->
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Search -->
    <div class="flex w-full items-center gap-2 sm:max-w-xl">
      <div class="relative flex-1">
        <input
          v-model="query"
          type="text"
          placeholder="Search..."
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none transition focus:border-red-300 focus:bg-white"
        />
      </div>
      <button
        class="inline-flex items-center justify-center rounded-full bg-[#C91818] p-3 text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="onSearch"
        aria-label="search"
      >
        <!-- search icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 101.415-1.415l-4.25-4.249A6 6 0 0010 4zm-4 6a4 4 0 118 0 4 4 0 01-8 0z"/>
        </svg>
      </button>
    </div>

    <!-- Right controls -->
    <div class="flex items-center gap-2">
      <!-- Sort: dropdown เลือกได้ -->
      <div class="relative" @keydown.escape="sortOpen=false">
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="sortOpen = !sortOpen"
          :title="sortDir === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด'"
        >
          <!-- sort icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 2a1 1 0 011 1v14.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4A1 1 0 013.707 15.293L6 17.586V3a1 1 0 011-1zM14 22a1 1 0 01-1-1V6.414l-2.293 2.293a1 1 0 11-1.414-1.414l4-4a1 1 0 011.414 0l4 4A1 1 0 0117.293 8.707L15 6.414V21a1 1 0 01-1 1z"/>
          </svg>
          <span>Sort</span>
          <span class="text-gray-400">({{ sortDir === 'desc' ? 'วันที่ล่าสุด' : 'วันที่เก่าสุด' }})</span>
        </button>

        <!-- เมนูตัวเลือก -->
        <div
          v-if="sortOpen"
          class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-gray-200 bg-white p-1 text-sm shadow-lg"
        >
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="applySort('desc')">
            วันที่ล่าสุด
          </button>
          <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-red-50" @click="applySort('asc')">
            วันที่เก่าสุด
          </button>
        </div>
      </div>

      <!-- Add New -->
      <button
        class="inline-flex items-center gap-2 rounded-xl bg-[#C91818] px-4 py-2 text-sm font-semibold text-white shadow hover:opacity-95 active:scale-[0.98]"
        @click="openAdd"
      >
        <span class="text-lg leading-none">＋</span>
        <span>Add New</span>
      </button>
    </div>
  </div>

  <!-- Table -->
  <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
    <table class="min-w-full divide-y divide-gray-100">
      <thead class="bg-gray-50 text-gray-600">
        <tr class="text-left text-sm">
          <th class="w-14 px-6 py-3 font-semibold">#</th>
          <th class="px-6 py-3 font-semibold">Category</th>
          <th class="px-6 py-3 font-semibold">Created by</th>
          <th class="px-6 py-3 font-semibold">Created date (D/M/Y)</th>
          <th class="w-16 px-6 py-3"></th>
        </tr>

      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, idx) in pagedRows"
          :key="row.id"
          class="text-sm text-gray-700 hover:bg-gray-50"
        >
          <td class="px-6 py-3">{{ startIndex + idx + 1 }}</td>
          <td class="px-6 py-3">{{ row.name }}</td>
          <td class="px-6 py-3">{{ row.createdBy }}</td>
          <td class="px-6 py-3">{{ formatDate(row.createdAt) }}</td>
          <td class="px-6 py-3">
            <button
              class="rounded-lg p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600"
              @click="remove(row.id)"
              aria-label="delete"
            >
              <!-- trash -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.278l.84 12.255A2 2 0 008.114 22h7.772a2 2 0 001.996-2.745L18.722 7H19a1 1 0 100-2h-3V4a1 1 0 00-1-1H9zm2 4a1 1 0 112 0v10a1 1 0 11-2 0V7z"/>
              </svg>
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

  <!-- Footer -->
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

  <!-- Add Modal -->
  <div v-if="addOpen" class="fixed inset-0 z-20 grid place-items-center bg-black/40 p-4" @click.self="closeAdd">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <h3 class="text-xl font-semibold text-gray-900">Create Category</h3>
      <div class="mt-5">
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Type name <span class="text-red-600">*</span>
        </label>
        <input
          v-model="newName"
          type="text"
          placeholder="Ex. สัมมนา"
          class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-200"
        />
        <!-- แสดงผู้ใช้ที่จะถูกบันทึก ตามรูป -->
        <p class="mt-2 text-xs text-gray-500">
          ระบบจะบันทึก Created by เป็นชื่อผู้ใช้:
          <span class="font-medium text-gray-700">{{ userName }}</span>
        </p>
      </div>
      <div class="mt-6 flex justify-between">
        <button
          @click="closeAdd"
          class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white px-5 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
        >
          ✕ Cancel
        </button>
        <button
          :disabled="!newName.trim()"
          @click="createCategory"
          class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          + Create
        </button>
      </div>
    </div>
  </div>

</template>

<script setup lang="ts">
/**
 * ====== ส่วน import ที่จำเป็น ======
 * - computed, ref, onMounted: ฟังก์ชันจาก Vue สำหรับสถานะ/คำนวณ/ฮุคเมื่อเมานต์
 * - Swal: กล่องแจ้งเตือน SweetAlert2
 * - axios: เรียก REST API
 */
import { computed, ref, onMounted } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

/**
 * ====== ตั้งค่า Axios พื้นฐาน ======
 * - baseURL เป็น /api (ชี้ไปที่ Laravel api.php ผ่าน proxy ของ dev server)
 * - เพิ่ม header Accept: application/json
 * - withCredentials: true เพื่อส่ง cookie ไป-กลับ (จำเป็นถ้า route อยู่หลัง 'web' middleware / CSRF)
 */
axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.withCredentials = true;

/**
 * ฟังก์ชัน ensureCsrf:
 * - เรียก /sanctum/csrf-cookie หนึ่งครั้งก่อน request แบบเปลี่ยนแปลงข้อมูล (POST/DELETE)
 * - Laravel จะเซ็ต cookie XSRF-TOKEN ให้ เบราว์เซอร์จะส่งกลับอัตโนมัติ
 */
async function ensureCsrf() {
  try {
    await axios.get("/sanctum/csrf-cookie");
  } catch (e) {
    console.error("Get CSRF cookie failed:", e);
  }
}

/** ====== กำหนดประเภทข้อมูลของแถวในตาราง ====== */
type Row = { id: number; name: string; createdBy: string; createdAt: string };

/**
 * ====== State (สถานะของหน้า) ======
 * rows         : ข้อมูลที่จะแสดงในตาราง
 * query        : ข้อความค้นหา
 * page         : หน้าปัจจุบัน
 * pageSize     : จำนวนรายการต่อหน้า
 * sortOpen     : สถานะเปิด/ปิดเมนู Sort
 * sortDir      : ทิศทางการเรียง ('asc' = เก่าสุดก่อน, 'desc' = ล่าสุดก่อน)
 * addOpen      : เปิด/ปิดโมดัลเพิ่มข้อมูล
 * newName      : ชื่อหมวดหมู่ใหม่จากอินพุต
 * loading      : สถานะกำลังโหลดข้อมูล
 */
const rows = ref<Row[]>([]);
const query = ref("");
const page = ref(1);
const pageSize = ref(10);
const sortOpen = ref(false);
const sortDir = ref<"asc" | "desc">("desc");
const addOpen = ref(false);
const newName = ref("");
const loading = ref(false);

/**
 * userName:
 * - ใช้แสดงว่าใครเป็นผู้สร้างใน UI (ฝั่ง DB อาจจะยังไม่บันทึกผู้สร้างจริง)
 * - ถ้ามีระบบล็อกอินฝั่งหน้าเว็บ นำชื่อผู้ใช้จริงมาเซ็ตในตัวแปรนี้
 */
const userName = ref("");

/** ====== Helpers (ฟังก์ชันช่วย) ====== */
/** ทำให้สตริงอยู่ในรูป trim และ lower สำหรับเทียบ/ค้นหา */
function norm(s: string) {
  return s.trim().toLowerCase();
}
/**
 * แปลง ISO date เป็น timestamp (ms)
 * - ถ้า invalid หรือว่าง ให้คืน 0 เพื่อไม่ทำให้ sort พัง
 */
function toTime(iso?: string | null) {
  const t = iso ? new Date(iso).getTime() : NaN;
  return Number.isFinite(t) ? t : 0;
}


/** แสดงวันที่แบบ DD/MM/YYYY เพื่อโชว์ในตาราง */
function formatDate(iso: string) {
  const d = new Date(iso);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

/**
 * ====== โหลดรายการหมวดหมู่จาก API ======
 * - GET /api/categories
 * - map ให้อยู่ในรูป Row เพื่อใช้กับตาราง
 * - createdAt: ใช้เวลาจาก DB ถ้ามี ไม่มีก็ fallback เป็นเวลาปัจจุบัน
 */
async function loadCategories() {
  loading.value = true;
  try {
    const res = await axios.get("/categories");
    const data = Array.isArray(res.data) ? res.data : res.data?.data || [];
    rows.value = (data as any[]).map((c) => ({
      id: c.id,
      name: c.cat_name ?? c.emc_name ?? "",
      createdBy: c.created_by ?? "-",
      createdAt: c.created_at ?? new Date().toISOString(),
    }));
  } catch (e: any) {
    console.error("GET /categories failed:", e?.response || e);
    Swal.fire({ title: "โหลดข้อมูลไม่สำเร็จ", text: e?.message ?? "", icon: "error" });
  } finally {
    loading.value = false;
  }
}

/**
 * onMounted:
 * - โหลดรายการครั้งแรก
 * - ผูก handler เพื่อปิดเมนู sort เมื่อคลิกนอกกล่อง
 */
onMounted(() => {
  loadCategories();
  // ปิดเมนู sort เมื่อคลิกนอก
  document.addEventListener("click", (e) => {
    const el = e.target as HTMLElement;
    if (!el.closest(".relative")) sortOpen.value = false;
  });
});

/**
 * ====== การจัดการ UI เบื้องต้น ======
 * openAdd/closeAdd  : เปิด/ปิด modal เพิ่มข้อมูล
 * onSearch          : เมื่อกดค้นหา ให้กลับไปหน้าแรก
 * applySort(dir)    : เปลี่ยนทิศทางการเรียง แล้วปิดเมนู sort
 */
function openAdd() {
  newName.value = "";
  addOpen.value = true;
}
function closeAdd() {
  addOpen.value = false;
}
function onSearch() {
  page.value = 1;
}
function applySort(dir: "asc" | "desc") {
  sortDir.value = dir;
  sortOpen.value = false;
  page.value = 1;
}

/**
 * ====== คำนวณรายการสำหรับแสดง (Filter -> Sort -> Paginate) ======
 * filtered : กรองด้วยคำค้นจาก name หรือ createdBy
 * sorted   : เรียงตาม createdAt (timestamp) ตามทิศทาง sortDir
 * total    : จำนวนทั้งหมดหลังกรอง
 * totalPages, startIndex, endIndex, pagedRows, visibleCountText : สำหรับเพจจิเนชัน
 */
const filtered = computed(() => {
  const q = norm(query.value);
  if (!q) return rows.value.slice();
  return rows.value.filter((r) => norm(r.name).includes(q) || norm(r.createdBy).includes(q));
});

const sorted = computed(() => {
  const copy = filtered.value.slice();
  const dir = sortDir.value === "asc" ? 1 : -1;
  copy.sort((a, b) => {
    const ta = toTime(a.createdAt);
    const tb = toTime(b.createdAt);
    return (ta - tb) * dir;
  });
  return copy;
});

const total = computed(() => sorted.value.length);
const totalPages = computed(() => Math.ceil(total.value / pageSize.value));
const startIndex = computed(() => (page.value - 1) * pageSize.value);
const endIndex = computed(() => Math.min(startIndex.value + pageSize.value, total.value));
const pagedRows = computed(() => sorted.value.slice(startIndex.value, endIndex.value));
const visibleCountText = computed(() =>
  total.value === 0 ? "0 จาก 0 รายการ" : `${endIndex.value} จาก ${total.value} รายการ`
);

/**
 * ====== เช็คชื่อซ้ำใน UI ======
 * - กันเคสพิมพ์ซ้ำก่อนยิง API เพื่อลด error และ UX ดีขึ้น
 * - เปรียบเทียบแบบไม่สนเคส/ช่องว่างหัวท้าย
 */
const isDuplicate = computed(() => {
  const name = norm(newName.value);
  if (!name) return false;
  return rows.value.some((r) => norm(r.name) === name);
});

/**
 * ====== สร้างหมวดหมู่ใหม่ (POST -> DB + อัปเดต UI) ======
 * ขั้นตอน:
 * 1) validate ช่องว่าง
 * 2) เช็คชื่อซ้ำใน UI
 * 3) ensureCsrf() เพื่อรับ XSRF-TOKEN (เมื่อใช้ web + auth)
 * 4) POST /api/categories { cat_name }
 * 5) อัปเดตตารางแบบทันที (unshift ด้านบน)
 * 6) แจ้งผลลัพธ์
 * 7) จัดการ error 422 (unique) และ error อื่น ๆ
 */
async function createCategory() {
  const name = newName.value.trim();
  if (!name) return;

  // กันชื่อซ้ำใน UI
  if (isDuplicate.value) {
    Swal.fire({ title: "มีชื่อนี้อยู่แล้ว", text: `ชื่อ "${name}" ถูกใช้งานอยู่ในรายการ`, icon: "warning" });
    return;
  }

  try {
    await ensureCsrf(); // สำคัญสำหรับ route ที่ครอบด้วย web+auth
    const res = await axios.post("/categories", { cat_name: name }); // key ต้องตรง DB (cat_name)
    const created = res.data?.data ?? res.data;

    rows.value.unshift({
      id: created.id,
      name: created.cat_name ?? name,
      createdBy: userName.value || "-", // แสดงชื่อผู้ใช้ในตาราง ถ้าไม่มีให้เป็น "-"
      // ใช้เวลาจริงจาก server ถ้ามี ไม่มีก็ fallback เวลาขณะบันทึก
      createdAt: created.created_at ?? new Date().toISOString(),
    });

    closeAdd();
    page.value = 1;
    Swal.fire({ title: "เพิ่มหมวดหมู่สำเร็จ!", text: `เพิ่ม "${name}" เรียบร้อย`, icon: "success" });
  } catch (err: any) {
    const status = err?.response?.status;
    const data = err?.response?.data;

    if (status === 422) {
      const msg = data?.errors?.cat_name?.[0] || data?.message || "ข้อมูลไม่ถูกต้อง";
      Swal.fire({ title: "เพิ่มไม่สำเร็จ", text: msg, icon: "error" });
    } else {
      Swal.fire({
        title: `เพิ่มไม่สำเร็จ${status ? ` (HTTP ${status})` : ""}`,
        text: typeof data === "string" ? data : JSON.stringify(data ?? {}, null, 2),
        icon: "error",
      });
    }
  }
}

/**
 * ====== ลบหมวดหมู่ (Soft delete) ======
 * แนวคิด:
 * - UI เอาออกก่อน (optimistic update) และเก็บ backup ไว้
 * - เรียก DELETE /api/categories/:id เพื่อเปลี่ยนสถานะเป็น inactive ใน DB
 * - ถ้า API error ให้ rollback (คืน UI จาก backup)
 * - ปรับเลขหน้าถ้าจำนวนแถวลดจนเกินจำนวนหน้า
 */
function remove(id: number) {
  Swal.fire({
    title: "ARE YOU SURE TO DELETE?",
    text: "ข้อมูลนี้จะถูกลบออกจากหน้าจอ (DB เก็บสถานะ inactive)",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    allowOutsideClick: false,
    allowEscapeKey: false,
  }).then(async (result) => {
    if (!result.isConfirmed) return;

    const backup = rows.value.slice();
    rows.value = rows.value.filter((r) => r.id !== id);

    try {
      await ensureCsrf(); // สำคัญก่อนลบ
      await axios.delete(`/categories/${id}`);
      const tp = totalPages.value || 1;
      if (page.value > tp) page.value = tp;
      Swal.fire({ title: "Deleted!", text: "ลบออกจากตารางแล้ว (ฐานข้อมูลยังเก็บแบบ inactive)", icon: "success" });
    } catch (err: any) {
      rows.value = backup; // rollback UI ถ้าพลาด
      const status = err?.response?.status;
      const data = err?.response?.data;
      Swal.fire({
        title: `ลบไม่สำเร็จ${status ? ` (HTTP ${status})` : ""}`,
        text: typeof data === "string" ? data : JSON.stringify(data ?? {}, null, 2),
        icon: "error",
      });
    }
  });
}
</script>

