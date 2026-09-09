<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_input_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculator_type_id')->constrained()->cascadeOnDelete();
            $table->string('field_key');
            $table->string('label');
            $table->enum('input_type', ['number', 'select', 'radio', 'text', 'repeater']);
            $table->json('options')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['calculator_type_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_input_fields');
    }
};
