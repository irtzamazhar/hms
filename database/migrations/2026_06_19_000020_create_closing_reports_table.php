<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closing_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->unsignedInteger('total_opd_patients')->default(0);
            $table->unsignedInteger('total_ipd_admissions')->default(0);
            $table->unsignedInteger('total_ipd_discharged')->default(0);
            $table->decimal('opd_revenue', 10, 2)->default(0);
            $table->decimal('ipd_revenue', 10, 2)->default(0);
            $table->decimal('pharmacy_revenue', 10, 2)->default(0);
            $table->decimal('lab_revenue', 10, 2)->default(0);
            $table->decimal('other_revenue', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('hospital_expenses', 10, 2)->default(0);
            $table->decimal('pharmacy_expenses', 10, 2)->default(0);
            $table->decimal('lab_expenses', 10, 2)->default(0);
            $table->decimal('salary_expenses', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('net_profit', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamp('closed_at');
            $table->timestamps();
        });

        Schema::create('monthly_closing_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_opd_patients')->default(0);
            $table->unsignedInteger('total_ipd_admissions')->default(0);
            $table->decimal('opd_revenue', 10, 2)->default(0);
            $table->decimal('ipd_revenue', 10, 2)->default(0);
            $table->decimal('pharmacy_revenue', 10, 2)->default(0);
            $table->decimal('lab_revenue', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('total_salaries', 10, 2)->default(0);
            $table->decimal('pharmacy_purchase_cost', 10, 2)->default(0);
            $table->decimal('pharmacy_profit', 10, 2)->default(0);
            $table->decimal('lab_profit', 10, 2)->default(0);
            $table->decimal('net_profit', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->unique(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closing_reports');
        Schema::dropIfExists('daily_closing_reports');
    }
};
