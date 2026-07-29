<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $supplier = Supplier::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'contact' => $request->contact,
        ]);

        return response()->json([
            'message' => 'Supplier created successfully',
            'supplier' => $supplier,
        ], 201);
    }

    public function update(Request $request, string $supplierId)
    {
        $supplier = $this->findOwnedSupplier($request, $supplierId);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $supplier->update([
            'name' => $request->name,
            'contact' => $request->contact,
        ]);

        return response()->json([
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier,
        ]);
    }

    public function destroy(Request $request, string $supplierId)
    {
        $supplier = $this->findOwnedSupplier($request, $supplierId);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }

    private function findOwnedSupplier(Request $request, string $supplierId): ?Supplier
    {
        return Supplier::where('tenant_id', $request->user()->tenant_id)
            ->find($supplierId);
    }
}
