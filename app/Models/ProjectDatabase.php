<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDatabase extends Model
{
    protected $fillable = ['project_id', 'host', 'database_name', 'database_user', 'secret_reference', 'status', 'connection_checked_at'];

    protected function casts(): array
    {
        return ['connection_checked_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
