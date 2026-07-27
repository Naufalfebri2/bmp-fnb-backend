<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomFieldDefinition::where('tenant_id', $request->user()->tenant_id);

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:ingredients,employees,menus',
            'field_name' => 'required|string|max:50',
            'field_type' => 'required|in:text,number,date,boolean,select',
            'select_options' => 'required_if:field_type,select|array',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $definition = CustomFieldDefinition::create([
            'tenant_id' => $request->user()->tenant_id,
            'entity_type' => $request->entity_type,
            'field_name' => $request->field_name,
            'field_type' => $request->field_type,
            'select_options' => $request->select_options,
            'is_required' => $request->is_required ?? false,
        ]);

        return response()->json([
            'message' => 'Custom field definition created successfully',
            'custom_field_definition' => $definition,
        ], 201);
    }

    public function destroy(Request $request, string $id)
    {
        $definition = CustomFieldDefinition::where('tenant_id', $request->user()->tenant_id)->find($id);

        if (!$definition) {
            return response()->json(['message' => 'Custom field definition not found'], 404);
        }

        $definition->delete();

        return response()->json(['message' => 'Custom field definition deleted successfully']);
    }
}
