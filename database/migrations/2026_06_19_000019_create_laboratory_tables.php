<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_test_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('lab_test_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('normal_range')->nullable();
            $table->string('unit')->nullable();
            $table->string('sample_type')->nullable();
            $table->unsignedInteger('turnaround_hours')->default(24);
            $table->text('preparation_instructions')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('lab_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opd_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ipd_admission_id')->nullable()->constrained()->nullOnDelete();
            $table->date('booking_date');
            $table->enum('shift', ['morning', 'evening', 'night'])->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'partial'])->default('pending');
            $table->enum('status', ['pending', 'sample_collected', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_date', 'shift']);
        });

        Schema::create('lab_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('lab_bookings')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('lab_tests')->cascadeOnDelete();
            $table->decimal('cost', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_cost', 10, 2);
            $table->enum('status', ['pending', 'sample_collected', 'processing', 'completed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('lab_bookings')->cascadeOnDelete();
            $table->foreignId('booking_item_id')->constrained('lab_booking_items')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('lab_tests')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('sample_id')->nullable();
            $table->dateTime('sample_collected_at')->nullable();
            $table->dateTime('result_entered_at')->nullable();
            $table->string('result_value')->nullable();
            $table->string('result_unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->enum('result_flag', ['normal', 'high', 'low', 'critical'])->nullable();
            $table->text('result_notes')->nullable();
            $table->string('report_file')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'verified'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_reports');
        Schema::dropIfExists('lab_booking_items');
        Schema::dropIfExists('lab_bookings');
        Schema::dropIfExists('lab_tests');
        Schema::dropIfExists('lab_test_categories');
    }
};
