<?php

namespace App\Http\Requests\Workspace;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', Rule::enum(CustomerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'shop_latitude' => ['nullable', 'numeric'],
            'shop_longitude' => ['nullable', 'numeric'],
        ];

        if ($this->user()->canManageAllVisits()) {
            $rules['assigned_user_id'] = [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role', [
                    UserRole::SalesRep->value,
                    UserRole::Manager->value,
                ])),
            ];
        }

        return $rules;
    }
}
