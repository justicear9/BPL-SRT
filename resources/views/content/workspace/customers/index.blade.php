@extends('layouts/layoutMaster')

@section('title', __('Customers'))

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
  ])
@endsection

@section('page-script')
  @vite('resources/assets/js/workspace-datatables.js')
@endsection

@section('content')
  @include('content.workspace.partials.flash')
  @include('content.workspace.partials.list-stats')

  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div class="me-auto">
          <h5 class="card-title mb-1">{{ __('Customers') }}</h5>
          <p class="mb-0 text-body-secondary">{{ __('Scoped to your access.') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          @can('import', \App\Models\Customer::class)
            <a href="{{ route('workspace.customers.import') }}" class="btn btn-label-primary">{{ __('Import customers') }}</a>
          @endcan
          @can('create', \App\Models\Customer::class)
            <a href="{{ route('workspace.customers.create') }}" class="btn btn-primary">{{ __('Add customer') }}</a>
          @endcan
        </div>
      </div>

      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
      <form method="get" class="row pt-4 g-4 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label" for="filter-customers-q">{{ __('Search') }}</label>
          <input type="text" name="q" id="filter-customers-q" value="{{ request('q') }}" class="form-control"
            placeholder="{{ __('Name, city, or phone') }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label" for="filter-customers-type">{{ __('Type') }}</label>
          <select name="type" id="filter-customers-type" class="form-select">
            <option value="">{{ __('All types') }}</option>
            @foreach (\App\Enums\CustomerType::cases() as $typeOption)
              <option value="{{ $typeOption->value }}" @selected(request('type') === $typeOption->value)>{{ $typeOption->label() }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4 d-flex flex-wrap align-items-end gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
          <a href="{{ route('workspace.customers.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>

    <div class="card-datatable text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Assigned rep') }}</th>
            <th>{{ __('City') }}</th>
            <th>{{ __('Contact persons') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($customers as $row)
            <tr>
              <td>{{ $row->name }}</td>
              <td>{{ $row->type?->label() }}</td>
              <td>{{ $row->assignedUser?->name ?? '—' }}</td>
              <td>{{ $row->city ?? '—' }}</td>
              <td class="small">
                @forelse ($row->contacts as $contact)
                  <div>{{ $contact->listLabel() }}</div>
                @empty
                  —
                @endforelse
              </td>
              <td class="text-end text-nowrap">
                @can('update', $row)
                  <a href="{{ route('workspace.customers.edit', $row) }}" class="btn btn-sm btn-text-primary">{{ __('Edit') }}</a>
                @endcan
                @can('delete', $row)
                  <form action="{{ route('workspace.customers.destroy', $row) }}" method="post" class="d-inline"
                    onsubmit="return confirm({{ json_encode(__('Delete this customer?')) }});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-text-danger">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $customers->links() }}
    </div>
  </div>
@endsection
