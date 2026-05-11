@extends('layouts/layoutMaster')

@section('title', __('Visit report'))

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
      <h5 class="card-title mb-1">{{ __('Visit report') }}</h5>
      <small class="text-body-secondary">{{ __('Recent visits (scoped to your access)') }}</small>
    </div>
    <div class="card-body border-bottom">
      @include('content.reports.partials.report-filters', ['salesReps' => $salesReps])
    </div>
    <div class="card-datatable table-responsive text-nowrap">
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
                <button type="button" class="btn btn-sm btn-text-primary" data-open-visit-modal
                  data-modal-url="{{ route('workspace.visits.modal', $visit) }}">{{ __('View') }}</button>
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

  @include('content.workspace.partials.visit-edit-modal')
@endsection
