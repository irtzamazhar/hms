<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hospital_name');
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('currency', 10)->default('PKR');
            $table->string('currency_symbol', 5)->default('₨');
            $table->string('timezone')->default('Asia/Karachi');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');
            $table->unsignedTinyInteger('morning_shift_start')->default(8);
            $table->unsignedTinyInteger('morning_shift_end')->default(14);
            $table->unsignedTinyInteger('evening_shift_start')->default(14);
            $table->unsignedTinyInteger('evening_shift_end')->default(20);
            $table->unsignedTinyInteger('night_shift_start')->default(20);
            $table->unsignedTinyInteger('night_shift_end')->default(8);
            $table->string('tax_label')->default('Tax');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('low_stock_alert')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_settings');
    }
};
