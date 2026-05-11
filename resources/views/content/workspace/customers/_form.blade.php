@php
  /** @var \App\Models\Customer|null $customer */
  $editing = isset($customer);
  $defaultType = \App\Enums\CustomerType::Pharmacy->value;
@endphp

@if (auth()->user()->canManageAllVisits())
  <div class="mb-4">
    <label class="form-label" for="customer-assigned">{{ __('Assigned to') }}</label>
    <select name="assigned_user_id" id="customer-assigned" class="form-select @error('assigned_user_id') is-invalid @enderror">
      <option value="">{{ __('Unassigned') }}</option>
      @foreach ($salesReps as $rep)
        <option value="{{ $rep->id }}"
          @selected((int) old('assigned_user_id', $editing ? ($customer->assigned_user_id ?? 0) : 0) === (int) $rep->id)>{{ $rep->name }}</option>
      @endforeach
    </select>
    @error('assigned_user_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
@endif

<div class="mb-4">
  <label class="form-label" for="customer-type">{{ __('Type') }}</label>
  <select name="type" id="customer-type" class="form-select @error('type') is-invalid @enderror" required>
    @foreach (\App\Enums\CustomerType::cases() as $type)
      <option value="{{ $type->value }}"
        @selected(old('type', $editing ? $customer->type->value : $defaultType) === $type->value)>{{ $type->label() }}</option>
    @endforeach
  </select>
  @error('type')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="customer-name">{{ __('Name') }}</label>
  <input type="text" name="name" id="customer-name" class="form-control @error('name') is-invalid @enderror"
    value="{{ old('name', $customer->name ?? '') }}" required maxlength="255">
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="customer-phone">{{ __('Phone') }}</label>
  <input type="text" name="phone" id="customer-phone" class="form-control @error('phone') is-invalid @enderror"
    value="{{ old('phone', $customer->phone ?? '') }}" maxlength="64">
  @error('phone')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="customer-address">{{ __('Address') }}</label>
  <input type="text" name="address_line" id="customer-address" class="form-control @error('address_line') is-invalid @enderror"
    value="{{ old('address_line', $customer->address_line ?? '') }}" maxlength="255">
  @error('address_line')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="row g-4 mb-2">
  <div class="col-md-6">
    <label class="form-label" for="customer-city">{{ __('City') }}</label>
    <input type="text" name="city" id="customer-city" class="form-control @error('city') is-invalid @enderror"
      value="{{ old('city', $customer->city ?? '') }}" maxlength="120">
    @error('city')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="customer-region">{{ __('Region') }}</label>
    <input type="text" name="region" id="customer-region" class="form-control @error('region') is-invalid @enderror"
      value="{{ old('region', $customer->region ?? '') }}" maxlength="120">
    @error('region')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>

<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="customer-lat">{{ __('Shop latitude') }}</label>
    <input type="text" name="shop_latitude" id="customer-lat" class="form-control @error('shop_latitude') is-invalid @enderror"
      value="{{ old('shop_latitude', $customer->shop_latitude ?? '') }}">
    @error('shop_latitude')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="customer-lng">{{ __('Shop longitude') }}</label>
    <input type="text" name="shop_longitude" id="customer-lng" class="form-control @error('shop_longitude') is-invalid @enderror"
      value="{{ old('shop_longitude', $customer->shop_longitude ?? '') }}">
    @error('shop_longitude')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>
