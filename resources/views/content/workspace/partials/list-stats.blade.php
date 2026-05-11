{{-- Vuexy-style stat row (matches app-user-list cards). Expects $listStats: list of arrays with keys label, value, caption (optional), icon (ti tabler-*), variant (primary|success|danger|warning|info|secondary). Optional delta + delta_class for trend text. --}}
@php
  $listStats = $listStats ?? [];
@endphp
@if (count($listStats))
  <div class="row g-6 mb-6">
    @foreach ($listStats as $stat)
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">{{ $stat['label'] }}</span>
                <div class="d-flex align-items-center my-1">
                  <h4 class="mb-0 me-2">{{ $stat['value'] }}</h4>
                  @if (! empty($stat['delta']))
                    <p class="{{ $stat['delta_class'] ?? 'text-success' }} mb-0">{{ $stat['delta'] }}</p>
                  @endif
                </div>
                @if (! empty($stat['caption']))
                  <small class="mb-0">{{ $stat['caption'] }}</small>
                @endif
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-{{ $stat['variant'] ?? 'primary' }}">
                  <i class="icon-base ti {{ $stat['icon'] ?? 'tabler-chart-bar' }} icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endif
