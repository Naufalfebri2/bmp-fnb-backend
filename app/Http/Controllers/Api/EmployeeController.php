<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Section;
use App\Services\CustomFieldValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request, string $sectionId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        return response()->json($section->employees);
    }

    public function store(Request $request, string $sectionId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:staff,admin,owner',
            'start_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validatedCustomFields = CustomFieldValidator::validate(
                $request->user()->tenant_id,
                'employees',
                $request->input('custom_fields', [])
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Custom field validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $employee = $section->employees()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role,
            'start_date' => $request->start_date,
            'base_salary' => $request->base_salary,
            'custom_fields' => $validatedCustomFields,
        ]);

        return response()->json([
            'message' => 'Employee created successfully',
            'employee' => $employee,
        ], 201);
    }

    public function update(Request $request, string $sectionId, string $employeeId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $employee = $section->employees()->find($employeeId);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20',
            'role' => 'sometimes|in:staff,admin,owner',
            'base_salary' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only(['name', 'phone', 'role', 'base_salary', 'is_active']);

        if ($request->has('custom_fields')) {
            try {
                $updateData['custom_fields'] = CustomFieldValidator::validate(
                    $request->user()->tenant_id,
                    'employees',
                    $request->input('custom_fields', [])
                );
            } catch (ValidationException $e) {
                return response()->json([
                    'message' => 'Custom field validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        }

        $employee->update($updateData);

        return response()->json([
            'message' => 'Employee updated successfully',
            'employee' => $employee,
        ]);
    }

    public function destroy(Request $request, string $sectionId, string $employeeId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $employee = $section->employees()->find($employeeId);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    private function findOwnedSection(Request $request, string $sectionId): ?Section
    {
        return Section::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($sectionId);
    }
}
