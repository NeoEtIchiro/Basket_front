const apiBaseUrl = 'http://api-sport.alwaysdata.net/endpoints/endpointRencontre.php';

/**
 * Récupère le token JWT depuis les cookies.
 * @returns {string|null}
 */
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
 * Effectue une requête API.
 * @param {string} endpoint 
 * @param {string} method 
 * @param {object|null} data 
 * @returns {Promise<object>}
 */
async function callApi(endpoint, method = 'GET', data = null) {
    let url = `${apiBaseUrl}${endpoint}`;
    const options = { method, headers: {} };

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
    console.log(responseData);
    return { response: responseData, code: response.status };
}

/**
 * Récupère toutes les rencontres via l'API et met à jour l'affichage.
 */
async function getAllMatches() {
    try {
        const filter = document.getElementById("matchFilter").value;
        let endpointParam = "";
        if (filter === "upcoming") {
            endpointParam = "?avenir";
        } else if (filter === "past") {
            endpointParam = "?passees";
        }
        const result = await callApi(endpointParam, 'GET');
        const matches = result.response.data || result.response;
        displayMatches(matches);
    } catch (error) {
        console.error("Erreur lors de la récupération des rencontres:", error);
    }
}

/**
 * Affiche la liste des rencontres dans le tableau HTML.
 * Filtre les matchs selon le filtre sélectionné.
 * Chaque match doit contenir : id, date_rencontre, lieu, adversaire et éventuellement resultat.
 * Pour les matchs passés, on affiche la colonne résultat et un bouton pour modifier le résultat.
 */
function displayMatches(matches) {
    const filter = document.getElementById("matchFilter").value;
    const tbody = document.getElementById("matchesTableBody");
    tbody.innerHTML = "";

    // Affiche ou masque la colonne Résultat en fonction du filtre
    const resultHeader = document.getElementById("resultHeader");
    resultHeader.style.display = (filter === "past") ? "table-cell" : "none";

    matches.forEach(match => {
        // Formatage de la date et de l'heure à partir du champ date_rencontre
        const dateObj = new Date(match.date_rencontre);
        const formattedDate = dateObj.toLocaleTimeString([], { hour: '2-digit', minute:'2-digit' })
                              + ' ' + dateObj.toLocaleDateString();

        let row = `<tr>
            <td>${formattedDate}</td>
            <td>${match.lieu}</td>
            <td>${match.adversaire}</td>`;
        if (filter === 'past') {
            row += `<td>${match.resultat || '-'}</td>`;
        }
        row += `<td>`;
        if (filter === 'upcoming') {
            row += `<button class="actionBtn editBtn" 
                        data-id="${match.id_rencontre}" 
                        data-date="${match.date_rencontre}" 
                        data-lieu="${match.lieu}" 
                        data-adversaire="${match.adversaire}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="actionBtn deleteBtn" data-id="${match.id_rencontre}">
                        <i class="fas fa-trash-alt"></i>
                    </button>`;
        } else if (filter === 'past') {
            row += `<button class="actionBtn editResultBtn" 
                        data-id="${match.id_rencontre}" 
                        data-resultat="${match.resultat || '0 - 0'}">
                        <i class="fas fa-edit"></i>
                    </button>`;
        }
        row += `</td></tr>`;
        tbody.innerHTML += row;
    });
    attachActionHandlers();
}

/**
 * Attache les gestionnaires d'événements sur les boutons d'édition et de suppression.
 */
function attachActionHandlers() {
    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("popupTitle").innerText = "Modifier la Rencontre";
            document.getElementById("matchId").value = this.dataset.id;
            // Extraction de la date et de l'heure à partir de data-date
            const dateTime = new Date(this.dataset.date);
            document.getElementById("date").value = dateTime.toISOString().split('T')[0];
            document.getElementById("time").value = dateTime.toTimeString().slice(0,5);
            document.getElementById("lieu").value = this.dataset.lieu;
            document.getElementById("adversaire").value = this.dataset.adversaire;
            document.getElementById("formAction").value = "update";
            document.querySelector(".formSubmitBtn").innerText = "Modifier la Rencontre";
            openPopup('popupForm');
        });
    });
    
    document.querySelectorAll(".deleteBtn").forEach(btn => {
        btn.addEventListener("click", async function() {
            if (confirm("Confirmer la suppression ?")) {
                try {
                    const result = await callApi('', 'DELETE', { id: this.dataset.id });
                    console.log("Suppression", result);
                    getAllMatches();
                } catch (error) {
                    console.error("Erreur lors de la suppression :", error);
                }
            }
        });
    });
    
    document.querySelectorAll(".editResultBtn").forEach(btn => {
        btn.addEventListener("click", function() {
            openPopup('resultPopupForm');
            document.getElementById("resultMatchId").value = this.dataset.id;
            const scores = (this.dataset.resultat || "0 - 0").split(" - ");
            document.getElementById("scoreNous").value = scores[0];
            document.getElementById("scoreAdversaire").value = scores[1];
        });
    });
}

/**
 * Gère la soumission du formulaire d'ajout/modification d'une rencontre.
 */
document.getElementById("matchForm").addEventListener("submit", async function(event) {
    event.preventDefault();
    const formAction = document.getElementById("formAction").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;
    const date_rencontre = date + ' ' + time;
    
    // Pour update classique, on fournit également un champ "resultat" vide par défaut.
    const data = {
        id: document.getElementById("matchId").value,
        date_rencontre: date_rencontre,
        lieu: document.getElementById("lieu").value,
        adversaire: document.getElementById("adversaire").value,
        resultat: "" // valeur par défaut si le résultat n'est pas mis à jour
    };

    try {
        if (formAction === "add") {
            const result = await callApi('', 'POST', data);
            console.log("Ajout", result);
        } else if (formAction === "update") {
            const result = await callApi('', 'PUT', data);
            console.log("Mise à jour", result);
        }
        closePopup('popupForm');
        getAllMatches();
    } catch (error) {
        console.error("Erreur lors de l'envoi du formulaire:", error);
    }
});

/**
 * Gère la soumission du formulaire de mise à jour du résultat.
 */
document.getElementById("resultForm").addEventListener("submit", async function(event) {
    event.preventDefault();
    const data = {
        id: document.getElementById("resultMatchId").value,
        score_nous: document.getElementById("scoreNous").value,
        score_adversaire: document.getElementById("scoreAdversaire").value
    };

    try {
        const result = await callApi('?update_resultat', 'PUT', data);
        console.log("Mise à jour du résultat", result);
        closePopup('resultPopupForm');
        getAllMatches();
    } catch (error) {
        console.error("Erreur lors de la mise à jour du résultat:", error);
    }
});

/**
 * Ouvre la popup spécifiée par son id.
 * @param {string} popupId 
 */
function openPopup(popupId) {
    document.getElementById(popupId).style.display = "block";
}

/**
 * Ferme la popup spécifiée par son id.
 * @param {string} popupId 
 */
function closePopup(popupId) {
    document.getElementById(popupId).style.display = "none";
}

/**
 * Gestion de la fermeture des popups via le bouton Close et en cliquant hors de la popup.
 */
document.querySelectorAll(".popup .close").forEach(closeBtn => {
    closeBtn.addEventListener("click", function() {
        this.parentElement.parentElement.style.display = "none";
    });
});
window.addEventListener("click", function(event) {
    document.querySelectorAll(".popup").forEach(popup => {
        if (event.target === popup) {
            popup.style.display = "none";
        }
    });
});


document.querySelector(".openPopupBtn").addEventListener("click", function() {
    document.getElementById("popupTitle").innerText = "Ajouter une Nouvelle Rencontre";
    document.getElementById("matchForm").reset();
    document.getElementById("matchId").value = "";
    document.getElementById("formAction").value = "add";
    document.querySelector(".formSubmitBtn").innerText = "Ajouter la Rencontre";
    openPopup('popupForm');
});
/**
 * Rafraîchit l'affichage lorsque le filtre change.
 */
document.getElementById("matchFilter").addEventListener("change", getAllMatches);

/**
 * Chargement initial des rencontres au chargement de la page.
 */
window.addEventListener("DOMContentLoaded", getAllMatches);