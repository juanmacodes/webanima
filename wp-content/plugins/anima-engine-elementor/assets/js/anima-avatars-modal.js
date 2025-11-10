(function(){
  const init = () => {
    if (document.body && document.body.classList.contains('elementor-editor-active')) {
      return;
    }

    const modalInstances = Array.from(document.querySelectorAll('#avatar-modal'));
    if (!modalInstances.length) {
      return;
    }

    const [modal, ...duplicates] = modalInstances;
    duplicates.forEach((el) => el.remove());

    if (modal.dataset.bound === 'true') {
      return;
    }

    modal.dataset.bound = 'true';

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');

    const html = document.documentElement;
    html.classList.remove('avatar-modal-open');
    const backdrop = modal.querySelector('.avatar-modal__backdrop');
    const closeBtn = modal.querySelector('.avatar-modal__close');
    const dialog = modal.querySelector('.avatar-modal__dialog');
    const imgEl = modal.querySelector('#am-img');
    const titleEl = modal.querySelector('#am-title');
    const descEl = modal.querySelector('#am-desc');
    const socialsEl = modal.querySelector('#am-socials');
    const socialTemplates = {
      twitter: { label: 'Twitter' },
      instagram: { label: 'Instagram' },
      tiktok: { label: 'TikTok' },
    };
    let lastTrigger = null;

    const closeModal = () => {
      if (modal.getAttribute('aria-hidden') === 'true') {
        return;
      }

      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      html.classList.remove('avatar-modal-open');

      if (imgEl) {
        imgEl.src = '';
        imgEl.alt = '';
      }

      if (titleEl) {
        titleEl.textContent = '';
      }

      if (descEl) {
        descEl.textContent = '';
      }

      if (socialsEl) {
        socialsEl.innerHTML = '';
      }

      if (lastTrigger && typeof lastTrigger.focus === 'function') {
        lastTrigger.focus();
      }
      lastTrigger = null;
    };

    const openModal = (button) => {
      if (!button) {
        return;
      }

      lastTrigger = button;
      const { title, img, desc, twitter, instagram, tiktok } = button.dataset;

      if (imgEl) {
        imgEl.src = img || '';
        imgEl.alt = title || '';
      }

      if (titleEl) {
        titleEl.textContent = title || '';
      }

      if (descEl) {
        descEl.textContent = desc || '';
      }

      if (socialsEl) {
        socialsEl.innerHTML = '';
        const links = { twitter, instagram, tiktok };
        Object.keys(links).forEach((key) => {
          const url = links[key];
          if (!url) {
            return;
          }
          const a = document.createElement('a');
          a.href = url;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
          a.textContent = socialTemplates[key].label;
          socialsEl.appendChild(a);
        });
      }

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      html.classList.add('avatar-modal-open');

      requestAnimationFrame(() => {
        if (dialog && typeof dialog.focus === 'function') {
          if (!dialog.hasAttribute('tabindex')) {
            dialog.setAttribute('tabindex', '-1');
          }
          dialog.focus();
        }
      });
    };

    const handleTriggerClick = (event) => {
      const target = event.target.closest('.avatar-detail');
      if (!target) {
        return;
      }
      event.preventDefault();
      openModal(target);
    };

    const handleKeyDown = (event) => {
      if (event.key === 'Escape') {
        closeModal();
      }
    };

    document.addEventListener('click', handleTriggerClick);

    [backdrop, closeBtn].forEach((el) => {
      if (!el) {
        return;
      }
      el.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal();
      });
    });

    document.addEventListener('keydown', handleKeyDown);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
