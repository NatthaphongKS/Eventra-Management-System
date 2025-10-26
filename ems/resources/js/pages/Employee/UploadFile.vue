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
                    <p class="text-sm font-semibold text-gray-800">Upload file Excel</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Drag and drop document to your support task
                    </p>

                    <Upload class="mt-2" v-model:file="file" :max-size-mb="50" @invalid="msg => (error = msg)"
                        @picked="() => (error = '')" @cleared="() => (error = '')" />

                    <!-- ปุ่ม Generate -->
                    <div class="mt-3 flex justify-end">
                        <button type="button" :disabled="!file || !!error || uploading" @click="upload"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                            <span class="material-symbols-outlined text-[18px] leading-none align-[-2px]">
                                upload
                            </span>
                            Generate Data
                        </button>
                    </div>

                    <!-- Error -->
                    <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
                </div>

                <!-- Divider -->
                <div class="mt-20 border-t"></div>

                <!-- Preview table -->
                <div class="mt-4 overflow-x-auto bg-white border rounded-2xl shadow-sm max-w-full">
                    <table class="min-w-full text-sm">
                        <colgroup>
                            <col style="width:72px" />
                            <col style="width:140px" />
                            <col style="width:240px" />
                            <col style="width:120px" />
                            <col style="width:140px" />
                            <col style="width:180px" />
                            <col style="width:160px" />
                            <col style="width:180px" />
                            <col style="width:220px" />
                            <col style="width:140px" />
                        </colgroup>
                        <thead class="bg-[#F6F6F6] text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">ID</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Name</th>
                                <th class="px-4 py-3 text-left">Nickname</th>
                                <th class="px-4 py-3 text-left">Phone</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Department</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Team</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Position</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Email</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">Date Add (D/M/Y)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, idx) in paged" :key="idx" class="border-t">
                                <td class="px-4 py-3">
                                    {{ (page - 1) * pageSize + idx + 1 }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.employeeId }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.name }}</td>
                                <td class="px-4 py-3">{{ r.nickname }}</td>
                                <td class="px-4 py-3">{{ r.phone }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.department }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.team }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.position }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ r.email }}</td>
                                <td class="px-4 py-3">{{ r.dateAdd }}</td>
                            </tr>

                            <tr v-if="!paged.length">
                                <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                    ไม่พบข้อมูลที่ตรงกับไฟล์
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- toolbar -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <span>แสดง</span>

                        <!-- pill select -->
                        <div class="relative">
                            <select v-model.number="pageSize"
                                class="appearance-none rounded-full border px-3 py-1.5 pr-8 border-[#B70E15] text-[#B70E15] bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                                <option v-for="s in [10, 25, 50, 100]" :key="s" :value="s">
                                    {{ s }}
                                </option>
                            </select>
                            <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[#B70E15]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </span>
                        </div>

                        <span>{{ visibleCountText }}</span>
                    </div>
                </div>

                <!-- pagination -->
                <div class="mt-8 flex items-center justify-center gap-4 text-[#B70E15] select-none">
                    <!-- Prev -->
                    <button class="grid h-14 w-14 place-items-center rounded-full disabled:opacity-40"
                        :disabled="page === 1" @click="page > 1 && (page--)" aria-label="Previous" title="Previous">
                        <svg viewBox="0 0 24 24" class="h-8 w-8" fill="currentColor">
                            <path d="M15.5 6.5 9 12l6.5 5.5V6.5z" />
                        </svg>
                    </button>

                    <!-- Numbers + dots -->
                    <div v-for="(it, idx) in pagerItems" :key="`pg-${idx}-${it}`" class="contents">
                        <div v-if="it === 'dots'" class="flex items-center gap-1 px-1">
                            <span class="h-1 w-1 rounded-full bg-[#B70E15]"></span>
                            <span class="h-1 w-1 rounded-full bg-[#B70E15]"></span>
                            <span class="h-1 w-1 rounded-full bg-[#B70E15]"></span>
                        </div>
                        <button v-else @click="page = it"
                            class="min-w-[36px] rounded-xl px-3 py-1.5 text-sm font-semibold transition" :class="page === it
                                ? 'bg-[#B70E15] text-white'
                                : 'border border-[#B70E15] text-[#B70E15] hover:bg-red-50'
                                ">
                            {{ it }}
                        </button>
                    </div>

                    <!-- Next -->
                    <button class="grid h-14 w-14 place-items-center rounded-full disabled:opacity-40"
                        :disabled="page === totalPages" @click="page < totalPages && (page++)" aria-label="Next"
                        title="Next">
                        <svg viewBox="0 0 24 24" class="h-8 w-8" fill="currentColor">
                            <path d="M8.5 6.5 15 12l-6.5 5.5V6.5z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="pb-8">
                <div class="mt-4 flex items-center justify-between">
                    <button type="button" @click="onCancel"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white border border-transparent hover:opacity-95 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        style="background:#B70E15">
                        <span class="material-symbols-outlined text-[18px] leading-none align-[-2px]">
                            close
                        </span>
                        Cancel
                    </button>

                    <button type="button" :disabled="!displayRows.length || creating" @click="onCreate"
                        class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px] leading-none align-[-2px]">
                            add
                        </span>
                        Create
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <EmployeeCreateSuccess :open="showCreateSuccess" @close="handleSuccessClose" />
        <EmployeeCannotCreate :open="showCannotCreate" @close="showCannotCreate = false" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import * as XLSX from 'xlsx'
import axios from 'axios'
import Upload from '@/components/Input/Upload.vue'
import EmployeeCreateSuccess from '@/components/Alert/Employee/EmployeeCreateSuccess.vue'
import EmployeeCannotCreate from '@/components/Alert/Employee/EmployeeCannotCreate.vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const emit = defineEmits(['cancel', 'uploaded', 'fileSelected'])

const router = useRouter()

/* ---------- state: upload / table ---------- */
const file = ref(null)
const error = ref('')
const uploading = ref(false)
const creating = ref(false)

const displayRows = ref([])
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

/* ---------- pagination ---------- */
watch(pageSize, () => {
    page.value = 1
})

const startIndex = computed(() => (page.value - 1) * pageSize.value)
const filtered = computed(() => displayRows.value)
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filtered.value.length / pageSize.value))
)

const visibleCountText = computed(() => {
    const total = filtered.value.length
    if (total === 0) return '0-0 จาก 0 รายการ'
    const from = startIndex.value + 1
    const to = Math.min(startIndex.value + pageSize.value, total)
    return `${from}-${to} รายการ`
})

const paged = computed(() => {
    const start = startIndex.value
    return filtered.value.slice(start, start + pageSize.value)
})

watch([filtered, pageSize], () => {
    const last = Math.max(1, Math.ceil(filtered.value.length / pageSize.value))
    if (page.value > last) page.value = last
})

/* ---------- excel read & parse ---------- */
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
        displayRows.value = mapRows(json)
        page.value = 1

        emit('uploaded', file.value)

        error.value = displayRows.value.length
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
        for (const cell of row) if (candidates.includes(cell)) score++
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

/* ---------- utils ---------- */
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

/* ---------- map rows for preview ---------- */
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
        // normalize header keys
        const norm = {}
        for (const [k, v] of Object.entries(r)) {
            const nk = normalizeKey(k)
            let mapped = keyAlias[nk]
            if (!mapped && nk.startsWith('dateadd')) mapped = 'dateAdd'
            norm[mapped || k] = v ?? ''
        }

        // "<prefix> <first> <last>"
        const fullName = [norm.prefix, norm.firstName, norm.lastName]
            .filter(Boolean)
            .join(' ')
            .trim()

        // normalise date
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

/* ---------- ensure masters exist ---------- */
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

/* ---------- success modal state ---------- */
const showCreateSuccess = ref(false)
const showCannotCreate = ref(false)


/* ---------- bulk create employees ---------- */
async function onCreate() {
    if (!displayRows.value.length || creating.value) return

    creating.value = true

    const prefixMap = { 'นาย': 1, 'นาง': 2, 'นางสาว': 3 }

    const successList = []
    const failList = []

    try {
        for (const row of displayRows.value) {
            // --- เตรียมชื่อ ---
            let emp_prefix = 1
            let emp_firstname = ''
            let emp_lastname = ''

            const parts = row.name.trim().split(/\s+/)
            if (parts.length >= 3) {
                emp_prefix = prefixMap[parts[0]] ?? 1
                emp_firstname = parts[1] ?? ''
                emp_lastname = parts.slice(2).join(' ')
            } else if (parts.length === 2) {
                emp_firstname = parts[0]
                emp_lastname = parts[1]
            } else if (parts.length === 1) {
                emp_firstname = parts[0]
            }

            // --- ensure master data ---
            const depId = await ensureDepartmentId(row.department || '')
            const posId = await ensurePositionId(row.position || '')
            const teamId = await ensureTeamId(row.team || '')

            if (!depId || !posId || !teamId) {
                failList.push({
                    emp_id: row.employeeId,
                    reason: 'Cannot create/find Department / Team / Position',
                })
                continue
            }

            const payload = {
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

            try {
                await axios.post('/save-employee', payload)
                successList.push(row.employeeId)
            } catch (empErr) {
                console.error(
                    'save-employee failed for',
                    row.employeeId,
                    empErr.response?.data || empErr.message
                )
                failList.push({
                    emp_id: row.employeeId,
                    reason:
                        empErr.response?.data?.message ||
                        empErr.response?.data?.error ||
                        'save-employee failed',
                })
            }
        }

        // ---- หลัง loop: ตัดสินใจเปิด modal ไหน ----
        if (successList.length > 0) {
            // อย่างน้อยมีบางแถวสร้างสำเร็จ
            // เคลียร์ preview + ขึ้น modal success
            displayRows.value = []
            file.value = null
            error.value = ''
            page.value = 1

            showCreateSuccess.value = true
            showCannotCreate.value = false
        } else {
            // ไม่มีแถวไหนสำเร็จเลย -> เปิด modal fail
            showCreateSuccess.value = false
            showCannotCreate.value = true
        }
    } finally {
        creating.value = false
    }
}


/* ---------- กด OK บน popup success ---------- */
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

/* ---------- pager buttons list ---------- */
const pagerItems = computed(() => {
    const pages = totalPages.value
    const cur = page.value
    const out = []
    if (pages <= 7) {
        for (let i = 1; i <= pages; i++) out.push(i)
        return out
    }
    out.push(1)
    const left = Math.max(2, cur - 1)
    const right = Math.min(pages - 1, cur + 1)
    if (left > 2) out.push('dots')
    for (let i = left; i <= right; i++) out.push(i)
    if (right < pages - 1) out.push('dots')
    out.push(pages)
    return out
})

/* ---------- cancel button ---------- */
function onCancel() {
    displayRows.value = []
    file.value = null
    error.value = ''
    page.value = 1
    router.push('/employee')
}
</script>
