<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

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
        $prefix = 'RX-' . now()->format('Ymd') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')->latest('id')->value('invoice_number');
        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
