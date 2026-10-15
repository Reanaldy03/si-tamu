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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uk_users_public_id');
            $table->string('name', 120);
            $table->string('email', 190)->unique('uk_users_email');
            $table->string('password', 255);
            $table->boolean('is_active')->default(true)->index('idx_users_is_active');
            $table->dateTime('last_login_at', 6)->nullable();
            $table->dateTime('password_changed_at', 6)->nullable();
            $table->rememberToken();
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users ADD CONSTRAINT chk_users_is_active CHECK (is_active IN (0, 1))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
