<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('purpose');
            $table->string('letter_number', 100)->nullable()->after('visit_date');
        });

        // Backfill: legacy rows keep a usable visit date derived from registration date.
        DB::table('visits')
            ->whereNull('visit_date')
            ->update(['visit_date' => DB::raw('DATE(registered_at)')]);

        Schema::table('visits', function (Blueprint $table) {
            $table->index('visit_date', 'idx_visits_visit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('idx_visits_visit_date');
            $table->dropColumn(['visit_date', 'letter_number']);
        });
    }
};
