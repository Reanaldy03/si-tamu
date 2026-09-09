<?php

namespace App\Models;

use App\Domain\Access\Enums\RoleCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'immutable_datetime',
            'password_changed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot('assigned_at', 'assigned_by_user_id');
    }

    /**
     * Visits created by this user (on behalf of guest).
     */
    public function createdVisits(): HasMany
    {
        return $this->hasMany(Visit::class, 'created_by_user_id');
    }

    /**
     * Visit status history records changed by this user.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(VisitStatusHistory::class, 'changed_by_user_id');
    }

    /**
     * Audit logs triggered by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }

    /**
     * Report exports requested by this user.
     */
    public function reportExports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'requested_by_user_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|RoleCode $role): bool
    {
        $roleSlug = $role instanceof RoleCode ? $role->value : $role;

        return $this->roles->contains(fn (Role $r) => $r->slug === $roleSlug);
    }

    /**
     * Check if user has a specific permission via any assigned role.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains(fn (Permission $p) => $p->slug === $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(RoleCode::ADMIN);
    }

    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->hasRole(RoleCode::STAFF);
    }
}
