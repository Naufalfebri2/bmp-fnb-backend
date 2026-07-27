<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->tenant);
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tenant = $request->user()->tenant;

        $tenant->update([
            'settings' => array_merge($tenant->settings ?? [], $request->settings),
        ]);

        return response()->json([
            'message' => 'Tenant settings updated successfully',
            'tenant' => $tenant,
        ]);
    }
}
