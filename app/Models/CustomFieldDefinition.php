<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;


class CustomFieldDefinition extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'field_name',
        'field_type',
        'select_options',
        'is_required',
    ];

    protected $casts = [
        'select_options' => 'array',
        'is_required' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}