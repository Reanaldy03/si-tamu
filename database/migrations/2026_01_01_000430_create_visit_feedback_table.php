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
        Schema::create('visit_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')
                ->unique('uk_visit_feedback_visit_id')
                ->constrained('visits')
                ->onUpdate('restrict')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('feedback_text')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('submitted_at', 6);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index('rating', 'idx_visit_feedback_rating');
            $table->index('submitted_at', 'idx_visit_feedback_submitted_at');
            $table->index(['rating', 'submitted_at'], 'idx_feedback_rating_date');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE visit_feedback ADD CONSTRAINT chk_visit_feedback_rating CHECK (rating >= 1 AND rating <= 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_feedback');
    }
};
