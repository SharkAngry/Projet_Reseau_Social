window.initPage = function (page) {
  if (page === "login") initLoginForm();
  if (page === "register") initRegisterForm();
  if (page === "forgot-password") initForgotForm();
  if (page === "reset-password") initResetForm();
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

<<<<<<< HEAD
    const nom = document.getElementById("reg-nom").value;
    const prenom = document.getElementById("reg-prenom").value;
    const email = document.getElementById("reg-email").value;
    const password = document.getElementById("reg-password").value;

    try {
      // Utilisation de la fonction globale apiRequest définie par ton groupe
      const data = await apiRequest("auth/register.php", "POST", {
=======
    const nom = document.getElementById("register-nom").value;
    const prenom = document.getElementById("register-prenom").value;
    const email = document.getElementById("register-email").value;
    const password = document.getElementById("register-password").value;

    try {
      await apiRequest("auth/register.php", "POST", {
>>>>>>> 9ffb86bf720583a812a2be570a246fdd7be81be7
        nom,
        prenom,
        email,
        password,
      });
<<<<<<< HEAD

      alert("Inscription réussie ! Un email de confirmation vous a été envoyé."); [cite: 21]
      
      // Une fois inscrit, on redirige l'utilisateur vers l'écran de connexion
      window.location.hash = "login"; 
=======
      window.location.hash = "login";
>>>>>>> 9ffb86bf720583a812a2be570a246fdd7be81be7
    } catch (err) {
      errorEl.textContent = err.message;
    }
  });
<<<<<<< HEAD
}
=======
}

function initForgotForm() {
  const form = document.getElementById("forgot-form");
  const messageEl = document.getElementById("forgot-message");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("forgot-email").value;

    try {
      const data = await apiRequest("auth/forgot-password.php", "POST", {
        email,
      });
      messageEl.style.color = "green";
      messageEl.textContent = data.message;
    } catch (err) {
      messageEl.style.color = "red";
      messageEl.textContent = err.message;
    }
  });
}

function initResetForm() {
  const form = document.getElementById("reset-form");
  const messageEl = document.getElementById("reset-message");
  const token = window.currentQuery.get("token");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const newPassword = document.getElementById("reset-password").value;

    try {
      const data = await apiRequest("auth/reset-password.php", "POST", {
        token,
        new_password: newPassword,
      });
      messageEl.style.color = "green";
      messageEl.textContent = data.message + " Redirection...";
      setTimeout(() => (window.location.hash = "login"), 1500);
    } catch (err) {
      messageEl.style.color = "red";
      messageEl.textContent = err.message;
    }
  });
}
>>>>>>> 9ffb86bf720583a812a2be570a246fdd7be81be7
