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

    const nom = document.getElementById("register-nom").value;
    const prenom = document.getElementById("register-prenom").value;
    const email = document.getElementById("register-email").value;
    const password = document.getElementById("register-password").value;

    try {
      await apiRequest("auth/register.php", "POST", {
        nom,
        prenom,
        email,
        password,
      });
      window.location.hash = "login";
    } catch (err) {
      errorEl.textContent = err.message;
    }
  });
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
