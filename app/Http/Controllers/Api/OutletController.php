<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $outlets = Outlet::where('tenant_id', $request->user()->tenant_id)
            ->with('sections')
            ->get();

        return response()->json($outlets);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'recording_mode' => 'nullable|in:simple,detail',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'qr_ordering_enabled' => 'nullable|boolean',
            'online_pickup_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $outlet = Outlet::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'recording_mode' => $request->recording_mode ?? 'simple',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'qr_ordering_enabled' => $request->boolean('qr_ordering_enabled'),
            'online_pickup_enabled' => $request->boolean('online_pickup_enabled'),
        ]);

        return response()->json([
            'message' => 'Outlet created successfully',
            'outlet' => $outlet,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $outlet = Outlet::where('tenant_id', $request->user()->tenant_id)
            ->with('sections')
            ->find($id);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        return response()->json($outlet);
    }

    public function update(Request $request, string $id)
    {
        $outlet = Outlet::where('tenant_id', $request->user()->tenant_id)->find($id);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'recording_mode' => 'sometimes|in:simple,detail',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'qr_ordering_enabled' => 'sometimes|boolean',
            'online_pickup_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $outlet->update($request->only([
            'name',
            'recording_mode',
            'latitude',
            'longitude',
            'qr_ordering_enabled',
            'online_pickup_enabled',
        ]));

        return response()->json([
            'message' => 'Outlet updated successfully',
            'outlet' => $outlet,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $outlet = Outlet::where('tenant_id', $request->user()->tenant_id)->find($id);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $outlet->delete();

        return response()->json(['message' => 'Outlet deleted successfully']);
    }
}
