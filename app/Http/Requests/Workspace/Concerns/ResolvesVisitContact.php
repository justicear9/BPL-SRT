<?php

namespace App\Http\Requests\Workspace\Concerns;

use Illuminate\Validation\Rule;

trait ResolvesVisitContact
{
    /**
     * @return array<string, mixed>
     */
    protected function visitContactRules(): array
    {
        return [
            'contact_id' => [
                'required',
                'integer',
                Rule::exists('contacts', 'id')->where(fn ($q) => $q->where(
                    'customer_id',
                    (int) $this->input('customer_id'),
                )),
            ],
        ];
    }
}
