<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Sale extends Model implements Auditable
{
    use AuditableTrait, HasFactory, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'invoice_number', 'patient_id', 'doctor_id', 'prescription_id',
        'sale_date', 'shift', 'subtotal', 'discount_percentage', 'discount_amount',
        'tax_amount', 'total_amount', 'paid_amount', 'change_amount',
        'payment_method', 'payment_status', 'customer_name', 'customer_phone',
        'notes', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * Net payable for the sale. There is no dedicated column — the stored
     * `total_amount` is already subtotal − discount + tax, so it is the net.
     * Exposed as `net_amount` for the views/exports that reference that name.
     */
    public function getNetAmountAttribute(): float
    {
        return (float) $this->total_amount;
    }

    /**
     * Friendly alias for the receipt identifier. The column is `invoice_number`;
     * views/exports refer to it as `sale_number`.
     */
    public function getSaleNumberAttribute(): ?string
    {
        return $this->invoice_number;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'RX-'.now()->format('Ymd').'-';
        $last = static::where('invoice_number', 'like', $prefix.'%')->latest('id')->value('invoice_number');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
