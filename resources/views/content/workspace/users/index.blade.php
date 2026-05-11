@extends('layouts/layoutMaster')

@section('title', __('Sales reps'))

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
          <h5 class="card-title mb-1">{{ __('Sales reps') }}</h5>
          <p class="mb-0 text-body-secondary">{{ __('Manage workspace accounts and roles.') }}</p>
        </div>
        @can('create', \App\Models\User::class)
          <a href="{{ route('workspace.users.create') }}" class="btn btn-primary">{{ __('Add user') }}</a>
        @endcan
      </div>

      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
      <form method="get" class="row pt-4 g-4 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label" for="filter-users-q">{{ __('Search') }}</label>
          <input type="text" name="q" id="filter-users-q" value="{{ request('q') }}" class="form-control"
            placeholder="{{ __('Name, username, or email') }}">
        </div>
        @if (auth()->user()->canManageAllVisits())
          <div class="col-12 col-md-4">
            <label class="form-label" for="filter-users-role">{{ __('Role') }}</label>
            <select name="role" id="filter-users-role" class="form-select">
              <option value="">{{ __('All roles') }}</option>
              @foreach (\App\Enums\UserRole::cases() as $roleOption)
                <option value="{{ $roleOption->value }}" @selected(request('role') === $roleOption->value)>{{ $roleOption->label() }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div class="col-12 col-md-4 d-flex flex-wrap align-items-end gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
          <a href="{{ route('workspace.users.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>

    <div class="card-datatable text-nowrap">
      <table class="datatable-workspace table table-bordered mb-0">
        <thead class="border-top">
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Username') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Role') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $row)
            <tr>
              <td>{{ $row->name }}</td>
              <td>{{ $row->username }}</td>
              <td>{{ $row->email }}</td>
              <td>{{ $row->role?->label() }}</td>
              <td class="text-end text-nowrap">
                @can('update', $row)
                  <a href="{{ route('workspace.users.edit', $row) }}" class="btn btn-sm btn-text-primary">{{ __('Edit') }}</a>
                @endcan
                @can('delete', $row)
                  <form action="{{ route('workspace.users.destroy', $row) }}" method="post" class="d-inline"
                    onsubmit="return confirm({{ json_encode(__('Remove this user?')) }});">
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
      {{ $users->links() }}
    </div>
  </div>
@endsection
