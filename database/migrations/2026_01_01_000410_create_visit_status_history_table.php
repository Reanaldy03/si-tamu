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
        Schema::create('visit_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')
                ->constrained('visits')
                ->onUpdate('restrict')
                ->restrictOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();
            $table->string('source', 20);
            $table->string('reason', 500)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->dateTime('changed_at', 6);

            $table->index(['visit_id', 'changed_at', 'id'], 'idx_visit_history_visit_changed');
            $table->index(['to_status', 'changed_at'], 'idx_visit_history_status_changed');
            $table->index(['changed_by_user_id', 'changed_at'], 'idx_visit_history_user_changed');
            $table->index('request_id', 'idx_visit_history_request_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE visit_status_history ADD CONSTRAINT chk_visit_history_from_status CHECK (from_status IS NULL OR from_status IN ('REGISTERED', 'ARRIVED', 'IN_SERVICE', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE visit_status_history ADD CONSTRAINT chk_visit_history_to_status CHECK (to_status IN ('REGISTERED', 'ARRIVED', 'IN_SERVICE', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE visit_status_history ADD CONSTRAINT chk_visit_history_source CHECK (source IN ('PUBLIC', 'STAFF', 'SYSTEM'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_status_history');
    }
};
