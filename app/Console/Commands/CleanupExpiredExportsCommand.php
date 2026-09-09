<?php

namespace App\Console\Commands;

use App\Domain\Reporting\Enums\ExportStatus;
use App\Models\ReportExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredExportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitamu:exports:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired ephemeral report export files and update status to EXPIRED';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pembersihan berkas ekspor laporan yang kedaluwarsa...');

        $expiredExports = ReportExport::where('status', ExportStatus::COMPLETED->value)
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredExports as $export) {
            if ($export->storage_disk && $export->storage_path && Storage::disk($export->storage_disk)->exists($export->storage_path)) {
                Storage::disk($export->storage_disk)->delete($export->storage_path);
            }

            $export->update([
                'status' => ExportStatus::EXPIRED,
            ]);

            $count++;
        }

        $this->info("Berhasil membersihkan {$count} berkas ekspor kedaluwarsa.");

        return Command::SUCCESS;
    }
}
