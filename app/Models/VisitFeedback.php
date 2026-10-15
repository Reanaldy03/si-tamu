<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitFeedback extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visit_feedback';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'visit_id',
        'rating',
        'feedback_text',
        'ip_address',
        'submitted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'submitted_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * The visit this feedback is associated with.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    /**
     * Get masked visitor name for privacy display (e.g. "Budi S***").
     */
    public function getMaskedVisitorNameAttribute(): string
    {
        $name = trim((string) ($this->visit?->visitor_name ?? 'Tamu Anonim'));
        $parts = preg_split('/\s+/', $name) ?: [];

        if (count($parts) <= 1) {
            return mb_strlen($name) > 1
                ? mb_substr($name, 0, 1).'***'
                : 'Tamu Anonim';
        }

        $first = $parts[0];
        $second = mb_substr($parts[1], 0, 1).'***';

        return $first.' '.$second;
    }

    /**
     * Get qualitative label for the numeric rating (1-5).
     */
    public function ratingLabel(): string
    {
        return match ($this->rating) {
            1 => 'Sangat Tidak Puas',
            2 => 'Tidak Puas',
            3 => 'Cukup',
            4 => 'Puas',
            5 => 'Sangat Puas',
            default => 'Tidak Diketahui',
        };
    }
}
