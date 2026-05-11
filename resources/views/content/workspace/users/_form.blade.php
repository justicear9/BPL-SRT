@php
  /** @var \App\Models\User|null $user */
  $editing = isset($user);
  $isSelf = $editing && (int) auth()->id() === (int) $user->id;
  $showRole = auth()->user()->canManageAllVisits() && ! $isSelf;
@endphp

<div class="mb-4">
  <label class="form-label" for="user-name">{{ __('Name') }}</label>
  <input type="text" name="name" id="user-name" class="form-control @error('name') is-invalid @enderror"
    value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="user-username">{{ __('Username') }}</label>
  <input type="text" name="username" id="user-username" class="form-control @error('username') is-invalid @enderror"
    value="{{ old('username', $user->username ?? '') }}" required maxlength="64" autocomplete="username"
    pattern="[a-zA-Z0-9][a-zA-Z0-9._-]*" title="{{ __('Letters, numbers, dot, underscore, or hyphen after the first character.') }}">
  <div class="form-text">{{ __('Used to sign in to the admin panel with your password.') }}</div>
  @error('username')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="user-email">{{ __('Email') }}</label>
  <input type="email" name="email" id="user-email" class="form-control @error('email') is-invalid @enderror"
    value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
  @error('email')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="user-password">{{ __('Password') }}</label>
  <input type="password" name="password" id="user-password"
    class="form-control @error('password') is-invalid @enderror"
    @if (! $editing) required @endif autocomplete="new-password">
  @if ($editing)
    <div class="form-text">{{ __('Leave blank to keep the current password.') }}</div>
  @endif
  @error('password')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="user-password-confirmation">{{ __('Confirm password') }}</label>
  <input type="password" name="password_confirmation" id="user-password-confirmation" class="form-control"
    @if (! $editing) required @endif autocomplete="new-password">
</div>

@if ($showRole)
  <div class="mb-4">
    <label class="form-label" for="user-role">{{ __('Role') }}</label>
    <select name="role" id="user-role" class="form-select @error('role') is-invalid @enderror" required>
      @foreach (\App\Enums\UserRole::cases() as $roleOption)
        <option value="{{ $roleOption->value }}"
          @selected(old('role', $editing ? $user->role->value : \App\Enums\UserRole::SalesRep->value) === $roleOption->value)>{{ $roleOption->label() }}</option>
      @endforeach
    </select>
    @error('role')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
@endif
