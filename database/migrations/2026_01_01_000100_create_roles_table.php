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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique('uk_roles_slug');
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->boolean('is_system')->default(false);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE roles ADD CONSTRAINT chk_roles_is_system CHECK (is_system IN (0, 1))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
