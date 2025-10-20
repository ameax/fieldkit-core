<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fieldkit_forms', function (Blueprint $table) {
            $table->id();

            // Purpose - unique identifier
            $table->string('purpose_token')->unique()->index();

            // Meta information
            $table->string('name');  // Display: "Customer Registration"
            $table->text('description')->nullable();

            // Status
            $table->boolean('is_active')->default(true)->index();

            // Multi-Tenant (optional)
            $table->string('owner_type')->nullable()->index();
            $table->unsignedBigInteger('owner_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fieldkit_forms');
    }
};