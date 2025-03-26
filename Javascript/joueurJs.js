const apiBaseUrl = 'http://api-sport.alwaysdata.net/endpoints/endpointJoueur.php';


function getJwtFromCookie() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'jwt') {
            return value;
        }
    }
    return null;
}

/**
 * Effectue une requête API via fetch.
 * @param {string} endpoint - L'endpoint à appeler (ex: '/add', '/update', '/delete', '/all')
 * @param {string} method - La méthode HTTP (GET, POST, PUT, DELETE)
 * @param {object|null} data - Les données à envoyer (si nécessaire)
 * @returns {Promise<object>} La réponse JSON et le code HTTP
 */
async function callApi(endpoint, method = 'GET', data = null) {
    let url = `${apiBaseUrl}${endpoint}`;
    const options = { method, headers: {} };

    // Récupérer le token JWT depuis les cookies
    const jwt = getJwtFromCookie();
    if (jwt) {
        options.headers['Authorization'] = `Bearer ${jwt}`;
    }

    if (method === 'POST' || method === 'PUT') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(data);
    } else if (method === 'DELETE' && data && data.id) {
        url += `?id=${encodeURIComponent(data.id)}`;
    }

    const response = await fetch(url, options);
    const responseData = await response.json();
    return { response: responseData, code: response.status };
}

/**
 * Gère la soumission du formulaire pour ajouter ou mettre à jour un joueur.
 */
document.getElementById("joueurForm").addEventListener("submit", async function(event) {
    event.preventDefault();
    const formAction = document.getElementById("formAction").value;
    const data = {
        id: document.getElementById("joueurId").value,
        nom: document.getElementById("nom").value,
        prenom: document.getElementById("prenom").value,
        numero_licence: document.getElementById("numero_licence").value,
        date_naissance: document.getElementById("date_naissance").value,
        taille: document.getElementById("taille").value,
        poids: document.getElementById("poids").value,
        commentaire: document.getElementById("commentaire").value,
        statut: document.getElementById("statut").value,
    };

    try {
        if (formAction === "add") {
            // Appel de l'API pour ajouter un joueur
            const result = await callApi('', 'POST', data);
            console.log("Ajout", result);
        } else if (formAction === "update") {
            // Appel de l'API pour mettre à jour un joueur
            const result = await callApi('', 'PUT', data);
            console.log("Update", result);
        }
        // Rafraîchir la liste des joueurs après l'action
        await getAllPlayers();
        closePopup();
    } catch (error) {
        console.error("Erreur lors de l'envoi du formulaire:", error);
    }
});

/**
 * Récupère tous les joueurs via l'API et met à jour l'affichage.
 */
async function getAllPlayers() {
    try {
        const result = await callApi('', 'GET');
        const players = result.response.data || result.response; // adapter selon le format renvoyé
        displayPlayers(players);
    } catch (error) {
        console.error("Erreur lors de la récupération des joueurs:", error);
    }
}

async function hasParticipated(playerId) {
    const result = await callApi(`?id=${playerId}`, 'GET');
    return result.response.data === true;
}

/**
 * Affiche la liste des joueurs dans le tableau HTML.
 * @param {Array} players - Liste des joueurs
 */
function displayPlayers(players) {
    const tbody = document.getElementById("playersTableBody");
    tbody.innerHTML = "";
    players.forEach(player => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${player.nom}</td>
            <td>${player.prenom}</td>
            <td>${player.numero_licence || player.licence}</td>
            <td>${new Date(player.dateNaissance).toLocaleDateString()}</td>
            <td>${player.taille} cm</td>
            <td>${player.poids} kg</td>
            <td>${player.commentaire}</td>
            <td>${player.statut}</td>
            <td>
                <button class="actionBtn editBtn"
                    data-id="${player.id_joueur}"
                    data-nom="${player.nom}"
                    data-prenom="${player.prenom}"
                    data-licence="${player.numero_licence || player.licence}"
                    data-dateNaissance="${player.dateNaissance}"
                    data-taille="${player.taille}"
                    data-poids="${player.poids}"
                    data-commentaire="${player.commentaire}"
                    data-statut="${player.statut}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete actionBtn deleteBtn" data-id="${player.id_joueur}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        // Vérifier si le joueur a participé et désactiver le bouton delete si c'est le cas.
        hasParticipated(player.id_joueur).then((hasPart) => {
            if (hasPart) {
                const deleteBtn = tr.querySelector(".delete");
                deleteBtn.disabled = true;
            }
        });
    });
    attachActionHandlers();
}

/**
 * Attache des gestionnaires d'événements aux boutons d'édition et de suppression.
 */
function attachActionHandlers() {
    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("popupTitle").innerText = "Modifier le Joueur";
            document.getElementById("joueurId").value = this.dataset.id;
            document.getElementById("nom").value = this.dataset.nom;
            document.getElementById("prenom").value = this.dataset.prenom;
            document.getElementById("numero_licence").value = this.dataset.licence;
            document.getElementById("date_naissance").value = this.dataset.dateNaissance;
            document.getElementById("taille").value = this.dataset.taille;
            document.getElementById("poids").value = this.dataset.poids;
            document.getElementById("commentaire").value = this.dataset.commentaire;
            document.getElementById("statut").value = this.dataset.statut;
            document.getElementById("formAction").value = "update";
            document.querySelector(".formSubmitBtn").innerText = "Modifier le Joueur";
            openPopup();
        });
    });

    document.querySelectorAll(".deleteBtn").forEach(btn => {
        btn.addEventListener("click", async function() {
            if (confirm("Confirmer la suppression ?")) {
                try {
                    const result = await callApi('', 'DELETE', { id: this.dataset.id });
                    console.log("Suppression", result);
                    await getAllPlayers();
                } catch (error) {
                    console.error("Erreur lors de la suppression :", error);
                }
            }
        });
    });
}

/**
 * Ouverture et fermeture du popup.
 */
function openPopup() {
    document.getElementById("popupForm").style.display = "block";
}
function closePopup() {
    document.getElementById("popupForm").style.display = "none";
}

// Gestion du bouton d'ouverture du popup en mode "ajout"
document.querySelector(".openPopupBtn").addEventListener("click", function() {
    document.getElementById("popupTitle").innerText = "Ajouter un Nouveau Joueur";
    document.getElementById("joueurForm").reset();
    document.getElementById("joueurId").value = "";
    document.getElementById("formAction").value = "add";
    document.querySelector(".formSubmitBtn").innerText = "Ajouter le Joueur";
    openPopup();
});

// Gestion de la fermeture du popup
document.querySelector(".close").addEventListener("click", closePopup);
window.addEventListener("click", function(event) {
    const popup = document.getElementById("popupForm");
    if (event.target === popup) {
        closePopup();
    }
});

// Récupère tous les joueurs au chargement de la page
window.addEventListener("DOMContentLoaded", getAllPlayers);