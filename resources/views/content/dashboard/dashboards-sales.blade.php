@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sales overview')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
    'resources/assets/vendor/libs/swiper/swiper.scss',
  ])
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/cards-advance.scss')
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/apex-charts/apexcharts.js',
    'resources/assets/vendor/libs/swiper/swiper.js',
  ])
@endsection

@section('page-script')
  <script>
    window.__SALES_DASHBOARD__ = @json($metrics);
    window.__SALES_DASHBOARD_IS_ADMIN__ = @json($isAdmin);
    window.__SALES_DASHBOARD_WEEK_MAP__ = @json($weekVisitMap);
  </script>
  @vite('resources/assets/js/dashboards-sales.js')
@endsection

@section('content')
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Visits ({{ $metrics['period_days'] }} days)</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ number_format($metrics['total_visits']) }}</h4>
              </div>
              <small class="mb-0">Logged field visits</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-map-pin icon-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">With sale / collection</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ number_format($metrics['visits_with_sale_or_collection']) }}</h4>
              </div>
              <small class="mb-0">Visits with orders or payments</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="icon-base ti tabler-shopping-cart icon-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Sample units</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ number_format($metrics['total_sample_units']) }}</h4>
              </div>
              <small class="mb-0">Total samples given</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="icon-base ti tabler-gift icon-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Order value</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $metrics['currency_symbol'] }}{{ number_format($metrics['order_value_total'], 0) }}</h4>
              </div>
              <small class="mb-0">Collections: {{ $metrics['currency_symbol'] }}{{ number_format($metrics['collections_total'], 0) }}</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if ($weekVisitMap !== null)
    <div class="row g-6 mb-6">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="mb-1">{{ __('Visit locations (this week)') }}</h5>
            <p class="card-subtitle text-body-secondary mb-0">
              {{ __('Monday–Sunday') }} · {{ $weekVisitMap['week_label'] }}
              — {{ number_format($weekVisitMap['visit_count']) }} {{ __('visits with GPS coordinates') }}
            </p>
          </div>
          <div class="card-body p-0">
            <div id="salesWeekVisitMap" class="rounded-bottom" style="height: 420px; min-height: 280px;"></div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <div class="row g-6 mb-6">
    <div class="col-xl-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-md-center align-items-start flex-md-row flex-column">
          <div class="card-title mb-md-0 mb-4">
            <h5 class="mb-1">Activity</h5>
            <p class="card-subtitle text-body-secondary mb-0">Last {{ $metrics['period_days'] }} days — {{ $isAdmin ? 'All reps' : 'Your territory' }}</p>
          </div>
        </div>
        <div class="card-body pb-0">
          <div id="salesVisitsAreaChart" class="mb-3"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="swiper-container swiper swiper-card-advance-bg h-100" id="sales-swiper-cards">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <div class="row text-white">
              <div class="col-12">
                <h5 class="text-white mb-1">Visit pulse</h5>
                <small>Orders vs collections by day</small>
              </div>
              <div class="col-12 mt-4">
                <div id="salesOrdersCollectionsBar" style="min-height: 200px;"></div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="row text-white">
              <div class="col-12">
                <h5 class="text-white mb-1">Samples</h5>
                <small>Units given out per day</small>
              </div>
              <div class="col-12 mt-4">
                <div id="salesSamplesSpark" style="min-height: 120px;"></div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="row text-white">
              <div class="col-12">
                <h5 class="text-white mb-1">Quick links</h5>
                <small>Manage your pipeline</small>
              </div>
              <div class="col-12 mt-4">
                <a href="{{ url('/admin') }}" class="btn btn-light btn-sm me-2 mb-2">Open records</a>
                <a href="{{ url('/admin/visits/create') }}" class="btn btn-outline-light btn-sm mb-2">New visit</a>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>

  <div class="row g-6">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-1">Samples trend</h5>
          <p class="card-subtitle text-body-secondary mb-0">Daily sample units</p>
        </div>
        <div class="card-body">
          <div id="salesSamplesColumnChart"></div>
        </div>
      </div>
    </div>
  </div>
@endsection
