<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 2)->nullable()->after('timeline');
            $table->decimal('vat_amount', 14, 2)->nullable()->after('subtotal');
            $table->decimal('total', 14, 2)->nullable()->after('vat_amount');
            $table->date('valid_until')->nullable()->after('total');
            $table->timestamp('sent_at')->nullable()->after('valid_until');
            $table->foreignId('reviewed_by')->nullable()->after('sent_at')->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['subtotal', 'vat_amount', 'total', 'valid_until', 'sent_at', 'internal_notes']);
        });
    }
};
