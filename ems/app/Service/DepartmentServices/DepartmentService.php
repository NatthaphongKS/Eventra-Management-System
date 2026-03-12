<?php

/**
 * ชื่อไฟล์: DepartmentService.php
 * คำอธิบาย: Service สำหรับจัดการ Logic ของ Department
 * ผู้จัดทำ: ณัฐพงศ์ คงศิลป์
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Service\DepartmentServices;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class DepartmentService
{
    /**
     * ชื่อฟังก์ชัน: index
     * คำอธิบาย: ดึงข้อมูล Department ทั้งหมด พร้อม Search, Sort, และ Filter
     * Input: $request (search, sortBy, sortOrder, status)
     * Output: JSON รายการ Department
     */
    public function index(Request $request)
    {
        try {
            $query = Department::query();

            // Filter by status
            if ($request->has('status') && !empty($request->get('status'))) {
                $statuses = $request->get('status');
                if (is_array($statuses)) {
                    $query->whereIn('dpm_delete_status', $statuses);
                }
            } else {
                // Default to active if no status filter is specified
                $query->where('dpm_delete_status', 'active');
            }

            // Search by name
            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where('dpm_name', 'like', "%{$search}%");
            }

            // Sort
            $sortBy = $request->get('sortBy', 'id');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $departments = $query->get([
                'id',
                'dpm_name',
                'dpm_delete_status',
            ]);

            return response()->json($departments);
        } catch (\Exception $e) {
            Log::error('DepartmentService@index: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching departments'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: show
     * คำอธิบาย: ดึงข้อมูล Department รายคน
     * Input: $id
     * Output: JSON รายละเอียด Department
     */
    public function show($id)
    {
        try {
            $department = Department::findOrFail($id, [
                'id',
                'dpm_name',
                'dpm_delete_status',
            ]);

            return response()->json($department);
        } catch (\Exception $e) {
            Log::error('DepartmentService@show: ' . $e->getMessage());
            return response()->json(['message' => 'Department not found'], 404);
        }
    }

    /**
     * ชื่อฟังก์ชัน: store
     * คำอธิบาย: สร้าง Department ใหม่
     * Input: $request
     * Output: JSON Department ที่สร้าง
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'dpm_name' => 'required|string|max:255',
                'dpm_delete_status' => 'required|in:active,inactive',
            ]);

            $department = Department::create($validated);

            return response()->json([
                'message' => 'Department created successfully',
                'data' => $department
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('DepartmentService@store: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating department'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: update
     * คำอธิบาย: อัปเดต Department
     * Input: $request, $id
     * Output: JSON Department ที่อัปเดต
     */
    public function update(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'dpm_name' => [
                    'required',
                    'string',
                    Rule::unique('ems_department', 'dpm_name')->ignore($id),
                    'max:255'
                ],
                'dpm_delete_status' => 'required|in:active,inactive',
            ]);

            $department->update($validated);

            return response()->json([
                'message' => 'Department updated successfully',
                'data' => $department
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('DepartmentService@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating department'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: destroy
     * คำอธิบาย: ลบ Department (Soft Delete)
     * Input: $id
     * Output: JSON response
     */
    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);

            // Check if department has related teams
            if ($department->teams()->where('tm_delete_status', 'active')->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete department with existing teams',
                ], 422);
            }

            $department->update(['dpm_delete_status' => 'inactive']);

            return response()->json([
                'message' => 'Department deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('DepartmentService@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting department'], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: getAll
     * คำอธิบาย: ดึง Department ทั้งหมดสำหรับ Dropdown (เฉพาะ Active)
     * Input: -
     * Output: JSON array ของ Department
     */
    public function getAll()
    {
        try {
            $departments = Department::where('dpm_delete_status', 'active')
                ->orderBy('dpm_name')
                ->get(['id', 'dpm_name']);

            return response()->json($departments);
        } catch (\Exception $e) {
            Log::error('DepartmentService@getAll: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching departments'], 500);
        }
    }
}
