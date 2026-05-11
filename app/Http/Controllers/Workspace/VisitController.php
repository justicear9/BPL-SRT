<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreVisitRequest;
use App\Http\Requests\Workspace\UpdateVisitRequest;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use App\Services\CustomerShopLocation;
use App\Services\VisitNestedWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Visit::class);

        $query = Visit::query()
            ->with(['customer', 'user', 'contact', 'order.lines', 'samples', 'collections'])
            ->orderByDesc('visited_at');

        $actor = auth()->user();
        if ($actor instanceof User && ! $actor->canManageAllVisits()) {
            $query->where('user_id', $actor->id);
        }

        if ($request->filled('from')) {
            $query->whereDate('visited_at', '>=', $request->date('from')->toDateString());
        }

        if ($request->filled('to')) {
            $query->whereDate('visited_at', '<=', $request->date('to')->toDateString());
        }

        if ($request->filled('user_id') && $request->user()->canManageAllVisits()) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $visits = $query->paginate(30)->withQueryString();

        $base = Visit::query();
        if ($actor instanceof User && ! $actor->canManageAllVisits()) {
            $base->where('user_id', $actor->id);
        }

        $listStats = [
            ['label' => __('Total visits'), 'value' => (string) (clone $base)->count(), 'caption' => __('All time'), 'icon' => 'tabler-map-pin', 'variant' => 'primary'],
            ['label' => __('This month'), 'value' => (string) (clone $base)->where('visited_at', '>=', now()->startOfMonth())->count(), 'caption' => __('Current period'), 'icon' => 'tabler-calendar', 'variant' => 'success'],
            ['label' => __('With order'), 'value' => (string) (clone $base)->whereHas('order')->count(), 'caption' => __('Recorded orders'), 'icon' => 'tabler-receipt', 'variant' => 'warning'],
            ['label' => __('With samples'), 'value' => (string) (clone $base)->whereHas('samples')->count(), 'caption' => __('Sampling activity'), 'icon' => 'tabler-flask', 'variant' => 'info'],
        ];

        $salesReps = $request->user()->canManageAllVisits()
            ? User::assignableFieldTeam()->get()
            : collect();

        return view('content.workspace.visits.index', compact('visits', 'listStats', 'salesReps'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Visit::class);

        return view('content.workspace.visits.create', $this->visitFormDependencies($request, null));
    }

    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($request->validated('customer_id'));
        Gate::authorize('view', $customer);

        $attrs = array_merge($request->visitAttributes(), [
            'contact_id' => (int) $request->validated('contact_id'),
        ]);
        $visit = Visit::query()->create($attrs);

        VisitNestedWriter::sync(
            $visit,
            $request->normalizedOrderLines(),
            $request->normalizedSamples(),
            $request->normalizedCollections(),
        );

        $visit->refresh();
        CustomerShopLocation::syncFromVisit($visit);

        return redirect()->route('workspace.visits.index')
            ->with('status', __('Visit recorded.'));
    }

    public function edit(Request $request, Visit $visit): View
    {
        Gate::authorize('update', $visit);

        $visit->load(['order.lines', 'samples', 'collections']);

        return view('content.workspace.visits.edit', $this->visitFormDependencies($request, $visit));
    }

    public function update(UpdateVisitRequest $request, Visit $visit): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($request->validated('customer_id'));
        Gate::authorize('view', $customer);

        $visit->update(array_merge($request->visitAttributes($visit), [
            'contact_id' => (int) $request->validated('contact_id'),
        ]));

        VisitNestedWriter::sync(
            $visit->fresh(),
            $request->normalizedOrderLines(),
            $request->normalizedSamples(),
            $request->normalizedCollections(),
        );

        $visit->refresh();
        CustomerShopLocation::syncFromVisit($visit);

        return redirect()->route('workspace.visits.index')
            ->with('status', __('Visit updated.'));
    }

    public function modal(Request $request, Visit $visit): View
    {
        Gate::authorize('view', $visit);

        $visit->load([
            'customer',
            'user',
            'contact',
            'order.lines.product',
            'samples.product',
            'collections',
        ]);

        return view('content.workspace.visits.modal-readonly', compact('visit'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function visitFormDependencies(Request $request, ?Visit $visit): array
    {
        $isPrivileged = $request->user()->canManageAllVisits();

        $customersQuery = Customer::query()->with('contacts')->orderBy('name');
        if (! $isPrivileged) {
            $customersQuery->where('assigned_user_id', $request->user()->id);
        }

        $customers = $customersQuery->get();

        $assignableUsers = $isPrivileged
            ? User::query()->orderBy('name')->get()
            : collect();

        $orderProducts = Product::query()->active()->orderBy('name')->get(['id', 'name', 'default_unit_price']);
        $sampleProducts = Product::query()->active()->where('can_be_sampled', true)->orderBy('name')->get(['id', 'name']);

        return [
            'visit' => $visit,
            'isPrivileged' => $isPrivileged,
            'customers' => $customers,
            'customerContactsForJs' => $customers->mapWithKeys(fn (Customer $c): array => [
                (string) $c->id => $c->contacts->map(fn (Contact $contact): array => [
                    'id' => $contact->id,
                    'label' => $contact->listLabel(),
                ])->values()->all(),
            ])->all(),
            'assignableUsers' => $assignableUsers,
            'orderProducts' => $orderProducts,
            'sampleProducts' => $sampleProducts,
            'orderLinesInitial' => $this->orderLinesInitial($visit),
            'samplesInitial' => $this->samplesInitial($visit),
            'collectionsInitial' => $this->collectionsInitial($visit),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function orderLinesInitial(?Visit $visit): array
    {
        if (! $visit?->relationLoaded('order') && $visit) {
            $visit->load('order.lines');
        }

        $lines = $visit?->order?->lines;
        if ($lines === null || $lines->isEmpty()) {
            return [['product_id' => '', 'quantity' => '1', 'unit_price' => '']];
        }

        return $lines->map(fn ($line): array => [
            'product_id' => (string) $line->product_id,
            'quantity' => (string) $line->quantity,
            'unit_price' => (string) $line->unit_price,
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function samplesInitial(?Visit $visit): array
    {
        if ($visit && ! $visit->relationLoaded('samples')) {
            $visit->load('samples');
        }

        $samples = $visit?->samples;
        if ($samples === null || $samples->isEmpty()) {
            return [['product_id' => '', 'quantity' => '1']];
        }

        return $samples->map(fn ($sample): array => [
            'product_id' => (string) $sample->product_id,
            'quantity' => (string) $sample->quantity,
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectionsInitial(?Visit $visit): array
    {
        if ($visit && ! $visit->relationLoaded('collections')) {
            $visit->load('collections');
        }

        $collections = $visit?->collections;
        if ($collections === null || $collections->isEmpty()) {
            return [['amount' => '', 'payment_method' => '', 'notes' => '']];
        }

        return $collections->map(fn ($row): array => [
            'amount' => (string) $row->amount,
            'payment_method' => (string) ($row->payment_method ?? ''),
            'notes' => (string) ($row->notes ?? ''),
        ])->values()->all();
    }
}
