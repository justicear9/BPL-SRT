@extends('layouts/layoutMaster')

@section('title', __('Collections report'))

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
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('Collections report') }}</h5>
      <small class="text-body-secondary">{{ __('Collections by visit (scoped to your access)') }}</small>
    </div>
    <div class="card-body border-bottom">
      @include('content.reports.partials.report-filters', ['salesReps' => $salesReps])
    </div>
    <div class="card-datatable table-responsive text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('Visit date') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Rep') }}</th>
            <th>{{ __('Payment method') }}</th>
            <th class="text-end">{{ __('Amount') }}</th>
            <th>{{ __('Notes') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($collections as $row)
            <tr>
              <td>{{ $row->visit?->visited_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td>{{ $row->visit?->customer?->name ?? '—' }}</td>
              <td>{{ $row->visit?->user?->name ?? '—' }}</td>
              <td>{{ $row->paymentMethodLabel() ?: '—' }}</td>
              <td class="text-end">{{ \App\Models\Setting::currencySymbol() }}{{ number_format((float) $row->amount, 2) }}</td>
              <td class="text-truncate" style="max-width: 240px;">{{ $row->notes ?: '—' }}</td>
              <td class="text-end">
                @if ($row->visit_id)
                  <button type="button" class="btn btn-sm btn-text-primary" data-open-visit-modal
                    data-modal-url="{{ route('workspace.visits.modal', ['visit' => $row->visit_id]) }}">{{ __('Visit') }}</button>
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
      {{ $collections->links() }}
    </div>
  </div>

  @include('content.workspace.partials.visit-edit-modal')
@endsection
