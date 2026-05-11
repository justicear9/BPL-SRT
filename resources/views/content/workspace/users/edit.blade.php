@extends('layouts/layoutMaster')

@section('title', __('Edit user'))

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
      <h5 class="card-title mb-1">{{ __('Edit user') }}</h5>
      <small class="text-body-secondary">{{ __('Update profile, email, password, or role (where allowed).') }}</small>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('workspace.users.update', $user) }}" class="row g-3 needs-validation" novalidate>
        @csrf
        @method('PUT')
        <div class="col-12">
          @include('content.workspace.users._form', ['user' => $user])
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
          <a href="{{ route('workspace.users.index') }}" class="btn btn-label-secondary">{{ __('Back') }}</a>
        </div>
      </form>
    </div>
  </div>
@endsection
