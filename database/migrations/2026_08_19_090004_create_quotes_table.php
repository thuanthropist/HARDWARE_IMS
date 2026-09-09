<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('status', ['requested', 'reviewed', 'sent', 'accepted', 'rejected', 'expired'])->default('requested');
            $table->enum('source', ['calculator', 'manual'])->default('manual');
            $table->foreignId('calculator_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->text('project_description')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->string('timeline')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
