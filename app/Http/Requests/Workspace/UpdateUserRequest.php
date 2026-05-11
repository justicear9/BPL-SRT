<?php

namespace App\Http\Requests\Workspace;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge(['username' => strtolower($this->string('username')->toString())]);
        }
    }

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $this->user()->can('update', $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $isSelf = (int) $user->id === (int) $this->user()->id;
        $managingOthers = $this->user()->canManageAllVisits() && ! $isSelf;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => [
                Rule::prohibitedIf($isSelf),
                Rule::requiredIf($managingOthers),
                Rule::enum(UserRole::class),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->filled('role')) {
                return;
            }

            /** @var User $user */
            $user = $this->route('user');
            $newRole = UserRole::from($this->string('role')->toString());
            if ($newRole === $user->role) {
                return;
            }

            if (! app(UserPolicy::class)->assignRole($this->user(), $newRole)) {
                $validator->errors()->add('role', __('You may not assign this role.'));
            }
        });
    }
}
