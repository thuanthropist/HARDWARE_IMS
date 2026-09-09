<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_formulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_type_id')->constrained()->cascadeOnDelete();
            $table->string('output_key');
            $table->string('label');
            $table->text('formula_expression');
            $table->string('unit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['calculator_type_id', 'output_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_formulas');
    }
};
