<?php

namespace App\Http\Controllers;

use App\Enums\UserCategory;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function recruiter(): View
    {
        return $this->create(UserCategory::Recruiter);
    }

    public function talent(): View
    {
        return $this->create(UserCategory::Talent);
    }

    public function storeRecruiter(Request $request): RedirectResponse
    {
        return $this->store($request, UserCategory::Recruiter);
    }

    public function storeTalent(Request $request): RedirectResponse
    {
        return $this->store($request, UserCategory::Talent);
    }

    private function create(UserCategory $category): View
    {
        return view('auth.register', compact('category'));
    }

    private function store(Request $request, UserCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ]);

        $preferredSubtype = match ($category) {
            UserCategory::Recruiter => 'corporate-recruiter',
            UserCategory::Talent => 'graduate',
            default => throw new \LogicException('Public registration is not available for this category.'),
        };

        $type = UserType::query()->where('slug', $preferredSubtype)->where('is_active', true)->first()
            ?? UserType::query()->where('category', $category)->whereNull('parent_id')->where('is_active', true)->firstOrFail();

        $user = DB::transaction(fn () => User::query()->create([
            'user_type_id' => $type->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->load('userType')->dashboardRoute());
    }
}
