<?php

/**
 * ชื่อไฟล์: EventService.php
 * คำอธิบาย: ไฟล์นี้เก็บ (Business Logic) สำหรับจัดการข้อมูล Event ส่วน crud
 * ชื่อผู้เขียน/แก้ไข: รวีโรจน์ สนธิ
 * วันที่จัดทำ/แก้ไข: 21 กุมภาพันธ์ 2569
 */

namespace App\Service\EventServices;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Connect;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Mail\EventInvitationMail;
use App\Mail\EventCancellationMail;
use App\Mail\EventUpdateMail;
use Carbon\Carbon;
use Exception;

class EventService
{
    /* ============================================================
       1) ดึงรายการอีเวนต์ทั้งหมด
    ============================================================ */
    public function index()
    {
        return Event::orderBy('id', 'asc')->get();
    }

    /* ============================================================
       2) ดึงรายชื่อผู้เข้าร่วม (connect list)
    ============================================================ */
    public function connectList($id)
    {
        // 1. ดึงข้อมูลผ่าน Model พร้อม Load ความสัมพันธ์
        $connections = Connect::with(['employee.department', 'employee.team', 'employee.position'])
            ->where('con_event_id', $id)
            ->where('con_delete_status', 'active')
            ->where('con_answer', '!=', 'not_invite')
            ->get();

        // 2. จัดรูปแบบข้อมูล + เรียงลำดับ
        $participants = $connections
            ->sortBy(fn($conn) => $conn->employee?->emp_id) // เรียงลำดับตาม emp_id
            ->values() // รีเซ็ต key ของ array หลัง sort
            ->map(function ($connect) {
                $emp = $connect->employee;

                // คืนค่าเป็น Object
                return (object) [
                    'id' => $emp?->id,
                    'emp_id' => $emp?->emp_id,
                    'emp_prefix' => $emp?->emp_prefix,
                    'emp_firstname' => $emp?->emp_firstname,
                    'emp_lastname' => $emp?->emp_lastname,
                    'emp_nickname' => $emp?->emp_nickname,
                    'emp_phone' => $emp?->emp_phone,
                    'emp_email' => $emp?->emp_email,
                    'department' => $emp?->department?->dpm_name,
                    'team' => $emp?->team?->tm_name,
                    'position' => $emp?->position?->pst_name,
                    'status' => $connect->con_answer,
                    'con_checkin_status' => $connect->con_checkin_status,
                    'con_reason' => $connect->con_reason
                ];
            });

        return [
            'employee_ids' => $participants->pluck('id')->toArray(),
            'participants' => $participants
        ];
    }

    /* ============================================================
       3) ดึงรายละเอียด Event + ไฟล์
    ============================================================ */
    public function show($id)
    {
        $event = Event::with('category')->find($id);

        if (!$event) {
            return null;
        }

        // ใช้ Model File แทน DB::table
        $event->files = File::where('file_event_id', $event->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($file) {
                $file->url = asset('storage/' . $file->file_path);
                return $file;
            });

        return $event;
    }

    /* ============================================================
    4) ดึงข้อมูลตอนกดปุ่ม Edit page
 ============================================================ */
    public function editPages($id)
    {
        // 1. ดึง Event พร้อม Category ด้วย Eager Loading (แทนการใช้ Join)
        $event = Event::with('category')->findOrFail($id);

        // *ทริค: เนื่องจากโค้ดเดิมตอน Join เราได้ฟิลด์ 'cat_name' มาอยู่ใน Event เลย
        // ถ้าฝั่ง Frontend (Vue/React/Blade) เรียกใช้ event.cat_name เราสามารถยัดค่ากลับไปแบบนี้ได้ครับ
        $event->cat_name = $event->category?->cat_name;

        // 2. ดึงข้อมูลไฟล์ด้วย Model File
        $files = File::where('file_event_id', $event->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($f) {
                $f->url = asset('storage/' . $f->file_path);
                return $f;
            });

        // 3. ดึง Guest IDs ด้วย Model Connect
        $guestIds = Connect::where('con_event_id', $event->id)
            ->where('con_delete_status', 'active')
            ->pluck('con_employee_id');

        return compact('event', 'files', 'guestIds');
    }

    /* ============================================================
   5) สร้าง Event ใหม่
============================================================ */
public function store(Request $request)
{
    return DB::transaction(function () use ($request) {

        // 1. Create Event
        $event = Event::create([
            'evn_title'       => $request->event_title,
            'evn_category_id' => $request->event_category_id,
            'evn_description' => $request->event_description,
            'evn_date'        => $request->event_date,
            'evn_timestart'   => $request->event_timestart,
            'evn_timeend'     => $request->event_timeend,
            'evn_duration'    => $request->event_duration,
            'evn_location'    => $request->event_location,
            'evn_file'        => $request->hasFile('attachments') ? 'have' : 'not_have',
            'evn_create_by'   => Auth::id(),
            'evn_status'      => 'upcoming',
        ]);

        // 2. Files
        $saved = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                $path = $file->store("events/{$event->id}", 'public');

                $saved[] = $event->files()->create([
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $path,
                    'file_type'   => $file->getClientMimeType(),
                    'file_size'   => $file->getSize(),
                    'uploaded_at' => now(),
                ]);
            }
        }

        // 3. Employees & Send Email (รวบเงื่อนไขไว้ด้วยกัน)
        $employees = $request->employee_ids ?? [];
        if (!empty($employees)) {
            // 3.1 บันทึกลงฐานข้อมูล
            $event->connects()->createMany(
                collect($employees)->map(fn($id) => [
                    'con_employee_id'   => $id,
                    'con_answer'        => 'pending',
                    'con_delete_status' => 'active'
                ])->toArray()
            );

            // 3.2 ส่ง Email
            $empList = Employee::whereIn('id', $employees)->get();
            foreach ($empList as $emp) {
                if ($emp->emp_email) {
                    $url = '/reply/' . Crypt::encryptString($event->id . '/' . $emp->id);
                    Mail::to($emp->emp_email)
                        ->send(new EventInvitationMail($emp, $event, $saved, $url));
                }
            }
        }

        return [
            'success'  => true,
            'event_id' => $event->id
        ];
    });
}

    /* ============================================================
   6) อัปเดต Event
    ============================================================ */
public function update(Request $request)
{
    //ถ้าพังจะ rollback
    return DB::transaction(function () use ($request) {

        $event = Event::lockForUpdate()->findOrFail($request->id);

        // เก็บค่าเก่าไว้เทียบ
        $old = [
            'date'     => $event->evn_date,
            'start'    => Carbon::parse($event->evn_timestart)->format('H:i'),
            'end'      => Carbon::parse($event->evn_timeend)->format('H:i'),
            'location' => $event->evn_location
        ];

        // อัปเดตข้อมูลหลัก
        $event->update([
            'evn_title'       => $request->evn_title,
            'evn_category_id' => $request->evn_category_id ?? $event->evn_category_id,
            'evn_description' => $request->evn_description ?? $event->evn_description,
            'evn_date'        => $request->evn_date ?? $event->evn_date,
            'evn_timestart'   => $request->evn_timestart ?? $event->evn_timestart,
            'evn_timeend'     => $request->evn_timeend ?? $event->evn_timeend,
            'evn_location'    => $request->evn_location ?? $event->evn_location,
            'evn_duration'    => isset($request->evn_duration)
                ? ceil(max(0, $request->evn_duration) / 60)
                : $event->evn_duration
        ]);

        /* ---------------- Files Delete ---------------- */
        if ($request->filled('delete_file_ids')) {
            $delIds = array_unique($request->delete_file_ids);

            // ใช้ Model File ค้นหาไฟล์ที่จะลบ
            $filesToDelete = File::where('file_event_id', $event->id)
                ->whereIn('id', $delIds)
                ->get();

            foreach ($filesToDelete as $f) {
                Storage::disk('public')->delete($f->file_path);
            }

            // ลบออกจาก Database
            File::whereIn('id', $delIds)->delete();
        }

        /* ---------------- Files Add ---------------- */
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $uploadedFile) {

                $path = $uploadedFile->store("events/{$event->id}", 'public');

                // ใช้ Relationship สร้างไฟล์
                $event->files()->create([
                    'file_name'   => $uploadedFile->getClientOriginalName(),
                    'file_path'   => $path,
                    'file_type'   => $uploadedFile->getClientMimeType(),
                    'file_size'   => $uploadedFile->getSize(),
                    'uploaded_at' => now()
                ]);
            }
        }

        // update flag
        $count = $event->files()->count();
        $event->evn_file = $count > 0 ? 'have' : 'not_have';
        $event->save();

        /* ---------------- Employees Add / Remove ---------------- */
        $idsToAdd = [];
        if ($request->has('employee_ids')) {
            $incoming = collect($request->employee_ids)->unique()->values()->all();

            // ใช้ Model Connect แทน DB::table
            $current = Connect::where('con_event_id', $event->id)
                ->where('con_delete_status', 'active')
                ->pluck('con_employee_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $idsToAdd    = array_values(array_diff($incoming, $current));
            $idsToRemove = array_values(array_diff($current, $incoming));

            // เพิ่มใหม่ หรืออัปเดตคนเดิมที่อาจจะเคยถูกลบไปแล้ว
            foreach ($idsToAdd as $empId) {
                $exists = Connect::where('con_event_id', $event->id)
                    ->where('con_employee_id', $empId)
                    ->first();

                if ($exists) {
                    $exists->update([
                        'con_delete_status' => 'active',
                        'con_answer'        => 'invalid',
                        'con_reason'        => null
                    ]);
                } else {
                    $event->connects()->create([
                        'con_employee_id'   => $empId,
                        'con_answer'        => 'invalid',
                        'con_delete_status' => 'active'
                    ]);
                }
            }

            // ส่งเชิญใหม่เฉพาะคนที่เพิ่งถูกเพิ่ม
            if (!empty($idsToAdd)) {
                $newEmployees = Employee::whereIn('id', $idsToAdd)->get();
                $filesForMail = $event->files()->get(); // ดึงไฟล์ผ่าน Relationship

                foreach ($newEmployees as $emp) {
                    if ($emp->emp_email) {
                        $url = url('/response?event_id=' . $event->id . '&employee_id=' . $emp->id);
                        Mail::to($emp->emp_email)
                            ->send(new EventInvitationMail($emp, $event, $filesForMail, $url));
                    }
                }
            }

            // ลบ
            if (!empty($idsToRemove)) {
                Connect::where('con_event_id', $event->id)
                    ->whereIn('con_employee_id', $idsToRemove)
                    ->update(['con_delete_status' => 'inactive']);

                $removed = Employee::whereIn('id', $idsToRemove)->get();
                foreach ($removed as $emp) {
                    if ($emp->emp_email) {
                        Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
                    }
                }
            }
        }

        /* ---------------- ส่งอัปเดตให้คนเดิมถ้ามี critical change ---------------- */
        $newStart = Carbon::parse($event->evn_timestart)->format('H:i');
        $newEnd   = Carbon::parse($event->evn_timeend)->format('H:i');

        $critical =
            $old['date']     != $event->evn_date ||
            $old['start']    != $newStart ||
            $old['end']      != $newEnd ||
            $old['location'] != $event->evn_location;

        if ($critical) {
            $existing = Connect::where('con_event_id', $event->id)
                ->where('con_delete_status', 'active')
                ->whereNotIn('con_employee_id', $idsToAdd)
                ->pluck('con_employee_id');

            Connect::whereIn('con_employee_id', $existing)
                ->update(['con_answer' => 'invalid', 'con_reason' => null]);

            $employees    = Employee::whereIn('id', $existing)->get();
            $filesForMail = $event->files()->get();

            foreach ($employees as $emp) {
                if ($emp->emp_email) {
                    $url = '/reply/' . Crypt::encryptString($event->id . '/' . $emp->id);
                    Mail::to($emp->emp_email)->send(new EventUpdateMail($emp, $event, $filesForMail, $url));
                }
            }
        }

        /* ---------------- Files Response ---------------- */
        $filesResponse = $event->files()
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($f) {
                $f->url = asset('storage/' . $f->file_path);
                return $f;
            });

        return [
            'message' => 'บันทึกข้อมูลสำเร็จ',
            'event'   => $event,
            'files'   => $filesResponse
        ];
    });
}

    /* ============================================================
       7) ตาราง Event (filter + sorting + query)
    ============================================================ */
    public function eventTable($request)
    {
        $this->syncEventStatus();

        $allowSort = [
            'evn_title' => 'ems_event.evn_title',
            'cat_name' => 'cat_name',
            'evn_date' => 'ems_event.evn_date',
            'evn_duration' => 'ems_event.evn_duration',
            'evn_num_guest' => 'evn_num_guest',
            'evn_sum_accept' => 'evn_sum_accept',
            'evn_status' => 'ems_event.evn_status',
        ];

        $sortBy = $request->query('sortBy', 'evn_date');
        $sortDir = $request->query('sortDir', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortCol = $allowSort[$sortBy] ?? 'ems_event.evn_date';
        $q = trim($request->query('q', ''));

        $subTotal = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active')
            ->where('con_answer', '!=', 'not_invite');

        $subAccept = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active')
            ->where('con_answer', 'accepted');

        $rows = Event::leftJoin('ems_categories as c', 'c.id', '=', 'ems_event.evn_category_id')
            ->select([
                'ems_event.id',
                'ems_event.evn_title',
                DB::raw('ems_event.evn_category_id as evn_cat_id'),
                DB::raw('COALESCE(c.cat_name,"") as cat_name'),
                'ems_event.evn_description',
                'ems_event.evn_date',
                'ems_event.evn_timestart',
                'ems_event.evn_timeend',
                'ems_event.evn_location',
                'ems_event.evn_duration',
                DB::raw('COALESCE(ems_event.evn_status,"") as evn_status')
            ])
            ->selectSub($subTotal, 'evn_num_guest')
            ->selectSub($subAccept, 'evn_sum_accept')
            ->where(function ($w) {
                $w->whereNull('ems_event.evn_status')
                    ->orWhere('ems_event.evn_status', '!=', 'deleted');
            })
            ->when($q !== '', function ($b) use ($q) {
                $like = "%{$q}%";
                $b->where(function ($w) use ($like) {
                    $w->where('ems_event.evn_title', 'like', $like)
                        ->orWhere('ems_event.evn_description', 'like', $like)
                        ->orWhere('ems_event.evn_status', 'like', $like)
                        ->orWhere('c.cat_name', 'like', $like);
                });
            })
            ->orderBy($sortCol, $sortDir)
            ->get();

        return $rows;
    }

    /* ============================================================
       8) อัปเดตสถานะ Event ตามเวลา
    ============================================================ */
    private function syncEventStatus()
    {
        $now = Carbon::now('Asia/Bangkok')->format('Y-m-d H:i:s');

        DB::table('ems_event')
            ->whereNotIn('evn_status', ['done', 'deleted'])
            ->whereRaw("TIMESTAMP(evn_date,evn_timeend) <= ?", [$now])
            ->update(['evn_status' => 'done']);

        DB::table('ems_event')
            ->whereNotIn('evn_status', ['ongoing', 'done', 'deleted'])
            ->whereRaw("TIMESTAMP(evn_date,evn_timestart) <= ?", [$now])
            ->whereRaw("TIMESTAMP(evn_date,evn_timeend) > ?", [$now])
            ->update(['evn_status' => 'ongoing']);

        DB::table('ems_event')
            ->whereNotIn('evn_status', ['upcoming', 'deleted'])
            ->whereRaw("TIMESTAMP(evn_date,evn_timestart) > ?", [$now])
            ->update(['evn_status' => 'upcoming']);
    }

    /* ============================================================
       9) Permission
    ============================================================ */
    public function permission()
    {
        $id = Auth::id();
        $perm = DB::table('ems_employees')->where('id', $id)->value('emp_permission');
        return $perm ? strtolower($perm) : null;
    }

    /* ============================================================
       10) ลบ Event + ส่งเมลแจ้งยกเลิก
    ============================================================ */
    public function deleted($id)
    {
        $event = Event::find($id);
        if (!$event)
            return false;

        $participants = DB::table('ems_connect')
            ->where('con_event_id', $event->id)
            ->where('con_delete_status', 'active')
            ->pluck('con_employee_id');

        $employees = Employee::whereIn('id', $participants)->get();

        foreach ($employees as $emp) {
            if ($emp->emp_email) {
                Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
            }
        }

        $event->update([
            'evn_status' => 'deleted',
            'evn_deleted_at' => now(),
            'evn_deleted_by' => Auth::id()
        ]);

        return true;
    }

    /* ============================================================
       11) getEventParticipants
    ============================================================ */
    public function getEventParticipants($eventId)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_delete_status', 'active')
            ->where('con_answer', '!=', 'not_invite')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN con_answer="accept" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN con_answer="denied" THEN 1 ELSE 0 END) as not_attending,
                SUM(CASE WHEN con_answer="invalid" THEN 1 ELSE 0 END) as pending
            ')
            ->first();
    }

    /* ============================================================
       12) eventInfo (metadata สำหรับหน้า create)
    ============================================================ */
    public function eventInfo()
    {
        $employees = Employee::with(['position:id,pst_name', 'department:id,dpm_name', 'team:id,tm_name'])
            ->where('emp_delete_status', 'active')
            ->orderBy('id', 'asc')
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
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'emp_id' => $e->emp_id,
                    'emp_prefix' => $e->emp_prefix,
                    'emp_firstname' => $e->emp_firstname,
                    'emp_lastname' => $e->emp_lastname,
                    'emp_nickname' => $e->emp_nickname,
                    'emp_email' => $e->emp_email,
                    'emp_phone' => $e->emp_phone,
                    'emp_position_id' => $e->emp_position_id,
                    'emp_department_id' => $e->emp_department_id,
                    'emp_team_id' => $e->emp_team_id,
                    'position_name' => optional($e->position)->pst_name,
                    'department_name' => optional($e->department)->dpm_name,
                    'team_name' => optional($e->team)->tm_name,
                ];
            });

        return [
            'categories' => Category::where('cat_delete_status', 'active')
                ->select('id', 'cat_name')
                ->orderBy('cat_name')
                ->get(),

            'employees' => $employees,

            'positions' => Position::where('pst_delete_status', 'active')
                ->select('id', 'pst_name')
                ->orderBy('pst_name')
                ->get(),

            'departments' => Department::where('dpm_delete_status', 'active')
                ->select('id', 'dpm_name')
                ->orderBy('dpm_name')
                ->get(),

            'teams' => Team::where('tm_delete_status', 'active')
                ->select('id', 'tm_name')
                ->orderBy('tm_name')
                ->get(),
        ];
    }

    /* ============================================================
       13) รายชื่อผู้เข้าร่วม (Filter)
    ============================================================ */
    public function getParticipants($eventId, $statusFilter)
    {
        $query = DB::table('ems_connect as c')
            ->join('ems_employees as e', 'c.con_employee_id', '=', 'e.id')
            ->leftJoin('ems_position as p', 'e.emp_position_id', '=', 'p.id')
            ->leftJoin('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->leftJoin('ems_team as t', 'e.emp_team_id', '=', 't.id')
            ->where('c.con_event_id', $eventId)
            ->where('c.con_delete_status', 'active')
            ->where('c.con_answer', '!=', 'not_invite')
            ->select([
                'e.*',
                'p.pst_name as position',
                'd.dpm_name as department',
                't.tm_name as team',
                'c.con_answer as status'
            ]);

        if ($statusFilter === 'pending') {
            $query->where('c.con_answer', 'invalid');
        } elseif ($statusFilter === 'accepted') {
            $query->where('c.con_answer', 'accept');
        } elseif ($statusFilter === 'declined') {
            $query->where('c.con_answer', 'denied');
        }

        $participants = $query->orderBy('e.emp_firstname')->get();

        return [
            'participants' => $participants,
            'statistics' => [
                'total' => $participants->count(),
                'attending' => $participants->where('status', 'accept')->count(),
                'not_attending' => $participants->where('status', 'denied')->count(),
                'pending' => $participants->where('status', 'invalid')->count(),
            ]
        ];
    }

    /* ============================================================
       14) getAttendanceData
    ============================================================ */
    public function getAttendanceData($eventId)
    {
        $event = Event::find($eventId);
        if (!$event)
            return null;

        $stats = DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_delete_status', 'active')
            ->where('con_answer', '!=', 'not_invite')
            ->selectRaw('
                COUNT(CASE WHEN con_answer="accept" THEN 1 END) as actual_attendance,
                COUNT(CASE WHEN con_answer="decline" THEN 1 END) as declined,
                COUNT(CASE WHEN con_answer IS NULL OR con_answer="pending" THEN 1 END) as pending,
                COUNT(*) as total_invited
            ')
            ->first();

        $percent = $stats->total_invited > 0
            ? round($stats->actual_attendance / $stats->total_invited * 100, 2)
            : 0;

        return [
            'event_title' => $event->evn_title,
            'actual_attendance' => $stats->actual_attendance ?? 0,
            'declined' => $stats->declined ?? 0,
            'pending' => $stats->pending ?? 0,
            'total_invited' => $stats->total_invited ?? 0,
            'attendance_percentage' => $percent
        ];
    }

    /* ============================================================
       15) eventStatistics
    ============================================================ */
    public function eventStatistics($eventIds)
    {
        if (empty($eventIds)) {
            return [
                'total_participation' => 0,
                'attending' => 0,
                'not_attending' => 0,
                'pending' => 0,
                'departments' => [],
                'teams' => [],
                'participants' => []
            ];
        }

        $stats = DB::table('ems_connect')
            ->whereIn('con_event_id', $eventIds)
            ->where('con_delete_status', 'active')
            ->where('con_answer', '!=', 'not_invite')
            ->selectRaw('
                COUNT(*) as total_participation,
                SUM(CASE WHEN con_answer="accepted" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN con_answer="denied" THEN 1 ELSE 0 END) as not_attending,
                SUM(CASE WHEN con_answer!="accepted" AND con_answer!="denied" THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        $employees = DB::table('ems_employees')
            ->where('emp_delete_status', 'active')
            ->count();

        $checkedIn = DB::table('ems_connect')
            ->whereIn('con_event_id', $eventIds)
            ->where('con_checkin_status', 1)
            ->count();

        $totalAssigned = $employees * count($eventIds);

        $departments = DB::table('ems_connect')
            ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
            ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->whereIn('ems_connect.con_event_id', $eventIds)
            ->where('ems_connect.con_delete_status', 'active')
            ->where('ems_connect.con_answer', '!=', 'not_invite')
            ->groupBy('ems_department.id', 'ems_department.dpm_name')
            ->selectRaw('
                ems_department.dpm_name as name,
                SUM(CASE WHEN ems_connect.con_answer="accepted" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN ems_connect.con_answer="denied" THEN 1 ELSE 0 END) as notAttending,
                SUM(CASE WHEN ems_connect.con_answer!="accepted" AND ems_connect.con_answer!="denied" THEN 1 ELSE 0 END) as pending
            ')
            ->get();

        $teams = DB::table('ems_connect')
            ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
            ->join('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
            ->whereIn('ems_connect.con_event_id', $eventIds)
            ->where('ems_connect.con_delete_status', 'active')
            ->where('ems_connect.con_answer', '!=', 'not_invite')
            ->groupBy('ems_team.id', 'ems_team.tm_name')
            ->selectRaw('
                ems_team.tm_name as name,
                SUM(CASE WHEN ems_connect.con_answer="accepted" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN ems_connect.con_answer="denied" THEN 1 ELSE 0 END) as notAttending,
                SUM(CASE WHEN ems_connect.con_answer!="accepted" AND ems_connect.con_answer!="denied" THEN 1 ELSE 0 END) as pending
            ')
            ->get();

        $participants = DB::table('ems_connect')
            ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
            ->leftJoin('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->leftJoin('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
            ->leftJoin('ems_position', 'ems_employees.emp_position_id', '=', 'ems_position.id')
            ->leftJoin('ems_event', 'ems_connect.con_event_id', '=', 'ems_event.id')
            ->whereIn('ems_connect.con_event_id', $eventIds)
            ->where('ems_connect.con_delete_status', 'active')
            ->where('ems_connect.con_answer', '!=', 'not_invite')
            ->select([
                'ems_employees.*',
                'ems_department.dpm_name as department',
                'ems_team.tm_name as team',
                'ems_position.pst_name as position',
                'ems_event.evn_title as event_title',
                'ems_connect.con_answer as status',
                'ems_connect.con_checkin_status',
                'ems_connect.con_event_id as event_id'
            ])
            ->orderBy('ems_employees.emp_id')
            ->get();

        return [
            'total_participation' => $stats->total_participation ?? 0,
            'attending' => $stats->attending ?? 0,
            'not_attending' => $stats->not_attending ?? 0,
            'pending' => $stats->pending ?? 0,
            'actual_attendance' => [
                'attended' => $checkedIn,
                'total_assigned' => $totalAssigned
            ],
            'departments' => $departments,
            'teams' => $teams,
            'participants' => $participants
        ];
    }
}
