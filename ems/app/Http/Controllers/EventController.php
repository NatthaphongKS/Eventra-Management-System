<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;      // ใช้เฉพาะ transaction
use Illuminate\Support\Facades\Mail;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;
use App\Mail\EventInvitationMail;
// use App\Mail\EventInvitation; // ถ้ายังใช้ตัวนี้อยู่ให้คงไว้
use App\Models\Connect;
use App\Models\File;       // กันชนกับ Facade/File

class EventController extends Controller
{
    /**
     * ดึงข้อมูลเมทาดาต้าทั้งหมดสำหรับหน้าแบบฟอร์มสร้างกิจกรรม
     * - Employees: ใช้ Eloquent + with() โหลดความสัมพันธ์ แล้ว map ให้มี *_name
     * - Positions/Departments/Teams/Categories: ดึงเฉพาะ active และจัดเรียงชื่อ
     */
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
                'id','emp_id','emp_prefix','emp_firstname','emp_lastname','emp_nickname',
                'emp_email','emp_phone','emp_position_id','emp_department_id','emp_team_id'
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
                    'emp_department_id'=> $e->emp_department_id,
                    'emp_team_id'      => $e->emp_team_id,
                    'position_name'    => optional($e->position)->pst_name,
                    'department_name'  => optional($e->department)->dpm_name,
                    'team_name'        => optional($e->team)->tm_name,
                ];
            });

        $categories  = Category::select('id','cat_name')->where('cat_delete_status','active')->orderBy('cat_name')->get();
        $positions   = Position::select('id','pst_name')->where('pst_delete_status','active')->orderBy('pst_name')->get();
        $departments = Department::select('id','dpm_name')->where('dpm_delete_status','active')->orderBy('dpm_name')->get();
        $teams       = Team::select('id','tm_name')->where('tm_delete_status','active')->orderBy('tm_name')->get();

        return response()->json(compact('categories','employees','positions','departments','teams'));
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

            'attachments'        => 'array',
            'attachments.*'      => 'file|max:51200|mimes:pdf,txt,doc,docx,jpg,jpeg,png,xlsx,xls',

            'employee_ids'       => 'required|array|min:1',
            'employee_ids.*'     => 'integer|exists:ems_employees,id',
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
                'evn_duration'     => $data['event_duration'] , 
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
                ->map(fn ($eid) => [
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
                ->get(['id','emp_email','emp_firstname','emp_lastname']);

            foreach ($employees as $emp) {
                if (!$emp->emp_email) { continue; }
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
        $allowSort = [
            'evn_title'      => 'ems_event.evn_title',
            'cat_name'       => 'cat_name',              // alias จาก select
            'evn_date'       => 'ems_event.evn_date',
            'evn_duration'   => 'ems_event.evn_duration',
            'evn_num_guest'  => 'evn_num_guest',         // from withCount
            'evn_sum_accept' => 'evn_sum_accept',        // from withCount
            'evn_status'     => 'ems_event.evn_status',
        ];

        $sortBy  = $request->query('sortBy', 'evn_date');
        $sortDir = strtolower($request->query('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = $allowSort[$sortBy] ?? 'ems_event.evn_date';

        $q = trim((string) $request->query('q', ''));

        $rows = Event::query()
            ->leftJoin('ems_categories as c', 'c.id', '=', 'ems_event.evn_category_id')
            ->withCount([
                'connects as evn_num_guest' => fn ($x) => $x->where('con_delete_status','active'),
                'connects as evn_sum_accept' => fn ($x) => $x->where('con_delete_status','active')->where('con_answer','accept'),
            ])
            ->select([
                'ems_event.id',
                'ems_event.evn_title',
                DB::raw('ems_event.evn_category_id as evn_cat_id'),
                DB::raw('COALESCE(c.cat_name, "") as cat_name'),
                'ems_event.evn_description',
                'ems_event.evn_date',
                'ems_event.evn_timestart',
                'ems_event.evn_timeend',
                'ems_event.evn_duration',
                DB::raw('COALESCE(ems_event.evn_status, "") as evn_status'),
            ])
            ->when($q !== '', function ($builder) use ($q) {
                $like = '%'.$q.'%';
                $builder->where(function ($w) use ($like) {
                    $w->where('ems_event.evn_title', 'like', $like)
                      ->orWhere('ems_event.evn_description', 'like', $like)
                      ->orWhere('ems_event.evn_status', 'like', $like)
                      ->orWhere('c.cat_name', 'like', $like);
                });
            })
            ->orderBy($sortCol, $sortDir)
            ->get();

        return response()->json($rows);
    }
}
