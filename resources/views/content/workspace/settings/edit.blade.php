@extends('layouts/layoutMaster')

@section('title', __('App settings'))

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  ])
@endsection

@section('content')
  @include('content.workspace.partials.flash')

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('App settings') }}</h5>
      <small class="text-body-secondary">{{ __('Organization-wide defaults. Only administrators can change these.') }}</small>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('workspace.settings.update') }}" class="row g-4 needs-validation" novalidate>
        @csrf
        @method('PUT')

        <div class="col-12 col-md-6">
          <label class="form-label" for="settings-currency-code">{{ __('Currency code') }}</label>
          <select name="currency_code" id="settings-currency-code" class="form-select @error('currency_code') is-invalid @enderror" required>
            @foreach ($currencyOptions as $code => $label)
              <option value="{{ $code }}" @selected(old('currency_code', $currencyCode) === $code)>{{ $label }}</option>
            @endforeach
          </select>
          @error('currency_code')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label" for="settings-currency-symbol">{{ __('Currency symbol') }}</label>
          <input type="text" name="currency_symbol" id="settings-currency-symbol" maxlength="8"
            class="form-control @error('currency_symbol') is-invalid @enderror"
            value="{{ old('currency_symbol', $currencySymbol) }}" required
            placeholder="$">
          <small class="text-body-secondary">{{ __('Shown before amounts in lists and reports (e.g. $, €, £, ₵ for Ghana Cedi).') }}</small>
          @error('currency_symbol')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
          <a href="{{ route('dashboard-sales') }}" class="btn btn-label-secondary">{{ __('Back to dashboard') }}</a>
        </div>
      </form>
    </div>
  </div>
@endsection
