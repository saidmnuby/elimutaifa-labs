<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = ['owner_id', 'name', 'slug', 'description', 'status'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(ServiceModule::class, 'project_modules')
            ->withPivot(['status', 'settings'])
            ->withTimestamps();
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(ProjectDomain::class);
    }

    public function databases(): HasMany
    {
        return $this->hasMany(ProjectDatabase::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class)->latest();
    }
}
