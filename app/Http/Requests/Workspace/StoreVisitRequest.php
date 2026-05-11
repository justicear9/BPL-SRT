<?php

namespace App\Http\Requests\Workspace;

use App\Models\Visit;
use App\Models\VisitCollection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    use Concerns\ResolvesVisitContact;

    public function authorize(): bool
    {
        return Gate::allows('create', Visit::class);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()->canManageAllVisits()) {
            $this->merge([
                'user_id' => $this->user()->id,
                'visited_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $privileged = $this->user()->canManageAllVisits();

        return [
            'user_id' => [
                Rule::requiredIf($privileged),
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'visited_at' => [
                Rule::requiredIf($privileged),
                'nullable',
                'date',
            ],
            'comments' => ['nullable', 'string', 'max:65535'],
            'visit_latitude' => ['nullable', 'numeric'],
            'visit_longitude' => ['nullable', 'numeric'],
            'order_lines' => ['nullable', 'array'],
            'order_lines.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'order_lines.*.quantity' => ['nullable', 'integer', 'min:1'],
            'order_lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'samples' => ['nullable', 'array'],
            'samples.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'samples.*.quantity' => ['nullable', 'integer', 'min:1'],
            'collections' => ['nullable', 'array'],
            'collections.*.amount' => ['nullable', 'numeric', 'min:0'],
            'collections.*.payment_method' => ['nullable', 'string', Rule::in(array_keys(VisitCollection::paymentMethodOptions()))],
            'collections.*.notes' => ['nullable', 'string', 'max:500'],
            ...$this->visitContactRules(),
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            foreach ($this->input('collections', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $amount = $row['amount'] ?? null;
                $hasAmount = $amount !== null && $amount !== '';
                $method = trim((string) ($row['payment_method'] ?? ''));
                if ($hasAmount && $method === '') {
                    $validator->errors()->add(
                        "collections.$index.payment_method",
                        __('Choose a payment method when recording an amount.'),
                    );
                }
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizedOrderLines(): array
    {
        return collect($this->input('order_lines', []))
            ->filter(fn (array $row): bool => ! empty($row['product_id']) && ! empty($row['quantity']))
            ->values()
            ->map(fn (array $row): array => [
                'product_id' => (int) $row['product_id'],
                'quantity' => (int) $row['quantity'],
                'unit_price' => $row['unit_price'] ?? null,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizedSamples(): array
    {
        return collect($this->input('samples', []))
            ->filter(fn (array $row): bool => ! empty($row['product_id']) && ! empty($row['quantity']))
            ->values()
            ->map(fn (array $row): array => [
                'product_id' => (int) $row['product_id'],
                'quantity' => (int) $row['quantity'],
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizedCollections(): array
    {
        return collect($this->input('collections', []))
            ->filter(fn (array $row): bool => isset($row['amount']) && $row['amount'] !== '' && $row['amount'] !== null)
            ->values()
            ->map(fn (array $row): array => [
                'amount' => $row['amount'],
                'payment_method' => (($m = trim((string) ($row['payment_method'] ?? ''))) !== '' ? $m : null),
                'notes' => $row['notes'] ?? null,
            ])
            ->all();
    }

    /**
     * @return array{user_id: int, customer_id: int, visited_at: \Carbon\Carbon, comments: ?string, visit_latitude: ?float, visit_longitude: ?float}
     */
    public function visitAttributes(): array
    {
        $validated = $this->validated();

        $lat = $validated['visit_latitude'] ?? null;
        $lng = $validated['visit_longitude'] ?? null;

        return [
            'user_id' => (int) $validated['user_id'],
            'customer_id' => (int) $validated['customer_id'],
            'visited_at' => $this->user()->canManageAllVisits()
                ? \Carbon\Carbon::parse($validated['visited_at'])
                : now(),
            'comments' => $validated['comments'] ?? null,
            'visit_latitude' => ($lat !== null && $lat !== '') ? (float) $lat : null,
            'visit_longitude' => ($lng !== null && $lng !== '') ? (float) $lng : null,
        ];
    }
}
