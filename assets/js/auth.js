window.initPage = function (page) {
  if (page === "login") {
    initLoginForm();
  }
  if (page === "register") {
    initRegisterForm();
  }
};

function initLoginForm() {
  const form = document.getElementById("login-form");
  const errorEl = document.getElementById("login-error");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorEl.textContent = "";

    const email = document.getElementById("login-email").value;
    const password = document.getElementById("login-password").value;

    try {
      const data = await apiRequest("auth/login.php", "POST", {
        email,
        password,
      });
      sessionStorage.setItem("token", data.token);
      sessionStorage.setItem("user", JSON.stringify(data.user));
      window.location.hash = "accueil";
    } catch (err) {
      errorEl.textContent = err.message;
    }
  });
}

function initRegisterForm() {
  const form = document.getElementById("register-form");
  const errorEl = document.getElementById("register-error");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorEl.textContent = "";

    const nom = document.getElementById("reg-nom").value;
    const prenom = document.getElementById("reg-prenom").value;
    const email = document.getElementById("reg-email").value;
    const password = document.getElementById("reg-password").value;

    try {
      // Utilisation de la fonction globale apiRequest définie par ton groupe
      const data = await apiRequest("auth/register.php", "POST", {
        nom,
        prenom,
        email,
        password,
      });

      alert("Inscription réussie ! Un email de confirmation vous a été envoyé."); [cite: 21]
      
      // Une fois inscrit, on redirige l'utilisateur vers l'écran de connexion
      window.location.hash = "login"; 
    } catch (err) {
      errorEl.textContent = err.message;
    }
  });
}