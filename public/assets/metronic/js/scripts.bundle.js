(function () {
  var queue = [
    '/assets/metronic/js/core.bundle.js',
    '/assets/metronic/vendors/ktui/ktui.min.js',
    '/assets/metronic/vendors/apexcharts/apexcharts.min.js',
    '/assets/metronic/js/widgets/general.js'
  ];

  function initComponents() {
    if (typeof window.KTComponents !== 'undefined' && typeof window.KTComponents.init === 'function') {
      window.KTComponents.init();
    }
  }

  function loadNext(index) {
    if (index >= queue.length) {
      initComponents();
      return;
    }

    var s = document.createElement('script');
    s.src = queue[index];
    s.async = false;
    s.onload = function () { loadNext(index + 1); };
    document.body.appendChild(s);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { loadNext(0); });
  } else {
    loadNext(0);
  }
})();
