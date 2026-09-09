<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('fulfilled_quantity')->default(0)->after('quantity');
            $table->enum('fulfillment_status', ['pending', 'fulfilled', 'backordered', 'dropped'])
                ->default('pending')
                ->after('fulfilled_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['fulfilled_quantity', 'fulfillment_status']);
        });
    }
};
