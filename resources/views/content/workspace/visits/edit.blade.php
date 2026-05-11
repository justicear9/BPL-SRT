@extends('layouts/layoutMaster')

@section('title', __('Edit visit'))

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  ])
@endsection

@section('page-script')
  @vite('resources/assets/js/workspace-visit-form.js')
@endsection

@section('content')
  @include('content.workspace.partials.flash')

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-1">{{ __('Edit visit') }}</h5>
      <small class="text-body-secondary">{{ __('Update visit details, items sold, samples, and collections.') }}</small>
    </div>
    <div class="card-body">
      @include('content.workspace.visits._form')
    </div>
  </div>

  @include('content.workspace.visits._contact-modal')
@endsection
