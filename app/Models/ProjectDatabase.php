<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDatabase extends Model
{
    protected $fillable = ['project_id', 'host', 'database_name', 'database_user', 'secret_reference', 'encrypted_password', 'status', 'connection_checked_at'];

    protected $hidden = ['encrypted_password'];

    protected function casts(): array
    {
        return [
            'connection_checked_at' => 'datetime',
            'encrypted_password' => 'encrypted',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
