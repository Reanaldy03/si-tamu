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
        Schema::table('visits', function (Blueprint $table) {
            $table->char('qr_token', 64)
                ->nullable()
                ->unique('uk_visits_qr_token')
                ->after('submission_token_hash');

            $table->dateTime('checked_in_at', 6)
                ->nullable()
                ->after('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropUnique('uk_visits_qr_token');
            $table->dropColumn(['qr_token', 'checked_in_at']);
        });
    }
};
