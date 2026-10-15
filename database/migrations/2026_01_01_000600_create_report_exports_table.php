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
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uk_report_exports_public_id');
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();
            $table->string('format', 10);
            $table->string('status', 20)->default('PENDING');
            $table->json('filters');
            $table->unsignedBigInteger('row_count')->nullable();
            $table->string('storage_disk', 50)->nullable();
            $table->string('storage_path', 1000)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->dateTime('expires_at', 6)->nullable();
            $table->dateTime('completed_at', 6)->nullable();
            $table->dateTime('failed_at', 6)->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->dateTime('last_downloaded_at', 6)->nullable();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['requested_by_user_id', 'created_at'], 'idx_report_exports_user_created');
            $table->index(['status', 'created_at'], 'idx_report_exports_status_created');
            $table->index(['status', 'expires_at'], 'idx_report_exports_status_expiry');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE report_exports ADD CONSTRAINT chk_report_exports_format CHECK (format IN ('PDF', 'XLSX', 'CSV'))");
            DB::statement("ALTER TABLE report_exports ADD CONSTRAINT chk_report_exports_status CHECK (status IN ('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'EXPIRED'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
