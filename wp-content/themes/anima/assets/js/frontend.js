(function () {
    'use strict';

    const cookieBanner = document.querySelector('[data-anima-cookie]');
    const storageKey = 'anima_cookie_consent_v1';

    const activateAnalytics = (status) => {
        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {
                ad_storage: status ? 'granted' : 'denied',
                analytics_storage: status ? 'granted' : 'denied'
            });
        }
    };

    if (cookieBanner) {
        const stored = localStorage.getItem(storageKey);
        if (!stored) {
            cookieBanner.hidden = false;
        } else {
            activateAnalytics(stored === 'granted');
        }
        const acceptBtn = cookieBanner.querySelector('[data-anima-cookie-accept]');
        const declineBtn = cookieBanner.querySelector('[data-anima-cookie-decline]');
        const setConsent = (value) => {
            localStorage.setItem(storageKey, value ? 'granted' : 'denied');
            activateAnalytics(value);
            cookieBanner.hidden = true;
        };
        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => setConsent(true));
        }
        if (declineBtn) {
            declineBtn.addEventListener('click', () => setConsent(false));
        }
    }

    const waitlistForm = document.querySelector('[data-anima-waitlist]');
    if (waitlistForm && window.AnimaFrontend) {
        waitlistForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(waitlistForm);
            const feedback = waitlistForm.querySelector('.anima-waitlist__feedback');
            const submitButton = waitlistForm.querySelector('button[type="submit"]');
            const defaultLabel = submitButton ? submitButton.dataset.textDefault : '';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando…';
            }
            try {
                const response = await fetch(window.AnimaFrontend.restUrl, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': window.AnimaFrontend.nonce
                    },
                    body: formData
                });
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Error');
                }
                if (feedback) {
                    feedback.textContent = window.AnimaFrontend.successLabel;
                }
                waitlistForm.reset();
            } catch (error) {
                if (feedback) {
                    feedback.textContent = window.AnimaFrontend.errorLabel;
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = defaultLabel || 'Enviar';
                }
            }
        });
    }

    document.querySelectorAll('a[href*="contacto"]').forEach((link) => {
        link.addEventListener('click', () => {
            if (typeof window.gtag === 'function') {
                window.gtag('event', 'cta_click', {
                    event_category: 'cta',
                    event_label: link.href
                });
            }
        });
    });
})();
