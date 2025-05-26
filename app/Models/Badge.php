<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
        'criteria',
        'points',
        'rarity',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * The users that have earned this badge.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'progress')
            ->withTimestamps();
    }

    /**
     * Get all user badge records for this badge.
     */
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }

    /**
     * Check if a user has earned this badge.
     */
    public function hasBeenEarnedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the progress of a user towards this badge.
     */
    public function getProgressFor(User $user): ?array
    {
        $userBadge = $this->users()->where('user_id', $user->id)->first();

        if (!$userBadge) {
            return null;
        }

        return $userBadge->pivot->progress;
    }

    /**
     * Scope to get active badges only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by rarity.
     */
    public function scopeByRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }
}
