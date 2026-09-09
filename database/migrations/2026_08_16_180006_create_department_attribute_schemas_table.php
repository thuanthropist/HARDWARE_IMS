<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_attribute_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key');
            $table->string('label');
            $table->string('input_type')->default('text');
            $table->string('unit')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['department_id', 'attribute_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_attribute_schemas');
    }
};
