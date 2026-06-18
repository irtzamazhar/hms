<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('token_number');
            $table->date('token_date');
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('shift', ['morning', 'evening', 'night']);
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['token_number', 'token_date', 'shift']);
            $table->index(['token_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
