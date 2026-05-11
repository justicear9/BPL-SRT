@php
  /** @var \App\Models\Visit|null $visit */
  $editing = isset($visit);
  $visitModel = $visit ?? null;
  $action = $editing ? route('workspace.visits.update', $visitModel) : route('workspace.visits.store');
  $orderLines = array_values(old('order_lines', $orderLinesInitial));
  $samplesRows = array_values(old('samples', $samplesInitial));
  $collectionsRows = array_values(old('collections', $collectionsInitial));
  $errKeys = collect($errors->keys());
  $openOrders = $errKeys->contains(fn (string $k): bool => $k === 'order_lines' || str_starts_with($k, 'order_lines.'));
  $openSamples = $errKeys->contains(fn (string $k): bool => $k === 'samples' || str_starts_with($k, 'samples.'));
  $openCollections = $errKeys->contains(fn (string $k): bool => $k === 'collections' || str_starts_with($k, 'collections.'));
  $countOrderLines = collect($orderLines)->filter(fn (array $r): bool => ! empty($r['product_id']) && (int) ($r['quantity'] ?? 0) >= 1)->count();
  $countSamples = collect($samplesRows)->filter(fn (array $r): bool => ! empty($r['product_id']) && (int) ($r['quantity'] ?? 0) >= 1)->count();
  $countCollections = collect($collectionsRows)->filter(fn (array $r): bool => isset($r['amount']) && $r['amount'] !== '' && $r['amount'] !== null)->count();
  $collectionsPaymentMethods = \App\Models\VisitCollection::paymentMethodOptions();
  $visitHasCustomer = (int) old('customer_id', $visitModel?->customer_id ?? 0) > 0;
@endphp

@once
  <style>
    .visit-collapse-chevron { transition: transform 0.2s ease; }
    [data-bs-toggle="collapse"]:not(.collapsed) .visit-collapse-chevron { transform: rotate(180deg); }
    .visit-section-toggle.collapsed:not(.visit-section-has-data) .visit-section-title {
      color: var(--bs-secondary-color);
    }
    .visit-section-toggle.visit-section-has-data .visit-section-title,
    .visit-section-toggle:not(.collapsed) .visit-section-title {
      color: var(--bs-heading-color);
    }
    .visit-section-toggle.visit-section-has-data:not(.collapsed),
    .visit-section-toggle:not(.collapsed) {
      background-color: rgba(var(--bs-primary-rgb), 0.06);
    }
  </style>
@endonce

<form method="post" action="{{ $action }}" class="needs-validation" novalidate data-workspace-visit-form
  data-customer-contacts='@json($customerContactsForJs)'
  data-workspace-customers-base="{{ url('workspace/customers') }}"
  data-placeholder-no-customer="{{ __('Select customer first') }}"
  data-placeholder-with-customer="{{ __('Search or select contact') }}"
  data-alert-select-customer="{{ __('Please select a customer first.') }}"
  data-order-line-unit="{{ __('order lines') }}"
  data-sample-unit="{{ __('samples') }}"
  data-collection-unit="{{ __('collections') }}">
  @csrf
  @if ($editing)
    @method('PUT')
  @endif

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <label class="form-label" for="visit-customer">{{ __('Customer') }}</label>
      <select name="customer_id" id="visit-customer" class="form-select select2 @error('customer_id') is-invalid @enderror" required
        data-placeholder="{{ __('Select customer') }}">
        <option value="">{{ __('Select customer') }}</option>
        @foreach ($customers as $customer)
          <option value="{{ $customer->id }}" @selected((int) old('customer_id', $visitModel?->customer_id ?? 0) === (int) $customer->id)>
            {{ $customer->name }}
          </option>
        @endforeach
      </select>
      @error('customer_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-12 @unless ($visitHasCustomer) d-none @endunless" data-visit-contact-row>
      <label class="form-label" for="visit-contact-person">{{ __('Contact person') }} <span class="text-danger">*</span></label>
      <div class="d-flex flex-wrap gap-2 align-items-start">
        <div class="flex-grow-1" style="min-width: 240px;">
          <select name="contact_id" id="visit-contact-person" class="form-select select2 @error('contact_id') is-invalid @enderror"
            @if ($visitHasCustomer) required @endif
            data-placeholder="{{ __('Search or select contact') }}">
            <option value="">{{ $visitHasCustomer ? __('Search or select contact') : __('Select customer first') }}</option>
            @foreach ($customers as $customer)
              @if ((int) old('customer_id', $visitModel?->customer_id ?? 0) === (int) $customer->id)
                @foreach ($customer->contacts as $contact)
                  <option value="{{ $contact->id }}" @selected((int) old('contact_id', $visitModel?->contact_id ?? 0) === (int) $contact->id)>
                    {{ $contact->listLabel() }}
                  </option>
                @endforeach
              @endif
            @endforeach
          </select>
        </div>
        <button type="button" class="btn btn-label-primary flex-shrink-0" data-open-new-contact-modal>{{ __('Add new…') }}</button>
      </div>
      <small class="text-body-secondary d-block mt-1">{{ __('Required. Pick someone you met, or add a new contact — they are saved on this customer.') }}</small>
      @error('contact_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    @if ($isPrivileged && $assignableUsers->isNotEmpty())
      <div class="col-12 col-lg-6">
        <label class="form-label" for="visit-owner">{{ __('Visit owner') }}</label>
        <select name="user_id" id="visit-owner" class="form-select select2 @error('user_id') is-invalid @enderror" required
          data-placeholder="{{ __('Select user') }}">
          <option value="">{{ __('Select user') }}</option>
          @foreach ($assignableUsers as $assignee)
            <option value="{{ $assignee->id }}" @selected((int) old('user_id', $visitModel?->user_id ?? 0) === (int) $assignee->id)>
              {{ $assignee->name }}
            </option>
          @endforeach
        </select>
        @error('user_id')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    @endif

    @if ($isPrivileged)
      <div class="col-12 col-lg-6">
        <label class="form-label" for="visit-at">{{ __('Visited at') }}</label>
        <input type="datetime-local" name="visited_at" id="visit-at"
          class="form-control @error('visited_at') is-invalid @enderror"
          value="{{ old('visited_at', $visitModel?->visited_at?->format('Y-m-d\TH:i')) }}" required>
        @error('visited_at')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    @endif

    <div class="col-12">
      <label class="form-label" for="visit-comments">{{ __('Comments') }}</label>
      <textarea name="comments" id="visit-comments" rows="3"
        class="form-control @error('comments') is-invalid @enderror">{{ old('comments', $visitModel?->comments ?? '') }}</textarea>
      @error('comments')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-12">
      <label class="form-label">{{ __('Location') }}</label>
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-label-secondary" data-geo-button>{{ __('Use current location') }}</button>
        <small class="text-body-secondary" data-geo-status></small>
      </div>
      <input type="hidden" name="visit_latitude" value="{{ old('visit_latitude', $visitModel?->visit_latitude ?? '') }}">
      <input type="hidden" name="visit_longitude" value="{{ old('visit_longitude', $visitModel?->visit_longitude ?? '') }}">
      @error('visit_latitude')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
      @error('visit_longitude')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="card visit-section-card mb-4 shadow-none border mt-1" data-visit-section="order_lines">
    <div class="card-header p-0 border-bottom-0" id="heading-visit-orders">
      <button
        class="visit-section-toggle btn w-100 py-3 px-4 d-flex justify-content-between align-items-start text-start rounded-0 border-0 shadow-none @if ($countOrderLines > 0) visit-section-has-data @endif @unless ($openOrders) collapsed @endunless"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-visit-orders"
        data-visit-section-toggle="order_lines"
        aria-expanded="{{ $openOrders ? 'true' : 'false' }}"
        aria-controls="collapse-visit-orders">
        <span class="pe-3">
          <span class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="fw-semibold visit-section-title">{{ __('Order lines') }}</span>
            <span class="badge rounded-pill bg-label-primary visit-section-count @if ($countOrderLines === 0) d-none @endif" data-visit-section-count="order_lines">{{ $countOrderLines }}</span>
          </span>
          <small class="text-body-secondary fw-normal d-block visit-section-hint" data-visit-section-hint="order_lines">{{ __('Optional products sold on this visit — expand to add rows.') }}</small>
          <small class="text-body fw-normal d-none visit-section-filled" data-visit-section-filled="order_lines"></small>
        </span>
        <i class="icon-base ti tabler-chevron-down visit-collapse-chevron flex-shrink-0 mt-1"></i>
      </button>
    </div>
    <div id="collapse-visit-orders" class="collapse @if ($openOrders) show @endif" aria-labelledby="heading-visit-orders" data-visit-section-panel="order_lines">
      <div class="card-body border-top pt-4">
  @error('order_lines')
    <div class="alert alert-danger mb-3">{{ $message }}</div>
  @enderror
  <div data-rows="order_lines" data-next-index="{{ count($orderLines) }}">
    @foreach ($orderLines as $idx => $row)
      <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
        <div class="col-12 col-md-5">
          <label class="form-label">{{ __('Product') }}</label>
          <select name="order_lines[{{ $idx }}][product_id]" class="form-select select2 @error('order_lines.'.$idx.'.product_id') is-invalid @enderror"
            data-placeholder="{{ __('Select product') }}" data-allow-clear="true">
            <option value="">{{ __('Select product') }}</option>
            @foreach ($orderProducts as $product)
              @php $pid = (string) ($row['product_id'] ?? ''); @endphp
              <option value="{{ $product->id }}" @selected((string) $product->id === $pid)>{{ $product->name }}</option>
            @endforeach
          </select>
          @error('order_lines.'.$idx.'.product_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">{{ __('Qty') }}</label>
          <input type="number" name="order_lines[{{ $idx }}][quantity]" min="1" class="form-control @error('order_lines.'.$idx.'.quantity') is-invalid @enderror"
            value="{{ $row['quantity'] ?? '1' }}">
          @error('order_lines.'.$idx.'.quantity')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">{{ __('Unit price') }}</label>
          <input type="number" name="order_lines[{{ $idx }}][unit_price]" min="0" step="0.01"
            class="form-control @error('order_lines.'.$idx.'.unit_price') is-invalid @enderror"
            value="{{ $row['unit_price'] ?? '' }}" placeholder="{{ __('Optional') }}">
          @error('order_lines.'.$idx.'.unit_price')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12 col-md-1 text-md-end">
          <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
        </div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-sm btn-primary mb-0" data-add-row="order_lines">{{ __('Add order line') }}</button>

  <template data-template="order_lines">
    <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
      <div class="col-12 col-md-5">
        <label class="form-label">{{ __('Product') }}</label>
        <select name="order_lines[__INDEX__][product_id]" class="form-select select2"
          data-placeholder="{{ __('Select product') }}" data-allow-clear="true">
          <option value="">{{ __('Select product') }}</option>
          @foreach ($orderProducts as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">{{ __('Qty') }}</label>
        <input type="number" name="order_lines[__INDEX__][quantity]" min="1" class="form-control" value="1">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">{{ __('Unit price') }}</label>
        <input type="number" name="order_lines[__INDEX__][unit_price]" min="0" step="0.01" class="form-control" placeholder="{{ __('Optional') }}">
      </div>
      <div class="col-12 col-md-1 text-md-end">
        <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
      </div>
    </div>
  </template>
      </div>
    </div>
  </div>

  <div class="card visit-section-card mb-4 shadow-none border" data-visit-section="samples">
    <div class="card-header p-0 border-bottom-0" id="heading-visit-samples">
      <button
        class="visit-section-toggle btn w-100 py-3 px-4 d-flex justify-content-between align-items-start text-start rounded-0 border-0 shadow-none @if ($countSamples > 0) visit-section-has-data @endif @unless ($openSamples) collapsed @endunless"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-visit-samples"
        data-visit-section-toggle="samples"
        aria-expanded="{{ $openSamples ? 'true' : 'false' }}"
        aria-controls="collapse-visit-samples">
        <span class="pe-3">
          <span class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="fw-semibold visit-section-title">{{ __('Samples') }}</span>
            <span class="badge rounded-pill bg-label-primary visit-section-count @if ($countSamples === 0) d-none @endif" data-visit-section-count="samples">{{ $countSamples }}</span>
          </span>
          <small class="text-body-secondary fw-normal d-block visit-section-hint" data-visit-section-hint="samples">{{ __('Sampled products only — expand when you left samples.') }}</small>
          <small class="text-body fw-normal d-none visit-section-filled" data-visit-section-filled="samples"></small>
        </span>
        <i class="icon-base ti tabler-chevron-down visit-collapse-chevron flex-shrink-0 mt-1"></i>
      </button>
    </div>
    <div id="collapse-visit-samples" class="collapse @if ($openSamples) show @endif" aria-labelledby="heading-visit-samples" data-visit-section-panel="samples">
      <div class="card-body border-top pt-4">
  @error('samples')
    <div class="alert alert-danger mb-3">{{ $message }}</div>
  @enderror
  <div data-rows="samples" data-next-index="{{ count($samplesRows) }}">
    @foreach ($samplesRows as $idx => $row)
      <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
        <div class="col-12 col-md-8">
          <label class="form-label">{{ __('Product') }}</label>
          <select name="samples[{{ $idx }}][product_id]" class="form-select select2 @error('samples.'.$idx.'.product_id') is-invalid @enderror"
            data-placeholder="{{ __('Select product') }}" data-allow-clear="true">
            <option value="">{{ __('Select product') }}</option>
            @foreach ($sampleProducts as $product)
              @php $pid = (string) ($row['product_id'] ?? ''); @endphp
              <option value="{{ $product->id }}" @selected((string) $product->id === $pid)>{{ $product->name }}</option>
            @endforeach
          </select>
          @error('samples.'.$idx.'.product_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-10 col-md-3">
          <label class="form-label">{{ __('Qty') }}</label>
          <input type="number" name="samples[{{ $idx }}][quantity]" min="1" class="form-control @error('samples.'.$idx.'.quantity') is-invalid @enderror"
            value="{{ $row['quantity'] ?? '1' }}">
          @error('samples.'.$idx.'.quantity')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-2 col-md-1 text-md-end">
          <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
        </div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-sm btn-primary mb-0" data-add-row="samples">{{ __('Add sample row') }}</button>

  <template data-template="samples">
    <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
      <div class="col-12 col-md-8">
        <label class="form-label">{{ __('Product') }}</label>
        <select name="samples[__INDEX__][product_id]" class="form-select select2"
          data-placeholder="{{ __('Select product') }}" data-allow-clear="true">
          <option value="">{{ __('Select product') }}</option>
          @foreach ($sampleProducts as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-10 col-md-3">
        <label class="form-label">{{ __('Qty') }}</label>
        <input type="number" name="samples[__INDEX__][quantity]" min="1" class="form-control" value="1">
      </div>
      <div class="col-2 col-md-1 text-md-end">
        <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
      </div>
    </div>
  </template>
      </div>
    </div>
  </div>

  <div class="card visit-section-card mb-4 shadow-none border" data-visit-section="collections">
    <div class="card-header p-0 border-bottom-0" id="heading-visit-collections">
      <button
        class="visit-section-toggle btn w-100 py-3 px-4 d-flex justify-content-between align-items-start text-start rounded-0 border-0 shadow-none @if ($countCollections > 0) visit-section-has-data @endif @unless ($openCollections) collapsed @endunless"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-visit-collections"
        data-visit-section-toggle="collections"
        aria-expanded="{{ $openCollections ? 'true' : 'false' }}"
        aria-controls="collapse-visit-collections">
        <span class="pe-3">
          <span class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="fw-semibold visit-section-title">{{ __('Collections') }}</span>
            <span class="badge rounded-pill bg-label-primary visit-section-count @if ($countCollections === 0) d-none @endif" data-visit-section-count="collections">{{ $countCollections }}</span>
          </span>
          <small class="text-body-secondary fw-normal d-block visit-section-hint" data-visit-section-hint="collections">{{ __('Cash or payments collected — expand when applicable.') }}</small>
          <small class="text-body fw-normal d-none visit-section-filled" data-visit-section-filled="collections"></small>
        </span>
        <i class="icon-base ti tabler-chevron-down visit-collapse-chevron flex-shrink-0 mt-1"></i>
      </button>
    </div>
    <div id="collapse-visit-collections" class="collapse @if ($openCollections) show @endif" aria-labelledby="heading-visit-collections" data-visit-section-panel="collections">
      <div class="card-body border-top pt-4">
  @error('collections')
    <div class="alert alert-danger mb-3">{{ $message }}</div>
  @enderror
  <div data-rows="collections" data-next-index="{{ count($collectionsRows) }}">
    @foreach ($collectionsRows as $idx => $row)
      <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
        <div class="col-12 col-md-3">
          <label class="form-label">{{ __('Amount') }}</label>
          <input type="number" name="collections[{{ $idx }}][amount]" min="0" step="0.01"
            class="form-control @error('collections.'.$idx.'.amount') is-invalid @enderror"
            value="{{ $row['amount'] ?? '' }}">
          @error('collections.'.$idx.'.amount')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">{{ __('Payment method') }}</label>
          <select name="collections[{{ $idx }}][payment_method]"
            class="form-select @error('collections.'.$idx.'.payment_method') is-invalid @enderror">
            <option value="">{{ __('Choose…') }}</option>
            @foreach ($collectionsPaymentMethods as $value => $label)
              <option value="{{ $value }}" @selected(old('collections.'.$idx.'.payment_method', $row['payment_method'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('collections.'.$idx.'.payment_method')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">{{ __('Notes') }}</label>
          <input type="text" name="collections[{{ $idx }}][notes]" maxlength="500"
            class="form-control @error('collections.'.$idx.'.notes') is-invalid @enderror"
            value="{{ $row['notes'] ?? '' }}">
          @error('collections.'.$idx.'.notes')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12 col-md-1 text-md-end">
          <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
        </div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-sm btn-primary mb-0" data-add-row="collections">{{ __('Add collection row') }}</button>

  <template data-template="collections">
    <div class="row g-2 mb-3 align-items-end border rounded p-3" data-repeater-row>
      <div class="col-12 col-md-3">
        <label class="form-label">{{ __('Amount') }}</label>
        <input type="number" name="collections[__INDEX__][amount]" min="0" step="0.01" class="form-control">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">{{ __('Payment method') }}</label>
        <select name="collections[__INDEX__][payment_method]" class="form-select">
          <option value="">{{ __('Choose…') }}</option>
          @foreach ($collectionsPaymentMethods as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">{{ __('Notes') }}</label>
        <input type="text" name="collections[__INDEX__][notes]" maxlength="500" class="form-control">
      </div>
      <div class="col-12 col-md-1 text-md-end">
        <button type="button" class="btn btn-sm btn-label-secondary mt-md-4" data-remove-row>{{ __('Remove') }}</button>
      </div>
    </div>
  </template>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex flex-wrap gap-2 pt-2">
    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    <a href="{{ route('workspace.visits.index') }}" class="btn btn-label-secondary">{{ __('Cancel') }}</a>
  </div>
</form>
