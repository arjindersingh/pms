<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.ads.edit', ['ads' => AdSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'publisher_id' => ['nullable', 'regex:/^ca-pub-\d{16}$/'],
            'homepage_top_slot' => ['nullable', 'regex:/^\d{5,20}$/'],
            'homepage_middle_slot' => ['nullable', 'regex:/^\d{5,20}$/'],
            'homepage_bottom_slot' => ['nullable', 'regex:/^\d{5,20}$/'],
        ], [
            'publisher_id.regex' => 'Enter a valid publisher ID such as ca-pub-1234567890123456.',
            '*.regex' => 'Enter the numeric ad slot ID from the AdSense ad unit code.',
        ]);

        foreach ([
            'enabled', 'auto_ads_enabled', 'show_placeholders',
            'homepage_top_enabled', 'homepage_middle_enabled', 'homepage_bottom_enabled',
        ] as $boolean) {
            $validated[$boolean] = $request->boolean($boolean);
        }

        AdSetting::current()->update($validated + ['updated_by' => $request->user()->id]);

        return back()->with('status', 'Google Ads settings saved.');
    }
}
