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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uk_visits_public_id');
            $table->char('submission_token_hash', 64)->nullable()->unique('uk_visits_submission_token_hash');

            $table->string('visitor_name', 150);
            $table->string('origin_organization', 200);
            $table->string('contact_phone', 40)->nullable();
            $table->string('visitor_email', 190)->nullable();
            $table->string('visitor_address', 500)->nullable();
            $table->string('visitor_occupation', 150)->nullable();
            $table->string('visitor_gender', 32)->nullable();
            $table->string('visitor_identity_type', 50)->nullable();
            $table->text('visitor_identity_encrypted')->nullable();
            $table->unsignedSmallInteger('party_size')->default(1);

            $table->foreignId('department_id')
                ->constrained('departments')
                ->onUpdate('restrict')
                ->restrictOnDelete();
            $table->string('department_name_snapshot', 150);
            $table->text('purpose');
            $table->text('notes')->nullable();

            $table->string('current_status', 32)->default('REGISTERED');
            $table->string('registration_source', 20)->default('PUBLIC');
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();

            $table->dateTime('registered_at', 6);
            $table->dateTime('status_updated_at', 6);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index('registered_at', 'idx_visits_registered_at');
            $table->index(['current_status', 'registered_at'], 'idx_visits_status_registered');
            $table->index(['department_id', 'registered_at'], 'idx_visits_department_registered');
            $table->index(['visitor_name', 'registered_at'], 'idx_visits_name_registered');
            $table->index(['origin_organization', 'registered_at'], 'idx_visits_origin_registered');
            $table->index(['created_by_user_id', 'registered_at'], 'idx_visits_created_by');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE visits ADD CONSTRAINT chk_visits_status CHECK (current_status IN ('REGISTERED', 'ARRIVED', 'IN_SERVICE', 'COMPLETED', 'CANCELLED'))");
            DB::statement("ALTER TABLE visits ADD CONSTRAINT chk_visits_registration_source CHECK (registration_source IN ('PUBLIC', 'STAFF', 'IMPORT'))");
            DB::statement('ALTER TABLE visits ADD CONSTRAINT chk_visits_party_size CHECK (party_size >= 1)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
