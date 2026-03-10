<?php

/**
 * ชื่อไฟล์: PositionService.php
 * คำอธิบาย: Service สำหรับจัดการ Logic ของ Position
 * ผู้จัดทำ: ณัฐพงศ์ คงศิลป์
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Service\PositionServices;

use App\Models\Position;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PositionService
{
    /**
     * ชื่อฟังก์ชัน: index
     * คำอธิบาย: ดึงข้อมูล Position ทั้งหมด พร้อม Search, Sort, และ Filter
     * Input: $request (search, sortBy, sortOrder, department, team, status)
     * Output: JSON รายการ Position
     */
    public function index(Request $request)
    {
        try {
            $query = Position::with(['team:id,tm_name,tm_department_id', 'team.department:id,dpm_name']);

            // Filter by department
            if ($request->has('department') && !empty($request->get('department'))) {
                $departments = $request->get('department');
                if (is_array($departments)) {
                    $query->whereHas('team', function ($q) use ($departments) {
                        $q->whereIn('tm_department_id', $departments);
                    });
                }
            }

            // Filter by team
            if ($request->has('team') && !empty($request->get('team'))) {
                $teams = $request->get('team');
                if (is_array($teams)) {
                    $query->whereIn('pst_team_id', $teams);
                }
            }

            // Filter by status
            if ($request->has('status') && !empty($request->get('status'))) {
                $statuses = $request->get('status');
                if (is_array($statuses)) {
                    $query->whereIn('pst_delete_status', $statuses);
                }
            } else {
                // Default to active if no status filter is specified
                $query->where('pst_delete_status', 'active');
            }

            // Search by name
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where('pst_name', 'like', "%{$search}%");
            }

            // Sort
            $sortBy = $request->get('sortBy', 'id');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $positions = $query->get([
                'id',
                'pst_name',
                'pst_team_id',
                'pst_delete_status',
            ])->map(function ($position) {
                return [
                    'id' => $position->id,
                    'pst_name' => $position->pst_name,
                    'pst_team_id' => $position->pst_team_id,
                    'tm_department_id' => optional($position->team)->tm_department_id,
                    'team_name' => optional($position->team)->tm_name,
                    'department_name' => optional(optional($position->team)->department)->dpm_name,
                    'pst_delete_status' => $position->pst_delete_status,
                ];
            });

            return response()->json($positions);
        } catch (\Exception $e) {
            Log::error('PositionService@index: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching positions'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: show
     * คำอธิบาย: ดึงข้อมูล Position รายคน
     * Input: $id
     * Output: JSON รายละเอียด Position
     */
    public function show($id)
    {
        try {
            $position = Position::with(['team:id,tm_name,tm_department_id', 'team.department:id,dpm_name'])
                ->findOrFail($id, [
                    'id',
                    'pst_name',
                    'pst_team_id',
                    'pst_delete_status',
                    'created_at',
                ]);

            return response()->json([
                'id' => $position->id,
                'pst_name' => $position->pst_name,
                'pst_team_id' => $position->pst_team_id,
                'tm_department_id' => optional($position->team)->tm_department_id,
                'team_name' => optional($position->team)->tm_name,
                'department_name' => optional(optional($position->team)->department)->dpm_name,
                'pst_delete_status' => $position->pst_delete_status,
                'created_at' => $position->created_at,
            ]);
        } catch (\Exception $e) {
            Log::error('PositionService@show: ' . $e->getMessage());
            return response()->json(['message' => 'Position not found'], 404);
        }
    }

    /**
     * ชื่อฟังก์ชัน: store
     * คำอธิบาย: สร้าง Position ใหม่
     * Input: $request
     * Output: JSON Position ที่สร้าง
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'pst_name' => 'required|string|max:255',
                'pst_team_id' => 'required|exists:ems_team,id',
                'pst_delete_status' => 'required|in:active,inactive',
            ]);

            // Check uniqueness of pst_name within the same team
            $exists = Position::where('pst_name', $validated['pst_name'])
                ->where('pst_team_id', $validated['pst_team_id'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => ['pst_name' => ['Position name already exists in this team']]
                ], 422);
            }

            $position = Position::create($validated);

            return response()->json([
                'message' => 'Position created successfully',
                'data' => $position
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PositionService@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating position'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: update
     * คำอธิบาย: อัปเดต Position
     * Input: $request, $id
     * Output: JSON Position ที่อัปเดต
     */
    public function update(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'pst_name' => 'required|string|max:255',
                'pst_team_id' => 'required|exists:ems_team,id',
                'pst_delete_status' => 'required|in:active,inactive',
            ]);

            // Check uniqueness of pst_name within the same team (excluding current position)
            $exists = Position::where('pst_name', $validated['pst_name'])
                ->where('pst_team_id', $validated['pst_team_id'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => ['pst_name' => ['Position name already exists in this team']]
                ], 422);
            }

            $position->update($validated);

            return response()->json([
                'message' => 'Position updated successfully',
                'data' => $position
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PositionService@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating position'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: destroy
     * คำอธิบาย: ลบ Position (Soft Delete)
     * Input: $id
     * Output: JSON response
     */
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);

            // Check if position has related employees
            if ($position->employees()->where('emp_delete_status', 'active')->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete position with existing employees',
                ], 422);
            }

            $position->update(['pst_delete_status' => 'inactive']);

            return response()->json([
                'message' => 'Position deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('PositionService@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting position'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: getByTeam
     * คำอธิบาย: ดึง Positions ตาม Team ID (สำหรับ Dropdown)
     * Input: $teamId
     * Output: JSON array ของ Position
     */
    public function getByTeam($teamId)
    {
        try {
            $positions = Position::where('pst_team_id', $teamId)
                ->where('pst_delete_status', 'active')
                ->orderBy('pst_name')
                ->get(['id', 'pst_name']);

            return response()->json($positions);
        } catch (\Exception $e) {
            Log::error('PositionService@getByTeam: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching positions'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: getAll
     * คำอธิบาย: ดึง Position ทั้งหมดสำหรับ Dropdown
     * Input: -
     * Output: JSON array ของ Position
     */
    public function getAll()
    {
        try {
            $positions = Position::where('pst_delete_status', 'active')
                ->with(['team:id,tm_name,tm_department_id', 'team.department:id,dpm_name'])
                ->orderBy('pst_name')
                ->get(['id', 'pst_name', 'pst_team_id'])
                ->map(function ($position) {
                    return [
                        'id' => $position->id,
                        'pst_name' => $position->pst_name,
                        'pst_team_id' => $position->pst_team_id,
                        'team_name' => optional($position->team)->tm_name,
                        'department_name' => optional(optional($position->team)->department)->dpm_name,
                    ];
                });

            return response()->json($positions);
        } catch (\Exception $e) {
            Log::error('PositionService@getAll: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching positions'], 500);
        }
    }
}
