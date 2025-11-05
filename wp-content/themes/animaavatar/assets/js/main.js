// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    const primaryNav = document.getElementById('primary-navigation');
    const menuToggle = document.querySelector('.menu-toggle');

    if (menuToggle && primaryNav) {
        menuToggle.addEventListener('click', () => {
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', String(!isExpanded));
            primaryNav.classList.toggle('is-active', !isExpanded);
        });

        primaryNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuToggle.setAttribute('aria-expanded', 'false');
                primaryNav.classList.remove('is-active');
            });
        });
    }

    const translate = (text, domain) => {
        if (window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function') {
            return window.wp.i18n.__(text, domain);
        }
        return text;
    };

    if (typeof Swiper !== 'undefined') {
        new Swiper('.hero-slider', {
            loop: true,
            speed: 700,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            keyboard: {
                enabled: true,
                onlyInViewport: true,
            },
            a11y: {
                enabled: true,
                prevSlideMessage: translate('Slide anterior', 'animaavatar'),
                nextSlideMessage: translate('Slide siguiente', 'animaavatar'),
            },
        });
    }

    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if ('IntersectionObserver' in window && animatedElements.length > 0) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach((element) => observer.observe(element));
    } else {
        animatedElements.forEach((element) => element.classList.add('visible'));
    }
});
