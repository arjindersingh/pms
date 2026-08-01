<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSessionHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = UserSessionHistory::query()->with(['user.userType']);
        $this->filters($query, $request);

        $activeCutoff = now()->subMinutes(config('session.lifetime'));
        $summaryQuery = UserSessionHistory::query();

        return view('admin.sessions.index', [
            'sessions' => $query->latest('logged_in_at')->paginate(20)->withQueryString(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'browsers' => UserSessionHistory::query()->whereNotNull('browser')->distinct()->orderBy('browser')->pluck('browser'),
            'systems' => UserSessionHistory::query()->whereNotNull('operating_system')->distinct()->orderBy('operating_system')->pluck('operating_system'),
            'summary' => [
                'total' => (clone $summaryQuery)->count(),
                'active' => (clone $summaryQuery)->whereNull('logged_out_at')->where('last_seen_at', '>=', $activeCutoff)->count(),
                'users' => (clone $summaryQuery)->distinct()->count('user_id'),
                'average_duration' => (int) ((clone $summaryQuery)->avg('duration_seconds') ?? 0),
                'today' => (clone $summaryQuery)->whereDate('logged_in_at', today())->count(),
            ],
            'deviceBreakdown' => UserSessionHistory::query()->selectRaw('device_type, count(*) as total')->groupBy('device_type')->pluck('total', 'device_type'),
        ]);
    }

    public function show(UserSessionHistory $session): View
    {
        return view('admin.sessions.show', [
            'session' => $session->load(['user.userType']),
            'activities' => $session->activities()->paginate(30),
        ]);
    }

    private function filters(Builder $query, Request $request): void
    {
        $query->when($request->filled('user'), fn (Builder $q) => $q->where('user_id', $request->integer('user')));
        $query->when($request->filled('search'), fn (Builder $q) => $q->where(fn (Builder $inner) => $inner
            ->where('ip_address', 'like', '%'.$request->string('search').'%')
            ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%'))));
        $query->when($request->filled('browser'), fn (Builder $q) => $q->where('browser', $request->string('browser')));
        $query->when($request->filled('os'), fn (Builder $q) => $q->where('operating_system', $request->string('os')));
        $query->when($request->filled('device'), fn (Builder $q) => $q->where('device_type', $request->string('device')));
        $query->when($request->filled('from'), fn (Builder $q) => $q->whereDate('logged_in_at', '>=', $request->date('from')));
        $query->when($request->filled('to'), fn (Builder $q) => $q->whereDate('logged_in_at', '<=', $request->date('to')));
        $query->when($request->filled('status'), function (Builder $q) use ($request): void {
            $cutoff = now()->subMinutes(config('session.lifetime'));
            match ($request->string('status')->value()) {
                'active' => $q->whereNull('logged_out_at')->where('last_seen_at', '>=', $cutoff),
                'logged_out' => $q->whereNotNull('logged_out_at'),
                'expired' => $q->whereNull('logged_out_at')->where('last_seen_at', '<', $cutoff),
                default => null,
            };
        });
    }
}
