<?php

namespace App\Models;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'email',
        'password_hash',
        'role',
        'employee_id',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}