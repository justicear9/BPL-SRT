@extends('layouts/layoutMaster')

@section('title', __('Import customers'))

@section('content')
  @include('content.workspace.partials.flash')

  <div class="card mb-6">
    <div class="card-body">
      <h5 class="card-title mb-2">{{ __('Import customers') }}</h5>
      <p class="text-body-secondary mb-0">
        {{ __('Upload a CSV or Excel (.xlsx) file. The first row must be column headers. Required columns: name, type.') }}
      </p>
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header">
      <h6 class="mb-0">{{ __('Column reference') }}</h6>
    </div>
    <div class="card-body">
      <ul class="mb-0">
        <li><strong>name</strong> — {{ __('required') }}</li>
        <li><strong>type</strong> — {{ __('required:') }} pharmacy, hospital, wholesaler, chemical_shop</li>
        <li><strong>phone, city, region, address_line</strong> — {{ __('optional') }}</li>
        <li><strong>shop_latitude, shop_longitude</strong> — {{ __('optional numbers') }}</li>
        <li><strong>assigned_user_email</strong> — {{ __('optional; match an existing user by email or username, or enter a rep label to auto-create a sales rep (username derived from the label, default password "password")') }}</li>
      </ul>
      <a href="{{ route('workspace.customers.import.template') }}" class="btn btn-sm btn-label-primary mt-3">
        {{ __('Download CSV template') }}
      </a>
    </div>
  </div>

  @if (session()->has('import_created'))
    <div class="alert alert-{{ session('import_errors') ? 'warning' : 'success' }} mb-4" role="alert">
      <p class="mb-1">{{ __('Imported :n new customers.', ['n' => session('import_created')]) }}</p>
      @if ((int) session('import_users_created', 0) > 0)
        <p class="mb-1 small">
          {{ __('Created :n new sales rep accounts (username + password "password" until changed).', ['n' => session('import_users_created')]) }}
        </p>
      @endif
      @if (session('import_errors'))
        <p class="mb-2 fw-medium">{{ __('Some rows were skipped:') }}</p>
        <ul class="mb-0 small">
          @foreach (session('import_errors') as $err)
            <li>
              @if (($err['line'] ?? 0) > 0)
                {{ __('Line :line', ['line' => $err['line']]) }} —
              @endif
              {{ $err['message'] }}
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="post" action="{{ route('workspace.customers.import.store') }}" enctype="multipart/form-data"
        class="row g-4">
        @csrf
        <div class="col-12">
          <label class="form-label" for="customer-import-file">{{ __('Spreadsheet file') }}</label>
          <input type="file" name="import" id="customer-import-file" required accept=".csv,.txt,.xlsx"
            class="form-control @error('import') is-invalid @enderror">
          @error('import')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-body-secondary">{{ __('Max 12 MB. UTF-8 CSV or .xlsx (first sheet only).') }}</small>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Run import') }}</button>
          <a href="{{ route('workspace.customers.index') }}" class="btn btn-label-secondary">{{ __('Back to customers') }}</a>
        </div>
      </form>
    </div>
  </div>
@endsection
