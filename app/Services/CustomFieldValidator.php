<?php

namespace App\Services;

use App\Models\CustomFieldDefinition;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomFieldValidator
{
    public static function validate(string $tenantId, string $entityType, array $customFieldsInput): array
    {
        $definitions = CustomFieldDefinition::where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->get();

        if ($definitions->isEmpty()) {
            return $customFieldsInput;
        }

        $rules = [];

        foreach ($definitions as $definition) {
            $fieldRules = [];

            $fieldRules[] = $definition->is_required ? 'required' : 'nullable';

            $fieldRules[] = match ($definition->field_type) {
                'text' => 'string',
                'number' => 'numeric',
                'date' => 'date',
                'boolean' => 'boolean',
                'select' => 'in:' . implode(',', $definition->select_options ?? []),
            };

            $rules[$definition->field_name] = $fieldRules;
        }

        $validator = Validator::make($customFieldsInput, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }
}
