<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE report_exports DROP CONSTRAINT chk_report_exports_format');
        // CSV remains valid only for historical rows; new requests are PDF/XLSX at the application boundary.
        DB::statement("ALTER TABLE report_exports ADD CONSTRAINT chk_report_exports_format CHECK (format IN ('PDF', 'XLSX', 'CSV'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE report_exports DROP CONSTRAINT chk_report_exports_format');
        DB::statement("ALTER TABLE report_exports ADD CONSTRAINT chk_report_exports_format CHECK (format IN ('CSV', 'XLSX'))");
    }
};
