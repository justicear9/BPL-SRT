<?php

namespace App\Http\Requests\Workspace;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge(['username' => strtolower($this->string('username')->toString())]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $role = UserRole::from($this->string('role')->toString());
            if (! app(UserPolicy::class)->assignRole($this->user(), $role)) {
                $validator->errors()->add('role', __('You may not assign this role.'));
            }
        });
    }
}
