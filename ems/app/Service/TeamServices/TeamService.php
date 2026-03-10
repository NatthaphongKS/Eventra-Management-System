<?php

/**
 * ชื่อไฟล์: TeamService.php
 * คำอธิบาย: Service สำหรับจัดการ Logic ของ Team
 * ผู้จัดทำ: ณัฐพงศ์ คงศิลป์
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Service\TeamServices;

use App\Models\Team;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class TeamService
{
    /**
     * ชื่อฟังก์ชัน: index
     * คำอธิบาย: ดึงข้อมูล Team ทั้งหมด พร้อม Search, Sort, และ Filter
     * Input: $request (search, sortBy, sortOrder, department, status)
     * Output: JSON รายการ Team
     */
    public function index(Request $request)
    {
        try {
            $query = Team::with(['department:id,dpm_name']);

            // Search by name
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where('tm_name', 'like', "%{$search}%");
            }

            // Filter by department
            if ($request->has('department') && !empty($request->get('department'))) {
                $departments = $request->get('department');
                if (is_array($departments)) {
                    $query->whereIn('tm_department_id', $departments);
                }
            }

            // Filter by status
            if ($request->has('status') && !empty($request->get('status'))) {
                $statuses = $request->get('status');
                if (is_array($statuses)) {
                    $query->whereIn('tm_delete_status', $statuses);
                }
            }

            // Sort
            $sortBy = $request->get('sortBy', 'id');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $teams = $query->get([
                'id',
                'tm_name',
                'tm_department_id',
                'tm_delete_status',
            ])->map(function ($team) {
                return [
                    'id' => $team->id,
                    'tm_name' => $team->tm_name,
                    'tm_department_id' => $team->tm_department_id,
                    'department_name' => optional($team->department)->dpm_name,
                    'tm_delete_status' => $team->tm_delete_status,
                ];
            });

            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('TeamService@index: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching teams'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: show
     * คำอธิบาย: ดึงข้อมูล Team รายคน
     * Input: $id
     * Output: JSON รายละเอียด Team
     */
    public function show($id)
    {
        try {
            $team = Team::with(['department:id,dpm_name'])->findOrFail($id, [
                'id',
                'tm_name',
                'tm_department_id',
                'tm_delete_status',
            ]);

            return response()->json([
                'id' => $team->id,
                'tm_name' => $team->tm_name,
                'tm_department_id' => $team->tm_department_id,
                'department_name' => optional($team->department)->dpm_name,
                'tm_delete_status' => $team->tm_delete_status,
            ]);
        } catch (\Exception $e) {
            Log::error('TeamService@show: ' . $e->getMessage());
            return response()->json(['message' => 'Team not found'], 404);
        }
    }

    /**
     * ชื่อฟังก์ชัน: store
     * คำอธิบาย: สร้าง Team ใหม่
     * Input: $request
     * Output: JSON Team ที่สร้าง
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'tm_name' => 'required|string|unique:ems_team,tm_name|max:255',
                'tm_department_id' => 'required|exists:ems_department,id',
                'tm_delete_status' => 'required|in:active,inactive',
            ]);

            $team = Team::create($validated);

            return response()->json([
                'message' => 'Team created successfully',
                'data' => $team
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('TeamService@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating team'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: update
     * คำอธิบาย: อัปเดต Team
     * Input: $request, $id
     * Output: JSON Team ที่อัปเดต
     */
    public function update(Request $request, $id)
    {
        try {
            $team = Team::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'tm_name' => [
                    'required',
                    'string',
                    Rule::unique('ems_team', 'tm_name')->ignore($id),
                    'max:255'
                ],
                'tm_department_id' => 'required|exists:ems_department,id',
            ]);

            $team->update($validated);

            return response()->json([
                'message' => 'Team updated successfully',
                'data' => $team
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('TeamService@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating team'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: destroy
     * คำอธิบาย: ลบ Team (Soft Delete)
     * Input: $id
     * Output: JSON response
     */
    public function destroy($id)
    {
        try {
            $team = Team::findOrFail($id);

            // Check if team has related positions
            if ($team->positions()->where('pst_delete_status', 'active')->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete team with existing positions',
                ], 422);
            }

            $team->update(['tm_delete_status' => 'inactive']);

            return response()->json([
                'message' => 'Team deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('TeamService@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting team'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: getByDepartment
     * คำอธิบาย: ดึง Teams ตาม Department ID (สำหรับ Dropdown)
     * Input: $departmentId
     * Output: JSON array ของ Team
     */
    public function getByDepartment($departmentId)
    {
        try {
            $teams = Team::where('tm_department_id', $departmentId)
                ->where('tm_delete_status', 'active')
                ->orderBy('tm_name')
                ->get(['id', 'tm_name']);

            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('TeamService@getByDepartment: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching teams'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: getAll
     * คำอธิบาย: ดึง Team ทั้งหมดสำหรับ Dropdown
     * Input: -
     * Output: JSON array ของ Team
     */
    public function getAll()
    {
        try {
            $teams = Team::where('tm_delete_status', 'active')
                ->with(['department:id,dpm_name'])
                ->orderBy('tm_name')
                ->get(['id', 'tm_name', 'tm_department_id'])
                ->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'tm_name' => $team->tm_name,
                        'tm_department_id' => $team->tm_department_id,
                        'department_name' => optional($team->department)->dpm_name,
                    ];
                });

            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('TeamService@getAll: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching teams'], 500);
        }
    }
}
