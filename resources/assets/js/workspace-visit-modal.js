'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('workspaceVisitEditModal');
  const modalBody = modalEl?.querySelector('[data-visit-modal-body]');
  if (!modalEl || !modalBody) {
    return;
  }

  modalEl.addEventListener('hidden.bs.modal', () => {
    modalBody.innerHTML = '';
  });

  document.querySelectorAll('[data-open-visit-modal]').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();
      const url = btn.getAttribute('data-modal-url');
      if (!url) {
        return;
      }

      modalBody.innerHTML =
        '<div class="text-center py-5"><span class="spinner-border text-primary" role="status"></span></div>';
      const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();

      try {
        const res = await fetch(url, {
          headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        if (!res.ok) {
          modalBody.innerHTML =
            '<div class="alert alert-danger m-3">' +
            (res.status === 403 ? 'Forbidden.' : 'Could not load visit.') +
            '</div>';
          return;
        }
        modalBody.innerHTML = await res.text();
      } catch {
        modalBody.innerHTML = '<div class="alert alert-danger m-3">Network error.</div>';
      }
    });
  });
});
