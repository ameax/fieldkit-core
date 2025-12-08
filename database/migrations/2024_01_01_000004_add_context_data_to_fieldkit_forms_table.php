<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fieldkit_forms', function (Blueprint $table) {
            // JSON column for flexible context configuration
            // Stores app-specific context data (e.g., shop_group_ids, regions, etc.)
            // Existing owner_type/owner_id columns are kept for backwards compatibility
            $table->json('context_data')->nullable()->after('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('fieldkit_forms', function (Blueprint $table) {
            $table->dropColumn('context_data');
        });
    }
};
