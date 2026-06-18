<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20);
            $table->string('contact_person')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->nullOnDelete();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->enum('unit', ['tablet', 'capsule', 'syrup', 'injection', 'drops', 'cream', 'sachet', 'vial', 'ampoule', 'patch', 'inhaler', 'other'])->default('tablet');
            $table->unsignedInteger('pack_size')->default(1);
            $table->string('strength')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('trade_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('minimum_stock')->default(10);
            $table->boolean('is_controlled')->default(false);
            $table->boolean('requires_prescription')->default(false);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'generic_name']);
            $table->index('stock_quantity');
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('remaining_quantity');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['medicine_id', 'expiry_date']);
            $table->index('expiry_date');
        });

        Schema::create('medicine_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'expired', 'return']);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['medicine_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_stocks');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('medicine_categories');
    }
};
