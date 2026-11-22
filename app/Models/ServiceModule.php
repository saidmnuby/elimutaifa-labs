<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceModule extends Model
{
    protected $fillable = ['key', 'name', 'version', 'description', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_modules')
            ->withPivot(['status', 'settings'])
            ->withTimestamps();
    }
}
