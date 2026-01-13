<template>

    <head>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    </head>

    <div class="flex-1 w-full">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 pt-6">
            <div>
                <h2 class="text-3xl font-semibold text-neutral-800 -ml-8">
                    Upload file information
                </h2>


                <div class="mt-2 flex justify-center">
                    <button type="button" @click="downloadTemplate"
                        class="rounded-full border border-[neutral-200] px-4 py-2 text-sm font-regular text-[#1d9bf0] bg-white hover:bg-blue-50 transition">
                        *Click to download template excel file*
                    </button>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-sm font-semibold text-gray-600">
                    Upload file Excel
                </p>
                <p class="text-sm text-gray-400 mt-1">
                    Drag and drop document to your support task
                </p>

                <Upload class="mt-2" v-model:file="file" :max-size-mb="50" @invalid="(msg) => (error = msg)"
                    @picked="() => { error = ''; isTableVisible = false; }" 
                    @cleared="() => { error = ''; isTableVisible = false; displayRows = []; }" />

                <div class="mt-4 flex justify-end">
                    <div :class="[
                        !file || !!error || uploading
                            ? 'opacity-50 cursor-not-allowed'
                            : 'cursor-pointer',
                    ]" :style="!file || !!error || uploading
                        ? 'pointer-events: none;'
                        : ''
                        " :title="!file || !!error || uploading
                            ? 'Please upload or drop file first'
                            : ''
                            ">
                        <GenerateDataButton :disabled="!file || !!error || uploading" @click="upload" />
                    </div>
                </div>

                <p v-if="error" class="text-sm text-red-500 mt-2">
                    {{ error }}
                </p>
            </div>
        </div>

        <div v-if="isTableVisible">

            <div class="mt-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-x-auto lg:overflow-x-visible">
                    <DataTable :loading="uploading || creating" :rows="paged" :columns="tableColumns" :page="page"
                        :page-size="pageSize" :total-items="totalItems" :page-size-options="[10, 25, 50, 100]"
                        :show-row-number="false" row-key="__rowKey" @update:page="(val) => (page = val)"
                        @update:pageSize="(val) => (pageSize = val)">
                        <template #header-index> # </template>

                        <template #cell-index="{ row }">
                            {{ row.__displayIndex }}
                        </template>

                        <template #empty> No Data Found </template>

                        <template #footer-info="{ from, to, total }">
                            <span>แสดง</span>

                            <div class="relative inline-block mx-2">
                                <select
                                    class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200 text-sm"
                                    :value="pageSize" @change="
                                    (e) => {
                                        pageSize = Number(e.target.value);
                                        page = 1;
                                    }
                                ">
                                    <option v-for="opt in [10, 25, 50, 100]" :key="opt" :value="opt">
                                        {{ opt }}
                                    </option>
                                </select>
                                <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </div>

                            <span class="text-sm">
                                {{ from }}-{{ to }} จาก {{ total }} รายการ
                            </span>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>

    <div class="pb-8" v-if="isTableVisible">
        <div
            class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex justify-start">
                <CancelButton @click="onCancel" />
            </div>

            <div class="flex justify-end" :class="canCreate
                ? 'cursor-pointer'
                : 'opacity-50 cursor-not-allowed'
                " :style="canCreate ? '' : 'pointer-events: none;'" :title="canCreate
                    ? ''
                    : 'Please upload file and click Generate Data first'
                    ">
                <CreateButton :disabled="!canCreate" @click="onCreate" />
            </div>
        </div>
    </div>

    <ModalAlert v-model:open="showCreateSuccess" title="Success" message="Create employee success" type="success"
        @confirm="handleSuccessClose" />
    <EmployeeCannotCreate :open="showCannotCreate" :message="errorMessage" @close="showCannotCreate = false" />
</template>

<script setup>
// ======================================================
// Core Vue APIs
// ======================================================
import { ref, computed, watch, onMounted } from "vue";

// Vue Router (ใช้สำหรับ redirect หน้า)
import { useRouter } from "vue-router";

// Excel libraries
// - XLSX : ใช้สำหรับอ่านไฟล์ Excel / CSV
// - ExcelJS : ใช้สำหรับสร้างไฟล์ Excel template
import * as XLSX from "xlsx";
import ExcelJS from "exceljs";

// HTTP client
import axios from "axios";

// ======================================================
// Components
// ======================================================
import Upload from "@/components/Input/Upload.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import EmployeeCannotCreate from "../../components/Alert/Employee/EmployeeCannotCreate.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import CreateButton from "@/components/Button/CreateButton.vue";
import GenerateDataButton from "@/components/Button/GenerateDataButton.vue";
import DataTable from "@/components/DataTable.vue";

// ======================================================
// Router instance
// ======================================================
const router = useRouter();

// ======================================================
// Upload / Table State
// ======================================================
const file = ref(null);            // ไฟล์ Excel ที่ upload
const error = ref("");             // error จากการอ่านไฟล์
const uploading = ref(false);      // loading ตอนอ่านไฟล์
const creating = ref(false);       // loading ตอนสร้าง employee

const displayRows = ref([]);       // ข้อมูลทั้งหมดจากไฟล์ (ยังไม่ยิง create)
const page = ref(1);               // หน้าปัจจุบัน
const pageSize = ref(10);          // จำนวนแถวต่อหน้า

// แก้ไข: เพิ่ม State ควบคุมการแสดงตาราง
const isTableVisible = ref(false);

// ======================================================
// Master Data Cache
// ======================================================
const departments = ref([]);       // แผนก
const positions = ref([]);         // ตำแหน่ง
const teams = ref([]);             // ทีม

// ======================================================
// Load master data จาก backend
// ======================================================
async function loadMeta() {
    try {
        const { data } = await axios.get("/meta");
        departments.value = data.departments || [];
        positions.value = data.positions || [];
        teams.value = data.teams || [];
    } catch (e) {
        console.error("Failed to load meta()", e);
    }
}

// ======================================================
// Lifecycle: โหลดข้อมูลตอนเข้า page
// ======================================================
onMounted(async () => {
    await Promise.all([
        loadMeta(),        // โหลด department / team / position
        loadCompanies(),   // ฟังก์ชันภายนอก (ต้องมีอยู่)
    ]);
});

// ======================================================
// Pagination computed
// ======================================================
const totalItems = computed(() => displayRows.value.length);

const paged = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    const slice = displayRows.value.slice(start, start + pageSize.value);

    return slice.map((row, idx) => ({
        ...row,
        __rowKey: row.employeeId || `${start + idx}`, // key สำหรับ DataTable
        __displayIndex: start + idx + 1,              // running number
    }));
});

// ======================================================
// ป้องกัน page เกินจำนวนจริง
// ======================================================
watch([pageSize, totalItems], () => {
    const lastPage = Math.max(1, Math.ceil(totalItems.value / pageSize.value));
    if (page.value > lastPage) page.value = lastPage;
});

// ======================================================
// Upload & Parse Excel
// ======================================================
async function upload() {
    // guard: ต้องมีไฟล์ และไม่มี error
    if (!file.value || error.value) return;
    uploading.value = true;
    
    // แก้ไข: Reset การแสดงผลก่อนเริ่ม
    isTableVisible.value = false;

    try {
        const ext = file.value.name.split(".").pop()?.toLowerCase();

        const data = await readFile(
            file.value,
            ext === "csv" ? "text" : "array"
        );

        const wb = XLSX.read(data, {
            type: ext === "csv" ? "string" : "array",
            cellDates: true,
        });

        const ws = wb.Sheets[wb.SheetNames[0]];

        const rowsAoA = XLSX.utils.sheet_to_json(ws, {
            header: 1,
            defval: "",
            blankrows: false,
        });

        const headerRowIdx = detectHeaderRow(rowsAoA);
        if (headerRowIdx === -1) {
            throw new Error(
                "Header row not found or does not match the required template."
            );
        }

        const headers = rowsAoA[headerRowIdx].map(h =>
            String(h).trim()
        );
        const dataAoA = rowsAoA.slice(headerRowIdx + 1);

        if (!dataAoA.length) {
            throw new Error("Please fill in all required information.");
        }
        // AoA → Object (raw data)
        const json = arraysToObjects(headers, dataAoA);

        // ================================
        // normalize key → lowercase + no space
        // ================================
        const normalizedRows = json.map(row => {
            const out = {};
            for (const [k, v] of Object.entries(row)) {
                const nk = String(k)
                    .toLowerCase()
                    .replace(/\s+/g, "");
                out[nk] = v;
            }
            return out;
        });

        // ================================
        // ตรวจข้อมูลที่จำเป็นต้องมี
        // ================================
        const requiredFields = [
            "company",
            "employeeid",
            "prefix",
            "firstname",
            "lastname",
            "nickname",
            "department",
            "team",
            "position",
            "phone",
            "email",
        ];

        const invalidRowIndex = normalizedRows.findIndex(row =>
            requiredFields.some(
                key => !row[key] || String(row[key]).trim() === ""
            )
        );

        if (invalidRowIndex !== -1) {
            throw new Error("Please fill in all required information.");
        }

        // ผ่าน validation แล้ว → map
        const mapped = mapRows(json);

        displayRows.value = mapped;
        page.value = 1;
        error.value = "";
        
        // แก้ไข: โหลดเสร็จแล้วค่อยโชว์ตาราง
        isTableVisible.value = true;

    } catch (e) {
        console.error(e);
        error.value =
            e?.message || "Unable to read file. Please check the information.";
        // ถ้า error ไม่ต้องโชว์ตาราง
        isTableVisible.value = false;
    } finally {
        uploading.value = false;
    }
}

// ======================================================
// Helper: ตรวจหา header row (บังคับต้องมีคอลัมน์สำคัญครบ)
// ======================================================
function detectHeaderRow(rowsAoA) {
    const requiredHeaders = [
        "company",
        "employeeid",
        "prefix",
        "firstname",
        "lastname",
        "nickname",
        "department",
        "team",
        "position",
        "phone",
        "email",
    ];

    const maxScan = Math.min(rowsAoA.length, 30);

    for (let i = 0; i < maxScan; i++) {
        const row = (rowsAoA[i] || []).map(cell =>
            String(cell || "")
                .toLowerCase()
                .replace(/\s+/g, "")
        );

        // ต้องมีครบทุกคอลัมน์
        const hasAllRequired = requiredHeaders.every(h => row.includes(h));
        if (hasAllRequired) {
            return i;
        }
    }

    return -1; // ไม่พบหัวตาราง
}

// ======================================================
// Helper: Array of Array → Object[]
// ======================================================
function arraysToObjects(headers, rows) {
    const out = [];
    for (const r of rows) {
        if (!r || r.every(v => v === "" || v == null)) continue;
        const obj = {};
        headers.forEach((h, idx) => {
            obj[h] = r[idx] ?? "";
        });
        out.push(obj);
    }
    return out;
}

// ======================================================
// Utility functions
// ======================================================
function readFile(f, mode = "array") {
    return new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(r.result);
        r.onerror = reject;
        mode === "text" ? r.readAsText(f) : r.readAsArrayBuffer(f);
    });
}

function normalizeKey(k) {
    return String(k)
        .toLowerCase()
        .replace(/\s+/g, "")
        .replace(/[_\-\/\\().]/g, "");
}

function normalizePhone(p) {
    if (p == null) return "";
    if (typeof p === "number") {
        return String(Math.trunc(p)).padStart(10, "0");
    }
    return String(p).replace(/[^\d]/g, "");
}

function todayDMY() {
    const d = new Date();
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yy = d.getFullYear();
    return `${dd}/${mm}/${yy}`;
}


// ======================================================
// Map & Normalize Excel Rows
// ======================================================
function mapRows(rows) {
    const keyAlias = {
        company: "company",
        employeeid: "employeeId",
        "employee id": "employeeId",
        รหัสพนักงาน: "employeeId",
        idพนักงาน: "employeeId",
        พนักงานid: "employeeId",
        คำนำหน้า: "prefix",
        คำนำหน้าชื่อ: "prefix",
        prefix: "prefix",
        ชื่อ: "firstName",
        firstname: "firstName",
        นามสกุล: "lastName",
        lastname: "lastName",
        ชื่อเล่น: "nickname",
        nickname: "nickname",
        position: "position",
        ตำแหน่ง: "position",
        department: "department",
        แผนก: "department",
        ฝ่าย: "department",
        team: "team",
        ทีม: "team",
        phone: "phone",
        โทรศัพท์: "phone",
        เบอร์: "phone",
        email: "email",
        อีเมล: "email",
    };

    return rows.map(r => {
        const norm = {};
        for (const [k, v] of Object.entries(r)) {
            const nk = normalizeKey(k);
            let mapped = keyAlias[nk];
            if (!mapped && nk.startsWith("dateadd")) mapped = "dateAdd";
            norm[mapped || k] = v ?? "";
        }

        const fullName = [norm.prefix, norm.firstName, norm.lastName]
            .filter(Boolean)
            .join(" ")
            .trim();

        return {
            company: (norm.company || "").toString().trim(),
            employeeId: (norm.employeeId || "").toString().trim(),
            displayEmployeeId: [
                (norm.company || "").toString().trim(),
                (norm.employeeId || "").toString().trim(),
            ]
                .filter(Boolean)
                .join(""),
            prefix: (norm.prefix || "").toString().trim(),
            firstName: (norm.firstName || "").toString().trim(),
            lastName: (norm.lastName || "").toString().trim(),
            name: fullName,
            nickname: (norm.nickname || "").toString().trim(),
            phone: normalizePhone(norm.phone),
            department: (norm.department || "").toString().trim(),
            team: (norm.team || "").toString().trim(),
            position: (norm.position || "").toString().trim(),
            email: (norm.email || "").toString().trim(),
            dateAdd: todayDMY(),
        };

    });
}

// ======================================================
// Resolve master data relationship
// ======================================================
function resolveMasterForRow(row) {
    const depName = (row.department || "").trim();
    const teamName = (row.team || "").trim();
    const posName = (row.position || "").trim();

    const isMatch = (a, b) =>
        String(a || "").trim().toLowerCase() ===
        String(b || "").trim().toLowerCase();

    const dep = departments.value.find(d => isMatch(d.dpm_name, depName));
    if (!dep) return { ok: false, reason: "notFound", target: "Department" };

    let team = teams.value.find(
        t => isMatch(t.tm_name, teamName) && t.tm_department_id === dep.id
    );
    if (!team) {
        const teamExists = teams.value.find(t => isMatch(t.tm_name, teamName));
        if (teamExists) {
            return { ok: false, reason: "teamNotInDepartment", dep, team: teamExists };
        }
        return { ok: false, reason: "notFound", target: "Team" };
    }

    let pos = positions.value.find(
        p => isMatch(p.pst_name, posName) && p.pst_team_id === team.id
    );
    if (!pos) {
        const posExists = positions.value.find(p => isMatch(p.pst_name, posName));
        if (posExists) {
            return { ok: false, reason: "positionNotInTeam", dep, team, pos: posExists };
        }
        return { ok: false, reason: "notFound", target: "Position" };
    }

    return { ok: true, dep, team, pos };
}

// ======================================================
// Modal state
// ======================================================
const errorMessage = ref("");
const showCreateSuccess = ref(false);
const showCannotCreate = ref(false);

// ======================================================
function resolveCompanyId(companyCode) {
    if (!companyCode) return null;

    const code = String(companyCode).trim().toUpperCase();

    const companyMap = {
        CN: 1,
        CNI: 2,
        CNT: 3,
        WA: 4,
    };

    return companyMap[code] || null;
}

// ======================================================
// Bulk create employees
// ======================================================
async function onCreate() {
    if (!displayRows.value.length || creating.value) return;
    creating.value = true;

    const preparedRows = [];
    showCreateSuccess.value = false;
    showCannotCreate.value = false;
    errorMessage.value = "";

    try {
        // ======================================
        // STEP 1: validate + prepare payload
        // ======================================
        for (const [index, row] of displayRows.value.entries()) {
            const resolved = resolveMasterForRow(row);

            if (!resolved.ok) {
                errorMessage.value =
                    "Please make sure the Department, Team, and Position are entered correctly.";
                showCannotCreate.value = true;
                return;
            }

            const prefixText = String(row.prefix || "").trim();

            const prefixLookup = {
                "นาย": 1,
                "นาง": 2,
                "นางสาว": 3,
            };

            const emp_prefix = prefixLookup[prefixText];
            if (!emp_prefix) {
                errorMessage.value = `Invalid prefix value (Row ${index + 2}).`;
                showCannotCreate.value = true;
                return;
            }

            const companyId = resolveCompanyId(row.company);

            if (!companyId) {
                errorMessage.value = `Invalid company value (Row ${index + 2}).`;
                showCannotCreate.value = true;
                return;
            }

            const companyCode = String(row.company || "").trim().toUpperCase();
            const empNumber = String(row.employeeId || "").trim();
            const empIdCombined = `${companyCode}${empNumber}`;


            preparedRows.push({
                payload: {
                    emp_company_id: companyId,
                    emp_id: empIdCombined,
                    emp_prefix: emp_prefix,
                    emp_firstname: row.firstName ?? "",
                    emp_lastname: row.lastName ?? "",
                    emp_nickname: row.nickname || null,
                    emp_email: row.email,
                    emp_phone: row.phone,
                    emp_position_id: resolved.pos.id,
                    emp_department_id: resolved.dep.id,
                    emp_team_id: resolved.team.id,
                    emp_password: null,
                    emp_permission: "employee",
                    emp_status: 2,
                },
            });
        }

        // ======================================
        // STEP 2: check duplicate
        // ======================================
        for (const { payload } of preparedRows) {
            const resp = await axios.post("/check-employee-duplicate", {
                emp_id: payload.emp_id,
                emp_phone: payload.emp_phone,
                emp_email: payload.emp_email,
            });

            if (resp.data?.duplicate) {
                errorMessage.value =
                    "Sorry, there are some data are already in the system.";
                showCannotCreate.value = true;
                return;
            }
        }

        // ======================================
        // STEP 3: insert
        // ======================================
        for (const { payload } of preparedRows) {
            await axios.post("/save-employee", payload);
        }

        showCreateSuccess.value = true;
        displayRows.value = [];

    } catch (e) {
        console.error(e);

        // =========================
        // ดึง error จากระบบ (Backend)
        // =========================
        if (e.response) {
            // Laravel / API ทั่วไป
            errorMessage.value =
                e.response.data?.message ||
                e.response.data?.error ||
                "System error occurred while creating employee records.";
        } else if (e.request) {
            // ยิง API ไม่ได้
            errorMessage.value =
                "Unable to connect to the server. Please try again later.";
        } else {
            // Error ฝั่ง JS
            errorMessage.value = e.message || "Unexpected system error.";
        }

        showCannotCreate.value = true;
    }
    finally {
        creating.value = false;
    }
}

// ======================================================
// Success modal close
// ======================================================
function handleSuccessClose() {
    showCreateSuccess.value = false;
    router.push("/employee");
}

// ======================================================
// Download Excel Template
// ======================================================
async function downloadTemplate() {
    try {
        // ensure meta
        if (
            !departments.value.length ||
            !teams.value.length ||
            !positions.value.length
        ) {
            await loadMeta();
        }

        const workbook = new ExcelJS.Workbook();

        /* ============================
           FIXED COMPANY LIST
        ============================ */
        const companyList = ["CN", "CNI", "CNT", "WA"];

        /* ============================
           SHEET 1: UploadTemplate
        ============================ */
        const sheet = workbook.addWorksheet("UploadTemplate");

        sheet.addRow([
            "Company",
            "Employee ID",
            "Prefix",
            "First Name",
            "Last Name",
            "Nickname",
            "Department",
            "Team",
            "Position",
            "Phone",
            "Email",
        ]);

        sheet.addRow([
            "CN",
            "1111",
            "นาย",
            "สมชาย",
            "เขียวสะอาด",
            "หมกมุ่น",
            "Artificial Intelligence",
            "Artificial Intelligence",
            "AI Engineer",
            "0912345678",
            "employee@example.com",
        ]);

        /* ============================
        HEADER STYLE
        ============================ */
        const headerRow = sheet.getRow(1);

        headerRow.eachCell((cell) => {
            cell.font = {
                bold: true,
                color: { argb: "FFFFFFFF" },
            };

            cell.fill = {
                type: "pattern",
                pattern: "solid",
                fgColor: { argb: "FFEF5350" },
            };

            cell.alignment = {
                vertical: "middle",
                horizontal: "center",
            };

            cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" },
            };
        });

        headerRow.height = 22;
        sheet.views = [{ state: "frozen", ySplit: 1 }];


        sheet.columns = [
            { width: 15 },
            { width: 18 },
            { width: 12 },
            { width: 18 },
            { width: 18 },
            { width: 15 },
            { width: 22 },
            { width: 22 },
            { width: 25 },
            { width: 15 },
            { width: 28 },
            { width: 15 },
        ];

        const maxRow = 1000;

        const prefixList = ["นาย", "นาง", "นางสาว"].join(",");
        const deptList = departments.value.map(d => d.dpm_name).join(",");
        const teamList = teams.value.map(t => t.tm_name).join(",");
        const posList = positions.value.map(p => p.pst_name).join(",");

        for (let r = 2; r <= maxRow; r++) {
            sheet.getCell(`A${r}`).dataValidation = {
                type: "list",
                allowBlank: false,
                formulae: [`"${companyList.join(",")}"`],
            };

            sheet.getCell(`C${r}`).dataValidation = {
                type: "list",
                allowBlank: false,
                formulae: [`"${prefixList}"`],
            };

            sheet.getCell(`G${r}`).dataValidation = {
                type: "list",
                formulae: [`"${deptList}"`],
            };

            sheet.getCell(`H${r}`).dataValidation = {
                type: "list",
                formulae: [`"${teamList}"`],
            };

            sheet.getCell(`I${r}`).dataValidation = {
                type: "list",
                formulae: [`"${posList}"`],
            };

            sheet.getCell(`B${r}`).numFmt = "@";
            sheet.getCell(`J${r}`).numFmt = "@";
            sheet.getCell(`L${r}`).numFmt = "@";
        }

        /* ============================
           SHEET 2: Reference (SORTED + STYLED)
        ============================ */
        const refSheet = workbook.addWorksheet("Reference");

        // ---------- Header ----------
        refSheet.addRow(["Department", "Team", "Position"]);

        const refHeaderRow = refSheet.getRow(1);
        refHeaderRow.eachCell((cell) => {
            cell.font = {
                bold: true,
                color: { argb: "FFFFFFFF" }, // ขาว
            };
            cell.fill = {
                type: "pattern",
                pattern: "solid",
                fgColor: { argb: "FFEF5350" }, // แดง
            };
            cell.alignment = {
                vertical: "middle",
                horizontal: "center",
            };
            cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" },
            };
        });
        refHeaderRow.height = 22;

        // Column width
        refSheet.columns = [
            { width: 22 }, // Department
            { width: 22 }, // Team
            { width: 28 }, // Position
        ];

        // Freeze header
        refSheet.views = [{ state: "frozen", ySplit: 1 }];

        // ---------- Prepare Maps ----------
        const depMap = new Map(
            departments.value.map(d => [d.id, d.dpm_name])
        );
        const teamMap = new Map(
            teams.value.map(t => [t.id, t])
        );

        // ---------- Collect rows ----------
        const refRows = [];

        positions.value.forEach(pos => {
            const team = teamMap.get(pos.pst_team_id);
            if (!team) return;

            const depName = depMap.get(team.tm_department_id);
            if (!depName) return;

            refRows.push({
                department: depName,
                team: team.tm_name,
                position: pos.pst_name,
            });
        });

        // ---------- SORT A → Z ----------
        refRows.sort((a, b) => {
            return (
                a.department.localeCompare(b.department, "th") ||
                a.team.localeCompare(b.team, "th") ||
                a.position.localeCompare(b.position, "th")
            );
        });

        // ---------- Add to sheet + STYLE ----------
        refRows.forEach((r, index) => {
            const rowNumber = refSheet.rowCount + 1;
            refSheet.addRow([r.department, r.team, r.position]);

            const row = refSheet.getRow(rowNumber);

            row.eachCell((cell) => {
                // Zebra row (สลับสี)
                if (rowNumber % 2 === 0) {
                    cell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FFF5F5F5" }, // เทาอ่อน
                    };
                }

                cell.font = { size: 12 };
                cell.alignment = {
                    vertical: "middle",
                    horizontal: "left",
                    wrapText: true,
                };

                cell.border = {
                    top: { style: "thin" },
                    left: { style: "thin" },
                    bottom: { style: "thin" },
                    right: { style: "thin" },
                };
            });

            row.height = 20;
        });

        // AutoFilter
        refSheet.autoFilter = {
            from: { row: 1, column: 1 },
            to: { row: 1, column: refSheet.columnCount },
        };


        /* ============================
           EXPORT
        ============================ */
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "employee-template.xlsx";
        a.click();
        URL.revokeObjectURL(url);

    } catch (err) {
        console.error("downloadTemplate failed:", err);
        alert("ไม่สามารถสร้างไฟล์ Excel ได้");
    }
}
// ======================================================
// Cancel
// ======================================================
function onCancel() {
    displayRows.value = [];
    file.value = null;
    error.value = "";
    page.value = 1;
    isTableVisible.value = false; // แก้ไข: ซ่อนตารางเมื่อกด Cancel
    router.push("/add-employee");
}

// ======================================================
// DataTable columns
// ======================================================
const tableColumns = [
    { key: "index", label: "#", class: "text-left w-[72px] whitespace-nowrap" },
    { key: "displayEmployeeId", label: "Employee ID", class: "text-left w-[180px]" },
    { key: "name", label: "Name", class: "text-left w-[240px]" },
    { key: "nickname", label: "Nickname", class: "text-left w-[120px]" },
    { key: "phone", label: "Phone", class: "text-left w-[140px]" },
    { key: "department", label: "Department", class: "text-left w-[180px]" },
    { key: "team", label: "Team", class: "text-left w-[160px]" },
    { key: "position", label: "Position", class: "text-left w-[180px]" },
    { key: "email", label: "Email", class: "text-left w-[220px]" },
    { key: "dateAdd", label: "Date Add (D/M/Y)", class: "text-center w-[140px]" },
];

// ======================================================
// Enable / Disable Create button
// ======================================================
const canCreate = computed(() => {
    return displayRows.value.length > 0 &&
        !creating.value &&
        !uploading.value;
});
</script>