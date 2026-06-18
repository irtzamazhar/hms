<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('employee_id')->nullable()->unique()->after('avatar');
            $table->enum('user_type', ['super_admin', 'hospital_admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'lab_technician', 'accountant'])->default('receptionist')->after('employee_id');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('user_type');
            $table->date('joining_date')->nullable()->after('status');
            $table->boolean('is_two_factor_enabled')->default(false)->after('joining_date');
            $table->string('two_factor_secret')->nullable()->after('is_two_factor_enabled');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_secret');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'employee_id', 'user_type', 'status',
                'joining_date', 'is_two_factor_enabled', 'two_factor_secret',
                'last_login_at', 'last_login_ip', 'deleted_at',
            ]);
        });
    }
};
