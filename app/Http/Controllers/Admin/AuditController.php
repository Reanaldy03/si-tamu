<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrganizationSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    /**
     * Human-readable Indonesian labels for audit event types (DESIGN.md §45.3).
     *
     * @var array<string, string>
     */
    public const EVENT_LABELS = [
        'auth.login_failed' => 'Gagal masuk',
        'auth.login_success' => 'Berhasil masuk',
        'auth.login_blocked_inactive' => 'Masuk ditolak (akun nonaktif)',
        'auth.logout' => 'Keluar',
        'report.export_requested' => 'Ekspor laporan diminta',
        'settings.updated' => 'Pengaturan diperbarui',
        'user.bootstrap_admin' => 'Admin awal dibuat',
        'user.created' => 'Pengguna dibuat',
        'user.updated' => 'Pengguna diperbarui',
        'user.status_toggled' => 'Status pengguna diubah',
        'visit.checked_in_qr' => 'Check-in lewat QR',
        'visit.registered' => 'Kunjungan didaftarkan',
        'visit.status_changed' => 'Status kunjungan diubah',
        'visit.data_corrected' => 'Data kunjungan diperbaiki',
        'department.created' => 'Departemen dibuat',
        'department.updated' => 'Departemen diperbarui',
        'department.status_toggled' => 'Status departemen diubah',
        'department.deleted' => 'Departemen dihapus',
    ];

    /**
     * Human-readable Indonesian labels for audit target entity types.
     *
     * @var array<string, string>
     */
    public const TARGET_LABELS = [
        'user' => 'Pengguna',
        'visit' => 'Kunjungan',
        'department' => 'Departemen',
        'report_exports' => 'Ekspor laporan',
        'organization_settings' => 'Pengaturan organisasi',
    ];

    /**
     * Resolve a human-readable label for an audit event type. Unknown slugs
     * fall back to a de-slugified sentence so nothing technical is ever shown
     * as the primary text.
     */
    public static function eventLabel(?string $eventType): string
    {
        if ($eventType === null || $eventType === '') {
            return '—';
        }

        return self::EVENT_LABELS[$eventType]
            ?? ucfirst(str_replace(['.', '_'], ' ', $eventType));
    }

    /**
     * Resolve a human-readable label for an audit target entity type.
     */
    public static function targetLabel(?string $targetType): string
    {
        if ($targetType === null || $targetType === '') {
            return '—';
        }

        return self::TARGET_LABELS[$targetType]
            ?? ucfirst(str_replace(['.', '_'], ' ', $targetType));
    }

    /**
     * Display a listing of immutable business audit logs.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $query = AuditLog::with('actorUser');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('target_public_id', 'like', "%{$q}%")
                    ->orWhere('event_type', 'like', "%{$q}%")
                    ->orWhere('target_type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->filled('actor_user_id')) {
            $query->where('actor_user_id', $request->integer('actor_user_id'));
        }

        if ($request->filled('date_from')) {
            $fromUtc = Carbon::parse($request->input('date_from'), $tz)->startOfDay()->utc();
            $query->where('occurred_at', '>=', $fromUtc);
        }

        if ($request->filled('date_to')) {
            $toUtc = Carbon::parse($request->input('date_to'), $tz)->endOfDay()->utc();
            $query->where('occurred_at', '<=', $toUtc);
        }

        $audits = $query->latest('id')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $eventTypes = AuditLog::select('event_type')->distinct()->pluck('event_type');

        return view('admin.audits.index', [
            'audits' => $audits,
            'users' => $users,
            'eventTypes' => $eventTypes,
            'tz' => $tz,
            'eventLabel' => self::eventLabel(...),
            'targetLabel' => self::targetLabel(...),
        ]);
    }

    /**
     * Display the specified audit log entry details.
     */
    public function show(AuditLog $audit): View
    {
        $this->authorize('view', $audit);

        $audit->load('actorUser');
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        return view('admin.audits.show', [
            'audit' => $audit,
            'tz' => $tz,
            'eventLabel' => self::eventLabel(...),
            'targetLabel' => self::targetLabel(...),
        ]);
    }
}
