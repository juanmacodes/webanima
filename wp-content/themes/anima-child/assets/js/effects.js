(function ($) {
  $(function () {
    const header = $('.site-header');
    if (!header.length) {
      return;
    }

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
  });
})(jQuery);
