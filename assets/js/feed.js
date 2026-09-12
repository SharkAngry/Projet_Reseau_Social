const _prevInitPage_feed = window.initPage || function () {};
let currentArticlesPage = 1;
let currentArticleSearch = "";

window.initPage = function (page) {
  _prevInitPage_feed(page);
  if (page === "accueil") {
    initPublishForm();
    initArticleSearch();
    currentArticlesPage = 1;
    loadArticles(1, false);
  }
};

function initPublishForm() {
  const form = document.getElementById("add-article-form");
  const fileInput = document.getElementById("article-image");
  const filePreview = document.getElementById("file-name-preview");
  if (!form) return;

  fileInput.addEventListener("change", () => {
    filePreview.textContent =
      fileInput.files.length > 0
        ? fileInput.files[0].name
        : "Aucun fichier sélectionné";
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const description = document.getElementById("article-description").value;

    const formData = new FormData();
    formData.append("description", description);
    if (fileInput.files.length > 0)
      formData.append("image", fileInput.files[0]);

    try {
      await apiRequest("articles/create-article.php", "POST", formData, true);
      form.reset();
      filePreview.textContent = "Aucun fichier sélectionné";
      currentArticlesPage = 1;
      loadArticles(1, false);
    } catch (error) {
      alert("Erreur : " + error.message);
    }
  });
}

async function loadArticles(page = 1, append = false) {
  const container = document.getElementById("articles-flux");
  if (!container) return;

  try {
    const data = await apiRequest(
      `articles/get-articles.php?page=${page}&per_page=10&q=${encodeURIComponent(currentArticleSearch)}`,
      "GET",
    );

    if (!append) {
      container.innerHTML = "";
    }

    if (!append && data.articles.length === 0) {
      container.innerHTML =
        "<p class='empty'>Aucune publication pour le moment.</p>";
    }

    data.articles.forEach((article) => {
      container.insertAdjacentHTML("beforeend", createArticleHtml(article));
    });

    currentArticlesPage = page;
    updateLoadMoreButton(data.pagination?.has_more === true);
  } catch (error) {
    container.innerHTML =
      "<p style='color:red;'>Erreur de chargement du fil.</p>";
  }
}

function updateLoadMoreButton(hasMore) {
  const button = document.getElementById("load-more-articles");
  if (!button) return;

  button.hidden = !hasMore;
  button.disabled = false;
  button.textContent = "Charger plus";
  button.onclick = () => {
    button.disabled = true;
    button.textContent = "Chargement...";
    loadArticles(currentArticlesPage + 1, true);
  };
}

function initArticleSearch() {
  const input = document.getElementById("article-search");
  if (!input || input.dataset.initialized) return;
  input.dataset.initialized = "true";
  let timer;
  input.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      currentArticleSearch = input.value.trim();
      currentArticlesPage = 1;
      loadArticles(1, false);
    }, 250);
  });
}

function createArticleHtml(article) {
  const avatar = escapeFeedHtml(
    article.photo_profil || "assets/images/default-avatar.png",
  );
  const imageHtml = article.image
    ? `<img src="assets/images/posts/${encodeURIComponent(article.image)}" class="post-image" alt="Image de la publication">`
    : "";
  const likedClass = article.my_reaction === "like" ? "active-like" : "";
  const dislikedClass =
    article.my_reaction === "dislike" ? "active-dislike" : "";
  const ownerActions =
    Number(article.owner_id) === Number(getCurrentUserId())
      ? `<div class="post-owner-actions">
        <button type="button" onclick="editArticle(${article.id})">Modifier</button>
        <button type="button" class="danger-link" onclick="deleteArticle(${article.id})">Supprimer</button>
      </div>`
      : "";

  return `
        <div class="post-card" id="post-${article.id}">
            <div class="post-header">
                <img src="${avatar}" class="avatar-sm" alt="Photo de profil">
                <div>
                  <h4>${escapeFeedHtml(article.prenom)} ${escapeFeedHtml(article.nom)}</h4>
                    <span class="post-date">${new Date(article.created_at).toLocaleString("fr-FR")}</span>
                </div>
                ${ownerActions}
            </div>
              <p class="post-description">${escapeFeedHtml(article.description)}</p>
            ${imageHtml}
            <div class="post-actions">
                <button class="btn-like ${likedClass}" onclick="handleReaction(${article.id}, 'like')">
                    👍 <span id="like-count-${article.id}">${article.likes_count}</span>
                </button>
                <button class="btn-dislike ${dislikedClass}" onclick="handleReaction(${article.id}, 'dislike')">
                    👎 <span id="dislike-count-${article.id}">${article.dislikes_count}</span>
                </button>
                <button class="btn-comment" onclick="toggleCommentsSection(${article.id})">💬 Commenter</button>
            </div>
            <div id="comments-area-${article.id}" class="comments-area" style="display:none;">
                <div id="comments-list-${article.id}"></div>
                <form onsubmit="handleCommentSubmit(event, ${article.id})" class="comment-form">
                    <input type="text" id="comment-input-${article.id}" placeholder="Écrire un commentaire...">
                    <button type="submit">Envoyer</button>
                </form>
            </div>
        </div>
    `;
}

function getCurrentUserId() {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}").id || 0;
  } catch (error) {
    return 0;
  }
}

async function editArticle(articleId) {
  const descriptionElement = document.querySelector(
    `#post-${articleId} .post-description`,
  );
  const currentDescription = descriptionElement?.textContent || "";
  const updatedDescription = window.prompt(
    "Modifier la publication",
    currentDescription,
  );
  if (
    updatedDescription === null ||
    updatedDescription.trim() === currentDescription.trim()
  )
    return;

  try {
    await apiRequest("articles/update-article.php", "PUT", {
      id: articleId,
      description: updatedDescription,
    });
    loadArticles(1, false);
  } catch (error) {
    alert(error.message);
  }
}

async function deleteArticle(articleId) {
  if (!window.confirm("Supprimer cette publication ?")) return;
  try {
    await apiRequest("articles/delete-article.php", "DELETE", {
      id: articleId,
    });
    loadArticles(1, false);
  } catch (error) {
    alert(error.message);
  }
}

function escapeFeedHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}
// Function to handle like/dislike reactions
async function handleReaction(articleId, type) {
  try {
    const data = await apiRequest("articles/like-article.php", "POST", {
      article_id: articleId,
      type,
    });
    document.getElementById(`like-count-${articleId}`).textContent =
      data.likes_count;
    document.getElementById(`dislike-count-${articleId}`).textContent =
      data.dislikes_count;

    const likeBtn = document.querySelector(`#post-${articleId} .btn-like`);
    const dislikeBtn = document.querySelector(
      `#post-${articleId} .btn-dislike`,
    );
    likeBtn.classList.remove("active-like");
    dislikeBtn.classList.remove("active-dislike");

    if (data.action !== "removed") {
      (data.type === "like" ? likeBtn : dislikeBtn).classList.add(
        data.type === "like" ? "active-like" : "active-dislike",
      );
    }
  } catch (error) {
    console.error(error);
  }
}
