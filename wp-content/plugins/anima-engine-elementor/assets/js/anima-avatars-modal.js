(function(){
  const modal = document.getElementById('avatar-modal');
  if (!modal) return;

  const html = document.documentElement;
  const backdrop = modal.querySelector('.avatar-modal__backdrop');
  const closeBtn = modal.querySelector('.avatar-modal__close');
  const dialog = modal.querySelector('.avatar-modal__dialog');
  const imgEl = modal.querySelector('#am-img');
  const titleEl = modal.querySelector('#am-title');
  const descEl = modal.querySelector('#am-desc');
  const socialsEl = modal.querySelector('#am-socials');
  let lastTrigger = null;

  const closeModal = () => {
    if (modal.getAttribute('aria-hidden') === 'true') return;
    modal.setAttribute('aria-hidden', 'true');
    html.classList.remove('avatar-modal-open');
    if (lastTrigger && typeof lastTrigger.focus === 'function') {
      lastTrigger.focus();
    }
    lastTrigger = null;
  };

  const socialTemplates = {
    twitter: { label: 'Twitter' },
    instagram: { label: 'Instagram' },
    tiktok: { label: 'TikTok' },
  };

  const openModal = (button) => {
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
        if (!url) return;
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.textContent = socialTemplates[key].label;
        socialsEl.appendChild(a);
      });
    }

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
    if (!target) return;
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
    if (!el) return;
    el.addEventListener('click', (event) => {
      event.preventDefault();
      closeModal();
    });
  });

  document.addEventListener('keydown', handleKeyDown);
})();
