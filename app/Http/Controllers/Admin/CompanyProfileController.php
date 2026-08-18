<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.company.edit', ['company' => CompanyProfile::current(), 'countries' => Country::available()->get(), 'states' => State::available()->with('country')->whereHas('country', fn ($query) => $query->available())->get()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'], 'legal_name' => ['nullable', 'string', 'max:180'], 'tagline' => ['nullable', 'string', 'max:180'], 'short_description' => ['nullable', 'string', 'max:500'], 'about' => ['nullable', 'string', 'max:10000'], 'mission' => ['nullable', 'string', 'max:3000'], 'vision' => ['nullable', 'string', 'max:3000'], 'values' => ['nullable', 'string', 'max:3000'],
            'registration_number' => ['nullable', 'string', 'max:100'], 'tax_number' => ['nullable', 'string', 'max:100'], 'founded_on' => ['nullable', 'date', 'before_or_equal:today'], 'industry' => ['nullable', 'string', 'max:120'], 'company_size' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:180'], 'support_email' => ['nullable', 'email', 'max:180'], 'phone' => ['nullable', 'string', 'max:40'], 'whatsapp' => ['nullable', 'string', 'max:40'], 'website' => ['nullable', 'url', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:180'], 'address_line_2' => ['nullable', 'string', 'max:180'], 'city' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', Rule::exists('states', 'display_name')->where('is_active', true)->whereNull('deleted_at')], 'postal_code' => ['nullable', 'string', 'max:30'], 'country' => ['nullable', Rule::exists('countries', 'display_name')->where('is_active', true)->whereNull('deleted_at')],
            'social_links' => ['array'], 'social_links.*' => ['nullable', 'url', 'max:255'], 'meta_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:170'], 'meta_keywords' => ['nullable', 'string', 'max:500'],
            'promotion_heading' => ['nullable', 'string', 'max:180'], 'promotion_text' => ['nullable', 'string', 'max:1000'], 'promotion_cta_label' => ['nullable', 'string', 'max:80'], 'promotion_cta_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'], 'logo_dark' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'], 'favicon' => ['nullable', 'image', 'mimes:png,ico,jpg,jpeg,webp', 'max:512'], 'cover' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]);
        $company = CompanyProfile::current();
        unset($data['logo'],$data['logo_dark'],$data['favicon'],$data['cover']);
        foreach (['logo' => 'logo_path', 'logo_dark' => 'logo_dark_path', 'favicon' => 'favicon_path', 'cover' => 'cover_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                if ($company->{$column}) {
                    Storage::disk('public')->delete($company->{$column});
                }$data[$column] = $request->file($input)->store('company', 'public');
            }
        }
        $company->update($data + ['promotion_enabled' => $request->boolean('promotion_enabled'), 'updated_by' => $request->user()->id]);

        return back()->with('status', 'Company profile and portal branding saved.');
    }
}
