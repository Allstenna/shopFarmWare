/**
 * Фильтрация товаров в каталоге
 * Обрабатывает клики по категориям и регионам (checkbox)
 */

document.addEventListener("DOMContentLoaded", function () {
  initCategoryFilters();
  initRegionFilters();
});

/**
 * Инициализация фильтров по категориям
 */
function initCategoryFilters() {
  const categoryButtons = document.querySelectorAll(".btn-category");

  categoryButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      const categoryId = this.dataset.categoryId;
      const isCurrentlyActive = this.classList.contains("active-category");

      // Если кнопка уже активна - сбрасываем фильтр
      if (isCurrentlyActive) {
        resetFilters();
        return;
      }

      // Получаем выбранные регионы
      const selectedRegions = getSelectedRegions();

      applyFilters(categoryId, selectedRegions);
    });
  });
}

/**
 * Инициализация фильтров по регионам (checkbox)
 */
function initRegionFilters() {
  const regionCheckboxes = document.querySelectorAll('input[name="regions[]"]');

  regionCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      // Получаем активную категорию
      const activeCategory = document.querySelector(
        ".btn-category.active-category",
      );
      const categoryId = activeCategory ? activeCategory.dataset.categoryId : 0;

      // Получаем все выбранные регионы
      const selectedRegions = getSelectedRegions();

      // Если ничего не выбрано - сброс
      if (selectedRegions.length === 0 && categoryId == 0) {
        resetFilters();
        return;
      }

      applyFilters(categoryId, selectedRegions);
    });
  });
}

/**
 * Получение выбранных регионов из checkbox
 * @returns {Array} Массив выбранных регионов
 */
function getSelectedRegions() {
  const checkboxes = document.querySelectorAll(
    'input[name="regions[]"]:checked',
  );
  const regions = [];
  checkboxes.forEach((checkbox) => {
    regions.push(checkbox.value);
  });
  return regions;
}

/**
 * Применение фильтров
 * @param {string|number} categoryId - ID категории (0 = все)
 * @param {Array} regions - Массив выбранных регионов
 */
function applyFilters(categoryId, regions) {
  const params = new URLSearchParams();
  params.set("category", categoryId);
  params.set("page", 1);

  // Добавляем каждый регион как отдельный параметр
  regions.forEach((region) => {
    params.append("regions[]", region);
  });

  window.location.href = "?" + params.toString();
}

/**
 * Применение фильтров для регионов (вызывается из onchange)
 */
function applyRegionFilters() {
  const activeCategory = document.querySelector(
    ".btn-category.active-category",
  );
  const categoryId = activeCategory ? activeCategory.dataset.categoryId : 0;
  const selectedRegions = getSelectedRegions();
  applyFilters(categoryId, selectedRegions);
}

/**
 * Сброс всех фильтров
 */
function resetFilters() {
  window.location.href = "?category=0&regions=&page=1";
}

/**
 * Обновление индикатора активных фильтров
 */
function updateFilterIndicators() {
  const urlParams = new URLSearchParams(window.location.search);
  const category = urlParams.get("category");
  const regions = urlParams.getAll("regions[]");

  const activeFiltersDiv = document.getElementById("active-filters");

  if (activeFiltersDiv) {
    if ((category && category != 0) || (regions && regions.length > 0)) {
      activeFiltersDiv.style.display = "block";
    } else {
      activeFiltersDiv.style.display = "none";
    }
  }
}

window.addEventListener("load", updateFilterIndicators);
