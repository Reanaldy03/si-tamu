<?php

namespace App\Models;

use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visits';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'submission_token_hash',
        'visitor_name',
        'origin_organization',
        'contact_phone',
        'visitor_email',
        'visitor_address',
        'visitor_occupation',
        'visitor_gender',
        'visitor_identity_type',
        'visitor_identity_encrypted',
        'party_size',
        'department_id',
        'department_name_snapshot',
        'purpose',
        'notes',
        'current_status',
        'registration_source',
        'created_by_user_id',
        'qr_token',
        'checked_in_at',
        'registered_at',
        'status_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_status' => VisitStatus::class,
            'registration_source' => RegistrationSource::class,
            'party_size' => 'integer',
            'visitor_identity_encrypted' => 'encrypted',
            'checked_in_at' => 'immutable_datetime',
            'registered_at' => 'immutable_datetime',
            'status_updated_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * The department designated for this visit.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * The user who recorded this visit on behalf of the guest, if any.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Complete timeline of status transitions for this visit.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(VisitStatusHistory::class, 'visit_id')
            ->orderBy('changed_at')
            ->orderBy('id');
    }

    /**
     * Latest status history record.
     */
    public function latestStatusHistory(): HasOne
    {
        return $this->hasOne(VisitStatusHistory::class, 'visit_id')
            ->latestOfMany('changed_at');
    }

    /**
     * Feedback submitted by the guest for this visit, if any.
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(VisitFeedback::class, 'visit_id');
    }

    /**
     * Get the route key for implicit model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
