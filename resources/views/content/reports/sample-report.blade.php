@extends('layouts/layoutMaster')

@section('title', __('Sample report'))

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
  @vite('resources/assets/js/workspace-datatables.js')
@endsection

@section('content')
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('Sample report') }}</h5>
      <small class="text-body-secondary">{{ __('Samples by visit (scoped to your access)') }}</small>
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
            <th>{{ __('Product') }}</th>
            <th class="text-end">{{ __('Qty') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($samples as $row)
            <tr>
              <td>{{ $row->visit?->visited_at?->format('Y-m-d') ?? '—' }}</td>
              <td>{{ $row->visit?->customer?->name ?? '—' }}</td>
              <td>{{ $row->visit?->user?->name ?? '—' }}</td>
              <td>{{ $row->product?->name ?? '—' }}</td>
              <td class="text-end">{{ $row->quantity }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $samples->links() }}
    </div>
  </div>
@endsection
