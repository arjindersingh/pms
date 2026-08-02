<?php

namespace App\Http\Controllers;

use App\Enums\UserCategory;
use App\Services\UserSessionTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(bool $administratorPortal = false): View
    {
        return view('auth.login', compact('administratorPortal'));
    }

    public function administrator(): View
    {
        return $this->create(true);
    }

    public function store(Request $request, UserSessionTracker $sessions, bool $administratorPortal = false): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect or the account is inactive.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user()->load(['userType', 'role']);

        $category = $user->userType?->category;
        $allowedPortal = $administratorPortal
            ? $category === UserCategory::Administrator
            : in_array($category, [UserCategory::Recruiter, UserCategory::Talent], true);

        if (! $user->userType?->is_active || ($user->role && ! $user->role->is_active) || ! $allowedPortal) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'This account cannot sign in through this portal.'])->onlyInput('email');
        }

        $sessions->start($request, $user, $request->boolean('remember'));

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function storeAdministrator(Request $request, UserSessionTracker $sessions): RedirectResponse
    {
        return $this->store($request, $sessions, true);
    }

    public function destroy(Request $request, UserSessionTracker $sessions): RedirectResponse
    {
        $wasAdministrator = $request->user()?->userType?->category === UserCategory::Administrator;

        $sessions->close($request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route($wasAdministrator ? 'administrator.login' : 'login')
            // Ask supported browsers to remove every cache, cookie, and storage
            // entry owned by this origin. This includes HttpOnly cookies and
            // IndexedDB data that JavaScript cannot reliably remove itself.
            ->withHeaders([
                'Clear-Site-Data' => '"cache", "cookies", "storage"',
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
            ]);
    }
}
