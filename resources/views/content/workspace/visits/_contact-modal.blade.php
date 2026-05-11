<div class="modal fade" id="workspaceVisitNewContactModal" tabindex="-1" aria-labelledby="workspaceVisitNewContactModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title" id="workspaceVisitNewContactModalLabel">{{ __('New contact person') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body">
        <p class="text-body-secondary small mb-3">{{ __('Saved on this customer for future visits.') }}</p>
        <div class="mb-3">
          <label class="form-label" for="workspace-new-contact-name">{{ __('Name') }}</label>
          <input type="text" class="form-control" id="workspace-new-contact-name" maxlength="255" autocomplete="name">
        </div>
        <div class="mb-3">
          <label class="form-label" for="workspace-new-contact-phone">{{ __('Phone number') }}</label>
          <input type="text" class="form-control" id="workspace-new-contact-phone" maxlength="10" inputmode="numeric"
            autocomplete="tel-national" placeholder="0541234567" pattern="0[0-9]{9}"
            title="{{ __('10 digits, numbers only, starting with 0') }}">
          <div class="form-text">{{ __('10 digits, numbers only, starting with 0.') }}</div>
        </div>
        <div class="mb-0">
          <label class="form-label" for="workspace-new-contact-position">{{ __('Position') }}</label>
          <input type="text" class="form-control" id="workspace-new-contact-position" maxlength="255" autocomplete="organization-title">
        </div>
        <div class="alert alert-danger mt-3 mb-0 d-none" role="alert" data-new-contact-error></div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-primary" data-save-new-contact>{{ __('Save contact') }}</button>
      </div>
    </div>
  </div>
</div>
