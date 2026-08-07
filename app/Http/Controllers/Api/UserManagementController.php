<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class UserManagementController extends Controller
{
    public function createManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
            'outlet_id' => 'required|uuid|exists:outlets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $outlet = Outlet::where('tenant_id', $request->user()->tenant_id)->find($request->outlet_id);

        if (!$outlet) {
            return response()->json([
                'message' => 'Outlet does not belong to this tenant',
            ], 422);
        }

        try {
            $manager = User::create([
                'tenant_id' => $request->user()->tenant_id,
                'outlet_id' => $outlet->id,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'role' => 'manager',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Manager account created successfully',
            'user' => $manager,
        ], 201);
    }
}