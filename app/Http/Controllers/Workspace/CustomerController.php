<?php

namespace App\Http\Controllers\Workspace;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\ImportCustomersRequest;
use App\Http\Requests\Workspace\StoreCustomerRequest;
use App\Http\Requests\Workspace\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\Imports\CustomerSpreadsheetImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /**
     * @return Builder<Customer>
     */
    protected function customersBaseQuery(): Builder
    {
        $query = Customer::query()->with([
            'assignedUser',
            'contacts' => fn ($q) => $q->orderBy('name'),
        ]);

        $user = auth()->user();
        if ($user instanceof User && ! $user->canManageAllVisits()) {
            $query->where('assigned_user_id', $user->id);
        }

        return $query;
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Customer::class);

        $query = $this->customersBaseQuery()->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('phone', 'like', $term));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        $customers = $query->paginate(25)->withQueryString();

        $base = $this->customersBaseQuery();
        $variants = ['primary', 'success', 'warning', 'info'];
        $listStats = [];
        foreach (CustomerType::cases() as $i => $type) {
            $listStats[] = [
                'label' => $type->label(),
                'value' => (string) (clone $base)->where('type', $type->value)->count(),
                'caption' => __('In your scope'),
                'icon' => 'tabler-building-store',
                'variant' => $variants[$i % count($variants)],
            ];
        }

        return view('content.workspace.customers.index', compact('customers', 'listStats'));
    }

    public function create(): View
    {
        Gate::authorize('create', Customer::class);

        $salesReps = User::assignableFieldTeam()->get();

        return view('content.workspace.customers.create', compact('salesReps'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! $request->user()->canManageAllVisits()) {
            unset($data['assigned_user_id']);
        }

        Customer::query()->create($data);

        return redirect()->route('workspace.customers.index')
            ->with('status', __('Customer created.'));
    }

    public function edit(Customer $customer): View
    {
        Gate::authorize('update', $customer);

        $customer->load(['contacts' => fn ($q) => $q->orderBy('name')]);

        $salesReps = User::assignableFieldTeam()->get();

        return view('content.workspace.customers.edit', compact('customer', 'salesReps'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();

        if (! $request->user()->canManageAllVisits()) {
            unset($data['assigned_user_id']);
        }

        $customer->update($data);

        return redirect()->route('workspace.customers.index')
            ->with('status', __('Customer updated.'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('workspace.customers.index')
            ->with('status', __('Customer deleted.'));
    }

    public function importForm(): View
    {
        Gate::authorize('import', Customer::class);

        return view('content.workspace.customers.import');
    }

    public function importStore(ImportCustomersRequest $request, CustomerSpreadsheetImporter $importer): RedirectResponse
    {
        $uploaded = $request->file('import');
        $source = $uploaded->getPathname();
        if ($source === '' || ! is_readable($source)) {
            return redirect()->route('workspace.customers.import')
                ->withErrors(['import' => __('Could not read the uploaded file.')]);
        }

        $extension = strtolower($uploaded->getClientOriginalExtension() ?: 'csv');
        if ($extension === 'txt') {
            $extension = 'csv';
        }

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            return redirect()->route('workspace.customers.import')
                ->withErrors(['import' => __('Unsupported file type.')]);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'custimp').'.'.$extension;
        copy($source, $tempPath);

        try {
            $result = $importer->importFromPath($tempPath);
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }

        $errors = $result['errors'];
        $maxFlash = 40;
        if (count($errors) > $maxFlash) {
            $extra = count($errors) - $maxFlash;
            $errors = array_slice($errors, 0, $maxFlash);
            $errors[] = ['line' => 0, 'message' => __(':count more issues not shown.', ['count' => $extra])];
        }

        return redirect()->route('workspace.customers.import')
            ->with('import_created', $result['created'])
            ->with('import_users_created', $result['users_created'] ?? 0)
            ->with('import_errors', $errors);
    }

    public function importTemplate(): StreamedResponse
    {
        Gate::authorize('import', Customer::class);

        $filename = 'customers-import-template.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");

            $header = ['name', 'type', 'phone', 'city', 'region', 'address_line', 'shop_latitude', 'shop_longitude', 'assigned_user_email'];
            fputcsv($out, $header);
            fputcsv($out, [
                __('Example Pharmacy'),
                'pharmacy',
                '+233 20 000 0000',
                'Accra',
                '',
                '',
                '',
                '',
                'rep@example.com',
            ]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
