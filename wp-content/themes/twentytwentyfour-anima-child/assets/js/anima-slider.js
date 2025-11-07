document.addEventListener('DOMContentLoaded', () => {
  if (typeof Swiper === 'undefined') {
    return;
  }

  document.querySelectorAll('.anima-slider .swiper').forEach((container) => {
    // eslint-disable-next-line no-new
    new Swiper(container, {
      loop: true,
      slidesPerView: 1,
      spaceBetween: 24,
      autoplay: {
        delay: 6000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: container.querySelector('.swiper-button-next'),
        prevEl: container.querySelector('.swiper-button-prev'),
      },
    });
  });
});
