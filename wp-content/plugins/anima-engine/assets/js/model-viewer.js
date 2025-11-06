(function () {
    'use strict';

    var slowConnections = ['slow-2g', '2g'];

    function getConnection() {
        return navigator.connection || navigator.mozConnection || navigator.webkitConnection || null;
    }

    function shouldUseFallback() {
        var prefersReducedMotion = false;

        if (window.matchMedia) {
            var mq = window.matchMedia('(prefers-reduced-motion: reduce)');
            if (typeof mq.addEventListener === 'function') {
                mq.addEventListener('change', function (event) {
                    if (!event.matches) {
                        document.querySelectorAll('[data-anima-model].is-fallback').forEach(function (container) {
                            container.dispatchEvent(new CustomEvent('anima-model-update'));
                        });
                    }
                });
            }

            prefersReducedMotion = mq.matches;
        }

        if (prefersReducedMotion) {
            return true;
        }

        var connection = getConnection();
        if (!connection) {
            return false;
        }

        if (connection.saveData) {
            return true;
        }

        if (connection.effectiveType && slowConnections.indexOf(connection.effectiveType) !== -1) {
            return true;
        }

        return false;
    }

    function parseConfig(container) {
        var raw = container.getAttribute('data-model-config');
        if (!raw) {
            return { attributes: {}, classes: [] };
        }

        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error('Anima Engine: error parsing model config', error);
            return { attributes: {}, classes: [] };
        }
    }

    function createModelViewerElement(config) {
        var viewer = document.createElement('model-viewer');
        var attrs = config.attributes || {};

        Object.keys(attrs).forEach(function (key) {
            if (attrs[key] !== '') {
                viewer.setAttribute(key, attrs[key]);
            }
        });

        if (Array.isArray(config.classes) && config.classes.length) {
            viewer.className = config.classes.join(' ');
        }

        if (config.style) {
            viewer.setAttribute('style', config.style);
        }

        return viewer;
    }

    function mount(container) {
        if (!container || container.getAttribute('data-model-mounted') === 'true') {
            return;
        }

        var config = parseConfig(container);
        var stage = container.querySelector('[data-model-stage]');
        var fallback = container.querySelector('[data-model-fallback]');
        var activateButton = container.querySelector('[data-activate-model]');

        if (!stage) {
            return;
        }

        function renderModel() {
            if (!stage) {
                return;
            }

            var viewer = createModelViewerElement(config);
            stage.innerHTML = '';
            stage.appendChild(viewer);
            container.setAttribute('data-model-mounted', 'true');
            container.classList.remove('is-fallback');
            container.dispatchEvent(new CustomEvent('anima-model-mounted', { bubbles: true }));
        }

        function enableFallbackMode() {
            if (!fallback) {
                renderModel();
                return;
            }

            container.classList.add('is-fallback');
            var video = fallback.querySelector('video');

            if (video) {
                video.play().catch(function () {
                    // Ignore autoplay errors
                });
            }

            if (activateButton) {
                activateButton.addEventListener('click', function () {
                    if (video) {
                        video.pause();
                    }

                    fallback.setAttribute('hidden', 'hidden');
                    renderModel();
                    activateButton.blur();
                }, { once: true });
            } else {
                renderModel();
            }
        }

        if (shouldUseFallback()) {
            enableFallbackMode();
        } else {
            renderModel();
        }

        container.addEventListener('anima-model-update', function () {
            if (container.getAttribute('data-model-mounted') === 'true') {
                return;
            }

            renderModel();
        });
    }

    function mountAll(context) {
        var scope = context || document;
        scope.querySelectorAll('[data-anima-model]').forEach(function (container) {
            mount(container);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        mountAll(document);
    });

    window.AnimaModelViewer = window.AnimaModelViewer || {};
    window.AnimaModelViewer.mount = mount;
    window.AnimaModelViewer.mountAll = mountAll;
})();
