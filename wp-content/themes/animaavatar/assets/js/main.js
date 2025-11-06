(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.nav-toggle');
        var menuContainer = document.getElementById('primary-menu-container');
        var megaItems = [];

        var closeMegaMenus = function () {
            if (!megaItems.length) {
                return;
            }

            megaItems.forEach(function (item) {
                item.classList.remove('is-open');
                var megaToggle = item.querySelector('.menu-link--mega');
                var panel = item.querySelector('.mega-menu');

                if (megaToggle) {
                    megaToggle.setAttribute('aria-expanded', 'false');
                }

                if (panel) {
                    panel.setAttribute('aria-hidden', 'true');
                }
            });
        };

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
                    closeMegaMenus();
                }
            };

            menuContainer.setAttribute('aria-hidden', 'true');
            syncWithMedia(mq);

            if (typeof mq.addEventListener === 'function') {
                mq.addEventListener('change', syncWithMedia);
            } else if (typeof mq.addListener === 'function') {
                mq.addListener(syncWithMedia);
            }

            toggle.addEventListener('click', function () {
                var isExpanded = this.getAttribute('aria-expanded') === 'true';
                updateMenu(!isExpanded);

                if (isExpanded) {
                    closeMegaMenus();
                }
            });
        }

        var header = document.querySelector('.site-header');
        if (header) {
            var lastScroll = 0;
            var ticking = false;

            var handleHeaderScroll = function () {
                var current = window.pageYOffset || document.documentElement.scrollTop;

                if (current > 12) {
                    header.classList.add('site-header--scrolled');
                } else {
                    header.classList.remove('site-header--scrolled');
                }

                if (current > lastScroll && current > header.offsetHeight + 40) {
                    header.classList.add('header--hidden');
                } else if (current < lastScroll - 6 || current <= 12) {
                    header.classList.remove('header--hidden');
                }

                lastScroll = current <= 0 ? 0 : current;
                ticking = false;
            };

            window.addEventListener('scroll', function () {
                if (!ticking) {
                    window.requestAnimationFrame(handleHeaderScroll);
                    ticking = true;
                }
            });

            handleHeaderScroll();
        }

        megaItems = Array.prototype.slice.call(document.querySelectorAll('.menu-item--mega'));

        if (megaItems.length) {
            closeMegaMenus();

            var desktopMq = window.matchMedia('(min-width: 961px)');

            megaItems.forEach(function (item) {
                var megaToggle = item.querySelector('.menu-link--mega');
                var panel = item.querySelector('.mega-menu');
                var hideTimeout = null;

                if (!megaToggle || !panel) {
                    return;
                }

                var openMega = function () {
                    closeMegaMenus();
                    item.classList.add('is-open');
                    megaToggle.setAttribute('aria-expanded', 'true');
                    panel.setAttribute('aria-hidden', 'false');
                };

                var closeMega = function () {
                    item.classList.remove('is-open');
                    megaToggle.setAttribute('aria-expanded', 'false');
                    panel.setAttribute('aria-hidden', 'true');
                };

                megaToggle.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (item.classList.contains('is-open')) {
                        closeMega();
                        return;
                    }

                    openMega();
                });

                item.addEventListener('mouseenter', function () {
                    if (!desktopMq.matches) {
                        return;
                    }

                    window.clearTimeout(hideTimeout);
                    openMega();
                });

                item.addEventListener('mouseleave', function () {
                    if (!desktopMq.matches) {
                        return;
                    }

                    hideTimeout = window.setTimeout(closeMega, 120);
                });

                item.addEventListener('focusin', function () {
                    if (!desktopMq.matches) {
                        return;
                    }

                    window.clearTimeout(hideTimeout);
                    openMega();
                });

                item.addEventListener('focusout', function (event) {
                    if (!desktopMq.matches) {
                        if (!item.contains(event.relatedTarget)) {
                            closeMega();
                        }

                        return;
                    }

                    if (!item.contains(event.relatedTarget)) {
                        hideTimeout = window.setTimeout(closeMega, 120);
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('.menu-item--mega')) {
                    closeMegaMenus();
                }
            });

            window.addEventListener('keyup', function (event) {
                if (event.key === 'Escape') {
                    closeMegaMenus();
                }
            });

            if (typeof desktopMq.addEventListener === 'function') {
                desktopMq.addEventListener('change', closeMegaMenus);
            } else if (typeof desktopMq.addListener === 'function') {
                desktopMq.addListener(closeMegaMenus);
            }
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
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                a11y: {
                    enabled: true,
                    prevSlideMessage: document.documentElement.lang === 'es' ? 'Diapositiva anterior' : 'Previous slide',
                    nextSlideMessage: document.documentElement.lang === 'es' ? 'Diapositiva siguiente' : 'Next slide',
                    slideRole: 'group'
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

        var avatarModal = document.querySelector('[data-avatar-modal]');
        if (avatarModal) {
            avatarModal.setAttribute('aria-hidden', 'true');
            var modalDialog = avatarModal.querySelector('[data-avatar-dialog]');
            var modalContent = avatarModal.querySelector('[data-avatar-content]');
            var openButtons = document.querySelectorAll('[data-avatar-modal-open]');
            var focusableSelectors = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';
            var focusableElements = [];
            var previousFocus = null;

            if (modalDialog) {
                modalDialog.setAttribute('tabindex', '-1');
            }

            var setFocusableElements = function () {
                if (!modalDialog) {
                    focusableElements = [];
                    return;
                }

                focusableElements = Array.prototype.slice.call(modalDialog.querySelectorAll(focusableSelectors)).filter(function (element) {
                    return !element.hasAttribute('disabled') && element.getAttribute('aria-hidden') !== 'true';
                });
            };

            avatarModal.addEventListener('anima-model-mounted', function () {
                setFocusableElements();
            });

            var closeModal = function () {
                if (avatarModal.hasAttribute('hidden')) {
                    return;
                }

                avatarModal.setAttribute('hidden', 'hidden');
                avatarModal.setAttribute('aria-hidden', 'true');
                avatarModal.removeAttribute('data-open');
                document.body.classList.remove('modal-open');

                if (modalContent) {
                    modalContent.innerHTML = '';
                }

                document.removeEventListener('keydown', trapKeyDown);

                if (previousFocus) {
                    previousFocus.focus();
                    previousFocus = null;
                }
            };

            var trapKeyDown = function (event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeModal();
                    return;
                }

                if (event.key !== 'Tab') {
                    return;
                }

                if (!focusableElements.length) {
                    event.preventDefault();
                    return;
                }

                var firstFocusable = focusableElements[0];
                var lastFocusable = focusableElements[focusableElements.length - 1];

                if (event.shiftKey) {
                    if (document.activeElement === firstFocusable || !modalDialog.contains(document.activeElement)) {
                        event.preventDefault();
                        lastFocusable.focus();
                    }
                } else if (document.activeElement === lastFocusable) {
                    event.preventDefault();
                    firstFocusable.focus();
                }
            };

            var openModal = function (templateId) {
                if (!modalDialog || !modalContent) {
                    return;
                }

                var template = document.getElementById(templateId);
                if (!template || !('content' in template)) {
                    return;
                }

                modalContent.innerHTML = '';
                modalContent.appendChild(template.content.cloneNode(true));

                avatarModal.removeAttribute('hidden');
                avatarModal.setAttribute('data-open', 'true');
                avatarModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                previousFocus = document.activeElement;

                if (window.AnimaModelViewer && typeof window.AnimaModelViewer.mountAll === 'function') {
                    window.AnimaModelViewer.mountAll(modalDialog);
                }

                setFocusableElements();

                document.addEventListener('keydown', trapKeyDown);

                if (focusableElements.length) {
                    focusableElements[0].focus();
                } else {
                    modalDialog.focus();
                }
            };

            openButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    var templateId = button.getAttribute('data-avatar-modal-open');
                    openModal(templateId);
                });
            });

            avatarModal.addEventListener('click', function (event) {
                if (event.target === avatarModal) {
                    closeModal();
                }
            });

            avatarModal.addEventListener('click', function (event) {
                if (event.target && event.target.matches('[data-avatar-modal-close]')) {
                    event.preventDefault();
                    closeModal();
                }
            });
        }
    });
})();
