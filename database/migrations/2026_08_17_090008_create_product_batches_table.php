<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('batch_number');
            $table->unsignedInteger('quantity');
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_variant_id', 'warehouse_id']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
