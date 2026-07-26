<?php

namespace App\Models;

use App\Models\CustomFieldDefinition;
use App\Models\Outlet;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Tenant extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'business_name',
        'plan',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function customFieldDefinitions(): HasMany
    {
        return $this->hasMany(CustomFieldDefinition::class);
    }
}
