<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $isAdmin = true;
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'developer_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $timezone = 'Africa/Dar_es_Salaam';
        $date = array_key_exists('date', $filters) ? ($filters['date'] ?? '') : now($timezone)->toDateString();

        $query = AuditEvent::query()->with(['actor', 'project.owner', 'project.repositories'])->latest('created_at');

        if ($isAdmin) {
            $query->where(function (Builder $events) {
                $events->whereHas('actor', fn (Builder $actor) => $actor->where('role', User::ROLE_DEVELOPER))
                    ->orWhereHas('project.owner', fn (Builder $owner) => $owner->where('role', User::ROLE_DEVELOPER));
            });
            if (! empty($filters['developer_id'])) {
                $developerId = (int) $filters['developer_id'];
                $query->where(function (Builder $events) use ($developerId) {
                    $events->where('actor_id', $developerId)
                        ->orWhereHas('project', fn (Builder $project) => $project->where('owner_id', $developerId));
                });
            }
        } else {
            $query->where(function (Builder $events) use ($user) {
                $events->where('actor_id', $user->id)
                    ->orWhereHas('project', fn (Builder $project) => $project->where('owner_id', $user->id));
            });
        }

        if ($date !== '') {
            $start = Carbon::createFromFormat('!Y-m-d', $date, $timezone)->utc();
            $end = $start->copy()->addDay();
            $query->where('created_at', '>=', $start)->where('created_at', '<', $end);
        }

        $activities = $query->paginate(20)->withQueryString();
        $labels = [
            'account.login' => 'Signed in',
            'account.logout' => 'Signed out',
            'account.registered' => 'Created a developer account',
            'account.email_changed' => 'Changed account email',
            'project.submitted' => 'Submitted a hosting request',
            'project.approved' => 'Project approved',
            'project.rejected' => 'Project request rejected',
            'project.resources_updated' => 'Project settings updated',
            'database.requested' => 'Requested a database',
            'database.approved' => 'Database access approved',
            'database.rejected' => 'Database request rejected',
            'database.credentials.viewed' => 'Viewed database connection details',
            'database.exported' => 'Exported a database backup',
            'database.query_executed' => 'Ran a database command',
            'database.table.created' => 'Created a database table',
            'database.table.deleted' => 'Deleted a database table',
            'database.row.created' => 'Added a database record',
            'database.row.updated' => 'Updated a database record',
            'database.row.deleted' => 'Deleted a database record',
            'deployment.queued' => 'Deployment started',
            'deployment.success' => 'Deployment completed',
            'deployment.failure' => 'Deployment failed',
            'deployment.cancelled' => 'Deployment cancelled',
            'developer.status_changed' => 'Developer account status changed',
        ];

        $activities->getCollection()->transform(function (AuditEvent $event) use ($labels) {
            $details = $event->details ?? [];
            $event->activity_label = $labels[$event->action] ?? str($event->action)->replace('.', ' ')->replace('_', ' ')->ucfirst()->toString();
            $event->developer_name = $event->actor?->role === User::ROLE_DEVELOPER
                ? $event->actor->name
                : ($event->project?->owner?->name ?? ($event->actor?->isAdmin() ? 'Platform admin' : 'System'));
            $event->project_name = $event->project?->name;
            $event->activity_summary = match ($event->action) {
                'project.submitted' => data_get($details, 'repository')
                    ?? $event->project?->repositories->first()?->full_name,
                'database.requested' => filled(data_get($details, 'requested_name_suffix'))
                    ? 'Requested name: '.data_get($details, 'requested_name_suffix') : null,
                'database.query_executed' => filled(data_get($details, 'operation'))
                    ? 'Command type: '.data_get($details, 'operation') : null,
                'database.table.created', 'database.table.deleted', 'database.row.created', 'database.row.updated', 'database.row.deleted' => filled(data_get($details, 'table'))
                    ? 'Table: '.data_get($details, 'table') : null,
                'deployment.queued', 'deployment.success', 'deployment.failure', 'deployment.cancelled' => filled(data_get($details, 'commit_sha'))
                    ? 'Commit: '.substr((string) data_get($details, 'commit_sha'), 0, 12) : null,
                'developer.status_changed' => filled(data_get($details, 'to'))
                    ? 'New account status: '.data_get($details, 'to') : null,
                default => null,
            };

            return $event;
        });

        $todayQuery = AuditEvent::query();
        if ($isAdmin) {
            $todayQuery->where(function (Builder $events) {
                $events->whereHas('actor', fn (Builder $actor) => $actor->where('role', User::ROLE_DEVELOPER))
                    ->orWhereHas('project.owner', fn (Builder $owner) => $owner->where('role', User::ROLE_DEVELOPER));
            });
        } else {
            $todayQuery->where(function (Builder $events) use ($user) {
                $events->where('actor_id', $user->id)
                    ->orWhereHas('project', fn (Builder $project) => $project->where('owner_id', $user->id));
            });
        }
        $todayStart = Carbon::today($timezone)->utc();
        $todayEnd = $todayStart->copy()->addDay();
        $todayCount = $todayQuery->where('created_at', '>=', $todayStart)->where('created_at', '<', $todayEnd)->count();

        return view('activities.index', [
            'activities' => $activities,
            'isAdmin' => $isAdmin,
            'developers' => $isAdmin ? User::where('role', User::ROLE_DEVELOPER)->orderBy('name')->get(['id', 'name', 'email']) : collect(),
            'selectedDate' => $date,
            'selectedDeveloper' => $filters['developer_id'] ?? '',
            'todayCount' => $todayCount,
        ]);
    }
}
