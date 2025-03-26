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

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const jwt = getJwtFromCookie();

        // Requête des statistiques globales
        const responseGlobal = await fetch("http://api-sport.alwaysdata.net/endpoints/endpointStats.php?type=global", {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${jwt}`
            }
        });
        const globalData = await responseGlobal.json();
        // On suppose que l'API renvoie dans data : total_matches, wins, draws, losses, win_percentage, draw_percentage, loss_percentage
        const global = globalData.data;

        // Mise à jour de la section globalStats
        const globalStatsDiv = document.getElementById("globalStats");
        globalStatsDiv.innerHTML = `
            <h2>Statistiques Globales</h2>
            <p>Nombre total de matchs : ${global.total_matches}</p>
            <p>Matchs gagnés : ${global.wins} (${global.win_percentage}%)</p>
            <p>Matchs nuls : ${global.draws} (${global.draw_percentage}%)</p>
            <p>Matchs perdus : ${global.losses} (${global.loss_percentage}%)</p>
        `;

        // Requête de la liste des joueurs via l'endpoint endpointJoueur.php
        const responsePlayers = await fetch("http://api-sport.alwaysdata.net/endpoints/endpointJoueur.php", {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${jwt}`
            }
        });
        const playersData = await responsePlayers.json();
        // On suppose que l'API renvoie un tableau de joueurs dans data
        const players = playersData.data;

        // Remplissage du select avec la liste des joueurs
        const playerSelect = document.getElementById("playerSelect");
        playerSelect.innerHTML = `<option value="">-- Sélectionner un joueur --</option>`;
        players.forEach(player => {
            const option = document.createElement("option");
            option.value = player.id_joueur; // clé identifiant le joueur
            option.textContent = player.nom + " " + player.prenom;
            playerSelect.appendChild(option);
        });

        // Requête pour récupérer les statistiques de tous les joueurs via endpointStats.php (type=player)
        const responsePlayerStats = await fetch("http://api-sport.alwaysdata.net/endpoints/endpointStats.php?type=player", {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${jwt}`
            }
        });
        const statsData = await responsePlayerStats.json();
        // On suppose que l'API renvoie un objet dont chaque propriété correspond à un joueur (ou un tableau d'objets incluant id_joueur)
        const playersStats = statsData.data;

        // Lorsque l'utilisateur sélectionne un joueur, on affiche les statistiques correspondantes
        playerSelect.addEventListener("change", () => {
            const selectedId = playerSelect.value;
            const playerStatsDiv = document.getElementById("playerStats");
            if (selectedId === "") {
                playerStatsDiv.innerHTML = "";
                return;
            }
            // Si playersStats est un objet indexé par id, on peut faire directement:
            const p = playersStats[selectedId];
            // Sinon, si c'est un tableau, utilisez find:
            // const p = playersStats.find(pl => pl.id_joueur == selectedId);

            if (!p) {
                playerStatsDiv.innerHTML = "<p>Aucune statistique trouvée pour ce joueur.</p>";
                return;
            }

            playerStatsDiv.innerHTML = `
                <h3>${p.nom_complet ? p.nom_complet : p.nom + " " + p.prenom}</h3>
                <p>Statut : ${p.statut || "0"}</p>
                <p>Total de matchs : ${p.total_matchs || "0"}</p>
                <p>Titularisations : ${p.titularisations || "0"}</p>
                <p>Moyenne des évaluations : ${p.moyenne_evaluations ? parseFloat(p.moyenne_evaluations).toFixed(2) : "0"}</p>
                <p>Pourcentage de matchs gagnés : ${p.pourcentage_matchs_gagnes ? parseFloat(p.pourcentage_matchs_gagnes).toFixed(2) + "%" : "0%"}</p>
                <p>Poste préféré : ${p.poste_prefere || "Aucun"}</p>
                <p>Sélections consécutives : ${p.selections_consecutives || "0"}</p>
            `;
        });
    } catch (error) {
        console.error("Erreur lors du chargement des statistiques :", error);
    }
});