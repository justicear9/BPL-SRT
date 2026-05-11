import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import path from 'path';
import iconsPlugin from './vite.icons.plugin.js';

// Vendor JS files used by the authenticated workspace layout
const vendorJsFiles = [
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/js/helpers.js',
  'resources/assets/vendor/js/menu.js',
];

// Libs used by currently routed pages (dashboard/workspace/reports)
const activeLibJsFiles = [
  'resources/assets/vendor/libs/@algolia/autocomplete-js.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js',
  'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/swiper/swiper.js',
];

const activeLibScssFiles = [
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/node-waves/node-waves.scss',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
];

// Processing Core, Themes & Pages Scss Files
const CoreScssFiles = [
  'resources/assets/vendor/scss/core.scss',
  'resources/assets/vendor/scss/pages/cards-advance.scss',
];

// Processing Fonts Scss & JS Files
const FontsScssFiles = [];
const FontsJsFiles = [];
const FontsCssFiles = ['resources/assets/vendor/fonts/iconify/iconify.css'];

const pageJsFiles = [
  'resources/assets/js/config.js',
  'resources/assets/js/dashboards-sales.js',
  'resources/assets/js/main.js',
  'resources/assets/js/workspace-datatables.js',
  'resources/assets/js/workspace-visit-form.js',
  'resources/assets/js/workspace-visit-modal.js',
];

// Processing Window Assignment for Libs like jKanban, pdfMake
function libsWindowAssignment() {
  return {
    name: 'libsWindowAssignment',

    transform(src, id) {
      if (id.includes('jkanban.js')) {
        return src.replace('this.jKanban', 'window.jKanban');
      } else if (id.includes('vfs_fonts')) {
        return src.replaceAll('this.pdfMake', 'window.pdfMake');
      }
    }
  };
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  let base = '/build/';
  try {
    const appUrl = env.APP_URL ? new URL(env.APP_URL) : null;
    const pathPrefix = appUrl ? appUrl.pathname.replace(/\/$/, '') : '';
    if (pathPrefix && pathPrefix !== '/') {
      base = `${pathPrefix}/build/`;
    }
  } catch {
    // keep default /build/ when APP_URL is not a valid URL
  }

  return {
    base,
    plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/assets/css/demo.css',
        'resources/js/app.js',
        ...pageJsFiles,
        ...vendorJsFiles,
        ...activeLibJsFiles,
        ...CoreScssFiles,
        ...activeLibScssFiles,
        ...FontsScssFiles,
        ...FontsJsFiles,
        ...FontsCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment(),
    iconsPlugin()
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'resources')
      }
    },
    json: {
      stringify: true // Helps with JSON import compatibility
    },
    build: {
      commonjsOptions: {
        include: [/node_modules/] // Helps with importing CommonJS modules
      }
    }
  };
});
