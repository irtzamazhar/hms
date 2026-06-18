<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['morning', 'evening', 'night']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('assignment_date');
            $table->enum('status', ['assigned', 'present', 'absent', 'late', 'on_leave'])->default('assigned');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();

            $table->unique(['user_id', 'assignment_date']);
            $table->index('assignment_date');
        });

        Schema::create('shift_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date');
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->decimal('opd_revenue', 10, 2)->default(0);
            $table->decimal('ipd_revenue', 10, 2)->default(0);
            $table->decimal('pharmacy_revenue', 10, 2)->default(0);
            $table->decimal('lab_revenue', 10, 2)->default(0);
            $table->decimal('other_revenue', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->unsignedInteger('opd_patients')->default(0);
            $table->unsignedInteger('ipd_patients')->default(0);
            $table->unsignedInteger('lab_tests')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->unique(['closing_date', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_closings');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
    }
};
