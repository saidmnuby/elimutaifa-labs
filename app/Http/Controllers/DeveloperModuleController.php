<?php

namespace App\Http\Controllers;

use App\Models\ServiceModule;
use Illuminate\View\View;

class DeveloperModuleController extends Controller
{
    public function show(string $moduleKey): View
    {
        $module = ServiceModule::where('key', $moduleKey)
            ->where('is_enabled', true)
            ->firstOrFail();

        $projects = auth()->user()->projects()
            ->when($moduleKey === 'database', fn ($query) => $query->where('projects.status', '!=', 'suspended'))
            ->when($moduleKey === 'github-deploy', fn ($query) => $query->whereHas('modules', fn ($moduleQuery) => $moduleQuery
                ->where('service_modules.key', $moduleKey)
                ->whereIn('project_modules.status', ['pending', 'active', 'rejected'])))
            ->with(['repositories', 'domains', 'databases', 'modules', 'databaseRequests'])
            ->latest()
            ->get();

        $developer = auth()->user();
        $projectCount = $developer->projects()
            ->whereIn('status', ['pending', 'active', 'suspended'])
            ->count();
        $projectLimit = $developer->projectLimit();

        return view('modules.show', compact('module', 'projects', 'developer', 'projectCount', 'projectLimit'));
    }
}
