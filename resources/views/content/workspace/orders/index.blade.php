@extends('layouts/layoutMaster')

@section('title', __('Orders'))

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
  @vite([
    'resources/assets/js/workspace-datatables.js',
    'resources/assets/js/workspace-visit-modal.js',
  ])
@endsection

@section('content')
  @include('content.workspace.partials.flash')
  @include('content.workspace.partials.list-stats')

  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div class="me-auto">
          <h5 class="card-title mb-1">{{ __('Visit orders') }}</h5>
          <p class="mb-0 text-body-secondary">{{ __('Read-only list with filters.') }}</p>
        </div>
      </div>

      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
      <form method="get" class="row pt-4 g-4 align-items-end">
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label" for="filter-orders-q">{{ __('Customer') }}</label>
          <input type="text" name="q" id="filter-orders-q" value="{{ request('q') }}" class="form-control"
            placeholder="{{ __('Search name') }}">
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label" for="filter-orders-from">{{ __('From') }}</label>
          <input type="date" name="from" id="filter-orders-from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label" for="filter-orders-to">{{ __('To') }}</label>
          <input type="date" name="to" id="filter-orders-to" value="{{ request('to') }}" class="form-control">
        </div>
        @if ($salesReps->isNotEmpty())
          <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label" for="filter-orders-rep">{{ __('Sales rep') }}</label>
            <select name="user_id" id="filter-orders-rep" class="form-select">
              <option value="">{{ __('All reps') }}</option>
              @foreach ($salesReps as $rep)
                <option value="{{ $rep->id }}" @selected((string) request('user_id') === (string) $rep->id)>{{ $rep->name }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div class="col-12 col-lg-auto d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
          <a href="{{ route('workspace.orders.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>

    <div class="card-datatable text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>#</th>
            <th>{{ __('Visit date') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Rep') }}</th>
            <th class="text-end">{{ __('Total') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($orders as $order)
            <tr>
              <td>{{ $order->id }}</td>
              <td>{{ $order->visit?->visited_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td>{{ $order->visit?->customer?->name ?? '—' }}</td>
              <td>{{ $order->visit?->user?->name ?? '—' }}</td>
              <td class="text-end">{{ \App\Models\Setting::currencySymbol() }}{{ number_format($order->lineTotal(), 2) }}</td>
              <td class="text-end">
                @if ($order->visit_id)
                  <button type="button" class="btn btn-sm btn-text-primary" data-open-visit-modal
                    data-modal-url="{{ route('workspace.visits.modal', ['visit' => $order->visit_id]) }}">{{ __('Open visit') }}</button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $orders->links() }}
    </div>
  </div>

  @include('content.workspace.partials.visit-edit-modal')
@endsection
