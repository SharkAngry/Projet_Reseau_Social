// assets/js/comments.js

// 1. Afficher/Masquer et charger les commentaires
async function toggleCommentsSection(articleId) {
    const commentsArea = document.getElementById(`comments-area-${articleId}`);
    const commentsList = document.getElementById(`comments-list-${articleId}`);

    if (!commentsArea) return;

    // Si la section est déjà ouverte, on la ferme
    if (commentsArea.style.display === "block") {
        commentsArea.style.display = "none";
        return;
    }

    // Sinon, on l'affiche et on charge les données
    commentsArea.style.display = "block";
    commentsList.innerHTML = "<p style='font-size:12px; color:#65676b; padding:5px;'>Chargement des commentaires...</p>";

    try {
        const response = await fetch(`api/articles/get-comments.php?article_id=${articleId}`);
        const comments = await response.json();

        commentsList.innerHTML = ""; // Vider le texte de chargement

        if (comments.length === 0) {
            commentsList.innerHTML = "<p style='font-size:12px; color:#65676b; padding:5px;'>Aucun commentaire. Soyez le premier à réagir !</p>";
            return;
        }

        comments.forEach(comment => {
            commentsList.insertAdjacentHTML("beforeend", createCommentHtml(comment));
        });

    } catch (error) {
        commentsList.innerHTML = "<p style='color:red; font-size:12px;'>Erreur de chargement.</p>";
    }
}

// 2. Soumettre un nouveau commentaire en AJAX (Zéro rechargement)
async function handleCommentSubmit(event, articleId) {
    event.preventDefault();

    const input = document.getElementById(`comment-input-${articleId}`);
    const commentsList = document.getElementById(`comments-list-${articleId}`);
    const contenu = input.value.trim();
    const userId = sessionStorage.getItem("user") ? JSON.parse(sessionStorage.getItem("user")).id : "1";

    if (!contenu) return;

    try {
        const response = await fetch("api/articles/add-comment.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-User-Id": userId
            },
            body: JSON.stringify({ article_id: articleId, contenu: contenu })
        });

        const data = await response.json();

        if (data.success) {
            input.value = ""; // Vider le champ de saisie

            // Si c'était le premier commentaire, on efface le texte "Aucun commentaire"
            if (commentsList.querySelector('p')) {
                commentsList.innerHTML = "";
            }

            // Ajouter le nouveau commentaire à la fin de la liste de manière fluide
            commentsList.insertAdjacentHTML("beforeend", createCommentHtml(data.comment));
        } else {
            alert(data.message);
        }

    } catch (error) {
        console.error("Erreur lors de l'envoi du commentaire :", error);
    }
}

// Fonction outil UI/UX pour structurer un commentaire sous forme de bulle (style Facebook)
function createCommentHtml(comment) {
    return `
        <div style="display:flex; align-items:flex-start; margin-bottom:10px; font-size:13px;">
            <img src="assets/images/avatars/${comment.photo_profil || 'default-avatar.png'}" style="width:32px; height:32px; border-radius:50%; margin-right:8px; object-fit:cover; margin-top:2px;">
            <div style="background:#f0f2f5; border-radius:18px; padding:8px 12px; max-width:85%;">
                <span style="font-weight:bold; color:#050505; display:block;">${comment.prenom} ${comment.nom}</span>
                <span style="color:#050505; word-break:break-word;">${comment.contenu}</span>
            </div>
        </div>
    `;
}