@php
  /** @var \App\Models\Product|null $product */
  $editing = isset($product);
@endphp

<div class="mb-4">
  <label class="form-label" for="product-sku">{{ __('SKU') }}</label>
  <input type="text" name="sku" id="product-sku" class="form-control @error('sku') is-invalid @enderror"
    value="{{ old('sku', $product->sku ?? '') }}" required maxlength="64">
  @error('sku')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="product-name">{{ __('Name') }}</label>
  <input type="text" name="name" id="product-name" class="form-control @error('name') is-invalid @enderror"
    value="{{ old('name', $product->name ?? '') }}" required maxlength="255">
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="product-uom">{{ __('Base unit of measure') }}</label>
  <input type="text" name="unit_of_measure" id="product-uom"
    class="form-control @error('unit_of_measure') is-invalid @enderror"
    value="{{ old('unit_of_measure', $product->unit_of_measure ?? '') }}" maxlength="32"
    placeholder="{{ __('e.g. BTL, PCS') }}">
  @error('unit_of_measure')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="product-category-code">{{ __('Item category code') }}</label>
  <input type="text" name="item_category_code" id="product-category-code"
    class="form-control @error('item_category_code') is-invalid @enderror"
    value="{{ old('item_category_code', $product->item_category_code ?? '') }}" maxlength="32"
    placeholder="{{ __('e.g. OTC, POM') }}">
  @error('item_category_code')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-4">
  <label class="form-label" for="product-price">{{ __('Default unit price') }}</label>
  <input type="number" step="0.01" min="0" name="default_unit_price" id="product-price"
    class="form-control @error('default_unit_price') is-invalid @enderror"
    value="{{ old('default_unit_price', $product->default_unit_price ?? '0') }}" required>
  @error('default_unit_price')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<input type="hidden" name="can_be_sampled" value="0">
<div class="form-check mb-3">
  <input class="form-check-input" type="checkbox" name="can_be_sampled" id="product-sampled" value="1"
    @checked((bool) (int) old('can_be_sampled', $editing ? (int) $product->can_be_sampled : 1))>
  <label class="form-check-label" for="product-sampled">{{ __('Can be sampled') }}</label>
</div>

<input type="hidden" name="is_active" value="0">
<div class="form-check mb-4">
  <input class="form-check-input" type="checkbox" name="is_active" id="product-active" value="1"
    @checked((bool) (int) old('is_active', $editing ? (int) $product->is_active : 1))>
  <label class="form-check-label" for="product-active">{{ __('Active') }}</label>
</div>
