@extends('layouts/layoutMaster')

@section('title', __('Visits'))

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
          <h5 class="card-title mb-1">{{ __('Visits') }}</h5>
          <p class="mb-0 text-body-secondary">{{ __('Create and edit visits here with orders, samples, and collections.') }}</p>
        </div>
        @can('create', \App\Models\Visit::class)
          <a href="{{ route('workspace.visits.create') }}" class="btn btn-primary">{{ __('New visit') }}</a>
        @endcan
      </div>

      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
      <form method="get" class="row pt-4 g-4 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label" for="filter-visits-from">{{ __('From') }}</label>
          <input type="date" name="from" id="filter-visits-from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label" for="filter-visits-to">{{ __('To') }}</label>
          <input type="date" name="to" id="filter-visits-to" value="{{ request('to') }}" class="form-control">
        </div>
        @if ($salesReps->isNotEmpty())
          <div class="col-12 col-md-3">
            <label class="form-label" for="filter-visits-rep">{{ __('Sales rep') }}</label>
            <select name="user_id" id="filter-visits-rep" class="form-select">
              <option value="">{{ __('All reps') }}</option>
              @foreach ($salesReps as $rep)
                <option value="{{ $rep->id }}" @selected((string) request('user_id') === (string) $rep->id)>{{ $rep->name }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div class="col-12 col-md-3 d-flex flex-wrap align-items-end gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
          <a href="{{ route('workspace.visits.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>

    <div class="card-datatable text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Contact') }}</th>
            <th>{{ __('Rep') }}</th>
            <th>{{ __('Order total') }}</th>
            <th>{{ __('Samples') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($visits as $visit)
            <tr>
              <td>{{ $visit->visited_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $visit->customer?->name ?? '—' }}</td>
              <td>{{ $visit->contact?->listLabel() ?? '—' }}</td>
              <td>{{ $visit->user?->name ?? '—' }}</td>
              <td>{{ \App\Models\Setting::currencySymbol() }}{{ number_format($visit->orderLineTotal(), 2) }}</td>
              <td>{{ $visit->samples->sum('quantity') }}</td>
              <td class="text-end">
                @can('update', $visit)
                  <a href="{{ route('workspace.visits.edit', $visit) }}" class="btn btn-sm btn-text-primary">{{ __('Full editor') }}</a>
                @endcan
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $visits->links() }}
    </div>
  </div>
@endsection
