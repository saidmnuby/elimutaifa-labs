<?php

use Illuminate\Foundation\Inspiring;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:make-admin {email}', function (string $email) {
    $user = User::where('email', mb_strtolower($email))->first();

    if (! $user) {
        $this->error('Account not found. Please register first.');

        return 1;
    }

    $user->forceFill([
        'role' => User::ROLE_ADMIN,
        'status' => User::STATUS_ACTIVE,
    ])->save();

    $this->info("{$user->email} sasa ni admin wa platform.");

    return 0;
})->purpose('Promote an existing account to the platform administrator');

Artisan::command('platform:make-admin {email}', function (string $email) {
    $user = User::where('email', mb_strtolower($email))->first();

    if (! $user) {
        $this->error('Account not found. Please register first, then run this command.');

        return 1;
    }

    $user->forceFill([
        'role' => User::ROLE_ADMIN,
        'status' => User::STATUS_ACTIVE,
    ])->save();

    $this->info("{$user->email} sasa ni admin wa platform.");

    return 0;
})->purpose('Promote an existing account to the platform administrator');
