<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_DEVELOPER = 'developer';
    public const PLAN_FREE = 'free';
    public const PLAN_PLUS = 'plus';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'github_username',
        'github_id',
        'github_verified_at',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'github_verified_at' => 'datetime',
            'plus_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN && $this->status === self::STATUS_ACTIVE;
    }

    public function planLimits(): array
    {
        return config('platform.plans.'.$this->effectivePlan(), config('platform.plans.free'));
    }

    public function projectLimit(): ?int
    {
        return $this->planLimits()['projects'];
    }

    public function databaseLimitPerProject(): ?int
    {
        return $this->planLimits()['databases_per_project'];
    }

    public function isPlus(): bool
    {
        return $this->effectivePlan() === self::PLAN_PLUS;
    }

    public function effectivePlan(): string
    {
        if ($this->plan === self::PLAN_PLUS && (! $this->plus_expires_at || $this->plus_expires_at->isFuture())) {
            return self::PLAN_PLUS;
        }

        return self::PLAN_FREE;
    }

}
