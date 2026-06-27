<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'medicine_id', 'batch_number', 'expiry_date', 'purchase_price',
        'sale_price', 'quantity', 'remaining_quantity', 'supplier_id',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->expiry_date->lte(now()->addDays($days));
    }

    public function scopeAvailable($query)
    {
        return $query->where('remaining_quantity', '>', 0)->where('expiry_date', '>', now());
    }
}
