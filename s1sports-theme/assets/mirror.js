document.addEventListener('DOMContentLoaded', () => {
  const sliders = new Map();
  if (window.Swiper) document.querySelectorAll('.breakdance-swiper-wrapper').forEach(wrapper => {
    const container = wrapper.querySelector('.swiper');
    if (!container || !container.querySelector('.swiper-wrapper')) return;
    const slides = container.querySelectorAll('.swiper-slide').length;
    const advanced = Boolean(wrapper.closest('.bde-advancedslider'));
    const aboutSlider = advanced && ['186', '196'].includes(wrapper.dataset.swiperId);
    const desktop = aboutSlider ? 3 : advanced ? 4 : slides > 4 ? 4 : slides === 3 ? 3 : 1;
    const gap = advanced ? 10 : slides > 4 ? 25 : 20;
    const pagination = wrapper.querySelector('.swiper-pagination');
    const slider = new Swiper(container, {
      slidesPerView: aboutSlider ? 1 : advanced ? 2 : 1,
      spaceBetween: gap,
      breakpoints: {
        480: { slidesPerView: advanced ? 2 : 1, spaceBetween: gap },
        768: { slidesPerView: desktop, spaceBetween: gap }
      },
      pagination: pagination ? { el: pagination, clickable: true } : undefined,
      loop: advanced,
      rewind: !advanced,
      keyboard: { enabled: true }
    });
    const host = wrapper.parentElement;
    const id = [...host.classList].find(name => /^bde-(?:advancedslider|post-loop)-\d+-\d+$/.test(name));
    if (id) sliders.set(id, slider);
    if (wrapper.dataset.swiperId) sliders.set(wrapper.dataset.swiperId, slider);
  });
  document.addEventListener('click', event => {
    const control = event.target.closest('a[data-action]');
    if (!control) return;
    let action;
    try { action = JSON.parse(control.dataset.action); } catch { return; }
    if (action?.type !== 'slider') return;
    const targetId = action.sliderOptions?.elementId || '';
    const slider = sliders.get(targetId) || sliders.get(targetId.match(/(\d+)$/)?.[1]);
    if (!slider) return;
    event.preventDefault();
    if (action.sliderOptions.actionType === 'previous') slider.slidePrev();
    if (action.sliderOptions.actionType === 'next') slider.slideNext();
  });
  document.querySelectorAll('.s1-mirror-language .wpml-ls-item-toggle').forEach(button => {
    button.addEventListener('click', (event) => { event.preventDefault();
      const open = button.getAttribute('aria-expanded') !== 'true';
      document.querySelectorAll('.s1-mirror-language .wpml-ls-item-toggle').forEach(item => {
        item.setAttribute('aria-expanded', 'false');
        item.closest('.wpml-ls-item')?.classList.remove('wpml-ls-item-open');
      });
      button.setAttribute('aria-expanded', String(open));
      button.closest('.wpml-ls-item')?.classList.toggle('wpml-ls-item-open', open);
    });
  });
  document.addEventListener('click', event => {
    if (event.target.closest('.s1-mirror-language')) return;
    document.querySelectorAll('.s1-mirror-language .wpml-ls-item-toggle').forEach(button => {
      button.setAttribute('aria-expanded', 'false');
      button.closest('.wpml-ls-item')?.classList.remove('wpml-ls-item-open');
    });
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.s1-mirror-language .wpml-ls-item-toggle').forEach(button => {
      button.setAttribute('aria-expanded', 'false');
      button.closest('.wpml-ls-item')?.classList.remove('wpml-ls-item-open');
    });
  });
  document.querySelectorAll('.breakdance-menu-toggle').forEach(button => {
    button.addEventListener('click', (event) => { event.preventDefault();
      const nav = button.closest('.breakdance-menu');
      const open = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', String(open));
      nav?.classList.toggle('breakdance-menu--enabled', open);
      nav?.classList.toggle('breakdance-responsive-menu--open', open);
      nav?.querySelector('.breakdance-menu-list')?.classList.toggle('is-visible', open);
    });
  });
  document.querySelectorAll('form[data-s1-contact]').forEach(form => {
    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const data = new FormData(form);
      data.set('action', 's1_contact');
      data.set('nonce', form.dataset.s1Nonce);
      data.set('language', document.documentElement.lang.startsWith('sv') ? 'sv' : 'en');
      const button = form.querySelector('[type="submit"]');
      if (button) button.disabled = true;
      let message = form.querySelector('.s1-form-message');
      if (!message) { message = document.createElement('p'); message.className = 's1-form-message'; message.setAttribute('role', 'status'); form.append(message); }
      try {
        const response = await fetch(form.dataset.s1Endpoint, { method: 'POST', body: data, credentials: 'same-origin' });
        const result = await response.json();
        if (!response.ok || !result.success || !result.data?.saved) throw new Error('submit failed');
        message.textContent = result.data.mail_sent
          ? (document.documentElement.lang.startsWith('sv') ? 'Vi har tagit emot ditt meddelande. Vi återkommer inom 24 timmar!' : 'We have received your message. We will get back to you within 24 hours!')
          : (document.documentElement.lang.startsWith('sv') ? 'Din förfrågan har sparats, men testmejlet kunde inte skickas från den lokala miljön.' : 'Your request was saved, but the test email could not be sent from the local environment.');
        form.reset();
      } catch {
        message.textContent = document.documentElement.lang.startsWith('sv') ? 'Ett fel har inträffat. Vänligen försök igen.' : 'An error has occurred. Please try again.';
      } finally { if (button) button.disabled = false; }
    });
  });
  const filters = document.querySelectorAll('#acf-filter-group .icon-filter-btn');
  const athleteGrid = document.querySelector('#my-ajax-loop');
  if (athleteGrid && filters.length) filters.forEach(button => {
    button.addEventListener('click', () => {
      const value = button.dataset.value || '';
      filters.forEach(item => item.classList.toggle('active', item === button));
      athleteGrid.querySelectorAll('.athlete-card-wrapper').forEach(card => {
        card.hidden = Boolean(value) && card.dataset.sport !== value;
      });
    });
  });
});
