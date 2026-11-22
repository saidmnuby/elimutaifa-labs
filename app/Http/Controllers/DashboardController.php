<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ServiceModule;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $projects = $user->status === User::STATUS_ACTIVE
            ? $user->projects()->with(['modules', 'repositories', 'domains'])->latest()->get()
            : collect();

        return view('dashboard', [
            'projects' => $projects,
            'projectCount' => $projects->count(),
            'moduleCount' => ServiceModule::where('is_enabled', true)->count(),
            'pendingDevelopers' => $user->isAdmin()
                ? User::where('role', User::ROLE_DEVELOPER)->where('status', User::STATUS_PENDING)->count()
                : null,
            'totalProjects' => $user->isAdmin() ? Project::count() : null,
        ]);
    }
}
