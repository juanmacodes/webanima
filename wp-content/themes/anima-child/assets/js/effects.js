(function ($) {
  $(function () {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const header = $('.site-header');
    if (header.length) {
      let lastScroll = 0;
      $(window).on('scroll', function () {
        const current = window.scrollY;
        header.toggleClass('is-scrolled', current > 32);
        if (current > lastScroll && current > 120) {
          header.addClass('is-hidden');
        } else {
          header.removeClass('is-hidden');
        }
        lastScroll = current;
      });
    }

    const hero = document.querySelector('.anima-hero');
    const pointerFine = window.matchMedia('(pointer: fine)').matches;
    if (hero && pointerFine && !prefersReducedMotion) {
      const maxX = 28;
      const maxY = 22;
      const moveHero = (event) => {
        const rect = hero.getBoundingClientRect();
        const offsetX = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
        const offsetY = ((event.clientY - rect.top) / rect.height - 0.5) * 2;
        hero.style.setProperty('--hero-shift-x', `${Math.round(offsetX * maxX)}px`);
        hero.style.setProperty('--hero-shift-y', `${Math.round(offsetY * maxY)}px`);
      };

      hero.addEventListener('pointermove', moveHero);
      hero.addEventListener('pointerleave', () => {
        hero.style.setProperty('--hero-shift-x', '0px');
        hero.style.setProperty('--hero-shift-y', '0px');
      });
    }

    document.querySelectorAll('.anima-logo-marquee').forEach((marquee) => {
      const track = marquee.querySelector('.anima-logo-marquee__track');
      if (!track) {
        return;
      }

      if (!prefersReducedMotion && !track.dataset.duplicated) {
        const clones = Array.from(track.children).map((child) => child.cloneNode(true));
        clones.forEach((clone) => track.appendChild(clone));
        track.dataset.duplicated = 'true';
      }

      if (prefersReducedMotion) {
        track.style.animation = 'none';
      }
    });

    document.querySelectorAll('[data-anima-hotspot]').forEach((hotspot) => {
      hotspot.addEventListener('click', () => {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: 'select_content',
          content_type: 'hotspot',
          item_id: hotspot.getAttribute('data-anima-hotspot'),
        });
      });
    });

    const lowSpecToggle = document.querySelector('[data-anima-low-spec]');
    if (lowSpecToggle) {
      lowSpecToggle.addEventListener('change', (event) => {
        document.body.classList.toggle('is-low-spec', event.target.checked);
      });
    }
  });
})(jQuery);
