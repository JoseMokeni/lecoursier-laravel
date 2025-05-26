<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'progress',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'progress' => 'array',
    ];

    /**
     * Get the user that earned the badge.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge that was earned.
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Scope to get recently earned badges.
     */
    public function scopeRecentlyEarned($query, int $days = 7)
    {
        return $query->where('earned_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter by badge category.
     */
    public function scopeByBadgeCategory($query, string $category)
    {
        return $query->whereHas('badge', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }
}
