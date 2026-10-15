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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique('uk_departments_public_id');
            $table->string('code', 50)->nullable()->unique('uk_departments_code');
            $table->string('name', 150);
            $table->string('description', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 6);
            $table->dateTime('updated_at', 6);

            $table->index(['is_active', 'sort_order', 'name'], 'idx_departments_active_sort_name');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE departments ADD CONSTRAINT chk_departments_is_active CHECK (is_active IN (0, 1))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
