<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;
use App\Mail\EventInvitationMail;
use App\Mail\EventInvitation;

class EventController extends Controller
{
    // ดึงข้อมูลทั้งหมดสำหรับแบบฟอร์มเดียว
    public function edit_pages($id) // GET /api/edit_event/{id}
    {
        $event = Event::join(
            'ems_categories', 'ems_event.evn_category_id', '=', 'evn_category_id'
        )->where('ems_event.id',$id)->first(); // ได้ Model ไม่ใช่ Collection
        return response()->json($event); // ส่งอ็อบเจ็กต์เดียว
    }

    public function Edit_event(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|exists:ems_event,id',
            'event_title' => 'required|string|max:255',
            'event_category_id' => 'required|exists:ems_categories,id',
            'event_description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_timestart' => 'required|date_format:H:i',
            'event_timeend' => 'required|date_format:H:i',
            'event_duration' => 'required|integer|min:0',   // นาทีจากฟอร์ม
            'event_location' => 'required|string|max:255',
        ]);

        // DB คุณเก็บ evn_duration เป็น "ชั่วโมง"
        $hours = (int) ceil(($data['event_duration'] ?? 0) / 60);

        $event = Event::query()
            ->where('id', $data['event_id'])
            ->get(['evn_title']);


    }
    function eventInfo()
    {
        $employees = Employee::join('ems_position', 'ems_employees.emp_position_id', '=', 'ems_position.id')
            ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->join('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
            ->select(
                'ems_employees.*',
                'ems_position.pst_name as position_name',
                'ems_department.dpm_name as department_name',
                'ems_team.tm_name as team_name'
            )
            ->where('ems_employees.emp_delete_status', 'active')
            ->get();

        $categories = Category::select('id', 'cat_name')->where('cat_delete_status', 'active')->orderBy('cat_name')->get();
        $positions = Position::select('id', 'pst_name')->where('pst_delete_status', 'active')->orderBy('pst_name')->get();
        $departments = Department::select('id', 'dpm_name')->where('dpm_delete_status', 'active')->orderBy('dpm_name')->get();
        $teams = Team::select('id', 'tm_name')->where('tm_delete_status', 'active')->orderBy('tm_name')->get();

        return response()->json(compact('categories', 'employees', 'positions', 'departments', 'teams'));
    }

    // สร้างกิจกรรม + อัปโหลดไฟล์ + แนบพนักงาน + ส่งอีเมล (แนบไฟล์)
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_title' => 'required|string|max:255',
            'event_category_id' => 'required|exists:ems_categories,id',
            'event_description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_timestart' => 'required|date_format:H:i',
            'event_timeend' => 'required|date_format:H:i',
            'event_duration' => 'required|integer|min:0',   // นาทีจากฟอร์ม
            'event_location' => 'required|string|max:255',

            'attachments' => 'array',
            'attachments.*' => 'file|max:51200|mimes:pdf,txt,doc,docx,jpg,jpeg,png,xlsx,xls',

            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:ems_employees,id',
        ]);

        // DB คุณเก็บ evn_duration เป็น "ชั่วโมง"
        $hours = (int) ceil(($data['event_duration'] ?? 0) / 60);

        return DB::transaction(function () use ($request, $data, $hours) {

            // 1) สร้างกิจกรรม
            $event = Event::create([
                'evn_title'        => $data['event_title'],
                'evn_category_id'  => $data['event_category_id'],
                'evn_description'  => $data['event_description'] ?? null,
                'evn_date'         => $data['event_date'],
                'evn_timestart'    => $data['event_timestart'],
                'evn_timeend'      => $data['event_timeend'],
                'evn_duration'     => $hours,
                'evn_location'     => $data['event_location'],
                'evn_file'         => $request->hasFile('attachments') ? 'have' : 'not_have',
                'evn_create_by'    => Auth::id(),
                'evn_status'       => 'upcoming',
            ]);

            // 2) อัปโหลดไฟล์ + บันทึก ems_event_files + เก็บรายการไว้สำหรับแนบในอีเมล
            $savedFiles = [];
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

                    // เก็บข้อมูลไฟล์ไว้ใช้แนบใน Mailable
                    $savedFiles[] = [
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ];
                }
            }

            // 3) แนบพนักงานเข้ากิจกรรม (ems_connect)
            $rows = collect($data['employee_ids'])->unique()->map(fn($eid) => [
                'con_event_id' => $event->id,
                'con_employee_id' => $eid,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
            ])->values()->all();

            DB::table('ems_connect')->insert($rows);

            // 4) ส่งอีเมลเชิญ (แนบไฟล์)
            $employees = Employee::whereIn('id', $data['employee_ids'])
                ->get(['id', 'emp_email', 'emp_firstname', 'emp_lastname']);

            foreach ($employees as $emp) {
                if (!$emp->emp_email) {
                    continue;
                }
                // ถ้าใช้คิว: ->queue(new EventInvitation($emp, $event, $savedFiles));
                Mail::to($emp->emp_email)->send(new EventInvitationMail($emp, $event, $savedFiles));
            }

            return response()->json([
                'message' => 'สร้างกิจกรรมและส่งอีเมลเชิญแล้ว',
                'event' => $event,
                'redirect' => '/event', // path ใน Vue
            ], 201);
        });
    }
    public function Eventtable(Request $request)
    {
        $t = (new Event)->getTable(); // 'ems_event'

        // allow-list สำหรับ sort จากฝั่ง server
        $allowSort = [
            'evn_title' => 'e.evn_title',
            'cat_name' => 'c.cat_name',
            'evn_date' => 'e.evn_date',
            'evn_duration' => 'e.evn_duration',
            'evn_num_guest' => DB::raw('(SELECT COUNT(*) FROM ems_connect ci WHERE ci.con_event_id = e.id AND ci.con_delete_status="active")'),
            'evn_sum_accept' => DB::raw('(SELECT COUNT(*) FROM ems_connect ca WHERE ca.con_event_id = e.id AND ca.con_delete_status="active" AND ca.con_answer = "accept")'),
            'evn_status' => 'e.evn_status',
        ];

        $sortBy = $request->query('sortBy', 'evn_date');
        $sortDir = strtolower($request->query('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = $allowSort[$sortBy] ?? 'e.evn_date';

        $q = trim((string) $request->query('q', ''));

        $rows = DB::table("$t as e")
            ->leftJoin('ems_categories as c', 'c.id', '=', 'e.evn_category_id')
            ->select([
                'e.id',
                'e.evn_title',
                DB::raw('e.evn_category_id as evn_cat_id'), // <-- alias ให้ Vue ใช้ catMap ได้
                DB::raw('COALESCE(c.cat_name, "") as cat_name'),
                'e.evn_description',
                'e.evn_date',
                'e.evn_timestart',
                'e.evn_timeend',
                'e.evn_duration',
                // นับจำนวนจริงจาก ems_connect
                DB::raw('(SELECT COUNT(*) FROM ems_connect ci WHERE ci.con_event_id = e.id AND ci.con_delete_status = "active") as evn_num_guest'),
                DB::raw('(SELECT COUNT(*) FROM ems_connect ca WHERE ca.con_event_id = e.id AND ca.con_delete_status = "active" AND ca.con_answer = "accept") as evn_sum_accept'),
                DB::raw('COALESCE(e.evn_status, "") as evn_status'),
            ])
            ->when($q !== '', function ($b) use ($q) {
                $like = '%' . $q . '%';
                $b->where(function ($w) use ($like) {
                    $w->where('e.evn_title', 'like', $like)
                        ->orWhere('e.evn_description', 'like', $like)
                        ->orWhere('e.evn_status', 'like', $like)
                        ->orWhere('c.cat_name', 'like', $like);
                });
            })
            ->orderBy($sortCol, $sortDir)
            ->get();
        return response()->json($rows);
    }
}
