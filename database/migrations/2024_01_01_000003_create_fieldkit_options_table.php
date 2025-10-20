<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fieldkit_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fieldkit_definition_id')
                ->constrained('fieldkit_definitions')
                ->onDelete('cascade');

            $table->string('value');
            $table->string('label');
            $table->text('description')->nullable();  // For radio with descriptions
            $table->string('icon')->nullable();
            $table->string('external_identifier')->nullable();  // For external systems (fallback: value)
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fieldkit_options');
    }
};