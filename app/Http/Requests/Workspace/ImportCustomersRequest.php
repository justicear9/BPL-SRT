<?php

namespace App\Http\Requests\Workspace;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImportCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('import', Customer::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'import' => [
                'required',
                File::types(['csv', 'txt', 'xlsx'])->max(12_288),
            ],
        ];
    }
}
