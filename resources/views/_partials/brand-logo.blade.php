@php
  $alt = config('variables.templateName') ?: 'Bedita Pharmaceuticals';
@endphp
<img src="{{ asset(config('branding.logo_full')) }}" alt="{{ $alt }}" width="180" height="40"
  class="app-brand-logo-img d-inline-block" />
