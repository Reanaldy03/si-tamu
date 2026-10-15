<?php

namespace App\Http\Controllers\Admin;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::withCount('visits');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $departments = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        $this->authorize('manage', Department::class);

        return view('admin.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(
        StoreDepartmentRequest $request,
        PublicIdGenerator $generator,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $data = $request->validated();
        $data['public_id'] = $generator->generate();

        $department = Department::create($data);

        $recordAudit->execute(
            eventType: 'department.created',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'department',
            targetId: $department->id,
            targetPublicId: $department->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            afterState: [
                'code' => $department->code,
                'name' => $department->name,
                'is_active' => $department->is_active,
            ]
        );

        return redirect()
            ->route('admin.departments.index')
            ->with('success', "Departemen {$department->name} berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): View
    {
        $this->authorize('manage', Department::class);

        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $before = [
            'code' => $department->code,
            'name' => $department->name,
            'description' => $department->description,
            'sort_order' => $department->sort_order,
            'is_active' => $department->is_active,
        ];

        $department->update($request->validated());

        $after = [
            'code' => $department->code,
            'name' => $department->name,
            'description' => $department->description,
            'sort_order' => $department->sort_order,
            'is_active' => $department->is_active,
        ];

        $recordAudit->execute(
            eventType: 'department.updated',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'department',
            targetId: $department->id,
            targetPublicId: $department->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: $before,
            afterState: $after
        );

        return redirect()
            ->route('admin.departments.index')
            ->with('success', "Departemen {$department->name} berhasil diperbarui.");
    }

    /**
     * Toggle the active status of the department.
     */
    public function toggleStatus(
        Request $request,
        Department $department,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $this->authorize('manage', Department::class);

        $oldStatus = $department->is_active;
        $department->is_active = ! $oldStatus;
        $department->save();

        $recordAudit->execute(
            eventType: 'department.status_toggled',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'department',
            targetId: $department->id,
            targetPublicId: $department->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: ['is_active' => $oldStatus],
            afterState: ['is_active' => $department->is_active]
        );

        $label = $department->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Status unit {$department->name} berhasil {$label}.");
    }

    /**
     * Remove the specified department from storage if no visits exist.
     */
    public function destroy(
        Request $request,
        Department $department,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $this->authorize('manage', Department::class);

        if ($department->visits()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Unit tidak dapat dihapus karena memiliki riwayat kunjungan. Silakan nonaktifkan unit ini.');
        }

        $snapshot = ['code' => $department->code, 'name' => $department->name];
        $id = $department->id;
        $publicId = $department->public_id;

        $department->delete();

        $recordAudit->execute(
            eventType: 'department.deleted',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'department',
            targetId: $id,
            targetPublicId: $publicId,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: $snapshot
        );

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Departemen berhasil dihapus.');
    }
}
