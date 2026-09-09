<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_output_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_type_id')->constrained()->cascadeOnDelete();
            $table->string('output_key');
            $table->json('product_attribute_filters');
            $table->enum('selection_strategy', ['cheapest_in_stock', 'cheapest', 'highest_stock'])->default('cheapest_in_stock');
            $table->timestamps();

            $table->unique(['calculator_type_id', 'output_key'], 'calc_output_mappings_type_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_output_product_mappings');
    }
};
