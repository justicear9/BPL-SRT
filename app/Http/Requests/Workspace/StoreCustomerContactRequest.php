<?php

namespace App\Http\Requests\Workspace;

use App\Rules\LocalTenDigitPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer !== null && Gate::allows('view', $customer);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => LocalTenDigitPhone::normalize($this->input('phone')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new LocalTenDigitPhone()],
            'position' => ['required', 'string', 'max:255'],
        ];
    }
}
