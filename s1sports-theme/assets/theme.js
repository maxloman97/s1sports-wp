document.querySelector('.menu-toggle')?.addEventListener('click', function () {
  const nav = document.querySelector('.site-nav');
  const open = nav?.classList.toggle('is-open');
  this.setAttribute('aria-expanded', open ? 'true' : 'false');
});
