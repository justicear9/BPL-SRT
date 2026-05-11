@extends('layouts/layoutMaster')

@section('title', __('My profile'))

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

  <div class="row">
    <div class="col-xl-4 col-lg-5 mb-4 mb-xl-0">
      <div class="card h-100">
        <div class="card-body text-center pt-12 pb-12">
          <div class="avatar avatar-xl mx-auto mb-3">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" width="110" height="110"
              class="rounded-circle" />
          </div>
          <h5 class="mb-1">{{ $user->name }}</h5>
          <small class="text-body-secondary d-block mb-1">{{ $user->email }}</small>
          <small class="text-body-secondary d-block mb-2">{{ __('Username') }}: {{ $user->username }}</small>
          @if ($user->role)
            <span class="badge bg-label-primary">{{ $user->role->label() }}</span>
          @endif

          <div class="d-flex justify-content-around flex-wrap gap-3 mt-4 px-xl-2">
            <div class="text-center">
              <h4 class="mb-0">{{ number_format($visitCount) }}</h4>
              <small class="text-body-secondary">{{ $visitStatLabel }}</small>
            </div>
            <div class="text-center">
              <h4 class="mb-0">{{ number_format($customerCount) }}</h4>
              <small class="text-body-secondary">{{ $customerStatLabel }}</small>
            </div>
          </div>

          <p class="small text-body-secondary border-top pt-4 mt-4 mb-0">
            {{ __('Member since :date', ['date' => $user->created_at->timezone(config('app.timezone'))->format('M j, Y')]) }}
          </p>
        </div>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7">
      <div class="card mb-0">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-1">{{ __('Account settings') }}</h5>
          <small class="text-body-secondary">{{ __('Update your name, email, or password.') }}</small>
        </div>
        <div class="card-body">
          <form method="post" action="{{ route('workspace.profile.update') }}" class="row g-3 needs-validation"
            novalidate>
            @csrf
            @method('PUT')

            <div class="col-12">
              <label class="form-label" for="profile-name">{{ __('Name') }}</label>
              <input type="text" name="name" id="profile-name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}" required maxlength="255">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label class="form-label" for="profile-email">{{ __('Email') }}</label>
              <input type="email" name="email" id="profile-email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}" required maxlength="255">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label class="form-label" for="profile-password">{{ __('New password') }}</label>
              <input type="password" name="password" id="profile-password"
                class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
              <div class="form-text">{{ __('Leave blank to keep your current password.') }}</div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label class="form-label" for="profile-password-confirmation">{{ __('Confirm password') }}</label>
              <input type="password" name="password_confirmation" id="profile-password-confirmation" class="form-control"
                autocomplete="new-password">
            </div>

            <div class="col-12 pt-2">
              <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
