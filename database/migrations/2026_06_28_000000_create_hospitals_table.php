<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tenant registry for the multi-tenant SaaS conversion. Each row is one
     * hospital (tenant). Tenant-owned tables gain a hospital_id in a later phase;
     * this table holds identity, status, and the trial/subscription lifecycle
     * that gates access.
     */
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();              // subdomain / tenant key
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Access lifecycle
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('plan')->nullable();            // trial / basic / pro …
            $table->enum('subscription_status', ['trialing', 'active', 'past_due', 'canceled'])
                ->default('trialing');
            $table->date('trial_ends_at')->nullable();     // 12-month free trial end
            $table->date('subscribed_until')->nullable();  // paid period end

            $table->json('settings')->nullable();          // per-tenant branding / config
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'subscription_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
