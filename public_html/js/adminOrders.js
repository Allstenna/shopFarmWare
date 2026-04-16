/**
 * adminOrders.js - Логика админ-панели заказов
 * ✅ Добавлен автопересчёт итогов при изменении количества
 */
document.addEventListener("DOMContentLoaded", () => {
  
  // 🔹 Расчёт доставки (идентично changeQty.js)
  function getDeliveryCost(subtotal) {
    if (subtotal >= 3000) return 0;
    if (subtotal >= 2000) return Math.ceil(subtotal * 0.1);
    if (subtotal >= 1000) return Math.ceil(subtotal * 0.25);
    return Math.ceil(subtotal * 0.35);
  }

  // 🔹 Обновление итогов заказа в UI
  function updateOrderTotals(subtotal, delivery, grandTotal) {
    const subtotalEl = document.getElementById('order-subtotal');
    const deliveryEl = document.getElementById('order-delivery');
    const grandTotalEl = document.getElementById('order-grand-total');
    
    if (subtotalEl) {
      subtotalEl.textContent = subtotal.toLocaleString('ru-RU') + ' руб.';
    }
    if (deliveryEl) {
      deliveryEl.textContent = delivery === 0 
        ? 'Бесплатно' 
        : delivery.toLocaleString('ru-RU') + ' руб.';
      deliveryEl.classList.toggle('free-delivery', delivery === 0);
    }
    if (grandTotalEl) {
      grandTotalEl.textContent = grandTotal.toLocaleString('ru-RU') + ' руб.';
    }
  }

  // 🔹 Применение цвета статуса
  function applyStatusColor(element, status) {
    element.className = 'order-stat';
    if (status === 'Новый') element.classList.add('status-new');
    else if (status === 'В обработке' || status === 'Отправлен')
      element.classList.add('status-process');
    else if (status === 'Отменен')
      element.classList.add('status-cancelled');
    else if (status === 'Выполнен')
      element.classList.add('status-completed');
  }

  // 🔹 Обновление UI одной позиции
  function updateItemUI(itemEl, newQty) {
    const qtyEl = itemEl.querySelector('.item-num');
    const priceEl = itemEl.querySelector('.price');
    const btnLess = itemEl.querySelector('.btn-less');
    const btnDelete = itemEl.querySelector('.btn-delete-item');
    const priceSnapshot = parseFloat(itemEl.dataset.priceSnapshot) || 0;
    const newSubtotal = newQty * priceSnapshot;

    priceEl.textContent = newSubtotal.toLocaleString('ru-RU') + ' руб.';
    itemEl.dataset.qty = newQty;

    if (newQty <= 0) {
      itemEl.classList.add('is-zero');
      qtyEl.style.display = 'none';
      btnLess.style.display = 'none';
      if (btnDelete) btnDelete.style.display = 'none';
      priceEl.style.textDecoration = 'line-through';
      priceEl.style.opacity = '0.6';
    } else {
      itemEl.classList.remove('is-zero');
      qtyEl.style.display = '';
      qtyEl.textContent = newQty + ' шт.';
      btnLess.style.display = '';
      if (btnDelete) btnDelete.style.display = '';
      priceEl.style.textDecoration = '';
      priceEl.style.opacity = '';
    }
  }

  // 🔹 AJAX-обёртка с обработкой новых полей ответа
  function sendItemUpdate(itemId, qty) {
    const formData = new FormData();
    formData.append('action', 'update_quantity');
    formData.append('item_id', itemId);
    formData.append('quantity', qty);
    
    return fetch('', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        // 🔹 Если сервер вернул новые итоги — обновляем UI
        if (data.success && data.new_subtotal !== undefined) {
          updateOrderTotals(data.new_subtotal, data.new_delivery, data.new_total);
        }
        return data;
      });
  }

  // 🔹 Обработчики кнопок +/- и 🗑️
  document.querySelectorAll('.btn-less, .btn-more, .btn-delete-item').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const itemEl = e.target.closest('.order-item');
      if (!itemEl) return;

      const itemId = itemEl.dataset.itemId;
      let currentQty = parseInt(itemEl.dataset.qty) || 0;
      let newQty = currentQty;

      if (e.target.classList.contains('btn-less')) {
        newQty = Math.max(0, currentQty - 1);
      } else if (e.target.classList.contains('btn-delete-item')) {
        if (!confirm('Удалить эту позицию из заказа? Товар будет перечёркнут.')) return;
        newQty = 0;
      } else {
        // btn-more
        newQty = currentQty <= 0 ? 1 : currentQty + 1;
      }

      // Блокировка на время запроса
      btn.disabled = true;
      btn.style.opacity = '0.6';

      sendItemUpdate(itemId, newQty)
        .then((data) => {
          if (data.success) {
            updateItemUI(itemEl, newQty);
            // 🔹 Если сервер не вернул итоги (старый ответ) — пересчитаем локально
            if (data.new_subtotal === undefined) {
              // Локальный пересчёт (как фолбэк)
              let subtotal = 0;
              document.querySelectorAll('.order-item:not(.is-zero)').forEach(el => {
                const qty = parseInt(el.dataset.qty) || 0;
                const price = parseFloat(el.dataset.priceSnapshot) || 0;
                subtotal += qty * price;
              });
              const delivery = getDeliveryCost(subtotal);
              updateOrderTotals(subtotal, delivery, subtotal + delivery);
            }
          } else {
            alert(data.message || 'Не удалось обновить');
          }
        })
        .catch((err) => {
          console.error('Ошибка сети:', err);
          alert('Проверьте соединение с сервером');
        })
        .finally(() => {
          btn.disabled = false;
          btn.style.opacity = '1';
        });
    });
  });

  // 🔹 Аккордеон: раскрытие/сворачивание
  document.querySelectorAll('.order-arrow').forEach((arrow) => {
    arrow.addEventListener('click', () => {
      const card = arrow.closest('.order-card');
      const details = card.querySelector('.order-details');
      const img = arrow.querySelector('.arrow');
      if (details.style.display === 'none') {
        details.style.display = 'block';
        img.classList.add('rotated');
      } else {
        details.style.display = 'none';
        img.classList.remove('rotated');
      }
    });
  });

  // 🔹 Кнопка "Изменить" статус
  document.querySelectorAll('.btn-edit-order').forEach((btn) => {
    btn.addEventListener('click', () => {
      const orderId = btn.dataset.orderId;
      const select = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
      const statEl = btn.closest('.order-card').querySelector('.order-stat');
      select.classList.toggle('d-none');
      if (!select.classList.contains('d-none')) {
        const currentStatus = statEl.textContent.trim();
        for (let opt of select.options) {
          opt.selected = opt.value === currentStatus;
        }
      }
    });
  });

  // 🔹 Смена статуса через AJAX
    document.querySelectorAll(".status-select").forEach((select) => {
      select.addEventListener("change", () => {
        const orderId = select.dataset.orderId;
        const newStatus = select.value;
        const btn = document.querySelector(`.btn-edit-order[data-order-id="${orderId}"]`);
        const statEl = btn.closest(".order-card").querySelector(".order-stat");
    
        select.disabled = true;
        btn.disabled = true;
        btn.textContent = "...";
    
        const formData = new FormData();
        formData.append("action", "update_order_status");
        formData.append("order_id", orderId);
        formData.append("status", newStatus);
    
        // 🔹 ИСПРАВЛЕНО: явный эндпоинт вместо window.location.href
        fetch("/admin/order/status", {  // 👈 Роутер направит в AdminController::updateOrderStatus()
          method: "POST",
          body: formData,
          headers: { "Accept": "application/json" }
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
              statEl.textContent = newStatus;
              applyStatusColor(statEl, newStatus);
              select.classList.add("d-none");
              
              // 🔹 Если заказ отменён — обнуляем итоги на странице
              if (data.was_cancelled) {
                const card = btn.closest(".order-card");
                const subtotalEl = card.querySelector("#order-subtotal");
                if (subtotalEl) subtotalEl.textContent = "0 руб.";
                
                const deliveryEl = card.querySelector("#order-delivery");
                if (deliveryEl) deliveryEl.textContent = "0 руб.";
                
                const grandTotalEl = card.querySelector("#order-grand-total");
                if (grandTotalEl) grandTotalEl.textContent = "0 руб.";
              }
            } else {
              alert(data.message || "Не удалось обновить статус");
            }
          })
          .catch((err) => {
            console.error("Ошибка сети:", err);
            alert(`Ошибка: ${err.message}\nПроверьте консоль (F12) для деталей`);
          })
          .finally(() => {
            select.disabled = false;
            btn.disabled = false;
            btn.textContent = "Изменить";
          });
      });
    });
});