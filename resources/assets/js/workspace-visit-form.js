'use strict';

function destroySelect2OnSelect(nativeSelect) {
  const $ = window.jQuery;
  if (!$?.fn?.select2) {
    return;
  }
  const $el = $(nativeSelect);
  if ($el.hasClass('select2-hidden-accessible')) {
    $el.select2('destroy');
  }
}

function destroyVisitSelect2(context) {
  const $ = window.jQuery;
  if (!$?.fn?.select2) {
    return;
  }
  $(context)
    .find('select.select2')
    .each(function () {
      destroySelect2OnSelect(this);
    });
}

function initSelect2OnSelect(nativeSelect) {
  const $ = window.jQuery;
  if (!$?.fn?.select2) {
    return;
  }
  const $el = $(nativeSelect);
  if ($el.hasClass('select2-hidden-accessible')) {
    return;
  }
  const collapse = nativeSelect.closest('.collapse');
  if (collapse && !collapse.classList.contains('show')) {
    return;
  }
  const $parent = $el.parent();
  if (!$parent.hasClass('position-relative')) {
    $el.wrap('<div class="position-relative"></div>');
  }
  const $wrap = $el.parent();
  const ph = $el.attr('data-placeholder');
  $el.select2({
    placeholder: ph || undefined,
    allowClear: $el.attr('data-allow-clear') === 'true',
    width: '100%',
    dropdownParent: $wrap,
  });
}

function initVisitSelect2InScope(scope) {
  scope.querySelectorAll('select.select2').forEach((sel) => {
    if (sel.id === 'visit-contact-person') {
      return;
    }
    initSelect2OnSelect(sel);
  });
}

function countFilledOrderLines(root) {
  const container = root.querySelector('[data-rows="order_lines"]');
  if (!container) {
    return 0;
  }
  let n = 0;
  container.querySelectorAll('[data-repeater-row]').forEach((row) => {
    const pid = row.querySelector('select[name*="[product_id]"]')?.value;
    const qty = parseInt(String(row.querySelector('input[name*="[quantity]"]')?.value || '0'), 10);
    if (pid && qty >= 1) {
      n += 1;
    }
  });
  return n;
}

function countFilledSamples(root) {
  const container = root.querySelector('[data-rows="samples"]');
  if (!container) {
    return 0;
  }
  let n = 0;
  container.querySelectorAll('[data-repeater-row]').forEach((row) => {
    const pid = row.querySelector('select[name*="[product_id]"]')?.value;
    const qty = parseInt(String(row.querySelector('input[name*="[quantity]"]')?.value || '0'), 10);
    if (pid && qty >= 1) {
      n += 1;
    }
  });
  return n;
}

function countFilledCollections(root) {
  const container = root.querySelector('[data-rows="collections"]');
  if (!container) {
    return 0;
  }
  let n = 0;
  container.querySelectorAll('[data-repeater-row]').forEach((row) => {
    const raw = row.querySelector('input[name*="[amount]"]')?.value;
    if (raw === undefined || raw === null || String(raw).trim() === '') {
      return;
    }
    const v = parseFloat(String(raw));
    if (!Number.isNaN(v)) {
      n += 1;
    }
  });
  return n;
}

function sectionUnitLabel(root, sectionKey) {
  if (sectionKey === 'order_lines') {
    return root.dataset.orderLineUnit || '';
  }
  if (sectionKey === 'samples') {
    return root.dataset.sampleUnit || '';
  }
  if (sectionKey === 'collections') {
    return root.dataset.collectionUnit || '';
  }
  return '';
}

function refreshVisitSectionSummaries(root) {
  const counts = {
    order_lines: countFilledOrderLines(root),
    samples: countFilledSamples(root),
    collections: countFilledCollections(root),
  };

  Object.keys(counts).forEach((sectionKey) => {
    const n = counts[sectionKey];
    const toggle = root.querySelector(`[data-visit-section-toggle="${sectionKey}"]`);
    if (!toggle) {
      return;
    }
    const collapsed = toggle.classList.contains('collapsed');
    const badge = root.querySelector(`[data-visit-section-count="${sectionKey}"]`);
    const hint = root.querySelector(`[data-visit-section-hint="${sectionKey}"]`);
    const filled = root.querySelector(`[data-visit-section-filled="${sectionKey}"]`);
    const unit = sectionUnitLabel(root, sectionKey);

    if (n > 0) {
      toggle.classList.add('visit-section-has-data');
    } else {
      toggle.classList.remove('visit-section-has-data');
    }

    if (badge) {
      badge.textContent = String(n);
      badge.classList.toggle('d-none', n === 0);
    }

    if (hint && filled) {
      if (n === 0) {
        hint.classList.remove('d-none');
        filled.classList.add('d-none');
      } else if (collapsed) {
        hint.classList.add('d-none');
        filled.classList.remove('d-none');
        filled.textContent = unit ? `${n} ${unit}` : String(n);
      } else {
        hint.classList.add('d-none');
        filled.classList.add('d-none');
      }
    }
  });
}

function bindWorkspaceContactPhoneInput(el) {
  if (!el || el.dataset.workspaceLocalPhoneBound === '1') {
    return;
  }
  el.dataset.workspaceLocalPhoneBound = '1';
  el.setAttribute('inputmode', 'numeric');
  el.setAttribute('maxlength', '10');
  const sanitize = () => {
    const next = el.value.replace(/\D/g, '').slice(0, 10);
    if (el.value !== next) {
      el.value = next;
    }
  };
  el.addEventListener('input', sanitize);
  el.addEventListener('paste', () => {
    window.requestAnimationFrame(sanitize);
  });
}

function setupVisitContactPerson(root) {
  function visitCustomerEl() {
    return root.querySelector('#visit-customer');
  }

  const contactSelect = root.querySelector('#visit-contact-person');
  const contactRow = root.querySelector('[data-visit-contact-row]');
  const modalEl = document.getElementById('workspaceVisitNewContactModal');
  const openModalBtn = root.querySelector('[data-open-new-contact-modal]');
  const saveModalBtn = modalEl?.querySelector('[data-save-new-contact]');
  const errEl = modalEl?.querySelector('[data-new-contact-error]');
  const nameInput = document.getElementById('workspace-new-contact-name');
  const phoneInput = document.getElementById('workspace-new-contact-phone');
  const positionInput = document.getElementById('workspace-new-contact-position');

  bindWorkspaceContactPhoneInput(phoneInput);

  let map = {};
  try {
    map = JSON.parse(root.getAttribute('data-customer-contacts') || '{}');
  } catch (_) {
    map = {};
  }

  let fallbackBackdropEl = null;

  function placeholderText(customerId) {
    if (!customerId) {
      return root.getAttribute('data-placeholder-no-customer') || '';
    }
    return root.getAttribute('data-placeholder-with-customer') || '';
  }

  function mergeContactIntoMap(customerId, entry) {
    const key = String(customerId);
    if (!map[key]) {
      map[key] = [];
    }
    map[key] = map[key].filter((r) => String(r.id) !== String(entry.id));
    map[key].push(entry);
  }

  function bindFallbackModalDismiss() {
    if (!modalEl || modalEl.dataset.visitFallbackDismissBound === '1' || window.bootstrap?.Modal) {
      return;
    }
    modalEl.dataset.visitFallbackDismissBound = '1';
    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
      btn.addEventListener('click', () => {
        hideContactModal();
      });
    });
  }

  function showContactModal() {
    if (!modalEl) {
      return;
    }
    clearModalError();
    const BsModal = window.bootstrap?.Modal;
    if (BsModal) {
      BsModal.getOrCreateInstance(modalEl).show();
      return;
    }
    bindFallbackModalDismiss();
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
    modalEl.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    if (!fallbackBackdropEl) {
      fallbackBackdropEl = document.createElement('div');
      fallbackBackdropEl.className = 'modal-backdrop fade show';
      document.body.appendChild(fallbackBackdropEl);
    }
    fallbackBackdropEl?.classList.add('show');
    window.setTimeout(() => nameInput?.focus(), 50);
  }

  function hideContactModal() {
    if (!modalEl) {
      return;
    }
    const Modal = window.bootstrap?.Modal;
    const inst = Modal?.getInstance(modalEl);
    if (inst) {
      inst.hide();
    } else {
      modalEl.classList.remove('show');
      modalEl.style.display = 'none';
      modalEl.setAttribute('aria-hidden', 'true');
      modalEl.removeAttribute('aria-modal');
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      fallbackBackdropEl?.remove();
      fallbackBackdropEl = null;
    }
  }

  function rebuildContactOptions() {
    if (!contactSelect) {
      return;
    }
    const cust = visitCustomerEl();
    if (!cust) {
      return;
    }
    const cid = String(cust.value || '');
    if (!cid) {
      return;
    }
    const rows = map[cid] || [];
    const previous = contactSelect.value;
    destroySelect2OnSelect(contactSelect);
    const ph = placeholderText(cid);
    contactSelect.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = ph;
    contactSelect.appendChild(empty);
    rows.forEach((row) => {
      const opt = document.createElement('option');
      opt.value = String(row.id);
      opt.textContent = row.label;
      contactSelect.appendChild(opt);
    });
    if (previous && [...contactSelect.options].some((o) => o.value === previous)) {
      contactSelect.value = previous;
    }
    initSelect2OnSelect(contactSelect);
  }

  /** @type {string} */
  let lastCustomerId = String(visitCustomerEl()?.value || '');

  function syncContactRowVisibility() {
    const cust = visitCustomerEl();
    const cid = String(cust?.value || '');
    const show = Boolean(cid);
    if (contactRow) {
      contactRow.classList.remove('d-none');
      if (!show) {
        contactRow.classList.add('d-none');
      }
    }
    if (contactSelect) {
      contactSelect.required = show;
      if (!show) {
        destroySelect2OnSelect(contactSelect);
        contactSelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholderText('');
        contactSelect.appendChild(opt);
        contactSelect.value = '';
      } else {
        rebuildContactOptions();
      }
    }
  }

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function showModalError(message) {
    if (!errEl) {
      return;
    }
    errEl.textContent = message;
    errEl.classList.remove('d-none');
  }

  function clearModalError() {
    if (!errEl) {
      return;
    }
    errEl.textContent = '';
    errEl.classList.add('d-none');
  }

  modalEl?.addEventListener('shown.bs.modal', () => {
    nameInput?.focus();
  });

  modalEl?.addEventListener('hidden.bs.modal', () => {
    if (fallbackBackdropEl) {
      fallbackBackdropEl.remove();
      fallbackBackdropEl = null;
    }
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
  });

  openModalBtn?.addEventListener('click', () => {
    const cid = visitCustomerEl()?.value;
    if (!cid) {
      window.alert(String(root.getAttribute('data-alert-select-customer') || 'Select a customer first.'));
      return;
    }
    showContactModal();
  });

  saveModalBtn?.addEventListener('click', () => {
    const cid = visitCustomerEl()?.value;
    if (!cid) {
      return;
    }
    clearModalError();
    const base = (root.getAttribute('data-workspace-customers-base') || '').replace(/\/$/, '');
    const url = `${base}/${encodeURIComponent(cid)}/contacts`;
    const payload = {
      name: (nameInput?.value || '').trim(),
      phone: (phoneInput?.value || '').trim(),
      position: (positionInput?.value || '').trim(),
    };
    fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    })
      .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          const msg =
            (data.errors && typeof data.errors === 'object' && Object.values(data.errors).flat().join(' ')) ||
            data.message ||
            `Request failed (${res.status})`;
          throw new Error(msg);
        }
        return data;
      })
      .then((data) => {
        const entry = data.contact;
        if (!entry?.id) {
          throw new Error('Invalid response');
        }
        mergeContactIntoMap(cid, entry);
        rebuildContactOptions();
        contactSelect.value = String(entry.id);
        const $ = window.jQuery;
        if ($) {
          $(contactSelect).val(String(entry.id)).trigger('change');
        }
        if (nameInput) {
          nameInput.value = '';
        }
        if (phoneInput) {
          phoneInput.value = '';
        }
        if (positionInput) {
          positionInput.value = '';
        }
        hideContactModal();
      })
      .catch((e) => {
        showModalError(e.message || String(e));
      });
  });

  function onCustomerChoiceChanged() {
    const cust = visitCustomerEl();
    const cid = String(cust?.value || '');
    if (cid !== lastCustomerId) {
      lastCustomerId = cid;
      if (contactSelect) {
        contactSelect.value = '';
      }
    }
    syncContactRowVisibility();
  }

  syncContactRowVisibility();

  return onCustomerChoiceChanged;
}

function workspaceVisitFormSetup(root) {
  if (!root) {
    return;
  }

  let refreshTimer;
  const scheduleRefresh = () => {
    window.clearTimeout(refreshTimer);
    refreshTimer = window.setTimeout(() => {
      refreshVisitSectionSummaries(root);
    }, 30);
  };

  const refreshCustomerContactUi = setupVisitContactPerson(root);

  initVisitSelect2InScope(root);

  const $jqRoot = window.jQuery;
  if ($jqRoot && refreshCustomerContactUi) {
    $jqRoot(root).off('.visitCustomerSync').on(
      'change.visitCustomerSync select2:select.visitCustomerSync select2:clear.visitCustomerSync',
      '#visit-customer',
      refreshCustomerContactUi,
    );
  }

  refreshCustomerContactUi?.();
  window.requestAnimationFrame(() => refreshCustomerContactUi?.());

  root.querySelectorAll('.collapse').forEach((el) => {
    el.addEventListener('shown.bs.collapse', () => {
      initVisitSelect2InScope(el);
      scheduleRefresh();
    });
    el.addEventListener('hidden.bs.collapse', () => {
      destroyVisitSelect2(el);
      scheduleRefresh();
    });
  });

  root.addEventListener('input', (e) => {
    if (root.contains(e.target)) {
      scheduleRefresh();
    }
  });
  root.addEventListener('change', (e) => {
    if (root.contains(e.target)) {
      scheduleRefresh();
    }
  });

  const $ = window.jQuery;
  if ($) {
    $(root).on('select2:select select2:clear', 'select.select2', scheduleRefresh);
  }

  root.querySelectorAll('[data-add-row]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.dataset.addRow;
      const tmpl = root.querySelector(`template[data-template="${key}"]`);
      const container = root.querySelector(`[data-rows="${key}"]`);
      if (!tmpl || !container) {
        return;
      }
      const ix = parseInt(container.dataset.nextIndex || '0', 10);
      const html = tmpl.innerHTML.replace(/__INDEX__/g, String(ix));
      container.insertAdjacentHTML('beforeend', html);
      container.dataset.nextIndex = String(ix + 1);
      const newRow = container.lastElementChild;
      if (newRow?.matches('[data-repeater-row]')) {
        initVisitSelect2InScope(newRow);
      }
      scheduleRefresh();
    });
  });

  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-row]');
    if (!btn || !root.contains(btn)) {
      return;
    }
    const row = btn.closest('[data-repeater-row]');
    const container = row?.closest('[data-rows]');
    if (!container || !row) {
      return;
    }
    const rows = container.querySelectorAll('[data-repeater-row]');
    if (rows.length <= 1) {
      return;
    }
    row.querySelectorAll('select.select2').forEach(destroySelect2OnSelect);
    row.remove();
    scheduleRefresh();
  });

  const latInput = root.querySelector('[name="visit_latitude"]');
  const lngInput = root.querySelector('[name="visit_longitude"]');
  const statusEl = root.querySelector('[data-geo-status]');

  const hasCoordinates = () => {
    if (!latInput || !lngInput) {
      return false;
    }
    return latInput.value.trim() !== '' && lngInput.value.trim() !== '';
  };

  const defaultGeoOptions = { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 };

  const captureLocation = ({ silentFailure, geoOptions }) => {
    if (!navigator.geolocation) {
      if (!silentFailure) {
        window.alert('Geolocation is not supported by this browser.');
      }
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        if (latInput) {
          latInput.value = String(pos.coords.latitude);
        }
        if (lngInput) {
          lngInput.value = String(pos.coords.longitude);
        }
        if (statusEl) {
          statusEl.textContent = `Captured: ${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
        }
      },
      () => {
        if (!silentFailure) {
          window.alert('Could not read location. Allow location access and try again.');
        }
      },
      { ...defaultGeoOptions, ...geoOptions },
    );
  };

  root.querySelector('[data-geo-button]')?.addEventListener('click', () => {
    captureLocation({ silentFailure: false });
  });

  // Fill coordinates without requiring a tap; deferred so Select2 / layout settle first.
  // Softer options than manual capture — often succeeds sooner on desktop Safari / Wi‑Fi only.
  if (!hasCoordinates()) {
    window.setTimeout(() => {
      if (!hasCoordinates()) {
        captureLocation({
          silentFailure: true,
          geoOptions: { enableHighAccuracy: false, timeout: 20000, maximumAge: 120000 },
        });
      }
    }, 500);
  }

  refreshVisitSectionSummaries(root);
}

window.workspaceVisitFormSetup = workspaceVisitFormSetup;

function bootWorkspaceVisitForm() {
  const root = document.querySelector('[data-workspace-visit-form]');
  if (root) {
    workspaceVisitFormSetup(root);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootWorkspaceVisitForm);
} else {
  bootWorkspaceVisitForm();
}
