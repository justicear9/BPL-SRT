<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreUserRequest;
use App\Http\Requests\Workspace\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query()->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('username', 'like', $term));
        }

        if ($request->filled('role') && $request->user()->canManageAllVisits()) {
            $query->where('role', $request->string('role'));
        }

        $users = $query->paginate(25)->withQueryString();

        $listStats = [
            ['label' => __('Total users'), 'value' => (string) User::query()->count(), 'caption' => __('All roles'), 'icon' => 'tabler-users', 'variant' => 'primary'],
            ['label' => __('Sales reps'), 'value' => (string) User::query()->where('role', UserRole::SalesRep)->count(), 'caption' => __('Field team'), 'icon' => 'tabler-user', 'variant' => 'success'],
            ['label' => __('Managers'), 'value' => (string) User::query()->where('role', UserRole::Manager)->count(), 'caption' => __('Catalog + visits'), 'icon' => 'tabler-crown', 'variant' => 'warning'],
            ['label' => __('Admins'), 'value' => (string) User::query()->where('role', UserRole::Admin)->count(), 'caption' => __('Full access'), 'icon' => 'tabler-shield', 'variant' => 'danger'],
        ];

        return view('content.workspace.users.index', compact('users', 'listStats'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('content.workspace.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);
        $data['role'] = UserRole::from($data['role']);

        User::query()->create($data);

        return redirect()->route('workspace.users.index')
            ->with('status', __('User created.'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('content.workspace.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);

        if (empty($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (array_key_exists('role', $data)) {
            $data['role'] = UserRole::from($data['role']);
        }

        $user->update($data);

        return redirect()->route('workspace.users.index')
            ->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('workspace.users.index')
            ->with('status', __('User removed.'));
    }
}
