<template>
    <head>
        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        />
    </head>

    <!-- MAIN CONTENT -->
    <div class="flex-1 w-full">
        <!-- โซนหัวข้อ + upload (จัดกึ่งกลาง, responsive) -->
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8 pt-6">
            <!-- Header -->
            <div>
                <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                    Upload file information
                </h2>

                <div class="mt-2 flex justify-center">
                    <button
                        type="button"
                        @click="downloadTemplate"
                        class="rounded-full border border-[neutral-200] px-4 py-2 text-xs font-medium italic text-[#1d9bf0] bg-white hover:bg-blue-50 transition"
                    >
                        *Click to download template excel file*
                    </button>
                </div>
            </div>

            <!-- Body: Upload block -->
            <div class="mt-6">
                <p class="text-sm font-semibold text-gray-800">
                    Upload file Excel
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Drag and drop document to your support task
                </p>

                <Upload
                    class="mt-2"
                    v-model:file="file"
                    :max-size-mb="50"
                    @invalid="(msg) => (error = msg)"
                    @picked="() => (error = '')"
                    @cleared="() => (error = '')"
                />

                <!-- ปุ่ม Generate -->
                <div class="mt-3 flex justify-end">
                    <div
                        :class="[
                            !file || !!error || uploading
                                ? 'opacity-50 cursor-not-allowed'
                                : 'cursor-pointer',
                        ]"
                        :style="
                            !file || !!error || uploading
                                ? 'pointer-events: none;'
                                : ''
                        "
                        :title="
                            !file || !!error || uploading
                                ? 'Please upload or drop file first'
                                : ''
                        "
                    >
                        <GenerateDataButton
                            :disabled="!file || !!error || uploading"
                            @click="upload"
                        />
                    </div>
                </div>

                <!-- Error ตอนอ่านไฟล์ -->
                <p v-if="error" class="text-sm text-red-500 mt-2">
                    {{ error }}
                </p>
            </div>
        </div>

        <!-- Divider -->
        <div class="mt-10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="border-t"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="mt-6 mx-auto w-full max-w-[1700px] px-4 sm:px-6 lg:px-8">
            <!-- จอเล็กยังเลื่อนได้, จอใหญ่โชว์เต็มไม่ต้อง scroll -->
            <div class="overflow-x-auto lg:overflow-x-visible">
                <DataTable
                    :loading="uploading || creating"
                    :rows="paged"
                    :columns="tableColumns"
                    :page="page"
                    :page-size="pageSize"
                    :total-items="totalItems"
                    :page-size-options="[10, 25, 50, 100]"
                    :show-row-number="false"
                    row-key="__rowKey"
                    @update:page="(val) => (page = val)"
                    @update:pageSize="(val) => (pageSize = val)"
                >
                    <!-- header "#" -->
                    <template #header-index> # </template>

                    <!-- cell "#" -->
                    <template #cell-index="{ row }">
                        {{ row.__displayIndex }}
                    </template>

                    <!-- empty state -->
                    <template #empty> No Data Found </template>

                    <!-- footer-info -->
                    <template #footer-info="{ from, to, total }">
                        <span>แสดง</span>

                        <div class="relative inline-block mx-2">
                            <select
                                class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200 text-sm"
                                :value="pageSize"
                                @change="
                                    (e) => {
                                        pageSize = Number(e.target.value);
                                        page = 1;
                                    }
                                "
                            >
                                <option
                                    v-for="opt in [10, 25, 50, 100]"
                                    :key="opt"
                                    :value="opt"
                                >
                                    {{ opt }}
                                </option>
                            </select>
                            <svg
                                class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
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

    <!-- Footer (ปุ่ม) -->
    <div class="pb-8">
        <div
            class="mx-auto w-full max-w-9xl px-4 sm:px-6 lg:px-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex justify-start">
                <CancelButton @click="onCancel" />
            </div>

            <div
                class="flex justify-end"
                :class="
                    canCreate
                        ? 'cursor-pointer'
                        : 'opacity-50 cursor-not-allowed'
                "
                :style="canCreate ? '' : 'pointer-events: none;'"
                :title="
                    canCreate
                        ? ''
                        : 'Please upload file and click Generate Data first'
                "
            >
                <CreateButton :disabled="!canCreate" @click="onCreate" />
            </div>
        </div>
    </div>

    <!-- Alerts / Modals -->
    <ModalAlert
        v-model:open="showCreateSuccess"
        title="Success"
        message="Create employee success"
        type="success"
        @confirm="handleSuccessClose"
    />
    <EmployeeCannotCreate
        :open="showCannotCreate"
        :message="errorMessage"
        @close="showCannotCreate = false"
    />
    <EmployeeCannotCreate
        :open="showCannotCreate"
        title="ERROR!"
        :message="createErrorMessage"
        @close="showCannotCreate = false"
    />
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import * as XLSX from "xlsx";
import axios from "axios";

/* components */
import Upload from "@/components/Input/Upload.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";
import EmployeeCannotCreate from "../../components/Alert/Employee/EmployeeCannotCreate.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import CreateButton from "@/components/Button/CreateButton.vue";
import GenerateDataButton from "@/components/Button/GenerateDataButton.vue";
import DataTable from "@/components/DataTable.vue";

const router = useRouter();

/* ---------- state: upload / table ---------- */
const file = ref(null);
const error = ref("");
const uploading = ref(false);
const creating = ref(false);

const displayRows = ref([]); // ข้อมูลทั้งหมดจากไฟล์ (ยังไม่ยิง create)
const page = ref(1);
const pageSize = ref(10);

/* ---------- meta cache ---------- */
const departments = ref([]);
const positions = ref([]);
const teams = ref([]);

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
onMounted(loadMeta);

/* ---------- table pagination computed ---------- */
const totalItems = computed(() => displayRows.value.length);

const paged = computed(() => {
    const start = (page.value - 1) * pageSize.value;
    const slice = displayRows.value.slice(start, start + pageSize.value);

    return slice.map((row, idx) => ({
        ...row,
        __rowKey: row.employeeId || `${start + idx}`,
        __displayIndex: start + idx + 1,
    }));
});

watch([pageSize, totalItems], () => {
    const lastPage = Math.max(1, Math.ceil(totalItems.value / pageSize.value));
    if (page.value > lastPage) page.value = lastPage;
});

/* ---------- upload & parse excel ---------- */
async function upload() {
    if (!file.value || error.value) return;
    uploading.value = true;

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
        if (headerRowIdx === -1) throw new Error("ไม่พบหัวตาราง");

        const headers = rowsAoA[headerRowIdx].map((h) => String(h).trim());
        const dataAoA = rowsAoA.slice(headerRowIdx + 1);

        const json = arraysToObjects(headers, dataAoA);
        const mapped = mapRows(json);

        displayRows.value = mapped;
        page.value = 1;

        error.value = mapped.length
            ? ""
            : "Unable to read file. Please check the information.";
    } catch (e) {
        console.error(e);
        error.value = "Unable to read file. Please check the information.";
    } finally {
        uploading.value = false;
    }
}

/* ---------- helper: detect header row ---------- */
function detectHeaderRow(rowsAoA) {
    const candidates = [
        "employee id",
        "employeeid",
        "id",
        "ชื่อเล่น",
        "nickname",
        "คำนำหน้า",
        "prefix",
        "ชื่อ",
        "firstname",
        "นามสกุล",
        "lastname",
        "position",
        "ตำแหน่ง",
        "department",
        "แผนก",
        "ฝ่าย",
        "team",
        "ทีม",
        "phone",
        "เบอร์",
        "โทรศัพท์",
        "email",
        "อีเมล",
        "date add",
        "date",
        "วันที่",
    ].map((s) => s.replace(/\s+/g, "").toLowerCase());

    const maxScan = Math.min(rowsAoA.length, 30);
    for (let i = 0; i < maxScan; i++) {
        const row = (rowsAoA[i] || []).map((x) =>
            String(x || "")
                .toLowerCase()
                .replace(/\s+/g, "")
        );
        let score = 0;
        for (const cell of row) {
            if (candidates.includes(cell)) score++;
        }
        if (score >= 2) return i;
    }
    return -1;
}

/* ---------- helper: AoA -> objects ---------- */
function arraysToObjects(headers, rows) {
    const out = [];
    for (const r of rows) {
        if (!r || r.every((v) => v === "" || v == null)) continue;
        const obj = {};
        headers.forEach((h, idx) => {
            obj[h] = r[idx] ?? "";
        });
        out.push(obj);
    }
    return out;
}

/* ---------- small utils ---------- */
function readFile(f, mode = "array") {
    return new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(r.result);
        r.onerror = reject;
        if (mode === "text") r.readAsText(f);
        else r.readAsArrayBuffer(f);
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

function toDMY(d) {
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yy = d.getFullYear();
    return `${dd}/${mm}/${yy}`;
}

/* ---------- mapRows (normalize headers / reshape row) ---------- */
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

        "date add": "dateAdd",
        date: "dateAdd",
        วันที่: "dateAdd",
    };

    return rows.map((r) => {
        const norm = {};
        for (const [k, v] of Object.entries(r)) {
            const nk = normalizeKey(k);
            let mapped = keyAlias[nk];
            if (!mapped && nk.startsWith("dateadd")) mapped = "dateAdd";
            norm[mapped || k] = v ?? "";
        }

        // รวมชื่อ
        const fullName = [norm.prefix, norm.firstName, norm.lastName]
            .filter(Boolean)
            .join(" ")
            .trim();

        // ปรับวันที่
        let dateAdd = "";
        if (norm.dateAdd) {
            if (norm.dateAdd instanceof Date) {
                dateAdd = toDMY(norm.dateAdd);
            } else if (!Number.isNaN(Date.parse(norm.dateAdd))) {
                dateAdd = toDMY(new Date(norm.dateAdd));
            } else if (!isNaN(Number(norm.dateAdd))) {
                const d = XLSX.SSF.parse_date_code(Number(norm.dateAdd));
                if (d) {
                    dateAdd = toDMY(new Date(Date.UTC(d.y, d.m - 1, d.d)));
                }
            }
        }

        return {
            employeeId: (norm.employeeId || "").toString().trim(),
            name: fullName,
            nickname: (norm.nickname || "").toString().trim(),
            phone: normalizePhone(norm.phone),
            department: (norm.department || "").toString().trim(),
            team: (norm.team || "").toString().trim(),
            position: (norm.position || "").toString().trim(),
            email: (norm.email || "").toString().trim(),
            dateAdd: (dateAdd || norm.dateAdd || "").toString().trim(),
        };
    });
}

function resolveMasterForRow(row) {
    const depName = (row.department || "").trim();
    const teamName = (row.team || "").trim();
    const posName = (row.position || "").trim();

    // ฟังก์ชันช่วยเปรียบเทียบ: ตัดช่องว่างหน้าหลัง + แปลงเป็นตัวพิมพ์เล็ก ก่อนเทียบ
    const isMatch = (a, b) =>
        String(a || "")
            .trim()
            .toLowerCase() ===
        String(b || "")
            .trim()
            .toLowerCase();

    // 1. ค้นหา Department
    const dep = departments.value.find((d) => isMatch(d.dpm_name, depName));
    if (!dep) {
        return { ok: false, reason: "notFound", target: "Department" };
    }

    // 2. ค้นหา Team (ต้องตรงทั้งชื่อ และต้องอยู่ในแผนกที่ถูกต้อง)
    let team = teams.value.find(
        (t) => isMatch(t.tm_name, teamName) && t.tm_department_id === dep.id
    );

    // ถ้าหาแบบตรงเป๊ะไม่เจอ (อาจจะชื่อถูกแต่อยู่ผิดแผนก หรือไม่มีชื่อนี้เลย)
    if (!team) {
        const teamExistsAnywhere = teams.value.find((t) =>
            isMatch(t.tm_name, teamName)
        );
        if (teamExistsAnywhere) {
            // ชื่อทีมมีอยู่จริง แต่อยู่คนละแผนก
            return {
                ok: false,
                reason: "teamNotInDepartment",
                dep,
                team: teamExistsAnywhere,
                pos: null,
            };
        } else {
            // ไม่มีชื่อทีมนี้เลย
            return { ok: false, reason: "notFound", target: "Team" };
        }
    }

    // 3. ค้นหา Position (ต้องตรงทั้งชื่อ และต้องอยู่ในทีมที่ถูกต้อง)
    let pos = positions.value.find(
        (p) => isMatch(p.pst_name, posName) && p.pst_team_id === team.id
    );

    // ถ้าหาแบบตรงเป๊ะไม่เจอ
    if (!pos) {
        const posExistsAnywhere = positions.value.find((p) =>
            isMatch(p.pst_name, posName)
        );
        if (posExistsAnywhere) {
            // ชื่อตำแหน่งมีอยู่จริง แต่อยู่คนละทีม
            return {
                ok: false,
                reason: "positionNotInTeam",
                dep,
                team,
                pos: posExistsAnywhere,
            };
        } else {
            // ไม่มีชื่อตำแหน่งนี้เลย
            return { ok: false, reason: "notFound", target: "Position" };
        }
    }

    // 4. เจอครบถ้วนสมบูรณ์
    return { ok: true, dep, team, pos };
}

/* ---------- modal states ---------- */
const errorMessage = ref("");
const showCreateSuccess = ref(false);
const showCannotCreate = ref(false);

/* ---------- bulk create employees (partial success logic) ---------- */
/* ---------- bulk create employees (partial success logic) ---------- */
async function onCreate() {
    if (!displayRows.value.length || creating.value) return;
    creating.value = true;

    // ✅ 1. ประกาศตัวแปรไว้ด้านบนสุดเพื่อให้ทุก Step เข้าถึงได้
    const preparedRows = []; 
    showCreateSuccess.value = false;
    showCannotCreate.value = false;
    errorMessage.value = "";

    const prefixMap = { นาย: 1, นาง: 2, นางสาว: 3 };

    try {
        // ---------------------------------
        // STEP 1: เตรียม payload และเช็กความถูกต้อง
        // ---------------------------------
        for (const row of displayRows.value) {
            const resolved = resolveMasterForRow(row);

            if (!resolved.ok) {
                let errText = "Invalid data structure.";
                if (resolved.reason === "notFound") {
                    const target = resolved.target || "Item";
                    errText = `${target} "${target === "Department" ? row.department : target === "Team" ? row.team : row.position}" not found in the system.`;
                } else if (resolved.reason === "teamNotInDepartment") {
                    errText = `The Team "${row.team}" does not belong to the Department "${row.department}".`;
                } else if (resolved.reason === "positionNotInTeam") {
                    errText = `The Position "${row.position}" does not belong to the Team "${row.team}".`;
                }
                
                errorMessage.value = errText;
                showCannotCreate.value = true;
                creating.value = false;
                return; // หยุดทันทีถ้าข้อมูลโครงสร้างผิด
            }

            const { dep, team, pos } = resolved;
            let emp_prefix = 1;
            let emp_firstname = "";
            let emp_lastname = "";

            const parts = (row.name || "").trim().split(/\s+/);
            if (parts.length >= 3) {
                emp_prefix = prefixMap[parts[0]] ?? 1;
                emp_firstname = parts[1] ?? "";
                emp_lastname = parts.slice(2).join(" ");
            } else if (parts.length === 2) {
                emp_firstname = parts[0] ?? "";
                emp_lastname = parts[1] ?? "";
            } else if (parts.length === 1 && parts[0]) {
                emp_firstname = parts[0];
            }

            preparedRows.push({
                row,
                payload: {
                    emp_id: (row.employeeId || "").trim(),
                    emp_prefix,
                    emp_nickname: row.nickname || null,
                    emp_firstname,
                    emp_lastname,
                    emp_email: (row.email || "").trim(),
                    emp_phone: (row.phone || "").trim(),
                    emp_position_id: pos.id,
                    emp_department_id: dep.id,
                    emp_team_id: team.id,
                    emp_password: "Password123",
                    emp_status: 2,
                    emp_company_id: 1 // อย่าลืมใส่ Company ID ที่ถูกต้องตาม Error SQL ก่อนหน้านี้
                },
            });
        }

        if (preparedRows.length === 0) {
            showCannotCreate.value = true;
            creating.value = false;
            return;
        }

        // ---------------------------------
        // STEP 2: ตรวจซ้ำกับระบบ
        // ---------------------------------
        let foundDuplicateInSystem = false;
        for (const { payload } of preparedRows) {
            const checkBody = {
                emp_id: payload.emp_id,
                emp_phone: payload.emp_phone,
                emp_email: payload.emp_email
            };

            try {
                const dupResp = await axios.post("/check-employee-duplicate", checkBody);
                if (dupResp.data?.duplicate === true) {
                    foundDuplicateInSystem = true;
                    break;
                }
            } catch (dupErr) {
                console.error("API Check Duplicate Failed:", dupErr);
                creating.value = false;
                return;
            }
        }

        if (foundDuplicateInSystem) {
            errorMessage.value = `Sorry, There are some data are already in the system.`;
            showCannotCreate.value = true;
            creating.value = false;
            return;
        }

        // ---------------------------------
        // STEP 3: ไม่มีซ้ำ -> insert
        // ---------------------------------
        let createdCount = 0;
        for (const { payload } of preparedRows) {
            try {
                await axios.post("/save-employee", payload);
                createdCount++;
            } catch (err) {
                console.error("save-employee failed", err);
            }
        }

        if (createdCount === 0) {
            errorMessage.value = "Sorry, Please try again later";
            showCannotCreate.value = true;
        } else {
            displayRows.value = [];
            file.value = null;
            error.value = "";
            page.value = 1;
            showCreateSuccess.value = true;
        }

    } catch (error) {
        console.error("Global Error:", error);
    } finally {
        creating.value = false;
    }
}

/* ---------- modal success close ---------- */
function handleSuccessClose() {
    showCreateSuccess.value = false;
    router.push("/employee");
}

async function downloadTemplate() {
    if (
        !departments.value.length &&
        !teams.value.length &&
        !positions.value.length
    ) {
        try {
            await loadMeta();
        } catch (e) {
            console.error("loadMeta in downloadTemplate failed", e);
        }
    }

    const wb = XLSX.utils.book_new();

    // 1. Data Prep
    const depMap = new Map(
        (departments.value || []).map((d) => [d.id, d.dpm_name])
    );
    const teamMap = new Map((teams.value || []).map((t) => [t.id, t]));

    const relationRows = [];
    (positions.value || []).forEach((p) => {
        const team = teamMap.get(p.pst_team_id);
        if (!team) return;
        const depName = depMap.get(team.tm_department_id) || "";

        relationRows.push({ d: depName, t: team.tm_name, p: p.pst_name });
    });

    relationRows.sort((a, b) => {
        return (
            a.d.localeCompare(b.d) ||
            a.t.localeCompare(b.t) ||
            a.p.localeCompare(b.p)
        );
    });

    const distinctDeps = [
        ...new Set(relationRows.map((x) => x.d).filter(Boolean)),
    ].sort();
    const distinctTeams = [
        ...new Set(relationRows.map((x) => x.t).filter(Boolean)),
    ].sort();
    const distinctPositions = [
        ...new Set(relationRows.map((x) => x.p).filter(Boolean)),
    ].sort();

    // 2. Reference Sheet
    const refHeader = ["Department", "Team", "Position", "", "", "", "", ""];
    const refSheetData = [refHeader];

    const maxRow = Math.max(
        relationRows.length,
        distinctDeps.length,
        distinctTeams.length,
        distinctPositions.length
    );

    for (let i = 0; i < maxRow; i++) {
        const rel = relationRows[i] || {};
        refSheetData.push([
            rel.d || "",
            rel.t || "",
            rel.p || "",
            "",
            "",
            distinctDeps[i] || "",
            distinctTeams[i] || "",
            distinctPositions[i] || "",
        ]);
    }

    const wsRef = XLSX.utils.aoa_to_sheet(refSheetData);

    const refRange = XLSX.utils.decode_range(wsRef["!ref"]);
    // AutoFilter
    wsRef["!autofilter"] = {
        ref: XLSX.utils.encode_range({
            s: { r: 0, c: 0 },
            e: { r: refRange.e.r, c: 2 },
        }),
    };
    // Cols
    wsRef["!cols"] = [
        { wch: 25 },
        { wch: 25 },
        { wch: 35 },
        { wch: 5, hidden: true },
        { wch: 5, hidden: true },
        { wch: 20, hidden: true },
        { wch: 20, hidden: true },
        { wch: 20, hidden: true },
    ];

    // 3. UploadTemplate Sheet
    const header = [
        "Company",
        "Employee ID",
        "ชื่อเล่น",
        "คำนำหน้า",
        "ชื่อ",
        "นามสกุล",
        "ID",
        "Department",
        "Team",
        "Position",
        "Phone",
        "Email",
        "Date Add",
    ];

    const validSample =
        relationRows.length > 0 ? relationRows[0] : { d: "", t: "", p: "" };
    const sampleRow = [
        "CN",
        "Test001",
        "มด",
        "นาย",
        "สมปอง",
        "แซ่บสุด",
        "—",
        validSample.d,
        validSample.t,
        validSample.p,
        "0918231678",
        "employee@example.com",
        "20/08/2025",
        "",
    ];

    const wsTemplate = XLSX.utils.aoa_to_sheet([header, sampleRow]);

    // AutoFilter
    const tRange = XLSX.utils.decode_range(wsTemplate["!ref"]);
    wsTemplate["!autofilter"] = {
        ref: XLSX.utils.encode_range({
            s: { r: 0, c: 0 },
            e: { r: tRange.e.r, c: tRange.e.c },
        }),
    };

    // Warning Text (Keep text, remove style)
    const noteCell = XLSX.utils.encode_cell({ r: 0, c: 13 });
    wsTemplate[noteCell] = {
        v: "*กรอกเบอร์ไม่ต้องกรอก 0 นำหน้า ระบบจะเพิ่ม 0 ให้อัตโนมัติ",
        t: "s",
    };

    // Apply Formats (Only Format, no visual style)
    for (let R = tRange.s.r; R <= tRange.e.r; ++R) {
        for (let C = tRange.s.c; C <= tRange.e.c; ++C) {
            const cellAddr = XLSX.utils.encode_cell({ r: R, c: C });
            if (!wsTemplate[cellAddr]) continue;

            // Formats
            if (C === 1 || C === 10) {
                wsTemplate[cellAddr].z = "@";
                wsTemplate[cellAddr].t = "s";
            } else if (C === 12) {
                wsTemplate[cellAddr].z = "dd/mm/yyyy";
            }
        }
    }

    // Cols
    const colWidths = header.map((h) => {
        if (h === "Email") return { wch: 28 };
        if (h === "Date Add") return { wch: 15 };
        return { wch: Math.max(12, String(h).length + 5) };
    });
    colWidths.push({ wch: 60 });
    wsTemplate["!cols"] = colWidths;

    // Data Validation
    const rowsBuffer = 1000;
    const listDeptRef = `Reference!$F$2:$F$${Math.max(
        2,
        distinctDeps.length + 1
    )}`;
    const listTeamRef = `Reference!$G$2:$G$${Math.max(
        2,
        distinctTeams.length + 1
    )}`;
    const listPosRef = `Reference!$H$2:$H$${Math.max(
        2,
        distinctPositions.length + 1
    )}`;

    wsTemplate["!dataValidation"] = [
        {
            sqref: `H2:H${rowsBuffer}`,
            type: "list",
            operator: "between",
            formula1: distinctDeps.length ? listDeptRef : '"No Data"',
            showErrorMessage: true,
            error: "กรุณาเลือกแผนกจากรายการ",
        },
        {
            sqref: `I2:I${rowsBuffer}`,
            type: "list",
            operator: "between",
            formula1: distinctTeams.length ? listTeamRef : '"No Data"',
            showErrorMessage: true,
            error: "กรุณาเลือกทีมจากรายการ",
        },
        {
            sqref: `J2:J${rowsBuffer}`,
            type: "list",
            operator: "between",
            formula1: distinctPositions.length ? listPosRef : '"No Data"',
            showErrorMessage: true,
            error: "กรุณาเลือกตำแหน่งจากรายการ",
        },
        {
            sqref: `M2:M${rowsBuffer}`,
            type: "date",
            operator: "between",
            formula1: "1",
            formula2: "73415",
            showInputMessage: true,
            promptTitle: "Date Format",
            prompt: "กรุณากรอกวันที่ในรูปแบบ วว/ดด/ปปปป (เช่น 20/08/2025)",
            showErrorMessage: true,
            error: "กรุณากรอกวันที่ให้ถูกต้องตามรูปแบบ",
        },
    ];

    // Save
    XLSX.utils.book_append_sheet(wb, wsTemplate, "UploadTemplate");
    XLSX.utils.book_append_sheet(wb, wsRef, "Reference");
    const wbout = XLSX.write(wb, { bookType: "xlsx", type: "array" }); // Remove cellStyles: true
    const blob = new Blob([wbout], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "employee-template.xlsx";
    a.click();
    URL.revokeObjectURL(url);
}

/* ---------- cancel button ---------- */
function onCancel() {
    displayRows.value = [];
    file.value = null;
    error.value = "";
    page.value = 1;
    router.push("/add-employee");
}

/* ---------- columns for DataTable ---------- */
const tableColumns = [
    {
        key: "index",
        label: "#",
        class: "text-left w-[72px] whitespace-nowrap",
    },
    {
        key: "employeeId",
        label: "ID",
        class: "text-left w-[140px] whitespace-nowrap",
    },
    {
        key: "name",
        label: "Name",
        class: "text-left w-[240px] whitespace-nowrap",
    },
    {
        key: "nickname",
        label: "Nickname",
        class: "text-left w-[120px] whitespace-nowrap",
    },
    {
        key: "phone",
        label: "Phone",
        class: "text-left w-[140px] whitespace-nowrap",
    },
    {
        key: "department",
        label: "Department",
        class: "text-left w-[180px] whitespace-nowrap",
    },
    {
        key: "team",
        label: "Team",
        class: "text-left w-[160px] whitespace-nowrap",
    },
    {
        key: "position",
        label: "Position",
        class: "text-left w-[180px] whitespace-nowrap",
    },
    {
        key: "email",
        label: "Email",
        class: "text-left w-[220px] whitespace-nowrap",
    },
    {
        key: "dateAdd",
        label: "Date Add (D/M/Y)",
        class: "text-center w-[140px] whitespace-nowrap",
    },
];

/* ---------- enable/disable ปุ่ม Create ---------- */
const canCreate = computed(() => {
    return displayRows.value.length > 0 && !creating.value && !uploading.value;
});
</script>
