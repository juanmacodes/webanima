(function () {
  if (typeof window === 'undefined') {
    return;
  }

  var settings = window.animaEngineSettings || {};

  var onReady = function (callback) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      callback();
    } else {
      document.addEventListener('DOMContentLoaded', callback);
    }
  };

  var createLightbox = function () {
    var existing = document.querySelector('.an-lightbox');
    if (existing) {
      return existing;
    }

    var lightbox = document.createElement('div');
    lightbox.className = 'an-lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', settings.lightboxLabel || 'Visor 3D');

    var inner = document.createElement('div');
    inner.className = 'an-lightbox__inner';

    var closeButton = document.createElement('button');
    closeButton.className = 'an-lightbox__close';
    closeButton.type = 'button';
    closeButton.innerHTML = '&times;';
    closeButton.addEventListener('click', function () {
      lightbox.classList.remove('is-active');
      lightbox.querySelector('.an-lightbox__content').innerHTML = '';
    });

    var content = document.createElement('div');
    content.className = 'an-lightbox__content';

    inner.appendChild(closeButton);
    inner.appendChild(content);
    lightbox.appendChild(inner);
    document.body.appendChild(lightbox);

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) {
        closeButton.click();
      }
    });

    return lightbox;
  };

  var mountLightboxContent = function (lightbox, data) {
    var container = lightbox.querySelector('.an-lightbox__content');
    container.innerHTML = '';

    if (!data) {
      return;
    }

    if (data.type === 'model-viewer') {
      var model = document.createElement('model-viewer');
      model.src = data.src;
      model.setAttribute('camera-controls', '');
      model.setAttribute('auto-rotate', '');
      model.setAttribute('style', 'width:100%;height:100%;background:#050510;');
      container.appendChild(model);
    } else {
      var iframe = document.createElement('iframe');
      iframe.src = data.src;
      iframe.title = data.title || 'Visor 3D';
      iframe.loading = 'lazy';
      container.appendChild(iframe);
    }
  };

  var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  var buildError = function (message) {
    var div = document.createElement('div');
    div.className = 'an-alert an-alert--error';
    div.textContent = message;
    return div;
  };

  var buildSuccess = function (message) {
    var div = document.createElement('div');
    div.className = 'an-alert an-alert--success';
    div.textContent = message;
    return div;
  };

  onReady(function () {
    var revealTargets = document.querySelectorAll('[data-anima-reveal]');

    if (!('IntersectionObserver' in window)) {
      revealTargets.forEach(function (element) {
        element.classList.add('is-visible');
      });
    } else {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
              observer.unobserve(entry.target);
            }
          });
        },
        {
          threshold: 0.15,
        }
      );

      revealTargets.forEach(function (element) {
        observer.observe(element);
      });
    }

    var lightbox = createLightbox();

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest('[data-anima-lightbox]');
      if (!trigger) {
        var syllabusButton = event.target.closest('.an-course-syllabus__button');
        if (syllabusButton) {
          event.preventDefault();
          var panelId = syllabusButton.getAttribute('aria-controls');
          var panel = panelId ? document.getElementById(panelId) : null;
          var expanded = syllabusButton.getAttribute('aria-expanded') === 'true';
          syllabusButton.setAttribute('aria-expanded', expanded ? 'false' : 'true');
          if (panel) {
            if (expanded) {
              panel.setAttribute('hidden', 'hidden');
            } else {
              panel.removeAttribute('hidden');
            }
          }
        }
        return;
      }

      event.preventDefault();
      var src = trigger.getAttribute('data-anima-lightbox');
      if (!src) {
        return;
      }

      var type = trigger.getAttribute('data-anima-lightbox-type') || 'iframe';
      mountLightboxContent(lightbox, {
        src: src,
        type: type,
        title: trigger.getAttribute('data-anima-lightbox-title') || trigger.getAttribute('aria-label') || trigger.textContent,
      });
      lightbox.classList.add('is-active');
      var closeButton = lightbox.querySelector('.an-lightbox__close');
      if (closeButton) {
        closeButton.focus({ preventScroll: true });
      }
    });

    var filterGroups = document.querySelectorAll('.an-grid__filters');
    filterGroups.forEach(function (group) {
      group.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-filter]');
        if (!button) {
          return;
        }

        event.preventDefault();
        var filter = button.getAttribute('data-filter');
        group.querySelectorAll('button[data-filter]').forEach(function (btn) {
          btn.classList.toggle('is-active', btn === button);
        });

        var grid = group.nextElementSibling;
        if (!grid || !grid.classList.contains('an-grid')) {
          return;
        }

        var cards = grid.querySelectorAll('.an-avatar-card');
        cards.forEach(function (card) {
          if (!filter || filter === '*' ) {
            card.hidden = false;
            return;
          }

          var categories = card.getAttribute('data-category') || '';
          var matches = categories.split(/\s+/).filter(Boolean).indexOf(filter) !== -1;
          card.hidden = !matches;
        });
      });
    });

    var forms = document.querySelectorAll('.an-course-enroll__form');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();

        var submitButton = form.querySelector('.an-course-enroll__submit');
        var messageBox = form.querySelector('.an-course-enroll__messages');
        if (messageBox) {
          messageBox.innerHTML = '';
        }

        var honeypot = form.querySelector('[name="anima_hp"]');
        if (honeypot && honeypot.value) {
          return;
        }

        var nameField = form.querySelector('[name="anima_name"]');
        var emailField = form.querySelector('[name="anima_email"]');
        var countryField = form.querySelector('[name="anima_country"]');
        var consentField = form.querySelector('[name="anima_consent"]');
        var target = form.getAttribute('data-target');
        var redirectUrl = form.getAttribute('data-url');
        var nonce = form.querySelector('[name="anima_waitlist_nonce"]');
        var courseId = form.getAttribute('data-course');

        var errors = [];

        if (!nameField || !nameField.value.trim()) {
          errors.push(settings.errorName || 'Introduce tu nombre.');
        }

        if (!emailField || !emailField.value.trim() || !emailPattern.test(emailField.value.trim())) {
          errors.push(settings.errorEmail || 'Introduce un email válido.');
        }

        if (consentField && !consentField.checked) {
          errors.push(settings.errorConsent || 'Debes aceptar la política de privacidad.');
        }

        if (errors.length && messageBox) {
          errors.forEach(function (message) {
            messageBox.appendChild(buildError(message));
          });
          return;
        }

        if (target === 'url' && redirectUrl) {
          window.open(redirectUrl, '_blank', 'noopener');
          if (messageBox) {
            messageBox.appendChild(buildSuccess(settings.successRedirect || 'Te hemos llevado a la página de inscripción.'));
          }
          form.reset();
          return;
        }

        if (!settings.restWaitlist) {
          if (messageBox) {
            messageBox.appendChild(
              buildError(settings.errorGeneric || 'No se pudo completar tu solicitud. Inténtalo más tarde.')
            );
          }
          return;
        }

        if (submitButton) {
          submitButton.disabled = true;
        }

        var payload = {
          name: nameField ? nameField.value.trim() : '',
          email: emailField ? emailField.value.trim() : '',
          country: countryField ? countryField.value.trim() : '',
          consent: consentField ? consentField.checked : false,
          course_id: courseId ? parseInt(courseId, 10) || 0 : 0,
          mode: target || 'waitlist',
        };

        var headers = {
          'Content-Type': 'application/json',
        };

        if (nonce && nonce.value) {
          headers['X-WP-Nonce'] = nonce.value;
        } else if (settings.nonce) {
          headers['X-WP-Nonce'] = settings.nonce;
        }

        fetch(settings.restWaitlist, {
          method: 'POST',
          headers: headers,
          body: JSON.stringify(payload),
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Request failed');
            }
            return response.json();
          })
          .then(function (data) {
            if (messageBox) {
              var message = data && data.message ? data.message : settings.successWaitlist || '¡Gracias!';
              messageBox.appendChild(buildSuccess(message));
            }
            form.reset();
          })
          .catch(function () {
            if (messageBox) {
              messageBox.appendChild(
                buildError(settings.errorGeneric || 'No se pudo completar tu solicitud. Inténtalo más tarde.')
              );
            }
          })
          .finally(function () {
            if (submitButton) {
              submitButton.disabled = false;
            }
          });
      });
    });
  });
})();
