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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('restrict')
                ->cascadeOnDelete();
            $table->foreignId('role_id')
                ->constrained('roles')
                ->onUpdate('restrict')
                ->restrictOnDelete()
                ->index('idx_user_roles_role_id');
            $table->dateTime('assigned_at', 6);
            $table->foreignId('assigned_by_user_id')
                ->nullable()
                ->index('idx_user_roles_assigned_by')
                ->constrained('users')
                ->onUpdate('restrict')
                ->nullOnDelete();

            $table->primary(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
