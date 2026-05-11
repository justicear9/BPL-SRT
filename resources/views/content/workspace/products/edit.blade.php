@extends('layouts/layoutMaster')

@section('title', __('Edit product'))

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
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('Edit product') }}</h5>
      <small class="text-body-secondary">{{ __('Changes apply to new lines; historical orders keep their captured prices.') }}</small>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('workspace.products.update', $product) }}" class="row g-3 needs-validation" novalidate>
        @csrf
        @method('PUT')
        <div class="col-12">
          @include('content.workspace.products._form', ['product' => $product])
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
          <a href="{{ route('workspace.products.index') }}" class="btn btn-label-secondary">{{ __('Back') }}</a>
        </div>
      </form>
    </div>
  </div>
@endsection
