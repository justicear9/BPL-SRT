import './bootstrap';
/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);

function registerPwaServiceWorker() {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  window.addEventListener('load', () => {
    const baseUrl = document.documentElement?.dataset?.baseUrl || window.location.origin;
    const normalizedBase = `${baseUrl}`.replace(/\/$/, '');
    const swUrl = `${normalizedBase}/sw.js`;

    navigator.serviceWorker.register(swUrl).catch(() => {
      // Keep failures silent so app behavior is unaffected.
    });
  });
}

registerPwaServiceWorker();
