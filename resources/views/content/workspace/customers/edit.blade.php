@extends('layouts/layoutMaster')

@section('title', __('Edit customer'))

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
      <h5 class="card-title mb-1">{{ __('Edit customer') }}</h5>
      <small class="text-body-secondary">{{ __('Keep assignment and location details aligned with the field team.') }}</small>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('workspace.customers.update', $customer) }}" class="row g-3 needs-validation" novalidate>
        @csrf
        @method('PUT')
        <div class="col-12">
          @include('content.workspace.customers._form', ['customer' => $customer])
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 pt-2">
          <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
          <a href="{{ route('workspace.customers.index') }}" class="btn btn-label-secondary">{{ __('Back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header border-bottom">
      <h6 class="mb-0">{{ __('Contact persons') }}</h6>
    </div>
    <div class="card-body">
      @forelse ($customer->contacts as $contact)
        <div class="small @unless ($loop->last) mb-2 @endunless">{{ $contact->listLabel() }}</div>
      @empty
        <p class="text-body-secondary mb-0">{{ __('None yet. Contacts are added when you record a visit or from the admin panel.') }}</p>
      @endforelse
    </div>
  </div>
@endsection
