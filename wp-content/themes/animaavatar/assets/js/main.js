(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.nav-toggle');
        var menuContainer = document.getElementById('primary-menu-container');

        if (toggle && menuContainer) {
            var mq = window.matchMedia('(min-width: 961px)');

            var updateMenu = function (expanded) {
                toggle.setAttribute('aria-expanded', String(expanded));
                menuContainer.classList.toggle('is-open', expanded);
                menuContainer.setAttribute('aria-hidden', expanded ? 'false' : 'true');
            };

            var syncWithMedia = function (event) {
                if (event.matches) {
                    menuContainer.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                    menuContainer.setAttribute('aria-hidden', 'false');
                } else {
                    updateMenu(false);
                }
            };

            syncWithMedia(mq);

            if (typeof mq.addEventListener === 'function') {
                mq.addEventListener('change', syncWithMedia);
            } else if (typeof mq.addListener === 'function') {
                mq.addListener(syncWithMedia);
            }

            toggle.addEventListener('click', function () {
                var isExpanded = this.getAttribute('aria-expanded') === 'true';
                updateMenu(!isExpanded);
            });
        }

        var animatedItems = document.querySelectorAll('.animate-on-scroll');
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });

            animatedItems.forEach(function (item) {
                observer.observe(item);
            });
        } else {
            animatedItems.forEach(function (item) {
                item.classList.add('is-visible');
            });
        }

        if (typeof Swiper !== 'undefined' && document.querySelector('.swiper')) {
            // eslint-disable-next-line no-new
            new Swiper('.swiper', {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 24,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2
                    },
                    1024: {
                        slidesPerView: 3
                    }
                }
            });
        }
    });
})();
