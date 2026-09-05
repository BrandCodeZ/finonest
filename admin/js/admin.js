// Finonest Admin - sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('#adminSidebar');
  const overlay = document.querySelector('#sidebarOverlay');
  const menuBtn = document.querySelector('#menuBtn');

  const close = () => {
    sidebar && sidebar.classList.remove('open');
    overlay && overlay.classList.remove('show');
  };

  menuBtn && menuBtn.addEventListener('click', () => {
    sidebar && sidebar.classList.toggle('open');
    overlay && overlay.classList.toggle('show');
  });
  overlay && overlay.addEventListener('click', close);

  // Any sidebar link closes the drawer on mobile
  sidebar && sidebar.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
});