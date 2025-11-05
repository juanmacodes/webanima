// assets/js/main.js

// Esperar a que el DOM esté listo (por seguridad, aunque con defer debería ejecutarse al final)
document.addEventListener('DOMContentLoaded', function() {

    // 1. Inicializar slider Swiper (si la librería está cargada)
    if (typeof Swiper !== 'undefined') {
        new Swiper('.swiper-container', {
            loop: true,
            autoplay: { delay: 5000 },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
        });
    }

    // 2. Animaciones al hacer scroll: IntersectionObserver para elementos con .animate-on-scroll
    const animateElems = document.querySelectorAll('.animate-on-scroll');
    if ( 'IntersectionObserver' in window && animateElems.length > 0 ) {
        let observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target); // dejar de observar una vez animado
                }
            });
        }, { threshold: 0.1 });
        animateElems.forEach(el => observer.observe(el));
    } else {
        // Fallback: si el navegador no soporta IntersectionObserver, mostrar todo directamente
        animateElems.forEach(el => el.classList.add('visible'));
    }

    // 3. (Opcional) Toggle Dark Mode - placeholder si se quisiera un botón para modo oscuro
    const toggleBtn = document.getElementById('dark-mode-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            // Se podría guardar preferencia en localStorage para persistir entre visitas
        });
    }

});
