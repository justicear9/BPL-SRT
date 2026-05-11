<?php

namespace App\Http\Requests\Workspace;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'unit_of_measure' => ['nullable', 'string', 'max:32'],
            'item_category_code' => ['nullable', 'string', 'max:32'],
            'default_unit_price' => ['required', 'numeric', 'min:0'],
            'can_be_sampled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_be_sampled' => $this->boolean('can_be_sampled'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
