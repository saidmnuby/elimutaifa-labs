<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    private const DEFAULTS = [
        'email_verification_required' => true,
        'login_email_2fa' => false,
        'notify_deployment_succeeded' => true,
        'notify_deployment_failed' => true,
    ];

    public function edit(): View
    {
        return view('admin.system-settings', [
            'settings' => collect(self::DEFAULTS)->mapWithKeys(fn (bool $default, string $key) => [
                $key => PlatformSetting::enabled($key, $default),
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(collect(self::DEFAULTS)->mapWithKeys(fn (bool $default, string $key) => [
            $key => ['sometimes', 'boolean'],
        ])->all());

        $before = collect(self::DEFAULTS)->mapWithKeys(fn (bool $default, string $key) => [
            $key => PlatformSetting::enabled($key, $default),
        ]);

        foreach (array_keys(self::DEFAULTS) as $key) {
            PlatformSetting::set($key, $request->boolean($key));
        }

        $after = collect(self::KEYS)->mapWithKeys(fn (string $key) => [$key => $request->boolean($key)]);
        ActivityLogger::record($request, 'system.settings_updated', details: [
            'changed' => $before->filter(fn (bool $value, string $key) => $value !== $after[$key])->keys()->all(),
        ]);

        return back()->with('success', 'System settings saved.');
    }
}
