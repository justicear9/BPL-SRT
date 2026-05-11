{{-- Expects $salesReps (iterable). --}}
@php($salesReps = $salesReps ?? collect())

<form method="get" class="row g-3 mb-0 align-items-end">
  <div class="col-sm-6 col-md-3 col-lg-2">
    <label class="form-label mb-1" for="report-from">{{ __('From') }}</label>
    <input type="date" name="from" id="report-from" value="{{ request('from') }}" class="form-control">
  </div>
  <div class="col-sm-6 col-md-3 col-lg-2">
    <label class="form-label mb-1" for="report-to">{{ __('To') }}</label>
    <input type="date" name="to" id="report-to" value="{{ request('to') }}" class="form-control">
  </div>
  @if ($salesReps->isNotEmpty())
    <div class="col-sm-6 col-md-4 col-lg-3">
      <label class="form-label mb-1" for="report-rep">{{ __('Sales rep') }}</label>
      <select name="user_id" id="report-rep" class="form-select">
        <option value="">{{ __('All reps') }}</option>
        @foreach ($salesReps as $rep)
          <option value="{{ $rep->id }}" @selected((string) request('user_id') === (string) $rep->id)>{{ $rep->name }}</option>
        @endforeach
      </select>
    </div>
  @endif
  <div class="col-sm-6 col-md-auto d-flex flex-wrap gap-2 pb-1">
    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
    <a href="{{ url()->current() }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
  </div>
</form>
