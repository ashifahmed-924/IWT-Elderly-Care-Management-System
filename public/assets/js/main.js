// Ask before delete / destructive actions
document.querySelectorAll('[data-confirm]').forEach((el) => {
  el.addEventListener('click', (e) => {
    if (!confirm(el.getAttribute('data-confirm'))) {
      e.preventDefault();
    }
  });
});
