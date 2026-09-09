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
        // Labor/installation lines have no product, so product_id must allow null.
        DB::statement('ALTER TABLE quote_items MODIFY COLUMN product_id BIGINT UNSIGNED NULL');

        Schema::table('quote_items', function (Blueprint $table) {
            $table->string('description')->nullable()->after('product_variant_id');
            $table->decimal('unit_price', 12, 2)->nullable()->after('quantity');
            $table->decimal('line_total', 14, 2)->nullable()->after('unit_price');
            $table->unsignedInteger('sort_order')->default(0)->after('line_total');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'unit_price', 'line_total', 'sort_order']);
        });

        DB::statement('ALTER TABLE quote_items MODIFY COLUMN product_id BIGINT UNSIGNED NOT NULL');
    }
};
