<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_type_id')->constrained()->restrictOnDelete();
            // No FK yet: the customers table doesn't exist until Phase 4 (storefront accounts).
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->json('input_data');
            $table->json('computed_output');
            $table->decimal('estimated_total', 14, 2)->default(0);
            $table->boolean('converted_to_cart')->default(false);
            // No FK yet: quotes are introduced in a later phase.
            $table->unsignedBigInteger('converted_to_quote_id')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('calculator_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_submissions');
    }
};
