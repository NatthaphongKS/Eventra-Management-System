<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;      // ใช้เฉพาะ transaction
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;
use App\Mail\EventInvitationMail;
use App\Mail\EventCancellationMail;
use App\Mail\EventUpdateMail;
use App\Models\Connect;
use App\Models\File;       // กันชนกับ Facade/File
use Exception;

class EventController extends Controller
{
    /**
     * ดึงข้อมูลเมทาดาต้าทั้งหมดสำหรับหน้าแบบฟอร์มสร้างกิจกรรม
     * - Employees: ใช้ Eloquent + with() โหลดความสัมพันธ์ แล้ว map ให้มี *_name
     * - Positions/Departments/Teams/Categories: ดึงเฉพาะ active และจัดเรียงชื่อ
     */

    // GET /api/event-info
    public function index()
    {
        // คืนข้อมูลเป็น array ของอีเวนต์
        return response()->json(Event::orderBy('id', 'asc')->get());
    }
    //คืนชุด employee_ids ของอีเวนต์นั้น
    public function connectList($id)
    {
        $ids = DB::table('ems_connect')
            ->where('con_event_id', $id)
            ->where(function ($q) {
                $q->whereNull('con_delete_status')
                    ->orWhere('con_delete_status', '')
                    ->orWhere('con_delete_status', 'active');
            })
            ->pluck('con_employee_id'); // => [1,2,3,...]

        return response()->json(['employee_ids' => $ids]);
    }

    // GET /api/event/{id}
    public function show($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['message' => 'Not found'], 404);
        }
        // คืน object ของอีเวนต์เดียว (ไม่ห่อ data)
        return response()->json($event);
    }
    // ดึงข้อมูลทั้งหมดสำหรับแบบฟอร์มเดียว
    public function edit_pages($id)
    {
        $event = Event::Join('ems_categories', 'ems_event.evn_category_id', '=', 'ems_categories.id') //join ผ่าน id ategory เพื่อเอาชื่อ cat_name ของ event นั้นๆ
            ->where('ems_event.id', $id)
            ->select([
                'ems_event.id',
                'ems_event.evn_title',
                'ems_event.evn_description',
                'ems_event.evn_date',
                'ems_event.evn_timestart',
                'ems_event.evn_timeend',
                'ems_event.evn_location',
                'ems_event.evn_duration',
                'ems_event.evn_category_id',
                'ems_categories.cat_name as cat_name', //เก็บเป็นชื่อ category ที่เคยเลือกไว้
            ])
            ->firstOrFail(); //->firstOrFail() → ดึง แถวแรกที่เจอ แต่ถ้า ไม่เจอข้อมูลเลย จะ throw Exception

        $files = DB::table('ems_event_files')
            ->where('file_event_id', $event->id)
            ->select('id', 'file_name', 'file_path', 'file_size')
            ->orderBy('id', 'asc')
            ->get() //ส่วนดึงข้อมูลจาก DB
            ->map(function ($f) { //ใช้สร้าง URL ให้ frontend โหลดไฟล์จริง ต้องมี php artisan storage:link

                //ข้อมูลจาก file_path ก่อน Map { id: 1, file_name: "contract.pdf", file_path: "events/12/contract.pdf", file_size: 120000 }

                $f->url = asset('storage/' . $f->file_path); //เพิ่มข้อมูล Link url เข้าไป เพื่อให้ frontend  url: "http://localhost:8000/storage/events/12/contract.pdf"

                return $f;
                //.map() = วนทุกไฟล์ใน Collection

                // $f->url = asset(...) = เพิ่ม field url ที่เป็นลิงก์จริงไปยังไฟล์ใน public/storage

                // return $f; = คืน object ที่ถูกแก้ไข
            });
        $guestIds = DB::table('ems_connect')
            ->where('con_event_id', $event->id)
            ->where('con_delete_status', 'active')
            ->pluck('con_employee_id'); // [1,5,9,...]

        return response()->json([ //ศ่วนส่งไปหน้าบ้าน
            'event' => $event,
            'files' => $files,
            'guest_ids' => $guestIds,
        ]);
    }

    public function Update(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:ems_event,id',
            'evn_title' => 'required|string|max:255',
            // ... (Validation rules อื่นๆ เหมือนเดิม) ...
            'evn_category_id' => 'sometimes|integer|exists:ems_categories,id',
            'evn_description' => 'sometimes|nullable|string',
            'evn_date' => 'sometimes|date',
            'evn_timestart' => 'sometimes',
            'evn_timeend' => 'sometimes',
            'evn_location' => 'sometimes|string|max:255',
            'evn_duration' => 'sometimes|integer|min:0',
            'attachments' => 'sometimes|array',
            'delete_file_ids' => 'sometimes|array',
            'employee_ids' => 'sometimes|array',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $event = Event::lockForUpdate()->findOrFail($data['id']);

            // 1. เก็บค่าเดิมไว้เปรียบเทียบ
            $oldDate      = $event->evn_date;
            $oldStart     = substr($event->evn_timestart, 0, 5);
            $oldEnd       = substr($event->evn_timeend, 0, 5);
            $oldLocation  = $event->evn_location;

            // 2. อัปเดตข้อมูล
            $event->evn_title = $data['evn_title'];
            if ($request->has('evn_category_id')) $event->evn_category_id = $data['evn_category_id'];
            if ($request->has('evn_description')) $event->evn_description = $data['evn_description'];
            if ($request->has('evn_date'))        $event->evn_date = $data['evn_date'];
            if ($request->has('evn_timestart'))   $event->evn_timestart = $data['evn_timestart'];
            if ($request->has('evn_timeend'))     $event->evn_timeend = $data['evn_timeend'];
            if ($request->has('evn_location'))    $event->evn_location = $data['evn_location'];
            if ($request->has('evn_duration')) {
                $minutes = max(0, (int) $data['evn_duration']);
                $event->evn_duration = (int) ceil($minutes / 60);
            }
            $event->save();

            // 3. ตรวจสอบว่ามีการแก้ไขข้อมูลสำคัญหรือไม่
            $newStart = substr($event->evn_timestart, 0, 5);
            $newEnd   = substr($event->evn_timeend, 0, 5);
            $isCriticalChange = (
                $oldDate != $event->evn_date ||
                $oldStart != $newStart ||
                $oldEnd != $newEnd ||
                $oldLocation != $event->evn_location
            );

            // ... (ส่วนจัดการไฟล์ - ลบ/เพิ่มไฟล์ คงเดิมไว้) ...
            if ($request->filled('delete_file_ids')) {
                $ids = array_values(array_unique($request->input('delete_file_ids', [])));
                $files = DB::table('ems_event_files')->where('file_event_id', $event->id)->whereIn('id', $ids)->get();
                foreach ($files as $f) Storage::disk('public')->delete($f->file_path);
                DB::table('ems_event_files')->where('file_event_id', $event->id)->whereIn('id', $ids)->delete();
            }
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');
                    DB::table('ems_event_files')->insert([
                        'file_event_id' => $event->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_at' => now(),
                    ]);
                }
            }
            $remain = DB::table('ems_event_files')->where('file_event_id', $event->id)->count();
            $event->evn_file = $remain > 0 ? 'have' : 'not_have';
            $event->save();


            // =========================================================
            // 4. จัดการพนักงาน (แยก Logic คนใหม่ / คนเก่า)
            // =========================================================
            $idsToAdd = []; // เก็บ ID คนมาใหม่ไว้กันซ้ำ

            if ($request->has('employee_ids')) {
                $incomingIds = collect($request->input('employee_ids') ?? [])->map(fn($id) => (int)$id)->unique()->values()->all();
                $currentActiveIds = DB::table('ems_connect')
                    ->where('con_event_id', $event->id)
                    ->where('con_delete_status', 'active')
                    ->pluck('con_employee_id')->map(fn($id) => (int)$id)->all();

                $idsToAdd = array_values(array_diff($incomingIds, $currentActiveIds));
                $idsToRemove = array_values(array_diff($currentActiveIds, $incomingIds));

                // 4.1 เพิ่มคนใหม่ (ADD) -> ส่ง Invitation Mail ปกติ
                if (!empty($idsToAdd)) {
                    foreach ($idsToAdd as $empId) {
                        $exists = DB::table('ems_connect')->where('con_event_id', $event->id)->where('con_employee_id', $empId)->first();
                        if ($exists) {
                            DB::table('ems_connect')->where('id', $exists->id)->update(['con_delete_status' => 'active', 'con_answer' => 'invalid', 'con_reason' => null]);
                        } else {
                            DB::table('ems_connect')->insert(['con_event_id' => $event->id, 'con_employee_id' => $empId, 'con_answer' => 'invalid', 'con_delete_status' => 'active']);
                        }
                    }

                    // *** ส่งอีเมลเชิญ (Invitation) ให้คนใหม่ ***
                    $newEmployees = Employee::whereIn('id', $idsToAdd)->get();
                    $currentFiles = DB::table('ems_event_files')->where('file_event_id', $event->id)->get();
                    foreach ($newEmployees as $emp) {
                        if ($emp->emp_email) {
                            Mail::to($emp->emp_email)->send(new EventInvitationMail($emp, $event, $currentFiles));
                        }
                    }
                }

                // 4.2 ลบคนเก่า (REMOVE) -> ส่ง Cancellation Mail
                if (!empty($idsToRemove)) {
                    DB::table('ems_connect')->where('con_event_id', $event->id)->whereIn('con_employee_id', $idsToRemove)->update(['con_delete_status' => 'inactive']);
                    $removedEmployees = Employee::whereIn('id', $idsToRemove)->get();
                    foreach ($removedEmployees as $emp) {
                        if ($emp->emp_email) Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
                    }
                }
            }

            // =========================================================
            // 5. แจ้งเตือนคนเดิม (Update) เฉพาะเมื่อมีการแก้ไขสำคัญ
            // =========================================================
            if ($isCriticalChange) {
                // รีเซ็ตสถานะ "เฉพาะคนเดิม" (ไม่รวมคนใหม่ เพราะคนใหม่เพิ่งได้ invite ไปตะกี้)
                // คนใหม่ ($idsToAdd) สถานะเป็น invalid อยู่แล้วจากการ insert/update ข้างบน

                // ดึงคนเดิมที่ Active อยู่
                $existingParticipants = DB::table('ems_connect')
                    ->where('con_event_id', $event->id)
                    ->where('con_delete_status', 'active')
                    ->whereNotIn('con_employee_id', $idsToAdd) // <--- สำคัญ: ไม่เอาคนเพิ่งเพิ่ม
                    ->pluck('con_employee_id');

                if ($existingParticipants->isNotEmpty()) {
                    // รีเซ็ตสถานะคนเดิมให้ตอบรับใหม่
                    DB::table('ems_connect')
                        ->where('con_event_id', $event->id)
                        ->whereIn('con_employee_id', $existingParticipants)
                        ->update(['con_answer' => 'invalid', 'con_reason' => null]);

                    // ส่งเมลแจ้ง Update ให้คนเดิม
                    $employeesToUpdate = Employee::whereIn('id', $existingParticipants)->get();
                    $filesToSend = DB::table('ems_event_files')->where('file_event_id', $event->id)->get();

                    foreach ($employeesToUpdate as $emp) {
                        if ($emp->emp_email) {
                            Mail::to($emp->emp_email)->send(new EventUpdateMail($emp, $event, $filesToSend));
                        }
                    }
                }
            }

            // (6) ส่งข้อมูลกลับ
            $files = DB::table('ems_event_files')
                ->where('file_event_id', $event->id)
                ->select('id', 'file_name', 'file_path', 'file_type', 'file_size', 'uploaded_at')
                ->orderBy('id', 'asc')->get()
                ->map(function ($f) {
                    $f->url = asset('storage/' . $f->file_path);
                    return $f;
                });

            return response()->json([
                'message' => 'บันทึกข้อมูลสำเร็จ',
                'event' => $event,
                'files' => $files,
            ], 200);
        });
    }

    /**
     * สร้างกิจกรรมใหม่ + อัปโหลดไฟล์ + ผูกผู้เข้าร่วม + ส่งอีเมลเชิญ
     * - เก็บ evn_duration เป็น "ชั่วโมง" (รับมาหน่วยนาที)
     * - ใช้ความสัมพันธ์ของ Eloquent: $event->files()->create(), $event->connects()->createMany()
     * - แนบไฟล์ใน Mailable จาก path ที่อัปโหลด
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_title'        => 'required|string|max:255',
            'event_category_id'  => 'required|exists:ems_categories,id',
            'event_description'  => 'nullable|string',
            'event_date'         => 'required|date',
            'event_timestart'    => 'required|date_format:H:i',
            'event_timeend'      => 'required|date_format:H:i',
            'event_duration'     => 'required|integer|min:0', // นาที
            'event_location'     => 'required|string|max:255',

            'attachments' => 'array',
            'attachments.*' => 'file|max:51200|mimes:pdf,txt,doc,docx,jpg,jpeg,png,xlsx,xls',

            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:ems_employees,id',
        ]);


        return DB::transaction(function () use ($request, $data) {

            // 1) สร้างกิจกรรม
            $event = Event::create([
                'evn_title'        => $data['event_title'],
                'evn_category_id'  => $data['event_category_id'],
                'evn_description'  => $data['event_description'] ?? null,
                'evn_date'         => $data['event_date'],
                'evn_timestart'    => $data['event_timestart'],
                'evn_timeend'      => $data['event_timeend'],
                'evn_duration'     => $data['event_duration'],
                'evn_location'     => $data['event_location'],
                'evn_file'         => $request->hasFile('attachments') ? 'have' : 'not_have',
                'evn_create_by'    => Auth::id(),
                'evn_status'       => 'upcoming',
            ]);

            // 2) อัปโหลดไฟล์ + บันทึกผ่านความสัมพันธ์ files()
            $savedFiles = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');

                    $fileRow = $event->files()->create([
                        'file_name'   => $file->getClientOriginalName(),
                        'file_path'   => $path,
                        'file_type'   => $file->getClientMimeType(),
                        'file_size'   => $file->getSize(),
                        'uploaded_at' => now(),
                    ]);

                    $savedFiles[] = [
                        'file_name' => $fileRow->file_name,
                        'file_path' => $fileRow->file_path,
                        'file_type' => $fileRow->file_type,
                        'file_size' => $fileRow->file_size,
                    ];
                }
            }

            // 3) ผูกผู้เข้าร่วม (ems_connect) ผ่านความสัมพันธ์ connects()
            $connectRows = collect($data['employee_ids'])
                ->unique()
                ->map(fn($eid) => [
                    // 'con_event_id' จะถูกใส่อัตโนมัติจากความสัมพันธ์
                    'con_employee_id'   => $eid,
                    'con_answer'        => 'invalid',
                    'con_reason'        => null,
                    'con_delete_status' => 'active',
                ])
                ->values()
                ->all();

            $event->connects()->createMany($connectRows);

            // 4) ส่งอีเมลเชิญ
            $employees = Employee::whereIn('id', $data['employee_ids'])
                ->get(['id', 'emp_email', 'emp_firstname', 'emp_lastname']);

            foreach ($employees as $emp) {
                if (!$emp->emp_email) {
                    continue;
                }
                Mail::to($emp->emp_email)->send(new EventInvitationMail($emp, $event, $savedFiles));
                // หรือใช้คิว: Mail::to(...)->queue(new EventInvitationMail(...));
            }

            return response()->json([
                'message'  => 'สร้างกิจกรรมและส่งอีเมลเชิญแล้ว',
                'event'    => $event,
                'redirect' => '/event',
            ], 201);
        });
    }

    /**
     * ตารางกิจกรรม (server-side query + ค้นหา + เรียงลำดับ)
     * - ใช้ Eloquent เป็นฐาน, join หมวดหมู่เพื่อให้ sort ตาม cat_name ได้
     * - ใช้ withCount เพื่อนับผู้เข้าร่วมจริง (active) และผู้ตอบรับ (accept)
     * - รองรับ keyword ค้นหาใน title/description/status และชื่อหมวดหมู่
     * - คืนค่า cat_name และ evn_cat_id (alias ของ evn_category_id) ให้ Vue ใช้งาน
     */
    public function Eventtable(Request $request)
    {
        $this->syncEventStatus();
        // อนุญาตให้ sort ตามชื่อคอลัมน์/alias ที่ select มา
        $allowSort = [
            'evn_title'      => 'ems_event.evn_title',
            'cat_name'       => 'cat_name',
            'evn_date'       => 'ems_event.evn_date',
            'evn_duration'   => 'ems_event.evn_duration',
            'evn_num_guest'  => 'evn_num_guest',
            'evn_sum_accept' => 'evn_sum_accept',
            'evn_status'     => 'ems_event.evn_status',
        ];

        $sortBy  = $request->query('sortBy', 'evn_date');
        $sortDir = strtolower($request->query('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = $allowSort[$sortBy] ?? 'ems_event.evn_date';

        $q = trim((string) $request->query('q', ''));

        // สร้าง subquery สำหรับนับทั้งหมด (active)
        $subTotal = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active');

        // สร้าง subquery สำหรับนับที่ตอบรับ (active + accept)
        $subAccept = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active')
            ->where('con_answer', 'accepted');

        $rows = Event::query()
            ->leftJoin('ems_categories as c', 'c.id', '=', 'ems_event.evn_category_id')

            // เลือกฟิลด์ + ผูก subquery เป็น alias
            ->select([
                'ems_event.id',
                'ems_event.evn_title',
                DB::raw('ems_event.evn_category_id as evn_cat_id'),
                DB::raw('COALESCE(c.cat_name, "") as cat_name'),
                // 'ems_event.evn_description', // ยังไม่ได้เรียกใช้ ลบได้แต่ยังเก็บไว้ก่อน
                'ems_event.evn_date',
                'ems_event.evn_timestart',
                'ems_event.evn_timeend',
                // 'ems_event.evn_duration', // ยังไม่ได้เรียกใช้ ลบได้แต่ยังเก็บไว้ก่อน
                DB::raw('COALESCE(ems_event.evn_status, "") as evn_status'),
            ])
            ->selectSub($subTotal,  'evn_num_guest')
            ->selectSub($subAccept, 'evn_sum_accept')

            // ไม่เอา status = deleted (ไม่ต้องพึ่ง scope ในโมเดล)
            ->where(function ($w) {
                $w->whereNull('ems_event.evn_status')
                    ->orWhere('ems_event.evn_status', '!=', 'deleted');
            })

            // ค้นหา
            ->when($q !== '', function ($builder) use ($q) {
                $like = '%' . $q . '%';
                $builder->where(function ($w) use ($like) {
                    $w->where('ems_event.evn_title', 'like', $like)
                        ->orWhere('ems_event.evn_description', 'like', $like)
                        ->orWhere('ems_event.evn_status', 'like', $like)
                        ->orWhere('c.cat_name', 'like', $like);
                });
            })

            // เรียงตามคอลัมน์ที่อนุญาต (รวม alias จาก selectSub)
            ->orderBy($sortCol, $sortDir)
            ->get();

        return response()->json($rows);
    }

    private function syncEventStatus(): void
    {
        $lock = Cache::lock('events:sync-status', 30); // ล็อก 30 วินาที

        if (!$lock->get()) {
            return; // มีคนอื่นเพิ่ง sync ไปแล้ว
        }

        $now = Carbon::now('Asia/Bangkok')->format('Y-m-d H:i:s');

        DB::table('ems_event')
            ->where(function ($q) {
                $q->whereNull('evn_status')
                    ->orWhereNotIn('evn_status', ['done', 'deleted']);
            })
            ->whereRaw("TIMESTAMP(evn_date, evn_timeend) <= ?", [$now])
            ->update(['evn_status' => 'done']);

        DB::table('ems_event')
            ->where(function ($q) {
                $q->whereNull('evn_status')
                    ->orWhereNotIn('evn_status', ['ongoing', 'done', 'deleted']);
            })
            ->whereRaw("TIMESTAMP(evn_date, evn_timestart) <= ?", [$now])
            ->whereRaw("TIMESTAMP(evn_date, evn_timeend) > ?", [$now])
            ->update(['evn_status' => 'ongoing']);

        DB::table('ems_event')
            ->where(function ($q) {
                $q->whereNull('evn_status')
                    ->orWhereNotIn('evn_status', ['upcoming', 'deleted']);
            })
            ->whereRaw("TIMESTAMP(evn_date, evn_timestart) > ?", [$now])
            ->update(['evn_status' => 'upcoming']);
    }

    // ดึงสิทธิ์ของผู้ใช้ปัจจุบัน
    public function permission()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['message' => 'Unauthenticated'], 401);

        $emp = Employee::find($userId);
        if (!$emp) return response()->json(['message' => 'Employee not found'], 404);

        return response()->json([
            'emp_permission' => strtolower((string) $emp->emp_permission),
        ]);
    }

    public function deleted($id)
    {
        try {
            // หน้า Event ไม่ได้เก็บข้อมูลผู้ใช้

            // 1. ผู้ใช้ต้อง login
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // 2. หา event
            $event = Event::find($id);
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $status = strtolower((string) $event->evn_status);

            // กันลบซ้ำ/กันสถานะ deleted
            if ($status === 'deleted') {
                return response()->json(['message' => 'Event already deleted'], 409);
            }

            // 3. ongoing ห้ามลบทุกกรณี
            if ($status === 'ongoing') {
                return response()->json(['message' => 'Ongoing event cannot be deleted'], 403);
            }

            // 4. ดึง emp_permission ของคนที่ลบ
            // *** สำคัญ: สมมติ Auth::id() = id ในตาราง ems_employees (ตามที่ใช้ evn_create_by = Auth::id())
            $emp = Employee::find($userId);
            if (!$emp) {
                return response()->json(['message' => 'Employee not found'], 404);
            }

            $perm = strtolower((string) $emp->emp_permission); // enabled / disabled

            // 5. เช็คสิทธิ์
            $canDelete = false;

            if ($perm === 'enabled') {
                $canDelete = in_array($status, ['upcoming', 'done'], true);
            } else {
                // disabled หรือค่าอื่นๆ ถือว่าเหมือน disabled
                $canDelete = ($status === 'upcoming');
            }

            if (!$canDelete) {
                return response()->json(['message' => 'You have no permission to delete this event'], 403);
            }

            //  6. ส่งอีเมลยกเลิกให้ผู้เข้าร่วม (active)
            $participantIds = DB::table('ems_connect')
                ->where('con_event_id', $event->id)
                ->where('con_delete_status', 'active') // เอาเฉพาะคนที่ยังไม่ถูกลบออกจากลิสต์
                ->pluck('con_employee_id');

            // 3. ดึงข้อมูล Employee และส่งอีเมล
            $employees = Employee::whereIn('id', $participantIds)->get();

            /*
            foreach ($employees as $emp) {
                if ($emp->emp_email) {
                    // ส่งอีเมลแจ้งยกเลิก
                    Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
                }
            }
            */

            foreach ($employees as $empItem) {
                if ($empItem->emp_email) {
                    // ส่งอีเมลแจ้งยกเลิก
                    Mail::to($empItem->emp_email)->send(new EventCancellationMail($empItem, $event));
                }
            }

            // 7. Soft delete
            $event->update([
                'evn_status'     => 'deleted',
                'evn_deleted_at' => Carbon::now(),
                'evn_deleted_by' => $userId,
            ]);

            return response()->json(['message' => 'Event deleted and notifications sent successfully']);
        } catch (Exception $e) {
            //Log error เพื่อดูปัญหาถ้าส่งเมลไม่ผ่าน
            Log::error("Delete Event Error: " . $e->getMessage());
            return response()->json(['message' => 'Event deleted failed'], 500);
        }
    }

    public function getEventParticipants($eventId)
    {
        try {
            // ดึงข้อมูลสถิติการเข้าร่วม
            $statistics = DB::table('ems_connect')
                ->where('con_event_id', $eventId)
                ->where('con_delete_status', 'active')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN con_answer = "accept" THEN 1 ELSE 0 END) as attending,
                    SUM(CASE WHEN con_answer = "denied" THEN 1 ELSE 0 END) as not_attending,
                    SUM(CASE WHEN con_answer = "invalid" THEN 1 ELSE 0 END) as pending
                ')
                ->first();

            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eventInfo()
    {
        $employees = Employee::with([
            'position:id,pst_name',
            'department:id,dpm_name',
            'team:id,tm_name',
        ])
            ->where('emp_delete_status', 'active')
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'emp_id',
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_nickname',
                'emp_email',
                'emp_phone',
                'emp_position_id',
                'emp_department_id',
                'emp_team_id'
            ])
            ->map(function (Employee $e) {
                return [
                    'id'               => $e->id,
                    'emp_id'           => $e->emp_id,
                    'emp_prefix'       => $e->emp_prefix,
                    'emp_firstname'    => $e->emp_firstname,
                    'emp_lastname'     => $e->emp_lastname,
                    'emp_nickname'     => $e->emp_nickname,
                    'emp_email'        => $e->emp_email,
                    'emp_phone'        => $e->emp_phone,
                    'emp_position_id'  => $e->emp_position_id,
                    'emp_department_id' => $e->emp_department_id,
                    'emp_team_id'      => $e->emp_team_id,
                    'position_name'    => optional($e->position)->pst_name,
                    'department_name'  => optional($e->department)->dpm_name,
                    'team_name'        => optional($e->team)->tm_name,
                ];
            });

        $categories = Category::select('id', 'cat_name')->where('cat_delete_status', 'active')->orderBy('cat_name')->get();
        $positions = Position::select('id', 'pst_name')->where('pst_delete_status', 'active')->orderBy('pst_name')->get();
        $departments = Department::select('id', 'dpm_name')->where('dpm_delete_status', 'active')->orderBy('dpm_name')->get();
        $teams = Team::select('id', 'tm_name')->where('tm_delete_status', 'active')->orderBy('tm_name')->get();

        return response()->json(compact('categories', 'employees', 'positions', 'departments', 'teams'));
    }

    public function getParticipants($id, Request $request)
    {
        try {
            // ตรวจสอบว่า event มีอยู่จริง
            $event = Event::find($id);
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $statusFilter = $request->get('status'); // accepted, declined, pending

            // ดึงข้อมูล participants จาก ems_connect และ join กับ employees
            $query = DB::table('ems_connect as c')
                ->join('ems_employees as e', 'c.con_employee_id', '=', 'e.id')
                ->leftJoin('ems_position as p', 'e.emp_position_id', '=', 'p.id')
                ->leftJoin('ems_department as d', 'e.emp_department_id', '=', 'd.id')
                ->leftJoin('ems_team as t', 'e.emp_team_id', '=', 't.id')
                ->where('c.con_event_id', $id)
                ->where(function ($q) {
                    $q->where('c.con_delete_status', 'active');
                })
                ->select([
                    'e.id',
                    'e.emp_id',
                    'e.emp_prefix',
                    'e.emp_firstname',
                    'e.emp_lastname',
                    'e.emp_nickname',
                    'e.emp_email',
                    'e.emp_phone',
                    'p.pst_name as position',
                    'd.dpm_name as department',
                    't.tm_name as team',
                    'c.con_answer as status'
                ]);

            // กรองตาม status ถ้ามี
            if ($statusFilter) {
                if ($statusFilter === 'pending') {
                    $query->where('c.con_answer', 'invalid');
                } elseif ($statusFilter === 'accepted') {
                    $query->where('c.con_answer', 'accept');
                } elseif ($statusFilter === 'declined') {
                    $query->where('c.con_answer', 'denied');
                }
            }

            $participants = $query->orderBy('e.emp_firstname')->get();

            // นับสถิติ - แก้ไขให้ตรงกับ database จริง
            $totalCount = $participants->count();
            $attendingCount = $participants->where('status', 'accept')->count();
            $notAttendingCount = $participants->where('status', 'denied')->count();
            $pendingCount = $participants->where('status', 'invalid')->count();

            \Log::info("Event $id participants stats:", [
                'total' => $totalCount,
                'attending' => $attendingCount,
                'not_attending' => $notAttendingCount,
                'pending' => $pendingCount,
                'participants_sample' => $participants->take(3)->toArray()
            ]);

            $statistics = [
                'total' => $totalCount,
                'attending' => $attendingCount,
                'not_attending' => $notAttendingCount,
                'pending' => $pendingCount
            ];

            return response()->json([
                'participants' => $participants,
                'statistics' => $statistics,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->evn_title,
                    'date' => $event->evn_date,
                    'status' => $event->evn_status
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in getParticipants for event $id: " . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving participants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendanceData($eventId)
    {
        try {
            // ตรวจสอบว่ามี event นี้หรือไม่
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            // ดึงข้อมูลการตอบรับจาก ems_connect table
            $attendanceStats = DB::table('ems_connect')
                ->where('con_event_id', $eventId)
                ->where(function ($query) {
                    $query->whereNull('con_delete_status')
                        ->orWhere('con_delete_status', '')
                        ->orWhere('con_delete_status', 'active');
                })
                ->selectRaw('
                    COUNT(CASE WHEN con_answer = "accept" THEN 1 END) as actual_attendance,
                    COUNT(CASE WHEN con_answer = "decline" THEN 1 END) as declined,
                    COUNT(CASE WHEN con_answer IS NULL OR con_answer = "" OR con_answer = "pending" THEN 1 END) as pending,
                    COUNT(*) as total_invited
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'event_id' => $eventId,
                    'event_title' => $event->evn_title,
                    'actual_attendance' => $attendanceStats->actual_attendance ?? 0,
                    'declined' => $attendanceStats->declined ?? 0,
                    'pending' => $attendanceStats->pending ?? 0,
                    'total_invited' => $attendanceStats->total_invited ?? 0,
                    'attendance_percentage' => $attendanceStats->total_invited > 0
                        ? round(($attendanceStats->actual_attendance / $attendanceStats->total_invited) * 100, 2)
                        : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function eventStatistics(Request $request)
    {
        try {
            $eventIds = $request->input('event_ids', []);

            if (empty($eventIds)) {
                return response()->json([
                    'total_participation' => 0,
                    'attending' => 0,
                    'not_attending' => 0,
                    'pending' => 0,
                    'departments' => [],
                    'participants' => []
                ]);
            }

            // Get aggregated statistics
            $stats = DB::table('ems_connect')
                ->whereIn('con_event_id', $eventIds)
                ->where('con_delete_status', 'active')
                ->selectRaw('
                    COUNT(*) as total_participation,
                    SUM(CASE WHEN con_answer = "accepted" THEN 1 ELSE 0 END) as attending,
                    SUM(CASE WHEN con_answer = "denied" THEN 1 ELSE 0 END) as not_attending,
                    SUM(CASE WHEN con_answer = "invalid" OR con_answer IS NULL THEN 1 ELSE 0 END) as pending
                ')
                ->first();

            // Get department breakdown
            $departments = DB::table('ems_connect')
                ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
                ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
                ->whereIn('ems_connect.con_event_id', $eventIds)
                ->where('ems_connect.con_delete_status', 'active')
                ->groupBy('ems_department.id', 'ems_department.dpm_name')
                ->selectRaw('
                    ems_department.dpm_name as name,
                    SUM(CASE WHEN ems_connect.con_answer = "accepted" THEN 1 ELSE 0 END) as attending,
                    SUM(CASE WHEN ems_connect.con_answer = "denied" THEN 1 ELSE 0 END) as notAttending,
                    SUM(CASE WHEN ems_connect.con_answer = "invalid" OR ems_connect.con_answer IS NULL THEN 1 ELSE 0 END) as pending
                ')
                ->get();

            // Get all participants (including same person in multiple events)
            $participants = DB::table('ems_connect')
                ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
                ->leftJoin('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
                ->leftJoin('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
                ->leftJoin('ems_position', 'ems_employees.emp_position_id', '=', 'ems_position.id')
                ->leftJoin('ems_event', 'ems_connect.con_event_id', '=', 'ems_event.id')
                ->whereIn('ems_connect.con_event_id', $eventIds)
                ->where('ems_connect.con_delete_status', 'active')
                ->select(
                    'ems_employees.id',
                    'ems_employees.emp_id',
                    'ems_employees.emp_prefix',
                    'ems_employees.emp_firstname',
                    'ems_employees.emp_lastname',
                    'ems_employees.emp_nickname',
                    'ems_employees.emp_phone',
                    'ems_employees.emp_email',
                    'ems_department.dpm_name as department',
                    'ems_team.tm_name as team',
                    'ems_position.pst_name as position',
                    'ems_event.evn_title as event_title',
                    'ems_connect.con_answer as status'
                )
                ->get(); // No unique() - show all participations

            return response()->json([
                'total_participation' => $stats->total_participation ?? 0,
                'attending' => $stats->attending ?? 0,
                'not_attending' => $stats->not_attending ?? 0,
                'pending' => $stats->pending ?? 0,
                'departments' => $departments,
                'participants' => $participants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving event statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
