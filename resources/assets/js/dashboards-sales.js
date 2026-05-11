/**
 * Sales overview dashboard — uses live metrics from window.__SALES_DASHBOARD__
 */
'use strict';

(function () {
  const payload = window.__SALES_DASHBOARD__;
  if (!payload) {
    return;
  }

  const cardColor = typeof config !== 'undefined' && config.colors ? config.colors.cardColor : '#fff';
  const labelColor = typeof config !== 'undefined' && config.colors ? config.colors.textMuted : '#7987a1';
  const headingColor = typeof config !== 'undefined' && config.colors ? config.colors.headingColor : '#433c50';
  const primary = typeof config !== 'undefined' && config.colors ? config.colors.primary : '#7367f0';
  const success = typeof config !== 'undefined' && config.colors ? config.colors.success : '#28c76f';
  const warning = typeof config !== 'undefined' && config.colors ? config.colors.warning : '#ff9f43';
  const info = typeof config !== 'undefined' && config.colors ? config.colors.info : '#00cfe8';

  const labels = payload.chart_labels || [];
  const currencySym = typeof payload.currency_symbol === 'string' ? payload.currency_symbol : '';

  const visitsEl = document.querySelector('#salesVisitsAreaChart');
  if (visitsEl && typeof ApexCharts !== 'undefined') {
    new ApexCharts(visitsEl, {
      chart: {
        height: 320,
        type: 'area',
        toolbar: { show: false },
        fontFamily: 'inherit'
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      colors: [primary],
      series: [{ name: 'Visits', data: payload.visits_by_day || [] }],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.35,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      grid: { borderColor: labelColor, strokeDashArray: 4, padding: { top: 0, bottom: 0, left: 12, right: 12 } },
      xaxis: {
        categories: labels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: labelColor, fontSize: '11px' } }
      },
      yaxis: {
        labels: { style: { colors: labelColor, fontSize: '11px' } }
      },
      markers: { size: 0 },
      tooltip: { theme: 'false', x: { show: true } }
    }).render();
  }

  const barEl = document.querySelector('#salesOrdersCollectionsBar');
  if (barEl && typeof ApexCharts !== 'undefined') {
    new ApexCharts(barEl, {
      chart: { height: 220, type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
      plotOptions: {
        bar: { horizontal: false, columnWidth: '42%', borderRadius: 4, borderRadiusApplication: 'end' }
      },
      colors: [success, info],
      series: [
        { name: currencySym ? `Order (${currencySym})` : 'Order value', data: payload.orders_value_by_day || [] },
        { name: currencySym ? `Collections (${currencySym})` : 'Collections', data: payload.collections_by_day || [] }
      ],
      dataLabels: { enabled: false },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      grid: { show: false, padding: { top: -20, bottom: 0, left: 0, right: 0 } },
      xaxis: {
        categories: labels,
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: { labels: { show: false } },
      legend: { show: true, position: 'bottom', labels: { colors: '#fff' } },
      tooltip: { theme: 'false' }
    }).render();
  }

  const sparkEl = document.querySelector('#salesSamplesSpark');
  if (sparkEl && typeof ApexCharts !== 'undefined') {
    new ApexCharts(sparkEl, {
      chart: { height: 120, type: 'area', sparkline: { enabled: true }, toolbar: { show: false } },
      colors: [warning],
      stroke: { width: 2, curve: 'smooth' },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [0, 100]
        }
      },
      series: [{ data: payload.samples_by_day || [] }],
      tooltip: { enabled: true, theme: 'false' }
    }).render();
  }

  const colEl = document.querySelector('#salesSamplesColumnChart');
  if (colEl && typeof ApexCharts !== 'undefined') {
    new ApexCharts(colEl, {
      chart: { height: 280, type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
      plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
      colors: [warning],
      series: [{ name: 'Sample units', data: payload.samples_by_day || [] }],
      dataLabels: { enabled: false },
      grid: { borderColor: labelColor, strokeDashArray: 4, padding: { top: 0, bottom: 0, left: 12, right: 12 } },
      xaxis: {
        categories: labels,
        axisBorder: { show: false },
        labels: { style: { colors: labelColor, fontSize: '11px' }, rotate: -45 }
      },
      yaxis: { labels: { style: { colors: labelColor, fontSize: '11px' } } },
      tooltip: { theme: 'false' }
    }).render();
  }

  const swiperEl = document.querySelector('#sales-swiper-cards');
  if (swiperEl && typeof Swiper !== 'undefined') {
    new Swiper(swiperEl, {
      loop: true,
      autoplay: { delay: 4500, disableOnInteraction: false },
      pagination: { clickable: true, el: '.swiper-pagination' }
    });
  }

  const weekMap = window.__SALES_DASHBOARD_WEEK_MAP__;
  const mapEl = document.querySelector('#salesWeekVisitMap');
  if (weekMap && mapEl && Array.isArray(weekMap.points)) {
    const ghanaCenter = [7.9465, -1.0232];
    const fill = typeof config !== 'undefined' && config.colors ? config.colors.primary : '#7367f0';

    Promise.all([import('leaflet/dist/leaflet.css'), import('leaflet')]).then(([, leafletMod]) => {
      const L = leafletMod.default;
      const points = weekMap.points;

      const map = L.map(mapEl, { scrollWheelZoom: false }).setView(ghanaCenter, 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(map);

      const layers = [];
      for (let i = 0; i < points.length; i++) {
        const p = points[i];
        const lat = Number(p.lat);
        const lng = Number(p.lng);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
          continue;
        }
        const c = L.circleMarker([lat, lng], {
          radius: 8,
          stroke: true,
          weight: 2,
          color: '#fff',
          fillColor: fill,
          fillOpacity: 0.92
        });
        c.bindPopup(typeof p.label === 'string' ? p.label : '');
        c.addTo(map);
        layers.push(c);
      }

      if (layers.length === 1) {
        map.setView([Number(points[0].lat), Number(points[0].lng)], 12);
      } else if (layers.length > 1) {
        const g = L.featureGroup(layers);
        map.fitBounds(g.getBounds().pad(0.12));
      }

      setTimeout(() => {
        map.invalidateSize();
      }, 400);
    });
  }
})();
