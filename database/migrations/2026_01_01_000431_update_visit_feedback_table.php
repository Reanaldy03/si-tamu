<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visit_feedback', function (Blueprint $table) {
            if (! Schema::hasColumn('visit_feedback', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('feedback_text');
            }

            $existingIndexes = collect(Schema::getIndexes('visit_feedback'))->pluck('name')->all();
            if (! in_array('idx_feedback_rating_date', $existingIndexes, true)) {
                $table->index(['rating', 'submitted_at'], 'idx_feedback_rating_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('visit_feedback', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            $table->dropIndex('idx_feedback_rating_date');
        });
    }
};
