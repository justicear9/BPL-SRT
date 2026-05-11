'use strict';

document.addEventListener('DOMContentLoaded', function () {
  if (typeof window.DataTable === 'undefined') {
    return;
  }

  document.querySelectorAll('table.datatable-workspace').forEach(function (el) {
    if (el.dataset.workspaceDtInit === '1') {
      return;
    }
    el.dataset.workspaceDtInit = '1';

    if (!el.closest('.table-responsive')) {
      const wrapper = document.createElement('div');
      wrapper.className = 'table-responsive';
      el.parentNode.insertBefore(wrapper, el);
      wrapper.appendChild(el);
    }

    new window.DataTable(el, {
      responsive: true,
      scrollX: true,
      paging: false,
      searching: true,
      info: false,
      order: []
    });
  });
});
