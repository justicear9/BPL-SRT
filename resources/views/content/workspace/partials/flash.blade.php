@if (session('status'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <div class="fw-semibold mb-2">{{ __('Please fix the errors below.') }}</div>
    <ul class="mb-0 ps-3">
      @foreach ($errors->all() as $message)
        <li>{{ $message }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
