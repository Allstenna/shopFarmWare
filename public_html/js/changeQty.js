/**
 * changeQty.js - Логика корзины для MVC-роутера
 */
document.addEventListener("DOMContentLoaded", () => {
  
  // 🔹 Расчёт стоимости доставки
  function getDeliveryCost(subtotal) {
    if (subtotal >= 3000) return 0;
    if (subtotal >= 2000) return Math.ceil(subtotal * 0.1);
    if (subtotal >= 1000) return Math.ceil(subtotal * 0.25);
    return Math.ceil(subtotal * 0.35);
  }

  // 🔹 Пересчёт итоговой суммы
  function recalcGrandTotal() {
    let itemsTotal = 0;
    document.querySelectorAll(".order-item:not(.is-zero)").forEach((item) => {
      const qtyText = item.querySelector(".item-num")?.textContent || "0";
      const qty = parseInt(qtyText) || 0;
      const price = parseFloat(item.dataset.priceSnapshot) || 0;
      itemsTotal += qty * price;
    });

    const delivery = getDeliveryCost(itemsTotal);
    const deliveryEl = document.getElementById("delivery-amount");
    if (deliveryEl) {
      deliveryEl.textContent = delivery === 0 ? "Бесплатно" : delivery.toLocaleString("ru-RU") + " руб.";
      deliveryEl.classList.toggle("free-delivery", delivery === 0);
    }

    const grandTotalEl = document.getElementById("cart-grand-total");
    if (grandTotalEl) {
      grandTotalEl.textContent = (itemsTotal + delivery).toLocaleString("ru-RU") + " руб.";
    }
  }

  // 🔹 Обновление UI одной строки товара
  function updateItemUI(itemEl, newQty) {
    const qtyEl = itemEl.querySelector(".item-num");
    const priceEl = itemEl.querySelector(".price");
    const btnLess = itemEl.querySelector(".btn-less");
    const priceSnapshot = parseFloat(itemEl.dataset.priceSnapshot) || 0;
    const subtotal = newQty * priceSnapshot;

    priceEl.textContent = subtotal.toLocaleString("ru-RU") + " руб.";
    priceEl.dataset.subtotal = subtotal;

    if (newQty <= 0) {
      itemEl.classList.add("is-zero");
      qtyEl.style.display = "none";
      btnLess.style.display = "none";
      priceEl.style.textDecoration = "line-through";
      priceEl.style.opacity = "0.5";
    } else {
      itemEl.classList.remove("is-zero");
      qtyEl.style.display = "";
      qtyEl.textContent = newQty + " шт.";
      btnLess.style.display = "";
      priceEl.style.textDecoration = "";
      priceEl.style.opacity = "";
    }
  }

  // 🔹 Обработчики кнопок + / -
  document.querySelectorAll(".btn-less, .btn-more").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const itemEl = e.target.closest(".order-item");
      if (!itemEl) return;

      const itemId = itemEl.dataset.itemId;
      const qtyEl = itemEl.querySelector(".item-num");
      let currentQty = itemEl.classList.contains("is-zero") ? 0 : parseInt(qtyEl.textContent) || 0;
      let newQty = e.target.classList.contains("btn-less")
        ? Math.max(0, currentQty - 1)
        : currentQty <= 0 ? 1 : currentQty + 1;

      btn.disabled = true;
      btn.style.opacity = "0.5";

      const params = new URLSearchParams({
        action: "update_quantity",
        item_id: itemId,
        quantity: newQty,
      });

      // 🔹 ИСПРАВЛЕНО: явный эндпоинт вместо window.location.href
      fetch("/cart/update", {  // 👈 Роутер направит в CartController::updateQuantity()
        method: "POST",
        body: params,
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
      })
        .then(async (res) => {
          // 🔹 Проверяем, что сервер вернул JSON, а не HTML
          const contentType = res.headers.get("content-type");
          if (!contentType || !contentType.includes("application/json")) {
            const text = await res.text();
            console.error("Server returned non-JSON:", text.substring(0, 300));
            throw new TypeError("Сервер вернул не JSON. Проверьте логи.");
          }
          return res.json();
        })
        .then((data) => {
          if (data.success) {
            updateItemUI(itemEl, newQty);
            recalcGrandTotal();
          } else {
            alert(data.message || "Не удалось обновить количество");
          }
        })
        .catch((err) => {
          console.error("Fetch error:", err);
          alert("Ошибка соединения с сервером. Проверьте консоль (F12).");
        })
        .finally(() => {
          btn.disabled = false;
          btn.style.opacity = "1";
        });
    });
  });

  // 🔹 Кнопка "Оформить заказ"
  document.querySelector(".btn-make-order")?.addEventListener("click", (e) => {
    const btn = e.target;
    if (btn.disabled) return;

    const hasItems = document.querySelectorAll(".order-item:not(.is-zero)").length > 0;
    if (!hasItems) {
      alert("Корзина пуста! Добавьте товары перед оформлением.");
      return;
    }

    btn.disabled = true;
    btn.textContent = "Оформляем...";

    // 🔹 ИСПРАВЛЕНО: явный эндпоинт для checkout
    fetch("/cart/checkout", {  // 👈 Роутер направит в CartController::checkout()
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "checkout" }),
    })
      .then(async (res) => {
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
          const text = await res.text();
          console.error("Checkout returned non-JSON:", text.substring(0, 300));
          throw new TypeError("Сервер вернул не JSON при оформлении заказа");
        }
        return res.json();
      })
      .then((data) => {
        if (data.success) {
          const orderCard = document.querySelector(".order-card");
          if (orderCard) {
            orderCard.style.transition = "all 0.4s ease";
            orderCard.style.backgroundColor = "#B4AE64";
            orderCard.style.borderRadius = "15px";
            orderCard.innerHTML = `
              <div style="padding: 40px 20px; text-align: center; color: #FFFFFF; font-size: 32px; font-weight: 600; letter-spacing: 0.5px;">
                ✅ Заказ успешно оформлен!
              </div>
            `;
            setTimeout(() => {
              orderCard.style.opacity = "0";
              orderCard.style.transform = "translateY(-20px)";
              setTimeout(() => {
                orderCard.style.display = "none";
                // window.location.href = '/catalog'; // Раскомментируйте для редиректа
              }, 500);
            }, 2000);
          }
        } else {
          alert(data.message || "Ошибка оформления заказа");
        }
      })
      .catch((err) => {
        console.error("Checkout error:", err);
        alert("Ошибка соединения с сервером");
      })
      .finally(() => {
        btn.disabled = false;
        btn.textContent = "Оформить заказ";
      });
  });

  // Первичный расчёт при загрузке
  recalcGrandTotal();
});