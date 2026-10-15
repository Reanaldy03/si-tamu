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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->string('actor_type', 20);
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();
            $table->string('target_type', 64)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_public_id', 64)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->dateTime('occurred_at', 6);

            $table->index(['event_type', 'occurred_at'], 'idx_audit_event_time');
            $table->index(['actor_user_id', 'occurred_at'], 'idx_audit_actor_time');
            $table->index(['target_type', 'target_id', 'occurred_at'], 'idx_audit_target_time');
            $table->index('request_id', 'idx_audit_request_id');
            $table->index('occurred_at', 'idx_audit_occurred_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_actor_type CHECK (actor_type IN ('USER', 'SYSTEM', 'PUBLIC'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
