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
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('organization_name', 200);
            $table->string('logo_path', 500)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('contact_email', 190)->nullable();
            $table->text('registration_instruction')->nullable();
            $table->text('privacy_notice')->nullable();
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->json('registration_form_config');
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE organization_settings ADD CONSTRAINT chk_organization_settings_singleton CHECK (id = 1)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
