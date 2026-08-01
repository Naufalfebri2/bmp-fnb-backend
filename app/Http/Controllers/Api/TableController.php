<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index(Request $request, string $sectionId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        return response()->json($section->tables);
    }

    public function store(Request $request, string $sectionId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $table = $section->tables()->create([
            'table_number' => $request->table_number,
            'qr_code' => (string) Str::uuid(),
        ]);

        return response()->json([
            'message' => 'Table created successfully',
            'table' => $table,
        ], 201);
    }

    public function update(Request $request, string $sectionId, string $tableId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $table = $section->tables()->find($tableId);

        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $table->update(['table_number' => $request->table_number]);

        return response()->json([
            'message' => 'Table updated successfully',
            'table' => $table,
        ]);
    }

    public function regenerateQrCode(Request $request, string $sectionId, string $tableId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $table = $section->tables()->find($tableId);

        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $table->update(['qr_code' => (string) Str::uuid()]);

        return response()->json([
            'message' => 'QR code regenerated successfully',
            'table' => $table,
        ]);
    }

    public function destroy(Request $request, string $sectionId, string $tableId)
    {
        $section = $this->findOwnedSection($request, $sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $table = $section->tables()->find($tableId);

        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $table->delete();

        return response()->json(['message' => 'Table deleted successfully']);
    }

    private function findOwnedSection(Request $request, string $sectionId): ?Section
    {
        return Section::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($sectionId);
    }
}
