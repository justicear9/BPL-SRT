@php
  /** @var \App\Models\Visit $visit */
  $sym = \App\Models\Setting::currencySymbol();
@endphp

<div class="visit-modal-readonly px-3 py-3">
  <p class="text-body-secondary small mb-4">{{ __('Summary only — open the full editor to change this visit.') }}</p>

  <dl class="row mb-4 small">
    <dt class="col-sm-4 col-md-3 text-body-secondary">{{ __('Customer') }}</dt>
    <dd class="col-sm-8 col-md-9 mb-2">{{ $visit->customer?->name ?? '—' }}</dd>

    @if ($visit->contact)
      <dt class="col-sm-4 col-md-3 text-body-secondary">{{ __('Contact person') }}</dt>
      <dd class="col-sm-8 col-md-9 mb-2">{{ $visit->contact->listLabel() }}</dd>
    @endif

    <dt class="col-sm-4 col-md-3 text-body-secondary">{{ __('Visit date') }}</dt>
    <dd class="col-sm-8 col-md-9 mb-2">{{ $visit->visited_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '—' }}</dd>

    <dt class="col-sm-4 col-md-3 text-body-secondary">{{ __('Sales rep') }}</dt>
    <dd class="col-sm-8 col-md-9 mb-2">{{ $visit->user?->name ?? '—' }}</dd>

    @if ($visit->visit_latitude !== null && $visit->visit_longitude !== null)
      <dt class="col-sm-4 col-md-3 text-body-secondary">{{ __('Location') }}</dt>
      <dd class="col-sm-8 col-md-9 mb-2">{{ number_format((float) $visit->visit_latitude, 5) }},
        {{ number_format((float) $visit->visit_longitude, 5) }}</dd>
    @endif

    @if ($visit->comments)
      <dt class="col-sm-4 col-md-3 text-body-secondary align-top">{{ __('Comments') }}</dt>
      <dd class="col-sm-8 col-md-9 mb-2 text-break">{{ $visit->comments }}</dd>
    @endif
  </dl>

  @if ($visit->order && $visit->order->lines->isNotEmpty())
    <h6 class="border-bottom pb-2 mb-3">{{ __('Order lines') }}</h6>
    <div class="table-responsive mb-4">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th>{{ __('Product') }}</th>
            <th class="text-end">{{ __('Qty') }}</th>
            <th class="text-end">{{ __('Unit price') }}</th>
            <th class="text-end">{{ __('Line total') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($visit->order->lines as $line)
            <tr>
              <td>{{ $line->product?->name ?? __('Unknown product') }}</td>
              <td class="text-end">{{ $line->quantity }}</td>
              <td class="text-end">{{ $sym }}{{ number_format((float) $line->unit_price, 2) }}</td>
              <td class="text-end">{{ $sym }}{{ number_format($line->lineTotal(), 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">{{ __('Order total') }}</th>
            <th class="text-end">{{ $sym }}{{ number_format($visit->orderLineTotal(), 2) }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  @endif

  @if ($visit->samples->isNotEmpty())
    <h6 class="border-bottom pb-2 mb-3">{{ __('Samples') }}</h6>
    <div class="table-responsive mb-4">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th>{{ __('Product') }}</th>
            <th class="text-end">{{ __('Quantity') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($visit->samples as $sample)
            <tr>
              <td>{{ $sample->product?->name ?? __('Unknown product') }}</td>
              <td class="text-end">{{ $sample->quantity }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  @if ($visit->collections->isNotEmpty())
    <h6 class="border-bottom pb-2 mb-3">{{ __('Collections') }}</h6>
    <div class="table-responsive mb-4">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th class="text-end">{{ __('Amount') }}</th>
            <th>{{ __('Payment method') }}</th>
            <th>{{ __('Notes') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($visit->collections as $row)
            <tr>
              <td class="text-end">{{ $sym }}{{ number_format((float) $row->amount, 2) }}</td>
              <td>{{ $row->paymentMethodLabel() ?: '—' }}</td>
              <td class="text-break">{{ $row->notes ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  @can('update', $visit)
    <div class="border-top pt-3 mt-2">
      <a href="{{ route('workspace.visits.edit', $visit) }}" class="btn btn-primary btn-sm">{{ __('Open full editor') }}</a>
    </div>
  @endcan
</div>
