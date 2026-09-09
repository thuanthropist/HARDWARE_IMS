<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending_confirmation', 'confirmed', 'processing', 'partially_fulfilled',
            'ready_for_pickup', 'out_for_delivery', 'completed', 'cancelled', 'rejected'
        ) NOT NULL DEFAULT 'pending_confirmation'");

        Schema::table('orders', function ($table) {
            $table->text('rejection_reason')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function ($table) {
            $table->dropColumn('rejection_reason');
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending_confirmation', 'confirmed', 'processing',
            'ready_for_pickup', 'out_for_delivery', 'completed', 'cancelled'
        ) NOT NULL DEFAULT 'pending_confirmation'");
    }
};
