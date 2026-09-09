<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Labor/installation lines converted from a quote have no product.
        DB::statement('ALTER TABLE order_items MODIFY COLUMN product_id BIGINT UNSIGNED NULL');

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('description')->nullable()->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        DB::statement('ALTER TABLE order_items MODIFY COLUMN product_id BIGINT UNSIGNED NOT NULL');
    }
};
