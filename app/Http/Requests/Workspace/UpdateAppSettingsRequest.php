<?php

namespace App\Http\Requests\Workspace;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'currency_code' => ['required', 'string', 'size:3', Rule::in(array_keys(Setting::currencyCodeOptions()))],
            'currency_symbol' => ['required', 'string', 'max:8'],
        ];
    }

    public function saveSettings(): void
    {
        $validated = $this->validated();
        Setting::setValue(Setting::KEY_CURRENCY_CODE, strtoupper($validated['currency_code']));
        Setting::setValue(Setting::KEY_CURRENCY_SYMBOL, $validated['currency_symbol']);
    }
}
