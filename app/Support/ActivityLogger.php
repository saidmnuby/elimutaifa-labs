<?php

namespace App\Support;

use App\Models\AuditEvent;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function record(
        Request $request,
        string $action,
        ?int $projectId = null,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        array $details = [],
    ): void {
        AuditEvent::create([
            'actor_id' => $request->user()?->id,
            'project_id' => $projectId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'details' => $details,
            'ip_address' => $request->ip(),
        ]);
    }
}
