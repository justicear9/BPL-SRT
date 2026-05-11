@extends('layouts/layoutMaster')

@section('title', __('Order report'))

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
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

  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('Order report') }}</h5>
      <small class="text-body-secondary">{{ __('Visit orders (scoped to your access)') }}</small>
    </div>
    <div class="card-body border-bottom">
      @include('content.reports.partials.report-filters', ['salesReps' => $salesReps])
    </div>
    <div class="card-datatable table-responsive text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('ID') }}</th>
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
                    data-modal-url="{{ route('workspace.visits.modal', ['visit' => $order->visit_id]) }}">{{ __('Visit') }}</button>
                @else
                  —
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
