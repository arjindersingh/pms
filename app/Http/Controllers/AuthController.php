<?php

namespace App\Http\Controllers;

use App\Enums\UserCategory;
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

    public function store(Request $request, bool $administratorPortal = false): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect or the account is inactive.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user()->load('userType');

        $category = $user->userType?->category;
        $allowedPortal = $administratorPortal
            ? $category === UserCategory::Administrator
            : in_array($category, [UserCategory::Recruiter, UserCategory::Talent], true);

        if (! $user->userType?->is_active || ! $allowedPortal) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'This account cannot sign in through this portal.'])->onlyInput('email');
        }

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function storeAdministrator(Request $request): RedirectResponse
    {
        return $this->store($request, true);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $wasAdministrator = $request->user()?->userType?->category === UserCategory::Administrator;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasAdministrator ? 'administrator.login' : 'login');
    }
}
