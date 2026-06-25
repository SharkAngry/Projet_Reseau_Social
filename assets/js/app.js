const routes = {
  login: "vues/clients/login.html",
  register: "vues/clients/register.html",
  "forgot-password": "vues/clients/forgot-password.html",
  "reset-password": "vues/clients/reset-password.html",
  accueil: "vues/clients/accueil.html",
  profil: "vues/clients/profil.html",
  amis: "vues/clients/amis.html",
  chat: "vues/clients/chat.html",
};

const PROTECTED_ROUTES = ["accueil", "profil", "amis", "chat"];

async function router() {
  const fullHash = window.location.hash.replace("#", "") || "login";
  const [hash, queryString] = fullHash.split("?");
  window.currentQuery = new URLSearchParams(queryString || "");

  const isLoggedIn = !!sessionStorage.getItem("token");

  if (PROTECTED_ROUTES.includes(hash) && !isLoggedIn) {
    window.location.hash = "login";
    return;
  }
  if (hash === "login" && isLoggedIn) {
    window.location.hash = "accueil";
    return;
  }

  const viewPath = routes[hash];
  if (!viewPath) {
    document.getElementById("app").innerHTML = "<p>Page introuvable</p>";
    return;
  }

  const response = await fetch(viewPath);
  document.getElementById("app").innerHTML = await response.text();

  if (typeof window.initPage === "function") {
    window.initPage(hash);
  }
}

window.addEventListener("hashchange", router);
window.addEventListener("DOMContentLoaded", router);
