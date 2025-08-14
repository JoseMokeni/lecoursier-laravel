<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'role',
        'status',
        'password',
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
     * Get the tasks assigned to the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the badges earned by the user.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'progress')
            ->withTimestamps();
    }

    /**
     * Get the user's statistics.
     */
    public function stats()
    {
        return $this->hasOne(UserStats::class);
    }

    /**
     * Get the user's badge earnings.
     */
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * isAdmin
     *
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
