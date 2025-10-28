<template>
    <div class="min-h-screen">

        <head>
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        </head>

        <div class="p-6 max-w-[1400px] ml-12">
            <!-- Header -->
            <div class="pt-6">
                <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                    Upload file information
                </h2>

                <div class="mt-2 flex justify-center">
                    <button type="button" @click="downloadTemplate"
                        class="rounded-full border border-[neutral-200] px-4 py-2 text-xs font-medium italic text-[#1d9bf0] bg-white hover:bg-blue-50 transition">
                        *Click to download template excel file*
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 max-w-[1400px] mx-auto ml-0 md:ml-10 lg:ml-20 xl:ml-28 2xl:ml-40">
                <!-- Upload block -->
                <div class="mt-4">
                    <p class="text-sm font-semibold text-gray-800">
                        Upload file Excel
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Drag and drop document to your support task
                    </p>

                    <Upload class="mt-2" v-model:file="file" :max-size-mb="50" @invalid="msg => (error = msg)"
                        @picked="() => (error = '')" @cleared="() => (error = '')" />

                    <!-- ปุ่ม Generate -->
                    <div class="mt-3 flex justify-end">
                        <div :class="[
                            (!file || !!error || uploading)
                                ? 'opacity-50 cursor-not-allowed'
                                : 'cursor-pointer'
                        ]" :style="(!file || !!error || uploading)
                                ? 'pointer-events: none;'
                                : ''" :title="(!file || !!error || uploading)
                                    ? 'Please upload or drop file first'
                                    : ''">
                            <Button variant="generate" :disabled="!file || !!error || uploading" @click="upload">Generate Data</Button>
                        </div>
                    </div>

                    <!-- Error ตอนอ่านไฟล์ -->
                    <p v-if="error" class="text-sm text-red-500 mt-2">
                        {{ error }}
                    </p>
                </div>
            </div>

            <!-- Divider -->
            <div class="mt-20 border-t ml-16"></div>

            <!-- Table -->
            <div class="px-6 w-full max-w-[1700px] mx-auto mt-6 ml-12">
                <DataTable :loading="uploading || creating" :rows="paged" :columns="tableColumns" :page="page"
                    :page-size="pageSize" :total-items="totalItems" :page-size-options="[10, 25, 50, 100]"
                    :show-row-number="false" row-key="__rowKey" @update:page="val => page = val"
                    @update:pageSize="val => pageSize = val">
                    <!-- header "#" -->
                    <template #header-index>
                        #
                    </template>

                    <!-- cell "#" -->
                    <template #cell-index="{ row }">
                        {{ row.__displayIndex }}
                    </template>

                    <!-- empty state -->
                    <template #empty>
                        No Data Found
                    </template>

                    <!-- footer-info -->
                    <template #footer-info="{ from, to, total }">
                        <span>แสดง</span>

                        <div class="relative inline-block">
                            <select
                                class="appearance-none rounded-full border border-red-700 bg-white px-2 py-1 pr-8 focus:outline-none focus:ring-2 focus:ring-rose-200"
                                :value="pageSize" @change="e => { pageSize = Number(e.target.value); page = 1; }">
                                <option v-for="opt in [10, 25, 50, 100]" :key="opt" :value="opt">
                                    {{ opt }}
                                </option>
                            </select>
                            <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-red-700"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </div>

                        <span>{{ from }}-{{ to }} จาก {{ total }} รายการ</span>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Footer -->
        <div class="pb-8">
            <div class="mt-4 flex items-center justify-between">
                <div class="ml-8">
                    <Button variant="cancel" @click="onCancel">Cancel</Button>
                </div>

                <div class="mr-8" :class="canCreate
                    ? 'cursor-pointer'
                    : 'opacity-50 cursor-not-allowed'" :style="canCreate
                            ? ''
                            : 'pointer-events: none;'" :title="canCreate
                            ? ''
                            : 'Please upload file and click Generate Data first'">
                    <Button variant="create" :disabled="!canCreate" @click="onCreate">Create</Button>
                </div>
            </div>
        </div>

        <!-- Alerts / Modals -->
        <EmployeeCreateSuccess :open="showCreateSuccess" @close="handleSuccessClose" />
        <EmployeeCannotCreate :open="showCannotCreate" @close="showCannotCreate = false" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import * as XLSX from 'xlsx'
import axios from 'axios'

/* components */
import Upload from '@/components/Input/Upload.vue'
import EmployeeCreateSuccess from '@/components/Alert/Employee/EmployeeCreateSuccess.vue'
import EmployeeCannotCreate from '@/components/Alert/Employee/EmployeeCannotCreate.vue'
import Button from '@/components/Button.vue'
import DataTable from '@/components/DataTable.vue'

const router = useRouter()

/* ---------- state: upload / table ---------- */
const file = ref(null)
const error = ref('')
const uploading = ref(false)
const creating = ref(false)

const displayRows = ref([]) // ข้อมูลทั้งหมดจากไฟล์ (ยังไม่ยิง create)
const page = ref(1)
const pageSize = ref(10)

/* ---------- meta cache ---------- */
const departments = ref([])
const positions = ref([])
const teams = ref([])

async function loadMeta() {
    try {
        const { data } = await axios.get('/meta')
        departments.value = data.departments || []
        positions.value = data.positions || []
        teams.value = data.teams || []
    } catch (e) {
        console.error('Failed to load meta()', e)
    }
}
onMounted(loadMeta)

/* ---------- table pagination computed ---------- */
const totalItems = computed(() => displayRows.value.length)

const paged = computed(() => {
    const start = (page.value - 1) * pageSize.value
    const slice = displayRows.value.slice(start, start + pageSize.value)

    return slice.map((row, idx) => ({
        ...row,
        __rowKey: row.employeeId || `${start + idx}`,
        __displayIndex: start + idx + 1
    }))
})

watch([pageSize, totalItems], () => {
    const lastPage = Math.max(1, Math.ceil(totalItems.value / pageSize.value))
    if (page.value > lastPage) page.value = lastPage
})

/* ---------- upload & parse excel ---------- */
async function upload() {
    if (!file.value || error.value) return
    uploading.value = true

    try {
        const ext = file.value.name.split('.').pop()?.toLowerCase()
        const data = await readFile(file.value, ext === 'csv' ? 'text' : 'array')

        const wb = XLSX.read(data, {
            type: ext === 'csv' ? 'string' : 'array',
            cellDates: true
        })
        const ws = wb.Sheets[wb.SheetNames[0]]

        const rowsAoA = XLSX.utils.sheet_to_json(ws, {
            header: 1,
            defval: '',
            blankrows: false
        })

        const headerRowIdx = detectHeaderRow(rowsAoA)
        if (headerRowIdx === -1) throw new Error('ไม่พบหัวตาราง')

        const headers = rowsAoA[headerRowIdx].map(h => String(h).trim())
        const dataAoA = rowsAoA.slice(headerRowIdx + 1)

        const json = arraysToObjects(headers, dataAoA)
        const mapped = mapRows(json)

        displayRows.value = mapped
        page.value = 1

        error.value = mapped.length
            ? ''
            : 'ไฟล์อ่านได้ แต่ไม่พบแถวข้อมูลหลังหัวตาราง'
    } catch (e) {
        console.error(e)
        error.value = 'ไม่สามารถอ่านไฟล์ได้ กรุณาตรวจสอบรูปแบบข้อมูล'
    } finally {
        uploading.value = false
    }
}

/* ---------- helper: detect header row ---------- */
function detectHeaderRow(rowsAoA) {
    const candidates = [
        'employee id', 'employeeid', 'id',
        'ชื่อเล่น', 'nickname',
        'คำนำหน้า', 'prefix',
        'ชื่อ', 'firstname',
        'นามสกุล', 'lastname',
        'position', 'ตำแหน่ง',
        'department', 'แผนก', 'ฝ่าย',
        'team', 'ทีม',
        'phone', 'เบอร์', 'โทรศัพท์',
        'email', 'อีเมล',
        'date add', 'date', 'วันที่'
    ].map(s => s.replace(/\s+/g, '').toLowerCase())

    const maxScan = Math.min(rowsAoA.length, 30)
    for (let i = 0; i < maxScan; i++) {
        const row = (rowsAoA[i] || []).map(x =>
            String(x || '').toLowerCase().replace(/\s+/g, '')
        )
        let score = 0
        for (const cell of row) {
            if (candidates.includes(cell)) score++
        }
        if (score >= 2) return i
    }
    return -1
}

/* ---------- helper: AoA -> objects ---------- */
function arraysToObjects(headers, rows) {
    const out = []
    for (const r of rows) {
        if (!r || r.every(v => v === '' || v == null)) continue
        const obj = {}
        headers.forEach((h, idx) => {
            obj[h] = r[idx] ?? ''
        })
        out.push(obj)
    }
    return out
}

/* ---------- small utils ---------- */
function readFile(f, mode = 'array') {
    return new Promise((resolve, reject) => {
        const r = new FileReader()
        r.onload = () => resolve(r.result)
        r.onerror = reject
        if (mode === 'text') r.readAsText(f)
        else r.readAsArrayBuffer(f)
    })
}

function normalizeKey(k) {
    return String(k)
        .toLowerCase()
        .replace(/\s+/g, '')
        .replace(/[_\-\/\\().]/g, '')
}

function normalizePhone(p) {
    if (p == null) return ''
    if (typeof p === 'number') {
        return String(Math.trunc(p)).padStart(10, '0')
    }
    return String(p).replace(/[^\d]/g, '')
}

function toDMY(d) {
    const dd = String(d.getDate()).padStart(2, '0')
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const yy = d.getFullYear()
    return `${dd}/${mm}/${yy}`
}

/* ---------- mapRows (normalize headers / reshape row) ---------- */
function mapRows(rows) {
    const keyAlias = {
        company: 'company',
        'employeeid': 'employeeId',
        'employee id': 'employeeId',
        'รหัสพนักงาน': 'employeeId',
        'idพนักงาน': 'employeeId',
        'พนักงานid': 'employeeId',

        'คำนำหน้า': 'prefix',
        'คำนำหน้าชื่อ': 'prefix',
        'prefix': 'prefix',

        'ชื่อ': 'firstName',
        'firstname': 'firstName',

        'นามสกุล': 'lastName',
        'lastname': 'lastName',

        'ชื่อเล่น': 'nickname',
        'nickname': 'nickname',

        'position': 'position',
        'ตำแหน่ง': 'position',

        'department': 'department',
        'แผนก': 'department',
        'ฝ่าย': 'department',

        'team': 'team',
        'ทีม': 'team',

        'phone': 'phone',
        'โทรศัพท์': 'phone',
        'เบอร์': 'phone',

        'email': 'email',
        'อีเมล': 'email',

        'date add': 'dateAdd',
        'date': 'dateAdd',
        'วันที่': 'dateAdd'
    }

    return rows.map(r => {
        const norm = {}
        for (const [k, v] of Object.entries(r)) {
            const nk = normalizeKey(k)
            let mapped = keyAlias[nk]
            if (!mapped && nk.startsWith('dateadd')) mapped = 'dateAdd'
            norm[mapped || k] = v ?? ''
        }

        // รวมชื่อ
        const fullName = [norm.prefix, norm.firstName, norm.lastName]
            .filter(Boolean)
            .join(' ')
            .trim()

        // ปรับวันที่
        let dateAdd = ''
        if (norm.dateAdd) {
            if (norm.dateAdd instanceof Date) {
                dateAdd = toDMY(norm.dateAdd)
            } else if (!Number.isNaN(Date.parse(norm.dateAdd))) {
                dateAdd = toDMY(new Date(norm.dateAdd))
            } else if (!isNaN(Number(norm.dateAdd))) {
                const d = XLSX.SSF.parse_date_code(Number(norm.dateAdd))
                if (d) {
                    dateAdd = toDMY(
                        new Date(Date.UTC(d.y, d.m - 1, d.d))
                    )
                }
            }
        }

        return {
            employeeId: (norm.employeeId || '').toString().trim(),
            name: fullName,
            nickname: (norm.nickname || '').toString().trim(),
            phone: normalizePhone(norm.phone),
            department: (norm.department || '').toString().trim(),
            team: (norm.team || '').toString().trim(),
            position: (norm.position || '').toString().trim(),
            email: (norm.email || '').toString().trim(),
            dateAdd: (dateAdd || norm.dateAdd || '').toString().trim()
        }
    })
}

/* ---------- ensure masters ---------- */
async function ensureDepartmentId(depName) {
    if (!depName) return null
    const found = departments.value.find(d => d.dpm_name === depName)
    if (found) return found.id
    try {
        const resp = await axios.post('/save-department', { dpm_name: depName })
        const newId = resp.data?.id
        if (!newId) throw new Error('No id from /save-department')
        departments.value.push({ id: newId, dpm_name: depName })
        return newId
    } catch (err) {
        console.error('create department failed', err)
        return null
    }
}

async function ensurePositionId(posName) {
    if (!posName) return null
    const found = positions.value.find(p => p.pst_name === posName)
    if (found) return found.id
    try {
        const resp = await axios.post('/save-position', { pst_name: posName })
        const newId = resp.data?.id
        if (!newId) throw new Error('No id from /save-position')
        positions.value.push({ id: newId, pst_name: posName })
        return newId
    } catch (err) {
        console.error('create position failed', err)
        return null
    }
}

async function ensureTeamId(teamName) {
    if (!teamName) return null
    const found = teams.value.find(t => t.tm_name === teamName)
    if (found) return found.id
    try {
        const resp = await axios.post('/save-team', { tm_name: teamName })
        const newId = resp.data?.id
        if (!newId) throw new Error('No id from /save-team')
        teams.value.push({ id: newId, tm_name: teamName })
        return newId
    } catch (err) {
        console.error('create team failed', err)
        return null
    }
}

/* ---------- modal states ---------- */
const showCreateSuccess = ref(false)
const showCannotCreate = ref(false)

/* ---------- bulk create employees (partial success logic) ---------- */
async function onCreate() {
    if (!displayRows.value.length || creating.value) return
    creating.value = true

    const prefixMap = { 'นาย': 1, 'นาง': 2, 'นางสาว': 3 }

    try {
        // เตรียม payload ที่สร้างได้จริง (master พร้อม) เท่านั้น
        const preparedRows = []

        for (const row of displayRows.value) {
            // แตกชื่อ
            let emp_prefix = 1
            let emp_firstname = ''
            let emp_lastname = ''

            const parts = (row.name || '').trim().split(/\s+/)
            if (parts.length >= 3) {
                emp_prefix = prefixMap[parts[0]] ?? 1
                emp_firstname = parts[1] ?? ''
                emp_lastname = parts.slice(2).join(' ')
            } else if (parts.length === 2) {
                emp_firstname = parts[0]
                emp_lastname = parts[1]
            } else if (parts.length === 1 && parts[0]) {
                emp_firstname = parts[0]
            }

            // ensure master ids
            const depId = await ensureDepartmentId(row.department || '')
            const posId = await ensurePositionId(row.position || '')
            const teamId = await ensureTeamId(row.team || '')

            // ถ้าหา master id ไม่ได้ -> ข้ามแถวนี้ไป (เราจะไม่พยายามสร้าง)
            if (!depId || !posId || !teamId) {
                continue
            }

            preparedRows.push({
                row,
                payload: {
                    emp_id: (row.employeeId || '').trim(),
                    emp_prefix,
                    emp_nickname: row.nickname || null,
                    emp_firstname,
                    emp_lastname,
                    emp_email: row.email || '',
                    emp_phone: row.phone || '',
                    emp_position_id: posId,
                    emp_department_id: depId,
                    emp_team_id: teamId,
                    emp_password: 'Password123',
                    emp_status: 2,
                }
            })
        }

        // ไม่มีใครพร้อมสร้างเลย
        if (preparedRows.length === 0) {
            // ไม่มี payload valid -> ถือว่า fail
            showCreateSuccess.value = false
            showCannotCreate.value = true
            return
        }

        // นับผลลัพธ์
        let createdCount = 0    // insert สำเร็จ
        let dupCount = 0        // duplicate พบในระบบ
        let hardFailCount = 0   // error อื่น

        for (const item of preparedRows) {
            const p = item.payload

            // เช็ค duplicate ก่อน (optional safety)
            let isDuplicate = false
            try {
                const dupResp = await axios.post('/check-employee-duplicate', {
                    emp_id: p.emp_id,
                    emp_phone: p.emp_phone,
                    emp_email: p.emp_email
                })
                if (dupResp.data?.duplicate) {
                    isDuplicate = true
                }
            } catch (dupErr) {
                // ถ้า duplicate check พัง เราจะไม่ block ตรงนี้
                console.warn('duplicate check failed, continue anyway', dupErr)
            }

            if (isDuplicate) {
                dupCount++
                continue
            }

            // ถ้าไม่ถูก mark ว่าซ้ำ ลอง save จริง
            try {
                await axios.post('/save-employee', p)
                createdCount++
            } catch (empErr) {
                console.error(
                    'save-employee failed for',
                    p.emp_id,
                    empErr.response?.data || empErr.message
                )

                const reasonTextRaw =
                    empErr.response?.data?.message ||
                    empErr.response?.data?.error ||
                    ''

                const looksLikeDup =
                    /already\s+exists/i.test(reasonTextRaw) ||
                    /already been taken/i.test(reasonTextRaw) ||
                    /duplicate/i.test(reasonTextRaw) ||
                    /ซ้ำ/.test(reasonTextRaw) ||
                    /มีอยู่แล้ว/.test(reasonTextRaw)

                if (looksLikeDup) {
                    dupCount++
                } else {
                    hardFailCount++
                }
            }
        }

        // ตัดสินใจ modal
        const shouldShowCannotCreate =
            dupCount > 0 ||
            hardFailCount > 0 ||
            createdCount === 0

        const shouldShowCreateSuccess = createdCount > 0

        // ถ้าสร้างได้บางคนขึ้นไป -> ล้างตาราง/ไฟล์
        if (createdCount > 0) {
            displayRows.value = []
            file.value = null
            error.value = ''
            page.value = 1
        }

        showCannotCreate.value = shouldShowCannotCreate
        showCreateSuccess.value = shouldShowCreateSuccess
    } finally {
        creating.value = false
    }
}

/* ---------- modal success close ---------- */
function handleSuccessClose() {
    showCreateSuccess.value = false
    router.push('/employee')
}

/* ---------- download template ---------- */
function downloadTemplate() {
    const header = [
        'Company',
        'Employee ID',
        'ชื่อเล่น',
        'คำนำหน้า',
        'ชื่อ',
        'นามสกุล',
        'ID',
        'Position',
        'Department',
        'Team',
        'Phone',
        'Email',
        'Date Add'
    ]

    const sampleRow = [
        'CN',
        'CN0001',
        'มด',
        'นาย',
        'สมปอง',
        'แซ่บสุด',
        '—',
        'Software Engineer',
        'Product Development',
        'Mobile',
        "'0918231678",
        'employee@example.com',
        '20/08/2025'
    ]

    const ws = XLSX.utils.aoa_to_sheet([header, sampleRow])
    ws['!cols'] = header.map(h => {
        if (h === 'Email') return { wch: 28 }
        return { wch: Math.max(12, String(h).length + 2) }
    })

    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1')

    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })
    const blob = new Blob([wbout], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })

    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'employee-template.xlsx'
    a.click()
    URL.revokeObjectURL(url)
}

/* ---------- cancel button ---------- */
function onCancel() {
    displayRows.value = []
    file.value = null
    error.value = ''
    page.value = 1
    router.push('/add-employee')
}

/* ---------- columns for DataTable ---------- */
const tableColumns = [
    {
        key: 'index',
        label: '#',
        class: 'text-left w-[72px] whitespace-nowrap'
    },
    {
        key: 'employeeId',
        label: 'ID',
        class: 'text-left w-[140px] whitespace-nowrap'
    },
    {
        key: 'name',
        label: 'Name',
        class: 'text-left w-[240px] whitespace-nowrap'
    },
    {
        key: 'nickname',
        label: 'Nickname',
        class: 'text-left w-[120px] whitespace-nowrap'
    },
    {
        key: 'phone',
        label: 'Phone',
        class: 'text-left w-[140px] whitespace-nowrap'
    },
    {
        key: 'department',
        label: 'Department',
        class: 'text-left w-[180px] whitespace-nowrap'
    },
    {
        key: 'team',
        label: 'Team',
        class: 'text-left w-[160px] whitespace-nowrap'
    },
    {
        key: 'position',
        label: 'Position',
        class: 'text-left w-[180px] whitespace-nowrap'
    },
    {
        key: 'email',
        label: 'Email',
        class: 'text-left w-[220px] whitespace-nowrap'
    },
    {
        key: 'dateAdd',
        label: 'Date Add (D/M/Y)',
        class: 'text-center w-[140px] whitespace-nowrap'
    }
]

/* ---------- enable/disable ปุ่ม Create ---------- */
const canCreate = computed(() => {
    return displayRows.value.length > 0 && !creating.value && !uploading.value
})
</script>
