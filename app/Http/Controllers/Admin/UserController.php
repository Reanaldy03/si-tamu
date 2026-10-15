<?php

namespace App\Http\Controllers\Admin;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of back-office users.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $roleSlug = $request->input('role');
            $query->whereHas('roles', fn ($r) => $r->where('slug', $roleSlug));
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('manage', User::class);

        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(
        StoreUserRequest $request,
        PublicIdGenerator $generator,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $data = $request->validated();
        $roleSlug = $data['role'];

        $user = User::create([
            'public_id' => $generator->generate(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'],
        ]);

        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
            'assigned_by_user_id' => $request->user()->id,
        ]);

        $recordAudit->execute(
            eventType: 'user.created',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'user',
            targetId: $user->id,
            targetPublicId: $user->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            afterState: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleSlug,
                'is_active' => $user->is_active,
            ]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil didaftarkan.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->authorize('manage', User::class);

        $roles = Role::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(
        UpdateUserRequest $request,
        User $user,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $data = $request->validated();
        $roleSlug = $data['role'];

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
            $updateData['password_changed_at'] = now();
        }

        $beforeRole = $user->primary_role_code?->value;
        $beforeActive = $user->is_active;

        $user->update($updateData);

        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->roles()->sync([
            $role->id => [
                'assigned_at' => now(),
                'assigned_by_user_id' => $request->user()->id,
            ],
        ]);

        if (! empty($data['password'])) {
            $user->setRememberToken(Str::random(60));
            $user->save();

            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $recordAudit->execute(
            eventType: 'user.updated',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'user',
            targetId: $user->id,
            targetPublicId: $user->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $beforeRole,
                'is_active' => $beforeActive,
            ],
            afterState: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleSlug,
                'is_active' => $user->is_active,
            ]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Toggle the active status of the user with self-protection.
     */
    public function toggleStatus(
        Request $request,
        User $user,
        RecordAuditEventAction $recordAudit
    ): RedirectResponse {
        $this->authorize('manage', User::class);

        if ($user->id === $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $oldStatus = $user->is_active;
        $user->is_active = ! $oldStatus;
        $user->save();

        $recordAudit->execute(
            eventType: 'user.status_toggled',
            actorType: 'USER',
            actorUserId: $request->user()->id,
            targetType: 'user',
            targetId: $user->id,
            targetPublicId: $user->public_id,
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip(),
            beforeState: ['is_active' => $oldStatus],
            afterState: ['is_active' => $user->is_active]
        );

        $label = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Akun pengguna {$user->name} berhasil {$label}.");
    }
}
