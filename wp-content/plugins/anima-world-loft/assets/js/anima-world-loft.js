(function () {
    'use strict';

    if (typeof window === 'undefined') {
        return;
    }

    const selectors = {
        stage: '[data-anima-world-loft-stage]',
        overlay: '[data-anima-world-loft-overlay]',
        start: '[data-anima-world-loft-start]'
    };

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function init() {
        document.querySelectorAll(selectors.stage).forEach((canvas) => {
            const overlay = canvas.closest('.anima-world-loft__canvas')?.querySelector(selectors.overlay);
            const startButton = overlay?.querySelector(selectors.start);

            if (startButton) {
                startButton.addEventListener('click', () => {
                    overlay?.setAttribute('hidden', 'hidden');
                    overlay?.classList.add('is-hidden');
                });
            }
        });
    }

    ready(init);
})();
