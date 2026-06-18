<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalSetting extends Model
{
    protected $fillable = [
        'hospital_name', 'logo', 'email', 'phone', 'address', 'city', 'state',
        'country', 'postal_code', 'currency', 'currency_symbol', 'timezone',
        'date_format', 'time_format', 'morning_shift_start', 'morning_shift_end',
        'evening_shift_start', 'evening_shift_end', 'night_shift_start', 'night_shift_end',
        'tax_label', 'tax_rate', 'low_stock_alert', 'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'low_stock_alert' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], ['hospital_name' => 'Hospital Management System']);
    }
}
