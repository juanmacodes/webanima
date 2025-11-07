(function () {
  if (typeof window === 'undefined') {
    return;
  }

  var STORAGE_PREFIX = 'animaProjectsTabs::';

  var ready = function (callback) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      callback();
    } else {
      document.addEventListener('DOMContentLoaded', callback);
    }
  };

  var parseJSON = function (value, fallback) {
    if (typeof value !== 'string') {
      return fallback;
    }

    try {
      return JSON.parse(value);
    } catch (error) {
      return fallback;
    }
  };

  var buildCacheKey = function (container, payload) {
    return (
      STORAGE_PREFIX +
      (container.dataset.endpoint || '') +
      '::' +
      (payload.servicio || '') +
      '::' +
      JSON.stringify([
        payload.per_page,
        payload.orderby,
        payload.order,
        payload.year_min,
        payload.year_max,
        payload.search || '',
        payload.layout,
        payload.card,
      ])
    );
  };

  var readCache = function (key) {
    if (!('sessionStorage' in window)) {
      return null;
    }

    try {
      var raw = window.sessionStorage.getItem(key);
      if (!raw) {
        return null;
      }

      var parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') {
        return null;
      }

      if (parsed.expire && Date.now() > parsed.expire) {
        window.sessionStorage.removeItem(key);
        return null;
      }

      return parsed.value || null;
    } catch (error) {
      return null;
    }
  };

  var writeCache = function (key, value, ttl) {
    if (!('sessionStorage' in window) || ttl <= 0) {
      return;
    }

    try {
      window.sessionStorage.setItem(
        key,
        JSON.stringify({ value: value, expire: Date.now() + ttl * 1000 })
      );
    } catch (error) {
      // Fail silently when storage quota is exceeded.
    }
  };

  var buildQueryParams = function (payload) {
    var params = new URLSearchParams();

    Object.keys(payload).forEach(function (key) {
      var value = payload[key];
      if (value === null || typeof value === 'undefined' || value === '') {
        return;
      }

      if (typeof value === 'object') {
        params.set(key, JSON.stringify(value));
      } else {
        params.set(key, value);
      }
    });

    return params;
  };

  var setActiveTab = function (tabs, panels, index, focus) {
    tabs.forEach(function (tab, tabIndex) {
      var selected = tabIndex === index;
      tab.setAttribute('aria-selected', selected ? 'true' : 'false');
      tab.setAttribute('tabindex', selected ? '0' : '-1');
      tab.classList.toggle('is-active', selected);
      if (selected && focus) {
        tab.focus();
      }
    });

    panels.forEach(function (panel, panelIndex) {
      if (panelIndex === index) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', 'hidden');
      }
    });
  };

  var handleKeydown = function (event, container, tabs) {
    var orientation = container.dataset.tabsPosition === 'left' ? 'vertical' : 'horizontal';
    var currentIndex = tabs.indexOf(event.currentTarget);
    var targetIndex = null;

    switch (event.key) {
      case 'ArrowRight':
        if (orientation === 'horizontal') {
          targetIndex = (currentIndex + 1) % tabs.length;
        }
        break;
      case 'ArrowLeft':
        if (orientation === 'horizontal') {
          targetIndex = (currentIndex - 1 + tabs.length) % tabs.length;
        }
        break;
      case 'ArrowDown':
        if (orientation === 'vertical') {
          targetIndex = (currentIndex + 1) % tabs.length;
        }
        break;
      case 'ArrowUp':
        if (orientation === 'vertical') {
          targetIndex = (currentIndex - 1 + tabs.length) % tabs.length;
        }
        break;
      case 'Home':
        targetIndex = 0;
        break;
      case 'End':
        targetIndex = tabs.length - 1;
        break;
      default:
        return;
    }

    if (typeof targetIndex === 'number' && targetIndex >= 0) {
      event.preventDefault();
      activateTab(container, targetIndex, true);
    }
  };

  var ensureLayout = function (panel) {
    if (!panel) {
      return;
    }

    if (panel.dataset.layout === 'carousel') {
      initCarousel(panel);
    }
  };

  var parseColumns = function (panel) {
    return parseJSON(panel.dataset.columns || '', {});
  };

  var parseCarousel = function (panel) {
    return parseJSON(panel.dataset.carousel || '', {});
  };

  var parseCardSettings = function (panel) {
    return parseJSON(panel.dataset.card || '', {});
  };

  var loadPanel = function (container, panel) {
    var inner = panel.querySelector('.anima-tabs__panel-inner');
    if (!inner) {
      return Promise.resolve();
    }

    var payload = parseJSON(panel.dataset.query || '', {});
    payload.card = payload.card || parseCardSettings(panel);
    payload.columns = payload.columns || parseColumns(panel);
    payload.carousel = payload.carousel || parseCarousel(panel);

    var cacheTtl = parseInt(container.dataset.cacheTtl || '0', 10) || 0;
    var cacheKey = buildCacheKey(container, payload);

    var cached = cacheTtl > 0 ? readCache(cacheKey) : null;
    if (cached && typeof cached.html === 'string') {
      inner.innerHTML = cached.html;
      panel.dataset.loaded = '1';
      ensureLayout(panel);
      return Promise.resolve();
    }

    var endpoint = container.dataset.endpoint;
    if (!endpoint) {
      return Promise.resolve();
    }

    inner.classList.add('is-loading');

    var params = buildQueryParams(payload);

    return fetch(endpoint + '?' + params.toString(), {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('request_failed');
        }
        return response.json();
      })
      .then(function (data) {
        if (!data || typeof data.html !== 'string') {
          return;
        }

        inner.innerHTML = data.html;
        panel.dataset.loaded = '1';
        panel.dataset.layout = data.layout || panel.dataset.layout || 'grid';

        if (cacheTtl > 0) {
          writeCache(cacheKey, { html: data.html, layout: data.layout || 'grid' }, cacheTtl);
        }

        ensureLayout(panel);
      })
      .catch(function () {
        inner.innerHTML = '<div class="an-empty" role="status">No se pudo cargar este servicio.</div>';
        panel.dataset.loaded = '1';
      })
      .finally(function () {
        inner.classList.remove('is-loading');
      });
  };

  var activateTab = function (container, index, focus) {
    var state = container.__animaTabsState;
    if (!state) {
      return;
    }

    var tabs = state.tabs;
    var panels = state.panels;

    if (!tabs[index] || !panels[index]) {
      return;
    }

    setActiveTab(tabs, panels, index, focus);

    var panel = panels[index];

    if (container.dataset.ajax !== '1') {
      ensureLayout(panel);
      return;
    }

    if (panel.dataset.loaded === '1') {
      ensureLayout(panel);
      maybePrefetch(container, index);
      return;
    }

    loadPanel(container, panel).then(function () {
      maybePrefetch(container, index);
    });
  };

  var maybePrefetch = function (container, currentIndex) {
    if (container.dataset.prefetch !== '1') {
      return;
    }

    var state = container.__animaTabsState;
    if (!state) {
      return;
    }

    var panels = state.panels;
    var nextIndex = currentIndex + 1;
    if (!panels[nextIndex] || panels[nextIndex].dataset.loaded === '1') {
      return;
    }

    loadPanel(container, panels[nextIndex]);
  };

  var initCarousel = function (panel) {
    var wrapper = panel.querySelector('.anima-carousel.swiper');
    if (!wrapper || wrapper.dataset.swiperInitialized === '1') {
      return;
    }

    var carousel = parseCarousel(panel);
    var columns = parseColumns(panel);
    var gap = 24;

    if (columns && columns.gap) {
      if (typeof columns.gap === 'string' && columns.gap.endsWith('px')) {
        gap = parseFloat(columns.gap);
      } else if (typeof columns.gap === 'object' && columns.gap.size) {
        gap = parseFloat(columns.gap.size);
      }
    }

    var options = {
      slidesPerView: Math.max(1, carousel.desktop || 3),
      spaceBetween: isNaN(gap) ? 24 : gap,
      loop: !!carousel.loop,
      speed: carousel.speed || 600,
      watchSlidesProgress: true,
      navigation: false,
      pagination: false,
      breakpoints: {
        0: {
          slidesPerView: Math.max(1, carousel.mobile || carousel.tablet || 1),
        },
        768: {
          slidesPerView: Math.max(1, carousel.tablet || carousel.desktop || 2),
        },
        1024: {
          slidesPerView: Math.max(1, carousel.desktop || 3),
        },
      },
    };

    if (carousel.autoplay) {
      options.autoplay = { delay: 3500, disableOnInteraction: false };
    }

    if (carousel.navigation === 'arrows' || carousel.navigation === 'arrows-dots' || !carousel.navigation) {
      options.navigation = {
        nextEl: wrapper.querySelector('.swiper-button-next'),
        prevEl: wrapper.querySelector('.swiper-button-prev'),
      };
    }

    if (carousel.navigation === 'dots' || carousel.navigation === 'arrows-dots') {
      options.pagination = {
        el: wrapper.querySelector('.swiper-pagination'),
        clickable: true,
      };
    }

    var initializer = null;
    if (window.elementorFrontend && window.elementorFrontend.utils && window.elementorFrontend.utils.swiper) {
      initializer = window.elementorFrontend.utils.swiper;
    }

    if (initializer) {
      initializer(wrapper, options);
      wrapper.dataset.swiperInitialized = '1';
      return;
    }

    if (window.Swiper) {
      // eslint-disable-next-line no-new
      new window.Swiper(wrapper, options);
      wrapper.dataset.swiperInitialized = '1';
    }
  };

  ready(function () {
    var containers = Array.prototype.slice.call(
      document.querySelectorAll('.anima-projects-tabs')
    );

    containers.forEach(function (container) {
      var tabs = Array.prototype.slice.call(
        container.querySelectorAll('.anima-tabs__button')
      );
      var panels = Array.prototype.slice.call(
        container.querySelectorAll('.anima-tabs__panel')
      );

      if (!tabs.length || !panels.length) {
        return;
      }

      container.classList.toggle(
        'anima-projects-tabs--vertical',
        container.dataset.tabsPosition === 'left'
      );

      tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function (event) {
          event.preventDefault();
          activateTab(container, index, false);
        });

        tab.addEventListener('keydown', function (event) {
          handleKeydown(event, container, tabs);
        });
      });

      container.__animaTabsState = {
        tabs: tabs,
        panels: panels,
      };

      if (container.dataset.ajax === '1') {
        panels.forEach(function (panel, index) {
          panel.dataset.loaded = index === 0 ? '1' : panel.dataset.loaded || '0';
        });
      }

      ensureLayout(panels[0]);
    });
  });
})();
