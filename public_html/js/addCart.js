document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".btn-to-cart").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const originalText = this.textContent;
      this.textContent = "⏳...";
      this.disabled = true;

      const formData = new FormData();
      formData.append("action", "add_to_cart");
      formData.append("product_id", this.dataset.productId);
      formData.append("quantity", "1");
      // 🔹 Если контроллер проверяет CSRF — добавьте токен:
      // formData.append("csrf_token", document.querySelector('meta[name="csrf-token"]')?.content || "");

      // 🔹 Используйте явный маршрут, а не window.location.href
      fetch("/cart/add", {  // 👈 Или "/?page=cart&action=add_to_cart" если роутер так настроен
        method: "POST",
        body: formData,
        // 🔹 Для отладки: увидеть, что реально пришёл с сервера
        // credentials: 'same-origin'
      })
        .then(async (res) => {
          // 🔹 Проверяем, что сервер вернул JSON, а не HTML
          const contentType = res.headers.get("content-type");
          if (!contentType || !contentType.includes("application/json")) {
            const text = await res.text();
            console.error("Server returned non-JSON:", text.substring(0, 200));
            throw new TypeError("Сервер вернул не JSON. Проверьте логи.");
          }
          return res.json();
        })
        .then((data) => {
          if (data.success) {
            this.textContent = "✅";
          } else {
            this.textContent = originalText;
            alert(data.message || "Произошла ошибка");
          }
        })
        .catch((err) => {
          console.error("Fetch error:", err);
          this.textContent = originalText;
          alert("Ошибка сети или сервера. Попробуйте позже.");
        })
        .finally(() => {
          this.disabled = false;
          setTimeout(() => {
            if (this.textContent === "✅") this.textContent = originalText;
          }, 2000);
        });
    });
  });
});