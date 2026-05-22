<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tables (Meja)
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique();
            $table->text('qr_code')->nullable();
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->timestamps();
        });

        // 2. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Menu Items
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 4. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->enum('status', ['draft', 'pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled'])->default('draft');
            $table->boolean('bill_requested')->default(false);
            $table->timestamp('bill_requested_at')->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cash_to_kasir', 'xendit']);
            $table->string('payment_type')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('change_amount', 12, 2)->nullable();
            $table->string('xendit_invoice_id')->nullable();
            $table->text('xendit_payment_url')->nullable();
            $table->string('xendit_status')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tables');
    }
};
