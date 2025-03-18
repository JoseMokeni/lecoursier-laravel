<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    /** @use HasFactory<\Database\Factories\RewardFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'points',
        'user_id',
    ];

    /**
     * Get the user that owns the reward.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
