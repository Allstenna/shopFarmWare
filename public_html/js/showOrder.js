document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".order-arrow").forEach((arrow) => {
    arrow.addEventListener("click", () => {
      const card = arrow.closest(".order-card");
      const details = card.querySelector(".order-details");
      const img = arrow.querySelector(".arrow");
      if (details.style.display === "none") {
        details.style.display = "block";
        img.classList.add("rotated");
      } else {
        details.style.display = "none";
        img.classList.remove("rotated");
      }
    });
  });

  document.querySelectorAll(".btn-edit-order").forEach((btn) => {
    btn.addEventListener("click", () => {
      const oid = btn.dataset.orderId;
      const sel = document.querySelector(
        `.status-select[data-order-id="${oid}"]`,
      );
      const stat = btn.closest(".order-card").querySelector(".order-stat");
      sel.classList.toggle("d-none");
      if (!sel.classList.contains("d-none")) {
        const current = stat.textContent.trim();
        for (let opt of sel.options) opt.selected = opt.value === current;
      }
    });
  });

  document.querySelectorAll(".status-select").forEach((sel) => {
    sel.addEventListener("change", () => {
      const oid = sel.dataset.orderId;
      const newStatus = sel.value;
      const btn = document.querySelector(
        `.btn-edit-order[data-order-id="${oid}"]`,
      );
      const stat = btn.closest(".order-card").querySelector(".order-stat");
      sel.disabled = true;
      btn.disabled = true;
      btn.textContent = "...";

      const formData = new FormData();
      formData.append("action", "update_order_status");
      formData.append("order_id", oid);
      formData.append("status", newStatus);

      fetch("", { method: "POST", body: formData })
        .then((r) => r.json())
        .then((d) => {
          if (d.success) {
            stat.textContent = newStatus;
            // Применяем цвет динамически
            stat.className = "order-stat";
            if (newStatus === "Новый") stat.classList.add("status-new");
            else if (newStatus === "В обработке" || newStatus === "Отправлен")
              stat.classList.add("status-process");
            else if (newStatus === "Отменен")
              stat.classList.add("status-cancelled");
            else if (newStatus === "Выполнен")
              stat.classList.add("status-completed");
            sel.classList.add("d-none");
          } else alert(d.message || "Не удалось обновить статус");
        })
        .catch(() => alert("Ошибка сети"))
        .finally(() => {
          sel.disabled = false;
          btn.disabled = false;
          btn.textContent = "Изменить";
        });
    });
  });
});
