(function () {
  'use strict';

  const settings = window.ANIMA_VARS || {};
  if (!('IntersectionObserver' in window)) {
    document.querySelectorAll('.animate-on-scroll').forEach((element) => {
      element.classList.add('is-visible');
    });
    return;
  }

  if (settings.enableAnimations === false) {
    document.querySelectorAll('.animate-on-scroll').forEach((element) => {
      element.classList.add('is-visible');
    });
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.2,
    rootMargin: '0px 0px -10% 0px'
  });

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.animate-on-scroll').forEach((element) => {
      observer.observe(element);
    });
  });
})();
