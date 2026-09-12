function notificationLabel(notification) {
  const actor = `${escapeNotificationText(notification.prenom)} ${escapeNotificationText(notification.nom)}`;
  if (notification.type === "like") return `${actor} a aimé votre publication.`;
  if (notification.type === "comment")
    return `${actor} a commenté votre publication.`;
  return `${actor} vous a envoyé une invitation.`;
}

function escapeNotificationText(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

async function loadNotifications() {
  const list = document.getElementById("notifications-list");
  const count = document.getElementById("notifications-count");
  if (!list || !count || !sessionStorage.getItem("token")) return;

  try {
    const data = await apiRequest("notifications/get.php", "GET");
    count.textContent = data.unread_count;
    count.hidden = data.unread_count === 0;
    list.innerHTML = data.notifications.length
      ? data.notifications
          .map(
            (item) =>
              `<div class="notification-item ${item.is_read ? "" : "unread"}">
                ${notificationLabel(item)}
                <small>${escapeNotificationText(new Date(item.created_at).toLocaleString("fr-FR"))}</small>
              </div>`,
          )
          .join("")
      : "<p class='empty'>Aucune notification.</p>";
  } catch (error) {
    list.innerHTML = "<p class='empty'>Notifications indisponibles.</p>";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("notifications-toggle");
  const panel = document.getElementById("notifications-panel");
  const markRead = document.getElementById("notifications-mark-read");

  toggle?.addEventListener("click", async () => {
    panel.hidden = !panel.hidden;
    if (!panel.hidden) await loadNotifications();
  });

  markRead?.addEventListener("click", async () => {
    await apiRequest("notifications/read.php", "POST");
    await loadNotifications();
  });

  loadNotifications();
  window.setInterval(loadNotifications, 30000);
});
