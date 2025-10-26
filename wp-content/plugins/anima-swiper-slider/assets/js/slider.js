(function () {
  const sliders = document.querySelectorAll('.anima-slider .swiper');
  if (!sliders.length) {
    return;
  }

  sliders.forEach(function (slider) {
    const parent = slider.closest('.anima-slider');
    const autoplay = parent.dataset.autoplay === 'false' ? false : { delay: parseInt(parent.dataset.autoplay, 10) || 5000 };
    const loop = parent.dataset.loop !== 'false';
    const pagination = parent.dataset.pagination !== 'false';

    new Swiper(slider, {
      loop,
      autoplay,
      speed: 600,
      slidesPerView: 1,
      spaceBetween: 24,
      grabCursor: true,
      pagination: pagination
        ? {
            el: parent.querySelector('.swiper-pagination'),
            clickable: true
          }
        : undefined,
      navigation: {
        nextEl: parent.querySelector('.swiper-button-next'),
        prevEl: parent.querySelector('.swiper-button-prev')
      },
      breakpoints: {
        1024: {
          slidesPerView: 1.2
        }
      }
    });
  });
})();
