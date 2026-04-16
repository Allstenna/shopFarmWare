document.addEventListener("DOMContentLoaded", function () {
  const sliderProducts = document.querySelector(".products-slider");
  const sliderControls = document.querySelector(".slider-controls");

  // Получаем текущую страницу из URL
  function getCurrentPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get("page");
    return page ? parseInt(page) : 1;
  }

  // Получаем общее количество страниц
  function getTotalPages() {
    if (sliderControls?.dataset.total) {
      return parseInt(sliderControls.dataset.total);
    }
    const pages = document.querySelectorAll(".page-num");
    if (pages.length > 0) {
      return parseInt(pages[pages.length - 1]?.dataset.page) || 1;
    }
    return 1;
  }
  function updateActivePage(page) {
    document.querySelectorAll(".page-num").forEach((btn) => {
      const btnPage = parseInt(btn.dataset.page);
      btn.classList.toggle("active", btnPage === page);
    });

    const prevBtn = document.querySelector(".btn-prev");
    const nextBtn = document.querySelector(".btn-next");
    const total = getTotalPages();

    if (prevBtn) {
      prevBtn.disabled = page <= 1;
      prevBtn.dataset.page = page - 1;
    }
    if (nextBtn) {
      nextBtn.disabled = page >= total;
      nextBtn.dataset.page = page + 1;
    }
  }

  // 🔹 Обновляет только номера страниц в контролах (без замены элемента)
  function updateControlsNumbers(currentPage, totalPages) {
    const pageIndex = document.querySelector(".page-index");
    if (!pageIndex) return;

    const start = Math.max(1, currentPage - 2);
    const end = Math.min(totalPages, currentPage + 2);

    // Сохраняем текущие кнопки или создаём новые
    let buttons = pageIndex.querySelectorAll(".page-num");

    // Если кнопок не хватает — создаём
    for (let i = buttons.length; i < end - start + 1; i++) {
      const btn = document.createElement("button");
      btn.className = "page-num";
      btn.type = "button";
      pageIndex.appendChild(btn);
    }

    // Обновляем существующие
    buttons = pageIndex.querySelectorAll(".page-num");
    buttons.forEach((btn, idx) => {
      const pageNum = start + idx;
      if (pageNum <= end) {
        btn.dataset.page = pageNum;
        btn.textContent = pageNum;
        btn.classList.toggle("active", pageNum === currentPage);
        btn.style.display = "";
        btn.onclick = function (e) {
          e.preventDefault();
          loadPage(pageNum);
        };
      } else {
        btn.style.display = "none";
      }
    });

    // Обновляем кнопки Назад/Вперёд
    const prevBtn = document.querySelector(".btn-prev");
    const nextBtn = document.querySelector(".btn-next");
    if (prevBtn) {
      prevBtn.disabled = currentPage <= 1;
      prevBtn.dataset.page = currentPage - 1;
    }
    if (nextBtn) {
      nextBtn.disabled = currentPage >= totalPages;
      nextBtn.dataset.page = currentPage + 1;
    }
  }

  // 🔹 Загрузка новой страницы
  function loadPage(page) {
    const total = getTotalPages();
    if (page < 1 || page > total) return;

    const params = new URLSearchParams(window.location.search);
    params.set("page", page);
    const newUrl = `?${params.toString()}`;

    // Блокируем интерфейс
    const buttons = document.querySelectorAll(".page-num, .btn-control");
    buttons.forEach((btn) => {
      btn.style.pointerEvents = "none";
      btn.style.opacity = "0.6";
    });

    fetch(newUrl)
      .then((response) => {
        if (!response.ok) throw new Error("Ошибка загрузки");
        return response.text();
      })
      .then((html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newPosts = doc.querySelector(".products-slider");

        if (newPosts && sliderPost) {
          // Плавная замена только постов
          sliderPost.style.opacity = "0";
          setTimeout(() => {
            sliderPost.innerHTML = newPosts.innerHTML;
            sliderPost.style.opacity = "1";

            // ✅ Обновляем контролы БЕЗ замены DOM-элемента
            updateControlsNumbers(page, total);

            // Обновляем URL и активное состояние
            history.pushState({ page }, "", newUrl);
            updateActivePage(page);

            // Реинициализируем обработчики для новых постов
            initPostHandlers();
          }, 150);
        }
      })
      .catch((err) => {
        console.error("Ошибка пагинации:", err);
        window.location.href = newUrl; // Фолбэк
      })
      .finally(() => {
        buttons.forEach((btn) => {
          btn.style.pointerEvents = "";
          btn.style.opacity = "";
        });
      });
  }
  function initPagination() {
    document.querySelectorAll(".page-num, .btn-control").forEach((btn) => {
      btn.onclick = function (e) {
        e.preventDefault();
        const page = parseInt(this.dataset.page);
        if (!isNaN(page)) {
          loadPage(page);
        }
      };
    });

    window.onpopstate = function (event) {
      const page = getCurrentPage();
      updateActivePage(page);
    };
  }

  // 🔹 Запуск
  const currentPage = getCurrentPage();
  updateActivePage(currentPage);
  initPagination();

  if (sliderProducts) {
    sliderProducts.style.transition = "opacity 0.15s ease";
  }
});
