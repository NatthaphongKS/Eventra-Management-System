<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Employee;
use App\Models\File;
use App\Mail\EventInvitationMail;
use App\Mail\EventCancellationMail;
use App\Mail\EventUpdateMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class EventService
{
    /**
     * ดึงข้อมูล Event ทั้งหมด
     */
    public function getAllEvents()
    {
        return Event::orderBy('id', 'asc')->get();
    }

    /**
     * ดึง Event รายการเดียว
     */
    public function getEventById($id)
    {
        return Event::find($id);
    }

    /**
     * ดึง Employee IDs ที่เกี่ยวข้องกับ Event
     */
    public function getConnectEmployeeIds($eventId)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where(function ($q) {
                $q->whereNull('con_delete_status')
                    ->orWhere('con_delete_status', '')
                    ->orWhere('con_delete_status', 'active');
            })
            ->pluck('con_employee_id');
    }

    /**
     * เตรียมข้อมูลสำหรับหน้าแก้ไข (Edit Page)
     */
    public function getEditPageData($id)
    {
        $event = Event::join('ems_categories', 'ems_event.evn_category_id', '=', 'ems_categories.id')
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
                'ems_categories.cat_name as cat_name',
            ])
            ->firstOrFail();

        $files = DB::table('ems_event_files')
            ->where('file_event_id', $event->id)
            ->select('id', 'file_name', 'file_path', 'file_size')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($f) {
                $f->url = asset('storage/' . $f->file_path);
                return $f;
            });

        $guestIds = DB::table('ems_connect')
            ->where('con_event_id', $event->id)
            ->where('con_delete_status', 'active')
            ->pluck('con_employee_id');

        return [
            'event' => $event,
            'files' => $files,
            'guest_ids' => $guestIds,
        ];
    }

    /**
     * สร้าง Event ใหม่
     */
    public function createEvent(array $data, $attachments = [])
    {
        return DB::transaction(function () use ($data, $attachments) {
            // 1. Create Event
            $event = Event::create([
                'evn_title'        => $data['event_title'],
                'evn_category_id'  => $data['event_category_id'],
                'evn_description'  => $data['event_description'] ?? null,
                'evn_date'         => $data['event_date'],
                'evn_timestart'    => $data['event_timestart'],
                'evn_timeend'      => $data['event_timeend'],
                'evn_duration'     => $data['event_duration'],
                'evn_location'     => $data['event_location'],
                'evn_file'         => !empty($attachments) ? 'have' : 'not_have',
                'evn_create_by'    => Auth::id(),
                'evn_status'       => 'upcoming',
            ]);

            // 2. Upload Files
            $savedFiles = [];
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $path = $file->store("events/{$event->id}", 'public');

                    // Save via relation
                    $event->files()->create([
                        'file_name'   => $file->getClientOriginalName(),
                        'file_path'   => $path,
                        'file_type'   => $file->getClientMimeType(),
                        'file_size'   => $file->getSize(),
                        'uploaded_at' => now(),
                    ]);

                    // Save to ems_file (redundant table handling as per original code)
                    $fileRecord = new File();
                    $fileRecord->file_name      = $file->getClientOriginalName();
                    $fileRecord->file_path      = $path;
                    $fileRecord->file_event_id  = $event->id;
                    $fileRecord->file_type      = $file->getMimeType();
                    $fileRecord->file_size      = $file->getSize();
                    $fileRecord->save();
                }
            }

            // 3. Connect Employees
            if (!empty($data['employee_ids'])) {
                $connectRows = collect($data['employee_ids'])
                    ->unique()
                    ->map(fn($eid) => [
                        'con_employee_id'   => $eid,
                        'con_answer'        => 'invalid',
                        'con_reason'        => null,
                        'con_delete_status' => 'active',
                    ])
                    ->values()
                    ->all();
                $event->connects()->createMany($connectRows);

                // 4. Send Emails
                $employees = Employee::whereIn('id', $data['employee_ids'])
                    ->get(['id', 'emp_email', 'emp_firstname', 'emp_lastname']);

                foreach ($employees as $emp) {
                    if ($emp->emp_email) {
                        Mail::to($emp->emp_email)->send(new EventInvitationMail($emp, $event, $savedFiles));
                    }
                }
            }

            return $event;
        });
    }

    /**
     * อัปเดต Event
     */
    public function updateEvent(array $data, $attachments = [], $deleteFileIds = [], $employeeIds = null)
    {
        return DB::transaction(function () use ($data, $attachments, $deleteFileIds, $employeeIds) {
            $event = Event::lockForUpdate()->findOrFail($data['id']);

            // 1. เก็บค่าเดิม & Update
            $oldDate      = $event->evn_date;
            $oldStart     = substr($event->evn_timestart, 0, 5);
            $oldEnd       = substr($event->evn_timeend, 0, 5);
            $oldLocation  = $event->evn_location;

            $event->fill($data); // ใช้ fill แทนการ set ทีละตัว (ต้องมี $fillable ใน Model)

            // Handle duration logic if needed explicitly or trust $data
            if (isset($data['evn_duration'])) {
                 $minutes = max(0, (int) $data['evn_duration']);
                 $event->evn_duration = (int) ceil($minutes / 60);
            }

            $event->save();

            // Check critical changes
            $newStart = substr($event->evn_timestart, 0, 5);
            $newEnd   = substr($event->evn_timeend, 0, 5);
            $isCriticalChange = (
                $oldDate != $event->evn_date ||
                $oldStart != $newStart ||
                $oldEnd != $newEnd ||
                $oldLocation != $event->evn_location
            );

            // 2. Handle Files (Delete/Upload)
            if (!empty($deleteFileIds)) {
                $ids = array_values(array_unique($deleteFileIds));
                $files = DB::table('ems_event_files')->where('file_event_id', $event->id)->whereIn('id', $ids)->get();
                foreach ($files as $f) Storage::disk('public')->delete($f->file_path);
                DB::table('ems_event_files')->where('file_event_id', $event->id)->whereIn('id', $ids)->delete();
            }
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
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
            // Update file status
            $remain = DB::table('ems_event_files')->where('file_event_id', $event->id)->count();
            $event->evn_file = $remain > 0 ? 'have' : 'not_have';
            $event->save();

            // 3. Handle Employees
            $idsToAdd = [];
            if ($employeeIds !== null) {
                $incomingIds = collect($employeeIds)->map(fn($id) => (int)$id)->unique()->values()->all();
                $currentActiveIds = DB::table('ems_connect')
                    ->where('con_event_id', $event->id)
                    ->where('con_delete_status', 'active')
                    ->pluck('con_employee_id')->map(fn($id) => (int)$id)->all();

                $idsToAdd = array_values(array_diff($incomingIds, $currentActiveIds));
                $idsToRemove = array_values(array_diff($currentActiveIds, $incomingIds));

                // Add New
                if (!empty($idsToAdd)) {
                    foreach ($idsToAdd as $empId) {
                        $exists = DB::table('ems_connect')->where('con_event_id', $event->id)->where('con_employee_id', $empId)->first();
                        if ($exists) {
                            DB::table('ems_connect')->where('id', $exists->id)->update(['con_delete_status' => 'active', 'con_answer' => 'invalid', 'con_reason' => null]);
                        } else {
                            DB::table('ems_connect')->insert(['con_event_id' => $event->id, 'con_employee_id' => $empId, 'con_answer' => 'invalid', 'con_delete_status' => 'active']);
                        }
                    }
                    // Email New
                    $newEmployees = Employee::whereIn('id', $idsToAdd)->get();
                    $currentFiles = DB::table('ems_event_files')->where('file_event_id', $event->id)->get();
                    foreach ($newEmployees as $emp) {
                        if ($emp->emp_email) Mail::to($emp->emp_email)->send(new EventInvitationMail($emp, $event, $currentFiles));
                    }
                }

                // Remove Old
                if (!empty($idsToRemove)) {
                    DB::table('ems_connect')->where('con_event_id', $event->id)->whereIn('con_employee_id', $idsToRemove)->update(['con_delete_status' => 'inactive']);
                    $removedEmployees = Employee::whereIn('id', $idsToRemove)->get();
                    foreach ($removedEmployees as $emp) {
                        if ($emp->emp_email) Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
                    }
                }
            }

            // 4. Critical Change Notification
            if ($isCriticalChange) {
                $existingParticipants = DB::table('ems_connect')
                    ->where('con_event_id', $event->id)
                    ->where('con_delete_status', 'active')
                    ->whereNotIn('con_employee_id', $idsToAdd)
                    ->pluck('con_employee_id');

                if ($existingParticipants->isNotEmpty()) {
                    DB::table('ems_connect')
                        ->where('con_event_id', $event->id)
                        ->whereIn('con_employee_id', $existingParticipants)
                        ->update(['con_answer' => 'invalid', 'con_reason' => null]);

                    $employeesToUpdate = Employee::whereIn('id', $existingParticipants)->get();
                    $filesToSend = DB::table('ems_event_files')->where('file_event_id', $event->id)->get();

                    foreach ($employeesToUpdate as $emp) {
                        if ($emp->emp_email) {
                            Mail::to($emp->emp_email)->send(new EventUpdateMail($emp, $event, $filesToSend));
                        }
                    }
                }
            }

            return $event;
        });
    }

    // ... คุณสามารถย้าย Logic ของ getTable, delete, stat มาใส่ในนี้ได้ในลักษณะเดียวกันครับ

    public function getEventTableData($q, $sortBy, $sortDir)
    {
         $allowSort = [
            'evn_title'      => 'ems_event.evn_title',
            'cat_name'       => 'cat_name',
            'evn_date'       => 'ems_event.evn_date',
            'evn_duration'   => 'ems_event.evn_duration',
            'evn_num_guest'  => 'evn_num_guest',
            'evn_sum_accept' => 'evn_sum_accept',
            'evn_status'     => 'ems_event.evn_status',
        ];
        $sortCol = $allowSort[$sortBy] ?? 'ems_event.evn_date';

        // Subqueries
        $subTotal = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active');

        $subAccept = DB::table('ems_connect')
            ->selectRaw('COUNT(*)')
            ->whereColumn('ems_connect.con_event_id', 'ems_event.id')
            ->where('con_delete_status', 'active')
            ->where('con_checkin_status', 1);

        return Event::query()
            ->leftJoin('ems_categories as c', 'c.id', '=', 'ems_event.evn_category_id')
            ->select([
                'ems_event.id',
                'ems_event.evn_title',
                DB::raw('ems_event.evn_category_id as evn_cat_id'),
                DB::raw('COALESCE(c.cat_name, "") as cat_name'),
                'ems_event.evn_date',
                'ems_event.evn_timestart',
                'ems_event.evn_timeend',
                DB::raw('COALESCE(ems_event.evn_status, "") as evn_status'),
            ])
            ->selectSub($subTotal,  'evn_num_guest')
            ->selectSub($subAccept, 'evn_sum_accept')
            ->where(function ($w) {
                $w->whereNull('ems_event.evn_status')
                    ->orWhere('ems_event.evn_status', '!=', 'deleted');
            })
            ->when($q !== '', function ($builder) use ($q) {
                $like = '%' . $q . '%';
                $builder->where(function ($w) use ($like) {
                    $w->where('ems_event.evn_title', 'like', $like)
                        ->orWhere('ems_event.evn_description', 'like', $like)
                        ->orWhere('ems_event.evn_status', 'like', $like)
                        ->orWhere('c.cat_name', 'like', $like);
                });
            })
            ->orderBy($sortCol, $sortDir)
            ->get();
    }
    public function getUserPermission()
    {
        $userId = Auth::id();
        if (!$userId) throw new Exception('Unauthenticated');

        $emp = Employee::find($userId);
        if (!$emp) throw new Exception('Employee not found');

        return strtolower((string) $emp->emp_permission);
    }

    /**
     * ลบ Event (Soft Delete + ส่งเมลแจ้งเตือน)
     */
    public function deleteEvent($id)
    {
        return DB::transaction(function () use ($id) {
            $event = Event::find($id);
            if (!$event) throw new Exception('Event not found');

            // หาผู้เข้าร่วมเพื่อส่งเมล
            $participantIds = DB::table('ems_connect')
                ->where('con_event_id', $event->id)
                ->where('con_delete_status', 'active')
                ->pluck('con_employee_id');

            $employees = Employee::whereIn('id', $participantIds)->get();
            foreach ($employees as $emp) {
                if ($emp->emp_email) {
                    Mail::to($emp->emp_email)->send(new EventCancellationMail($emp, $event));
                }
            }

            // Soft Delete
            $event->update([
                'evn_status'     => 'deleted',
                'evn_deleted_at' => Carbon::now(),
                'evn_deleted_by' => Auth::id(),
            ]);

            return true;
        });
    }

    /**
     * สถิติผู้เข้าร่วมราย Event
     */
    public function getEventParticipantsStats($eventId)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_delete_status', 'active')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN con_answer = "accept" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN con_answer = "denied" THEN 1 ELSE 0 END) as not_attending,
                SUM(CASE WHEN con_answer = "invalid" THEN 1 ELSE 0 END) as pending
            ')
            ->first();
    }

    /**
     * ข้อมูลสำหรับ Dropdown ต่างๆ (Employees, Categories, etc.)
     */
    public function getEventInfoForForm()
    {
        $employees = Employee::with(['position:id,pst_name', 'department:id,dpm_name', 'team:id,tm_name'])
            ->where('emp_delete_status', 'active')
            ->orderBy('id', 'desc')
            ->get(['id', 'emp_id', 'emp_prefix', 'emp_firstname', 'emp_lastname', 'emp_nickname', 'emp_email', 'emp_phone', 'emp_position_id', 'emp_department_id', 'emp_team_id'])
            ->map(function ($e) {
                // map ข้อมูลตาม format เดิม
                return [
                    'id' => $e->id,
                    'emp_id' => $e->emp_id,
                    // ... (fields อื่นๆ)
                    'position_name' => optional($e->position)->pst_name,
                    'department_name' => optional($e->department)->dpm_name,
                    'team_name' => optional($e->team)->tm_name,
                ];
            });

        $categories  = \App\Models\Category::select('id', 'cat_name')->where('cat_delete_status', 'active')->orderBy('cat_name')->get();
        $positions   = \App\Models\Position::select('id', 'pst_name')->where('pst_delete_status', 'active')->orderBy('pst_name')->get();
        $departments = \App\Models\Department::select('id', 'dpm_name')->where('dpm_delete_status', 'active')->orderBy('dpm_name')->get();
        $teams       = \App\Models\Team::select('id', 'tm_name')->where('tm_delete_status', 'active')->orderBy('tm_name')->get();

        return compact('categories', 'employees', 'positions', 'departments', 'teams');
    }

    /**
     * รายชื่อผู้เข้าร่วม + Filter Status
     */
    public function getParticipantsList($id, $statusFilter = null)
    {
        $event = Event::find($id);
        if (!$event) throw new Exception('Event not found');

        $query = DB::table('ems_connect as c')
            ->join('ems_employees as e', 'c.con_employee_id', '=', 'e.id')
            ->leftJoin('ems_position as p', 'e.emp_position_id', '=', 'p.id')
            ->leftJoin('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->leftJoin('ems_team as t', 'e.emp_team_id', '=', 't.id')
            ->where('c.con_event_id', $id)
            ->where('c.con_delete_status', 'active')
            ->select([
                'e.id', 'e.emp_id', 'e.emp_prefix', 'e.emp_firstname', 'e.emp_lastname',
                'e.emp_nickname', 'e.emp_email', 'e.emp_phone',
                'p.pst_name as position', 'd.dpm_name as department', 't.tm_name as team',
                'c.con_answer as status'
            ]);

        if ($statusFilter) {
            if ($statusFilter === 'pending') $query->where('c.con_answer', 'invalid');
            elseif ($statusFilter === 'accepted') $query->where('c.con_answer', 'accept');
            elseif ($statusFilter === 'declined') $query->where('c.con_answer', 'denied');
        }

        $participants = $query->orderBy('e.emp_firstname')->get();

        // Recalculate stats for this specific filtered view (or total)
        // ... Logic การนับเหมือนเดิม ...
        $totalCount = $participants->count();
        // (จริงๆ ควร Query นับแยกถ้าจะเอา Total ของทั้ง Event แต่ตามโค้ดเดิมใช้นับจาก Collection)

        // เพื่อความแม่นยำและ Performance แนะนำใช้วิธี count จาก DB แยกต่างหากเหมือน getEventParticipantsStats
        // แต่ถ้าเอาตามโค้ดเดิมก็ return ชุดข้อมูลกลับไปได้เลย

        return [
            'participants' => $participants,
            'statistics' => [/* ...ใส่ค่าที่นับได้... */],
            'event' => ['id' => $event->id, 'title' => $event->evn_title, 'date' => $event->evn_date, 'status' => $event->evn_status]
        ];
    }

    /**
     * ข้อมูล Attendance Data สำหรับกราฟ
     */
    public function getAttendanceData($eventId)
    {
        $event = Event::find($eventId);
        if (!$event) throw new Exception('Event not found');

        $attendanceStats = DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where(function($query) {
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

        return [
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
        ];
    }
}