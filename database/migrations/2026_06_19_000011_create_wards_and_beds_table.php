<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('ward_type', ['general', 'private', 'icu', 'nicu', 'emergency', 'maternity', 'surgical', 'pediatric'])->default('general');
            $table->unsignedInteger('total_beds')->default(0);
            $table->text('description')->nullable();
            $table->unsignedInteger('floor')->default(1);
            $table->enum('status', ['active', 'inactive', 'under_maintenance'])->default('active');
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('room_number');
            $table->enum('room_type', ['general', 'private', 'semi_private', 'icu'])->default('general');
            $table->decimal('charge_per_day', 10, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();

            $table->unique(['ward_id', 'room_number']);
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bed_number');
            $table->enum('bed_type', ['standard', 'electric', 'bariatric', 'pediatric'])->default('standard');
            $table->decimal('charge_per_day', 10, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->timestamps();

            $table->unique(['ward_id', 'bed_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('wards');
    }
};
