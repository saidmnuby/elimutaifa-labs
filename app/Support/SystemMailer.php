<?php

namespace App\Support;

use App\Models\User;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SystemMailer
{
    public function to(string $email, string $subject, array $content): bool
    {
        try {
            Mail::send('emails.system', ['mailData' => $content], function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }
    }

    public function admins(string $subject, array $content, ?string $preference = null): void
    {
        if ($preference !== null && ! PlatformSetting::enabled($preference, true)) {
            return;
        }

        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('status', User::STATUS_ACTIVE)
            ->pluck('email')
            ->filter()
            ->unique()
            ->each(fn (string $email) => $this->to($email, $subject, $content));
    }
}
