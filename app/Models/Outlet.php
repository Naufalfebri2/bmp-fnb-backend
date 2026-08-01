<?php

namespace App\Models;

use App\Models\CashAccount;
use App\Models\Menu;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Section;
use App\Models\TableBooking;
use App\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id',
        'name',
        'recording_mode',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function cashAccounts(): HasMany
    {
        return $this->hasMany(CashAccount::class);
    }

    public function tableBookings(): HasMany
    {
        return $this->hasMany(TableBooking::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
