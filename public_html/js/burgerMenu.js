document.addEventListener('DOMContentLoaded', () => {
  const burger = document.querySelector('.burger-menu');
  const menu = document.querySelector('.page-list');

  if (!burger || !menu) return;

  // Открытие/закрытие по клику
  burger.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('active');
    burger.classList.toggle('active');
    burger.setAttribute('aria-expanded', isOpen);
  });

  // Закрытие при клике на ссылку меню
  menu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      menu.classList.remove('active');
      burger.classList.remove('active');
      burger.setAttribute('aria-expanded', 'false');
    });
  });

  // Закрытие при клике вне меню
  document.addEventListener('click', (e) => {
    if (!burger.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove('active');
      burger.classList.remove('active');
      burger.setAttribute('aria-expanded', 'false');
    }
  });
});