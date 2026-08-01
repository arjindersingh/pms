<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function editProfile(): View
    {
        return view('account.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function settings(): View
    {
        return view('account.settings');
    }

    public function editPassword(): View
    {
        return view('account.password');
    }

    public function editErrorSettings(Request $request): View
    {
        $settings = (object) array_merge(
            config('error-display.defaults'),
            $request->user()->errorSetting?->only(array_keys(config('error-display.defaults'))) ?? [],
        );

        return view('account.error-settings', compact('settings'));
    }

    public function updateErrorSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'placement' => ['required', Rule::in(array_keys(config('error-display.placements')))],
            'font_family' => ['required', Rule::in(array_keys(config('error-display.fonts')))],
            'font_size' => ['required', 'integer', 'between:12,20'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'density' => ['required', Rule::in(array_keys(config('error-display.densities')))],
            'motion' => ['required', Rule::in(array_keys(config('error-display.motions')))],
            'auto_dismiss_seconds' => ['required', 'integer', 'between:0,30'],
            'show_icon' => ['boolean'], 'allow_dismiss' => ['boolean'], 'group_messages' => ['boolean'],
        ]);

        foreach (['show_icon', 'allow_dismiss', 'group_messages'] as $boolean) {
            $validated[$boolean] = $request->boolean($boolean);
        }

        $request->user()->errorSetting()->updateOrCreate([], $validated);

        return back()->with('status', 'Error display settings saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'Password changed successfully.');
    }
}
