document.addEventListener("DOMContentLoaded", () => {
  // 🔹 Обработчик удаления товара
  document.querySelectorAll(".btn-delete-item-home").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      
      const productId = this.dataset.productId;
      const productName = this.dataset.productName;
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      
      // 🔹 Подтверждение действия
      if (!confirm(`Удалить товар "${productName}"?\nЭто действие нельзя отменить.`)) {
        return;
      }
      
      // Визуальная блокировка
      const originalText = this.textContent;
      this.textContent = "⏳";
      this.disabled = true;
      
      const formData = new FormData();
      formData.append("id", productId);
      formData.append("csrf_token", csrfToken || "");
      
      fetch("/home/delete", {  // 👈 Роутер направит в HomeController::deleteProductFromHome()
        method: "POST",
        body: formData,
        headers: { "Accept": "application/json" }
      })
        .then(async (res) => {
          // Проверка, что ответ — JSON
          const contentType = res.headers.get("content-type");
          if (!contentType || !contentType.includes("application/json")) {
            const text = await res.text();
            console.error("Server returned non-JSON:", text.substring(0, 200));
            throw new TypeError("Сервер вернул не JSON");
          }
          return res.json();
        })
        .then((data) => {
          if (data.success) {
            // Удаляем карточку товара из DOM с анимацией
            const card = btn.closest(".product-card");
            if (card) {
              card.style.transition = "all 0.3s ease";
              card.style.opacity = "0";
              card.style.transform = "scale(0.95)";
              setTimeout(() => card.remove(), 300);
            }
            // Показать уведомление
            alert("✅ " + data.message);
          } else {
            alert("❌ " + (data.message || "Не удалось удалить товар"));
          }
        })
        .catch((err) => {
          console.error("Delete error:", err);
          alert("Ошибка соединения с сервером");
        })
        .finally(() => {
          btn.textContent = originalText;
          btn.disabled = false;
        });
    });
  });
});