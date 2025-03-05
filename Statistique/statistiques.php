<?php
require_once __DIR__ . '/../Database/db_rencontres.php';
require_once __DIR__ . '/../Database/db_joueurs.php';
require_once __DIR__ . '/../Login/auth.php';

// Récupérer les données des matchs
$rencontres = getAllRencontres();
$joueurs = getAllJoueurs();

// Calculer les statistiques globales
$total_matches = count($rencontres);
$wins = 0;
$draws = 0;
$losses = 0;

foreach ($rencontres as $rencontre) {
    $resultat = explode(' - ', $rencontre['resultat']);
    if (count($resultat) == 2) {
        list($score_nous, $score_adversaire) = $resultat;
        if ($score_nous > $score_adversaire) {
            $wins++;
        } elseif ($score_nous == $score_adversaire) {
            $draws++;
        } else {
            $losses++;
        }
    }
}

$win_percentage = $total_matches ? ($wins / $total_matches) * 100 : 0;
$draw_percentage = $total_matches ? ($draws / $total_matches) * 100 : 0;
$loss_percentage = $total_matches ? ($losses / $total_matches) * 100 : 0;

// Calculer les statistiques par joueur
$player_stats = [];
foreach ($joueurs as $joueur) {
    $player_stats[$joueur['id_joueur']] = [
        'statut' => $joueur['statut'],
        'postes' => [],
        'titularisations' => 0,
        'remplacements' => 0,
        'moyenne_evaluations' => 0,
        'matchs_gagnes' => 0,
        'total_matchs' => 0,
        'selections_consecutives' => 0,
    ];
}

foreach ($rencontres as $rencontre) {
    $resultat = explode(' - ', $rencontre['resultat']);
    if (count($resultat) == 2) {
        list($score_nous, $score_adversaire) = $resultat;
        $joueurs_rencontre = getJoueursByRencontre($rencontre['id_rencontre']);
        foreach ($joueurs_rencontre as $joueur) {
            $player_stats[$joueur['id_joueur']]['total_matchs']++;
            if ($joueur['titulaire']) {
                $player_stats[$joueur['id_joueur']]['titularisations']++;
            } else {
                $player_stats[$joueur['id_joueur']]['remplacements']++;
            }
            $player_stats[$joueur['id_joueur']]['moyenne_evaluations'] += $joueur['note'];
            if ($score_nous > $score_adversaire) {
                $player_stats[$joueur['id_joueur']]['matchs_gagnes']++;
            }
            if (!isset($player_stats[$joueur['id_joueur']]['postes'][$joueur['poste']])) {
                $player_stats[$joueur['id_joueur']]['postes'][$joueur['poste']] = 0;
            }
            $player_stats[$joueur['id_joueur']]['postes'][$joueur['poste']]++;
        }
    }
}

foreach ($player_stats as $id_joueur => $stats) {
    if ($stats['total_matchs'] > 0) {
        $player_stats[$id_joueur]['moyenne_evaluations'] /= $stats['total_matchs'];
    }
    $player_stats[$id_joueur]['pourcentage_matchs_gagnes'] = $stats['total_matchs'] ? ($stats['matchs_gagnes'] / $stats['total_matchs']) * 100 : 0;
    arsort($player_stats[$id_joueur]['postes']);
    $player_stats[$id_joueur]['poste_prefere'] = key($player_stats[$id_joueur]['postes']);
}

// Calculer le nombre de sélections consécutives pour chaque joueur
foreach ($joueurs as $joueur) {
    $selections_consecutives = 0;
    $max_selections_consecutives = 0;
    foreach ($rencontres as $rencontre) {
        $joueurs_rencontre = getJoueursByRencontre($rencontre['id_rencontre']);
        $is_selected = false;
        foreach ($joueurs_rencontre as $joueur_rencontre) {
            if ($joueur_rencontre['id_joueur'] == $joueur['id_joueur']) {
                $is_selected = true;
                break;
            }
        }
        if ($is_selected) {
            $selections_consecutives++;
            if ($selections_consecutives > $max_selections_consecutives) {
                $max_selections_consecutives = $selections_consecutives;
            }
        } else {
            $selections_consecutives = 0;
        }
    }
    $player_stats[$joueur['id_joueur']]['selections_consecutives'] = $max_selections_consecutives;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/stats.css">
    <title>Statistiques</title>
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container">
    <h1>Statistiques</h1>

    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'Globales')" id="defaultOpen">Statistiques Globales</button>
        <button class="tablinks" onclick="openTab(event, 'Joueurs')">Statistiques par Joueur</button>
    </div>

    <div id="Globales" class="tabcontent">
        <h2>Statistiques Globales</h2>
        <p>Nombre total de matchs : <?= $total_matches ?></p>
        <p>Matchs gagnés : <?= $wins ?> (<?= number_format($win_percentage, 2) ?>%)</p>
        <p>Matchs nuls : <?= $draws ?> (<?= number_format($draw_percentage, 2) ?>%)</p>
        <p>Matchs perdus : <?= $losses ?> (<?= number_format($loss_percentage, 2) ?>%)</p>
    </div>

    <div id="Joueurs" class="tabcontent">
        <h2>Statistiques par Joueur</h2>
        <label for="playerSelect">Choisir un joueur :</label>
        <select id="playerSelect" onchange="showPlayerStats()">
            <option value="">--Sélectionner un joueur--</option>
            <?php foreach ($joueurs as $joueur): ?>
                <option value="<?= $joueur['id_joueur'] ?>"><?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
        <div id="playerStats">
            <!-- Les statistiques du joueur sélectionné seront affichées ici -->
        </div>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    function showPlayerStats() {
        var playerSelect = document.getElementById("playerSelect");
        var playerId = playerSelect.value;
        var playerStatsDiv = document.getElementById("playerStats");

        if (playerId) {
            var playerStats = <?= json_encode($player_stats) ?>;
            var stats = playerStats[playerId];

            if (stats.total_matchs > 0) {
                var html = `
                    <h3>Statistiques de ${playerSelect.options[playerSelect.selectedIndex].text}</h3>
                    <p>Statut : ${stats.statut}</p>
                    <p>Poste Préféré : ${stats.poste_prefere}</p>
                    <p>Titularisations : ${stats.titularisations}</p>
                    <p>Remplacements : ${stats.remplacements}</p>
                    <p>Moyenne des Évaluations : ${stats.moyenne_evaluations.toFixed(2)}</p>
                    <p>Pourcentage de Matchs Gagnés : ${stats.pourcentage_matchs_gagnes.toFixed(2)}%</p>
                    <p>Sélections Consécutives : ${stats.selections_consecutives}</p>
                `;
            } else {
                var html = `
                    <h3>Statistiques de ${playerSelect.options[playerSelect.selectedIndex].text}</h3>
                    <p>Statut : ${stats.statut}</p>
                    <p>Ce joueur n'a pas encore participé à un match et il ne peut pas y avoir de statistiques sur lui.</p>
                `;
            }

            playerStatsDiv.innerHTML = html;
        } else {
            playerStatsDiv.innerHTML = "";
        }
    }

    // Open the default tab
    document.getElementById("defaultOpen").click();
</script>

</body>
</html>