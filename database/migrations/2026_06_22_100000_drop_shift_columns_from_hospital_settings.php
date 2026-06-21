<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_settings', function (Blueprint $table) {
            $table->dropColumn([
                'morning_shift_start', 'morning_shift_end',
                'evening_shift_start', 'evening_shift_end',
                'night_shift_start',   'night_shift_end',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('hospital_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('morning_shift_start')->default(8);
            $table->unsignedTinyInteger('morning_shift_end')->default(14);
            $table->unsignedTinyInteger('evening_shift_start')->default(14);
            $table->unsignedTinyInteger('evening_shift_end')->default(20);
            $table->unsignedTinyInteger('night_shift_start')->default(20);
            $table->unsignedTinyInteger('night_shift_end')->default(8);
        });
    }
};
