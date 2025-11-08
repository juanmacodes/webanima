(function () {
  const modal = document.getElementById('avatar-modal');
  const img = document.getElementById('am-img');
  const title = document.getElementById('am-title');
  const desc = document.getElementById('am-desc');
  const socials = document.getElementById('am-socials');
  if (!modal || !img || !title || !desc || !socials) {
    return;
  }

  const closeButton = modal.querySelector('.avatar-modal__close');
  let lastTrigger = null;
  let previousOverflow = '';

  function renderSocials(data) {
    socials.innerHTML = '';
    const socialConfigs = [
      { key: 'twitter', label: 'X/Twitter' },
      { key: 'instagram', label: 'Instagram' },
      { key: 'tiktok', label: 'TikTok' }
    ];

    socialConfigs.forEach(({ key, label }) => {
      const href = (data[key] || '').trim();
      if (!href) {
        return;
      }
      const anchor = document.createElement('a');
      anchor.className = 'avatar-social';
      anchor.href = href;
      anchor.target = '_blank';
      anchor.rel = 'noopener';
      anchor.textContent = label;
      socials.appendChild(anchor);
    });
  }

  function openModal(data, trigger) {
    lastTrigger = trigger || null;

    img.src = data.img || '';
    img.alt = data.title || '';
    title.textContent = data.title || '';
    desc.textContent = data.desc || '';

    renderSocials(data);

    previousOverflow = document.documentElement.style.overflow;
    document.documentElement.style.overflow = 'hidden';

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    if (closeButton) {
      closeButton.focus();
    }
  }

  function closeModal() {
    if (!modal.classList.contains('is-open')) {
      return;
    }

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.style.overflow = previousOverflow || '';

    if (lastTrigger && typeof lastTrigger.focus === 'function') {
      lastTrigger.focus();
    }
  }

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('.avatar-detail');
    if (trigger) {
      const data = {
        title: trigger.dataset.title || '',
        img: trigger.dataset.img || '',
        desc: trigger.dataset.desc || '',
        twitter: trigger.dataset.twitter || '',
        instagram: trigger.dataset.instagram || '',
        tiktok: trigger.dataset.tiktok || ''
      };

      openModal(data, trigger);
      return;
    }

    if (event.target.matches('[data-close]')) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeModal();
    }
  });
})();
