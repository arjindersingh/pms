<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSessionHistory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSessionTracker
{
    public function __construct(private readonly UserAgentParser $agents) {}

    public function start(Request $request, User $user, bool $remembered = false): UserSessionHistory
    {
        $now = now();
        $details = $this->agents->parse($request->userAgent());

        $history = UserSessionHistory::query()->firstOrCreate(
            ['session_hash' => $this->hash($request)],
            $details + [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'locale' => $request->getPreferredLanguage(),
                'login_method' => 'password',
                'remembered' => $remembered,
                'logged_in_at' => $now,
                'last_seen_at' => $now,
                'referrer_host' => parse_url((string) $request->headers->get('referer'), PHP_URL_HOST),
            ],
        );

        $request->session()->put('_tracked_session_history_id', $history->id);

        return $history;
    }

    public function record(Request $request, Response $response): void
    {
        if (! $request->user() || ! $request->hasSession()) return;

        $session = $this->find($request)
            ?? $this->start($request, $request->user(), auth()->viaRemember());
        $now = now();

        $session->update([
            'last_seen_at' => $now,
            'duration_seconds' => $session->logged_in_at->diffInSeconds($now),
            'request_count' => $session->request_count + 1,
            'last_route' => $request->route()?->getName(),
            'last_path' => '/'.ltrim($request->path(), '/'),
        ]);

        $session->activities()->create([
            'method' => $request->method(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'response_status' => $response->getStatusCode(),
            'occurred_at' => $now,
        ]);
    }

    public function close(Request $request, string $reason = 'logout'): void
    {
        if (! $request->hasSession()) return;

        $session = $this->find($request);
        if (! $session || $session->logged_out_at) return;

        $now = now();
        $session->update([
            'last_seen_at' => $now,
            'logged_out_at' => $now,
            'duration_seconds' => $session->logged_in_at->diffInSeconds($now),
            'ended_reason' => $reason,
        ]);
    }

    private function hash(Request $request): string
    {
        return hash('sha256', $request->session()->getId());
    }

    private function find(Request $request): ?UserSessionHistory
    {
        $historyId = $request->session()->get('_tracked_session_history_id');

        return ($historyId ? UserSessionHistory::query()->find($historyId) : null)
            ?? UserSessionHistory::query()->where('session_hash', $this->hash($request))->first();
    }
}
