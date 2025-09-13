<template>
    <div class="min-h-screen">

        <head>
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        </head>
        <section class="max-w-6xl mx-auto p-6">
            <div class="bg-white rounded-2xl shadow-sm border">

                <!-- Card header -->
                <div class="px-6 pt-6">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                        Upload file information
                    </h2>

                    <div class="mt-2 flex items-center justify-center gap-3 flex-wrap">
                        <p class="text-xs text-gray-400 italic text-center">
                            *Click the button to download excel file*
                        </p>
                        <button type="button"
                            class="rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 text-[#444444]"
                            @click="downloadTemplate">
                            <b>Download</b>
                        </button>
                    </div>
                </div>

                <!-- Card body -->
                <div class="px-6 pb-6">

                    <!-- Upload block -->
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-gray-800">Upload file Excel</p>
                        <p class="text-xs text-gray-400 mt-1">Drag and drop document to your support task</p>

                        <!-- ▼ Drop zone: ลดขนาดกรอบเมื่อมีไฟล์ -->
                        <div
                            class="mt-2 rounded-2xl border-2 border-rose-300 border-dashed bg-rose-50 relative transition-all">
                            <label for="file-input" class="block">
                                <div class="relative cursor-pointer rounded-2xl transition-all" :class="[
                                    dragOver ? 'ring-2 ring-rose-300' : '',
                                    file ? 'min-h-[60px] p-2' : 'min-h-[300px] pt-20 pb-6 px-4'
                                ]" @dragover.stop.prevent="onDragOver" @dragleave.prevent="dragOver = false"
                                    @drop.stop.prevent="onDrop">
                                    <!-- แสดงชื่อไฟล์ -->
                                    <template v-if="file">
                                        <div class="px-2">
                                            <div
                                                class="flex items-center justify-between rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div
                                                        class="flex items-center justify-center h-8 w-8 rounded-md bg-red-600">
                                                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24"
                                                            fill="currentColor">
                                                            <path
                                                                d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5L13.5 2H6Z" />
                                                            <path d="M14 2v6h6" fill="#fff" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm text-gray-700 truncate">{{ file.name }}</span>
                                                </div>
                                                <button type="button"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                                    @click.stop="clearFile" aria-label="Remove file" title="Remove">
                                                    ✕
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- เนื้อหาเมื่อยังไม่มีไฟล์ -->
                                    <template v-else>
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <svg class="w-18 h-16 mb-3" viewBox="0 0 24 24" aria-hidden="true">
                                                <path class="text-rose-400" fill="currentColor"
                                                    d="M6 19h12a5 5 0 0 0 1.02-9.9A7 7 0 0 0 6.06 9.6 4 4 0 0 0 6 19Z" />
                                                <path fill="#ffffff" d="M12 8l-3.5 3.5h2.5V17h2v-5.5H15.5L12 8Z" />
                                            </svg>
                                            <p class="text-gray-600">Choose a file or drag &amp; drop it here</p>
                                            <p class="text-xs text-gray-400 mt-1">Excel/CSV (.xls, .xlsx, .csv) · Up to
                                                50MB</p>
                                            <button type="button"
                                                class="mt-4 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                                @click="openPicker">
                                                Browse files
                                            </button>
                                        </div>
                                    </template>

                                    <!-- input file -->
                                    <input id="file-input" ref="fileInput" type="file"
                                        accept=".xls,.xlsx,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv"
                                        class="hidden" @change="onPick" />
                                </div>
                            </label>

                            <!-- ปุ่ม Upload -->
                            <div class="absolute right-4 -bottom-5 translate-y-full">
                                <button type="button" :disabled="!file || !!error || uploading" @click="upload"
                                    class="inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                    <span
                                        class="material-symbols-outlined text-[18px] leading-none align-[-2px]">upload</span>
                                    Upload
                                </button>
                            </div>
                        </div>

                        <!-- Error -->
                        <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
                    </div>


                    <!-- Divider -->
                    <div class="mt-20 border-t"></div>


                    <!-- table -->
                    <div class="mt-4 overflow-x-auto bg-white border rounded-2xl shadow-sm">
                        <table class="min-w-full text-sm">
                            <colgroup>
                                <col style="width:72px" />
                                <col style="width:140px" />
                                <col style="width:280px" />
                                <col style="width:140px" />
                                <col style="width:160px" />
                                <col style="width:240px" />
                                <col style="width:180px" />
                                <col style="width:220px" />
                                <col style="width:240px" />
                            </colgroup>
                            <thead class="bg-[#F6F6F6] text-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">ID</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Name</th>
                                    <th class="px-4 py-3 text-left">Nickname</th>
                                    <th class="px-4 py-3 text-left">Phone</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Department</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Team</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Position</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">Date Add (d/m/Y)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(r, idx) in paged" :key="idx" class="border-t">
                                    <td class="px-4 py-3">{{ (page - 1) * pageSize + idx + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ r.employeeId }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ r.name }}</td>
                                    <td class="px-4 py-3">{{ r.nickname }}</td>
                                    <td class="px-4 py-3">{{ r.phone }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ r.department }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ r.team }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ r.position }}</td>
                                    <td class="px-4 py-3">{{ r.dateAdd }}</td>
                                </tr>
                                <tr v-if="!paged.length">
                                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                        ไม่พบข้อมูลที่ตรงกับคำค้นหา
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- toolbar -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4">
                        <!-- ซ้าย: แสดง [select] และช่วง -->
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <span>แสดง</span>

                            <!-- pill select สีแดง + ลูกศร -->
                            <div class="relative">
                                <select v-model.number="pageSize" class="appearance-none rounded-full border px-3 py-1.5 pr-8
               border-[#B70E15] text-[#B70E15] bg-white
               hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                                    <option v-for="s in [10, 25, 50, 100]" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <span
                                    class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-[#B70E15]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7 10l5 5 5-5z" />
                                    </svg>
                                </span>
                            </div>
                            <!-- ข้อความช่วง -->
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
                                class="min-w-[36px] rounded-xl px-3 py-1.5 text-sm font-semibold transition"
                                :class="page === it ? 'bg-[#B70E15] text-white' : 'border border-[#B70E15] text-[#B70E15] hover:bg-red-50'">
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
            </div>

            <!-- Card footer (Cancel / Create) -->
            <div class="px-6 pb-8">
                <div class="mt-4 flex items-center justify-between">
                    <button type="button" @click="onCancel"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white border border-transparent hover:opacity-95 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        style="background:#B70E15">
                        <span class="material-symbols-outlined text-[18px] leading-none align-[-2px]">close</span>
                        Cancel
                    </button>


                    <button type="button" :disabled="!displayRows.length" @click="onCreate"
                        class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50">
                        <span class="material-symbols-outlined text-[18px] leading-none align-[-2px]">
                            add
                        </span>
                        Create
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import * as XLSX from 'xlsx'

const emit = defineEmits(['cancel', 'uploaded', 'fileSelected'])

/* ---------- Upload state ---------- */
const router = useRouter()
const fileInput = ref(null)
const file = ref(null)
const error = ref('')
const dragOver = ref(false)
const uploading = ref(false)

const MAX_SIZE = 50 * 1024 * 1024
const ACCEPT_EXT = ['xls', 'xlsx', 'csv']
const ACCEPT_MIME = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/csv'
]

/* ---------- Table state ---------- */
const displayRows = ref([])
const page = ref(1)
const pageSize = ref(10)

watch(pageSize, () => { page.value = 1 })
const startIndex = computed(() => (page.value - 1) * pageSize.value)
const filtered = computed(() => displayRows.value)
const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize.value)))

const visibleCountText = computed(() => {
    const total = filtered.value.length
    if (total === 0) return '0-0 จาก 0 รายการ'
    const from = startIndex.value + 1
    const to = Math.min(startIndex.value + pageSize.value, total)
    return `${from}-${to} จาก ${total} รายการ`
})
const paged = computed(() => {
    const start = startIndex.value
    return filtered.value.slice(start, start + pageSize.value)
})
watch([filtered, pageSize], () => {
    const last = Math.max(1, Math.ceil(filtered.value.length / pageSize.value))
    if (page.value > last) page.value = last
})

/* ---------- UI handlers ---------- */
function openPicker() { fileInput.value?.click() }
function onPick(e) { const f = e.target.files?.[0]; handlePicked(f); e.target.value = '' }
function onDrop(e) {
    dragOver.value = false
    const f = e.dataTransfer?.items?.length
        ? Array.from(e.dataTransfer.items).find(i => i.kind === 'file')?.getAsFile()
        : e.dataTransfer?.files?.[0]
    handlePicked(f)
}
function onDragOver(e) { dragOver.value = true; if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy' }
function clearFile() { file.value = null; error.value = ''; emit('fileSelected', null) }

/* ---------- Read & parse ---------- */
function handlePicked(f) {
    error.value = ''
    if (!f) return
    const ext = f.name.split('.').pop()?.toLowerCase()
    if (!ext || !ACCEPT_EXT.includes(ext)) {
        error.value = 'Please select Excel/CSV file (.xls / .xlsx / .csv)'
        file.value = null
        emit('fileSelected', null)
        return
    }
    if (f.size > MAX_SIZE) {
        error.value = 'File is too large (max 50MB)'
        file.value = null
        emit('fileSelected', null)
        return
    }
    file.value = f
    emit('fileSelected', f)
}

async function upload() {
    if (!file.value || error.value) return
    uploading.value = true
    try {
        const ext = file.value.name.split('.').pop()?.toLowerCase()
        const data = await readFile(file.value, ext === 'csv' ? 'text' : 'array')

        // อ่าน workbook + แผ่นแรก
        const wb = XLSX.read(data, { type: ext === 'csv' ? 'string' : 'array', cellDates: true })
        const ws = wb.Sheets[wb.SheetNames[0]]

        // หา header แท้จาก 30 แถวแรก (รองรับไฟล์เทมเพลตที่ให้มา)
        const rowsAoA = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '', blankrows: false })
        const headerRowIdx = detectHeaderRow(rowsAoA)
        if (headerRowIdx === -1) throw new Error('ไม่พบหัวตาราง')

        const headers = rowsAoA[headerRowIdx].map(h => String(h).trim())
        const dataAoA = rowsAoA.slice(headerRowIdx + 1)

        // แปลงเป็น array-of-objects และ map ให้เป็น schema ของตาราง
        const json = arraysToObjects(headers, dataAoA)
        displayRows.value = mapRows(json)
        page.value = 1
        emit('uploaded', file.value)

        if (!displayRows.value.length) {
            error.value = 'ไฟล์อ่านได้ แต่ไม่พบแถวข้อมูลหลังหัวตาราง'
        } else {
            error.value = ''
        }
    } catch (e) {
        console.error(e)
        error.value = 'ไม่สามารถอ่านไฟล์ได้ กรุณาตรวจสอบรูปแบบข้อมูล'
    } finally {
        uploading.value = false
    }
}

/* ---------- Helpers ---------- */
function detectHeaderRow(rowsAoA) {
    // คีย์เวิร์ดหัวตารางที่ไฟล์เทมเพลตใช้ (ไทย/อังกฤษ)
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
        'date add', 'date', 'วันที่'
    ].map(s => s.replace(/\s+/g, '').toLowerCase())

    const maxScan = Math.min(rowsAoA.length, 30)
    for (let i = 0; i < maxScan; i++) {
        const row = (rowsAoA[i] || [])
            .map(x => String(x || '').toLowerCase().replace(/\s+/g, ''))
        let score = 0
        for (const cell of row) if (candidates.includes(cell)) score++
        if (score >= 2) return i
    }
    return -1
}

function arraysToObjects(headers, rows) {
    const out = []
    for (const r of rows) {
        if (!r || r.every(v => v === '' || v == null)) continue
        const obj = {}
        headers.forEach((h, idx) => { obj[h] = r[idx] ?? '' })
        out.push(obj)
    }
    return out
}

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
    return String(k).toLowerCase().replace(/\s+/g, '').replace(/[_\-\/\\().]/g, '')
}
function normalizePhone(p) {
    if (p == null) return ''
    if (typeof p === 'number') return String(Math.trunc(p)).padStart(10, '0')
    const s = String(p).replace(/[^\d]/g, '')
    return s // ไม่บังคับ 10 หลัก เผื่อเบอร์ภายใน
}
function toDMY(d) {
    const dd = String(d.getDate()).padStart(2, '0')
    const mm = String(d.getMonth() + 1).padStart(2, '0')
    const yy = d.getFullYear()
    return `${dd}/${mm}/${yy}`
}

// map แถวจากไฟล์ → schema ของตารางที่แสดง
function mapRows(rows) {
    // เคร่งกับหัวตารางที่มักพบในไฟล์เทมเพลต
    const keyAlias = {
        company: 'company',

        // รหัสพนักงาน
        'employeeid': 'employeeId', 'employee id': 'employeeId',
        'รหัสพนักงาน': 'employeeId', 'idพนักงาน': 'employeeId', 'พนักงานid': 'employeeId',

        // ชื่อ-สกุล
        'คำนำหน้า': 'prefix', 'คำนำหน้าชื่อ': 'prefix', 'prefix': 'prefix',
        'ชื่อ': 'firstName', 'firstname': 'firstName',
        'นามสกุล': 'lastName', 'lastname': 'lastName',
        'ชื่อเล่น': 'nickname', 'nickname': 'nickname',

        // องค์กร/ตำแหน่ง
        'position': 'position', 'ตำแหน่ง': 'position',
        'department': 'department', 'แผนก': 'department', 'ฝ่าย': 'department',
        'team': 'team', 'ทีม': 'team',

        // อื่น ๆ
        'phone': 'phone', 'โทรศัพท์': 'phone', 'เบอร์': 'phone',
        'email': 'email',
        'date add': 'dateAdd', 'date': 'dateAdd', 'วันที่': 'dateAdd'
    }

    return rows.map(r => {
        // ทำ key ให้เรียบ + แมปชื่อในระบบ
        const norm = {}
        for (const [k, v] of Object.entries(r)) {
            const nk = normalizeKey(k);
            let mapped = keyAlias[nk];
            if (!mapped && nk.startsWith('dateadd')) mapped = 'dateAdd';
            norm[mapped || k] = v ?? '';
        }

        // name = prefix + firstName + lastName
        const name = [norm.prefix, norm.firstName, norm.lastName].filter(Boolean).join(' ').trim()

        // Date Add → d/m/Y (รองรับ text, Date, และ serial number ของ Excel)
        let dateAdd = ''
        if (norm.dateAdd) {
            if (norm.dateAdd instanceof Date) {
                dateAdd = toDMY(norm.dateAdd)
            } else if (!Number.isNaN(Date.parse(norm.dateAdd))) {
                dateAdd = toDMY(new Date(norm.dateAdd))
            } else if (!isNaN(Number(norm.dateAdd))) {
                const d = XLSX.SSF.parse_date_code(Number(norm.dateAdd))
                if (d) dateAdd = toDMY(new Date(Date.UTC(d.y, d.m - 1, d.d)))
            }
        }

        return {
            employeeId: (norm.employeeId || '').toString().trim(),
            name,
            nickname: (norm.nickname || '').toString().trim(),
            phone: normalizePhone(norm.phone),
            department: (norm.department || '').toString().trim(),
            team: (norm.team || '').toString().trim(),
            position: (norm.position || '').toString().trim(),
            email: (norm.email || '').toString().trim(),
            dateAdd: (norm.dateAdd || '').toString().trim(),
        }
    })
}

/* ---------- Others ---------- */
function downloadTemplate() {
    const header = ['Company', 'Employee ID', 'ชื่อเล่น', 'คำนำหน้า', 'ชื่อ', 'นามสกุล', 'ID', 'Position', 'Department', 'Team', 'Phone', 'Date Add']
    const sampleRow = ['CN', 'CN0001', 'มด', 'นาย', 'สมปอง', 'แซ่บสุด', '—', 'Software Engineer', 'Engineering', 'CodeCraft', "'0918231678", '20/08/2025']
    const ws = XLSX.utils.aoa_to_sheet([header, sampleRow])
    ws['!cols'] = header.map(h => ({ wch: Math.max(12, String(h).length + 2) }))
    const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Sheet1')
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })
    const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = URL.createObjectURL(blob); const a = document.createElement('a')
    a.href = url; a.download = 'employee-template.xlsx'; a.click(); URL.revokeObjectURL(url)
}

function onCancel() {
    if (window.history.state && window.history.state.back) router.back()
    else emit('cancel')
}

/* ---------- Pager numbers (สไตล์รูปที่ 2) ---------- */
const pagerItems = computed(() => {
    const pages = totalPages.value
    const cur = page.value
    const out = []
    if (pages <= 7) { for (let i = 1; i <= pages; i++) out.push(i); return out }
    out.push(1)
    const left = Math.max(2, cur - 1)
    const right = Math.min(pages - 1, cur + 1)
    if (left > 2) out.push('dots')
    for (let i = left; i <= right; i++) out.push(i)
    if (right < pages - 1) out.push('dots')
    out.push(pages)
    return out
})

</script>
