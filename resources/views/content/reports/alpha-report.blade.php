@extends('layouts/layoutMaster')

@section('title', __('Alpha report'))

@section('content')
  <div class="row g-6">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-1">{{ __('Alpha report') }}</h5>
          <small class="text-body-secondary">{{ __('Placeholder workspace for future alpha metrics') }}</small>
        </div>
        <div class="card-body">
          <p class="text-body-secondary mb-0">
            {{ __('Alpha reporting is not configured yet. Use this section once your alpha metrics and data source are defined.') }}
          </p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card bg-label-primary h-100">
        <div class="card-body">
          <h6 class="mb-2 text-primary">{{ __('Next steps') }}</h6>
          <ul class="ps-3 mb-0 small text-body">
            <li>{{ __('Define the alpha metric and grain (daily / weekly).') }}</li>
            <li>{{ __('Connect the backing query or warehouse table.') }}</li>
            <li>{{ __('Add filters consistent with other reports (dates, rep).') }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection
