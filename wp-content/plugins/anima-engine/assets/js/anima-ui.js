(function () {
  if (typeof window === 'undefined') {
    return;
  }

  var onReady = function (callback) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      callback();
    } else {
      document.addEventListener('DOMContentLoaded', callback);
    }
  };

  onReady(function () {
    var revealTargets = document.querySelectorAll('[data-anima-reveal]');

    if (!('IntersectionObserver' in window)) {
      revealTargets.forEach(function (element) {
        element.classList.add('is-visible');
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.15,
      }
    );

    revealTargets.forEach(function (element) {
      observer.observe(element);
    });
  });
})();
