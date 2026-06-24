function initFriendsModule() {
    loadSuggestions();
    loadInvitations();
    loadFriendsList();

    const searchInput = document.getElementById("search-users-input");
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            loadSuggestions(e.target.value);
        });
    }
}

async function loadSuggestions(searchQuery = "") {
    const resultsContainer = document.getElementById("search-results");
    if (!resultsContainer) return;

    try {
        const users = await sendRequest(`api/friends/search.php?query=${encodeURIComponent(searchQuery)}`, "GET");
        resultsContainer.innerHTML = users.length === 0 ? "<p class='empty'>Aucun utilisateur trouvé.</p>" : "";
        users.forEach(user => {
            resultsContainer.innerHTML += `
                <div class="user-card">
                    <img src="assets/images/avatars/${user.avatar || 'default.png'}" class="avatar-sm">
                    <h4>${user.prenom} ${user.nom}</h4>
                    <div class="card-actions">
                        <button class="btn-action btn-add" onclick="handleFriendAction('send', ${user.id})">Ajouter</button>
                    </div>
                </div>
            `;
        });
    } catch (error) {
        console.error(error);
    }
}

async function loadInvitations() {
    const listContainer = document.getElementById("invitations-list");
    if (!listContainer) return;

    try {
        const invitations = await sendRequest("api/friends/get-invitations.php", "GET");
        listContainer.innerHTML = invitations.length === 0 ? "<p class='empty'>Aucune invitation.</p>" : "";
        invitations.forEach(invite => {
            listContainer.innerHTML += `
                <div class="user-card alert-card">
                    <img src="assets/images/avatars/${invite.avatar || 'default.png'}" class="avatar-sm">
                    <h4>${invite.prenom} ${invite.nom}</h4>
                    <div class="card-actions">
                        <button class="btn-action btn-accept" onclick="handleFriendAction('accept', ${invite.id})">Accepter</button>
                        <button class="btn-action btn-decline" onclick="handleFriendAction('decline', ${invite.id})">Refuser</button>
                    </div>
                </div>
            `;
        });
    } catch (error) {
        console.error(error);
    }
}

async function loadFriendsList() {
    const listContainer = document.getElementById("my-friends-list");
    if (!listContainer) return;

    try {
        const friends = await sendRequest("api/friends/get-friends.php", "GET");
        listContainer.innerHTML = friends.length === 0 ? "<p class='empty'>Vous n'avez pas encore d'amis.</p>" : "";
        friends.forEach(friend => {
            listContainer.innerHTML += `
                <div class="user-card">
                    <img src="assets/images/avatars/${friend.avatar || 'default.png'}" class="avatar-sm">
                    <h4>${friend.prenom} ${friend.nom}</h4>
                    <div class="card-actions">
                        <button class="btn-action btn-remove" onclick="handleFriendAction('remove', ${friend.id})">Retirer</button>
                    </div>
                </div>
            `;
        });
    } catch (error) {
        console.error(error);
    }
}

async function handleFriendAction(actionType, targetId) {
    try {
        const response = await sendRequest("api/friends/action.php", "POST", {
            action: actionType,
            target_id: targetId
        });
        if (response.status === "success") {
            initFriendsModule();
        } else {
            alert(response.message || "Une erreur est survenue.");
        }
    } catch (error) {
        console.error(error);
    }
}