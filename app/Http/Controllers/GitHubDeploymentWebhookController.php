<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Deployment;
use App\Models\Project;
use App\Models\Repository;
use App\Models\ServiceModule;
use App\Support\GitHubDeploymentDispatcher;
use App\Support\SystemMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GitHubDeploymentWebhookController extends Controller
{
    public function webhook(Request $request, GitHubDeploymentDispatcher $dispatcher): JsonResponse
    {
        $secret = (string) config('services.github.app_webhook_secret');
        $signature = (string) $request->header('X-Hub-Signature-256', '');
        if ($secret === '') {
            report(new \RuntimeException('GitHub App webhook secret is not configured.'));

            return response()->json(['message' => 'Webhook is not configured.'], 503);
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        if ($request->header('X-GitHub-Event') !== 'push') {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $payload = $request->json()->all();
        $deliveryId = (string) $request->header('X-GitHub-Delivery', '');
        $repositoryName = data_get($payload, 'repository.full_name');
        $installationId = data_get($payload, 'installation.id');
        $ref = data_get($payload, 'ref');
        $commitSha = data_get($payload, 'after');

        if ($deliveryId === '' || ! is_string($repositoryName)
            || ! is_int($installationId) || $installationId < 1
            || ! is_string($ref) || ! preg_match('/\Arefs\/heads\/(.+)\z/', $ref, $branchMatch)
            || ! is_string($commitSha) || ! preg_match('/\A[0-9a-f]{40}\z/i', $commitSha)
            || data_get($payload, 'deleted') === true) {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $repository = Repository::query()
            ->whereRaw('LOWER(full_name) = ?', [mb_strtolower($repositoryName)])
            ->with(['project.owner', 'project.domains', 'project.modules'])
            ->first();

        if (! $repository
            || $repository->github_installation_id !== $installationId
            || $repository->approved_at === null
            || $repository->branch !== $branchMatch[1]) {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $project = $repository->project;
        $domain = $project?->domains->first();
        $module = ServiceModule::query()->where('key', 'github-deploy')->where('is_enabled', true)->first();
        $target = $domain?->document_root;
        $pathIsSafe = is_string($target)
            && preg_match('~\A/home/elimutai/[A-Za-z0-9._/-]+\z~D', $target) === 1
            && ! preg_match('~(?:\A|/)\.\.?(/|\z)~', $target);

        if (! $project
            || $project->status !== Project::STATUS_ACTIVE
            || $project->owner?->status !== 'active'
            || ! $domain || $domain->status !== 'active'
            || ! $pathIsSafe || ! $module
            || ! $project->modules->contains(fn ($item) =>
                $item->id === $module->id && $item->pivot->status === 'active'
            )) {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        try {
            $deployment = DB::transaction(function () use ($repository, $project, $deliveryId, $commitSha) {
                return Deployment::create([
                    'project_id' => $project->id,
                    'repository_id' => $repository->id,
                    'commit_sha' => strtolower($commitSha),
                    'github_delivery_id' => $deliveryId,
                    'status' => 'queued',
                    'trigger_source' => 'github_webhook',
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            $deployment = Deployment::query()->where('github_delivery_id', $deliveryId)->first();
            if (! $deployment
                || $deployment->failure_summary !== 'GitHub Actions could not start the deployment.') {
                return response()->json(['received' => true, 'duplicate' => true]);
            }

            $deployment->forceFill([
                'status' => 'queued',
                'failure_summary' => null,
                'finished_at' => null,
            ])->save();
        }

        try {
            $dispatcher->dispatch([
                'deployment_id' => (string) $deployment->id,
                'repository' => $repository->full_name,
                'branch' => $repository->branch,
                'target' => $target,
                'source_sha' => strtolower($commitSha),
                'storage_limit_mb' => (string) ($project->owner->planLimits()['storage_mb'] ?? 0),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $deployment->forceFill([
                'status' => 'failed',
                'failure_summary' => 'GitHub Actions could not start the deployment.',
                'finished_at' => now(),
            ])->save();

            return response()->json(['message' => 'Deployment could not be queued.'], 502);
        }

        AuditEvent::create([
            'project_id' => $project->id,
            'action' => 'deployment.queued',
            'subject_type' => Deployment::class,
            'subject_id' => (string) $deployment->id,
            'details' => ['repository' => $repository->full_name, 'commit_sha' => strtolower($commitSha)],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['received' => true, 'deployment_id' => $deployment->id], 202);
    }

    public function callback(Request $request, SystemMailer $mailer): JsonResponse
    {
        $secret = (string) config('services.github.deploy_callback_secret');
        $timestamp = (string) $request->header('X-Deployment-Timestamp', '');
        $signature = (string) $request->header('X-Deployment-Signature', '');
        $body = $request->getContent();

        if ($secret === '' || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300) {
            return response()->json(['message' => 'Invalid callback.'], 401);
        }
        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);
        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid callback signature.'], 401);
        }

        $payload = $request->json()->all();
        $deploymentId = filter_var($payload['deployment_id'] ?? null, FILTER_VALIDATE_INT);
        $status = $payload['status'] ?? null;
        $sourceSha = $payload['source_sha'] ?? null;
        if (! $deploymentId || ! in_array($status, ['success', 'failure', 'cancelled'], true)
            || ! is_string($sourceSha) || ! preg_match('/\A[0-9a-f]{40}\z/i', $sourceSha)) {
            return response()->json(['message' => 'Invalid callback payload.'], 422);
        }

        $deployment = Deployment::query()
            ->whereKey($deploymentId)
            ->where('trigger_source', 'github_webhook')
            ->where('commit_sha', strtolower($sourceSha))
            ->firstOrFail();
        if (! in_array($deployment->status, ['queued', 'running'], true)) {
            return response()->json(['received' => true, 'already_finished' => true]);
        }

        $deployment->forceFill([
            'status' => match ($status) {
                'success' => 'succeeded',
                'cancelled' => 'cancelled',
                default => 'failed',
            },
            'failure_summary' => $status === 'failure' ? 'GitHub Actions deployment step failed.' : null,
            'finished_at' => now(),
        ])->save();

        $deployment->load(['project.owner', 'repository']);
        $project = $deployment->project;
        $success = $status === 'success';
        $details = [
            'Project' => $project?->name ?? 'Project',
            'Repository' => $deployment->repository?->full_name ?? 'Unknown',
            'Commit' => substr($deployment->commit_sha, 0, 12),
            'Result' => $success ? 'Succeeded' : ($status === 'cancelled' ? 'Cancelled' : 'Failed'),
        ];
        $mailer->admins('Deployment '.($success ? 'succeeded' : 'failed').' · TechwareTz Control Center (TCC)', [
            'eyebrow' => 'Deployment update',
            'title' => $success ? 'Deployment completed' : 'Deployment needs attention',
            'intro' => $success
                ? 'A registered project was deployed successfully.'
                : 'A registered project deployment did not complete successfully.',
            'details' => $details,
            'button_label' => 'Open projects',
            'button_url' => route('admin.projects'),
        ], $success ? 'notify_deployment_succeeded' : 'notify_deployment_failed');

        if ($project?->owner) {
            $mailer->to(
                $project->owner->email,
                'Deployment '.($success ? 'completed' : 'failed').' · TechwareTz Control Center (TCC)',
                [
                    'eyebrow' => 'Deployment update',
                    'title' => $success ? 'Your deployment is live' : 'Your deployment needs attention',
                    'intro' => $success
                        ? 'The latest version of your project was deployed successfully.'
                        : 'The latest deployment did not complete. Please check the deployment status in your workspace.',
                    'details' => $details,
                    'button_label' => 'Open workspace',
                    'button_url' => route('modules.show', 'github-deploy'),
                ]
            );
        }

        AuditEvent::create([
            'project_id' => $deployment->project_id,
            'action' => 'deployment.'.$status,
            'subject_type' => Deployment::class,
            'subject_id' => (string) $deployment->id,
            'details' => ['commit_sha' => $deployment->commit_sha],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['received' => true]);
    }
}
