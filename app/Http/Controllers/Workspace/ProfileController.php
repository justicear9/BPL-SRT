<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\UpdateProfileRequest;
use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        Gate::authorize('update', $user);

        $visitStatLabel = $user->canManageAllVisits()
            ? __('Workspace visits')
            : __('My visits');

        $customerStatLabel = $user->canManageAllVisits()
            ? __('Customers')
            : __('Assigned customers');

        $visitCount = $user->canManageAllVisits()
            ? Visit::query()->count()
            : $user->visits()->count();

        $customerCount = $user->canManageAllVisits()
            ? Customer::query()->count()
            : $user->assignedCustomers()->count();

        return view('content.workspace.profile.edit', compact(
            'user',
            'visitStatLabel',
            'customerStatLabel',
            'visitCount',
            'customerCount',
        ));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        Gate::authorize('update', $user);

        $data = $request->validated();
        unset($data['password_confirmation']);

        if (empty($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('workspace.profile.edit')
            ->with('status', __('Profile saved.'));
    }
}
