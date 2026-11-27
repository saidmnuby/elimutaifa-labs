<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use App\Support\SystemMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === User::ROLE_DEVELOPER, 404);

        return view('plans.index', ['user' => $request->user()]);
    }

    public function requestPlus(Request $request, SystemMailer $mailer): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE, 403);

        if ($user->plan === User::PLAN_PLUS) {
            return redirect()->route('plans.index')->with('success', 'Your account is already on Plus.');
        }

        AuditEvent::create([
            'actor_id' => $user->id,
            'action' => 'plan.upgrade_requested',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'details' => ['requested_plan' => User::PLAN_PLUS],
            'ip_address' => $request->ip(),
        ]);

        $mailer->admins('Plus plan request · TechwareTz Control Center (TCC)', [
            'eyebrow' => 'Plan upgrade request',
            'title' => 'A developer requested Plus',
            'intro' => 'A developer wants to upgrade from Free to Plus. Contact them to arrange activation.',
            'details' => [
                'Developer' => $user->name,
                'Email' => $user->email,
                'Requested plan' => 'Plus',
                'Price options' => 'TZS 3,000 monthly or TZS 10,000 yearly',
            ],
            'button_label' => 'Open developers',
            'button_url' => route('admin.developers'),
        ]);

        return redirect()->route('plans.index')->with('success', 'Your Plus request was sent to the platform admin. They will contact you to complete activation.');
    }
}
