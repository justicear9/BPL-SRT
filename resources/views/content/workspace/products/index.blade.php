@extends('layouts/layoutMaster')

@section('title', __('Products'))

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
  ])
@endsection

@section('page-script')
  @vite('resources/assets/js/workspace-datatables.js')
@endsection

@section('content')
  @include('content.workspace.partials.flash')
  @include('content.workspace.partials.list-stats')

  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div class="me-auto">
          <h5 class="card-title mb-1">{{ __('Products') }}</h5>
          <p class="mb-0 text-body-secondary">{{ __('Catalog pricing and sampling flags.') }}</p>
        </div>
        @can('create', \App\Models\Product::class)
          <a href="{{ route('workspace.products.create') }}" class="btn btn-primary">{{ __('Add product') }}</a>
        @endcan
      </div>

      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
      <form method="get" class="row pt-4 g-4 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label" for="filter-products-q">{{ __('Search') }}</label>
          <input type="text" name="q" id="filter-products-q" value="{{ request('q') }}" class="form-control"
            placeholder="{{ __('SKU, name, or category') }}">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label" for="filter-products-active">{{ __('Status') }}</label>
          <select name="is_active" id="filter-products-active" class="form-select">
            <option value="">{{ __('All') }}</option>
            <option value="1" @selected(request('is_active') === '1')>{{ __('Active') }}</option>
            <option value="0" @selected(request('is_active') === '0')>{{ __('Inactive') }}</option>
          </select>
        </div>
        <div class="col-12 col-md-4 d-flex flex-wrap align-items-end gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
          <a href="{{ route('workspace.products.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>

    <div class="card-datatable text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('SKU') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Unit price') }}</th>
            <th>{{ __('Sampled') }}</th>
            <th>{{ __('Active') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($products as $row)
            <tr>
              <td>{{ $row->sku }}</td>
              <td>{{ $row->name }}</td>
              <td>{{ $row->item_category_code ?: '—' }}</td>
              <td>{{ \App\Models\Setting::currencySymbol() }}{{ number_format((float) $row->default_unit_price, 2) }}</td>
              <td>{{ $row->can_be_sampled ? __('Yes') : __('No') }}</td>
              <td>{{ $row->is_active ? __('Yes') : __('No') }}</td>
              <td class="text-end text-nowrap">
                @can('update', $row)
                  <a href="{{ route('workspace.products.edit', $row) }}" class="btn btn-sm btn-text-primary">{{ __('Edit') }}</a>
                @endcan
                @can('delete', $row)
                  <form action="{{ route('workspace.products.destroy', $row) }}" method="post" class="d-inline"
                    onsubmit="return confirm({{ json_encode(__('Delete this product?')) }});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-text-danger">{{ __('Delete') }}</button>
                  </form>
                @endcan
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $products->links() }}
    </div>
  </div>
@endsection
