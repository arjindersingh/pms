<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function states(Country $country): JsonResponse
    {
        abort_unless($country->is_active && ! $country->trashed(), 404);

        return response()->json($country->states()->available()->get(['id', 'display_name']));
    }

    public function districts(State $state): JsonResponse
    {
        abort_unless($state->is_active && ! $state->trashed() && $state->country()->available()->exists(), 404);

        return response()->json($state->districts()->available()->get(['id', 'display_name']));
    }
}
