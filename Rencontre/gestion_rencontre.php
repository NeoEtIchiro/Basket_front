<?php
date_default_timezone_set('Europe/Paris'); // Remplacez par votre fuseau horaire
require_once __DIR__ . '/../Login/auth.php';
require_once __DIR__ . '/../Database/db_joueurs.php';
require_once __DIR__ . '/../Database/db_rencontres.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Récupérer les données de mise à jour
        $id = $_POST['id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $lieu = $_POST['lieu'];
        $adversaire = $_POST['adversaire'];
        $resultat = isset($_POST['resultat']) ? $_POST['resultat'] : '0 - 0';
        
        // Combiner la date et l'heure
        $date_rencontre = $date . ' ' . $time;

        // Vérifier si la nouvelle date est dans le passé
        if (strtotime($date_rencontre) < time()) {
            $error_message = "La date du match ne peut pas être dans le passé.";
        } else {
            // Appel de la fonction pour mettre à jour le match
            updateRencontre($id, $date_rencontre, $adversaire, $lieu);
            
            // Redirection pour éviter la soumission multiple
            header("Location: gestion_rencontre.php?filter=" . $_POST['filter']);
            exit;
        }
    } else if (isset($_POST['delete_rencontre'])) {
        $id_rencontre = $_POST['id'];
        
        deleteRencontre($id_rencontre);
    } else if (isset($_POST['add'])) {
        // Récupérer les données d'ajout
        $date = $_POST['date'];
        $time = $_POST['time'];
        $lieu = $_POST['lieu'];
        $adversaire = $_POST['adversaire'];
        
        // Combiner la date et l'heure
        $date_rencontre = $date . ' ' . $time;

        addRencontre($date_rencontre, $adversaire, $lieu);
        header("Location: gestion_rencontre.php?filter=" . $_POST['filter']);
        exit;
    } elseif (isset($_POST['update_resultat'])) {
        $id_rencontre = $_POST['id_rencontre'];
        $score_nous = $_POST['score_nous'];
        $score_adversaire = $_POST['score_adversaire'];
        $resultat = $score_nous . ' - ' . $score_adversaire;

        updateResultatRencontre($id_rencontre, $resultat);

        header("Location: gestion_rencontre.php?filter=" . $_POST['filter']);
        exit;
    }
}


// Récupération de tous les joueurs
$rencontres_a_venir = getRencontresAVenir();
$rencontres_passees = getRencontresPassees();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/rencontre.css">
    <link rel="stylesheet" href="../Css/popup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Gestion des Rencontres</title>
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container">
    <!-- Select pour choisir entre les matchs à venir et les matchs passés -->
    <h1><select id="matchSelect" onchange="location = this.value;">
        <option value="gestion_rencontre.php?filter=upcoming" <?= $filter == 'upcoming' ? 'selected' : '' ?>>Matchs à venir</option>
        <option value="gestion_rencontre.php?filter=past" <?= $filter == 'past' ? 'selected' : '' ?>>Matchs passés</option>
    </select></h1>

    <!-- Formulaire d'ajout de rencontre -->
    <button class="openPopupBtn">Ajouter un Nouveau Match</button>

    <?php if ($error_message): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Popup Form -->
    <div id="popupForm" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <h2 id="popupTitle">Ajouter un Nouveau Match</h2>
            <form id="rencontreForm" action="gestion_rencontre.php" method="post">
                <input type="hidden" id="rencontreId" name="id">
                <input type="hidden" id="formAction" name="add">
                <input type="hidden" name="filter" value="<?= $filter ?>">
                <label for="date">Date :</label>
                <input type="date" id="date" name="date" required><br>
                <label for="time">Heure :</label>
                <input type="time" id="time" name="time" required><br>

                <label for="lieu">Lieu :</label>
                <input type="text" id="lieu" name="lieu" required><br>

                <label for="adversaire">Adversaire :</label>
                <input type="text" id="adversaire" name="adversaire" required><br>

                <button type="submit" id="formSubmitBtn">Ajouter le Match</button>
            </form>
        </div>
    </div>

    <!-- Table des matchs à venir -->
    <table id="upcomingMatches" style="display: <?= $filter == 'upcoming' ? 'table' : 'none' ?>;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Lieu</th>
                <th>Adversaire</th>
                <th class="adaptive-column">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rencontres_a_venir as $rencontre): ?>
            <tr>
                <td><?= date('H:i d/m/Y', strtotime($rencontre['date_rencontre'])) ?></td>
                <td><?= htmlspecialchars($rencontre['lieu']) ?></td>
                <td><?= htmlspecialchars($rencontre['adversaire']) ?></td>
                <td>
                    <div class="action-container">
                        <button type="button" class="editBtn actionBtn" 
                                data-id="<?= $rencontre['id_rencontre'] ?>"
                                data-date="<?= htmlspecialchars($rencontre['date_rencontre']) ?>"
                                data-lieu="<?= htmlspecialchars($rencontre['lieu']) ?>"
                                data-adversaire="<?= htmlspecialchars($rencontre['adversaire']) ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="details_rencontre.php" method="get" style="display:inline;">
                            <input type="hidden" name="id_rencontre" value="<?= $rencontre['id_rencontre'] ?>">
                            <input type="hidden" name="mode" value="noter">
                            <button type="submit" class="actionBtn">
                                <i class="fas fa-users"></i>
                            </button>
                        </form>                     
                        <form action="gestion_rencontre.php" method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $rencontre['id_rencontre'] ?>">
                            <input type="hidden" name="filter" value="<?= $filter ?>">
                            <button class="delete actionBtn" type="submit" name="delete_rencontre">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Table des matchs passés -->
    <table id="pastMatches" style="display: <?= $filter == 'past' ? 'table' : 'none' ?>;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Lieu</th>
                <th>Adversaire</th>
                <th class="adaptive-column">Résultat</th>
                <th class="adaptive-column">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rencontres_passees as $rencontre): ?>
            <?php
                // Split du résultat "0 - 0" pour extraire chaque score
                $score_parts = explode(' - ', $rencontre['resultat']);
                $score_nous         = $score_parts[0] ?? 0;
                $score_adversaire   = $score_parts[1] ?? 0;
            ?>
            <tr>
                <td><?= date('H:i d/m/Y', strtotime($rencontre['date_rencontre'])) ?></td>
                <td><?= htmlspecialchars($rencontre['lieu']) ?></td>
                <td><?= htmlspecialchars($rencontre['adversaire']) ?></td>
                <td><?= htmlspecialchars($rencontre['resultat']) ?></td>
                <td>
                    <div class="action-container">
                        <!-- Bouton pour modifier le résultat de la rencontre -->
                        <button 
                            type="button" 
                            class="editResultBtn actionBtn"
                            data-id_rencontre="<?= $rencontre['id_rencontre'] ?>"
                            data-score_nous="<?= $score_nous ?>"
                            data-score_adversaire="<?= $score_adversaire ?>"
                        >
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="details_rencontre.php" method="get" style="display:inline;">
                            <input type="hidden" name="id_rencontre" value="<?= $rencontre['id_rencontre'] ?>">
                            <input type="hidden" name="mode" value="noter">
                            <button type="submit" class="actionBtn">
                                <i class="fas fa-users"></i>
                            </button>
                        </form>                    
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Popup Form for Editing Result -->
    <div id="resultPopupForm" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <h2>Modifier le Résultat</h2>
            <form id="resultForm" action="gestion_rencontre.php" method="post">
                <input type="hidden" id="resultRencontreId" name="id_rencontre">
                <input type="hidden" name="filter" value="<?= $filter ?>">
                <label for="scoreNous">Notre Score :</label>
                <input type="number" id="scoreNous" name="score_nous" required><br>
                <label for="scoreAdversaire">Score Adversaire :</label>
                <input type="number" id="scoreAdversaire" name="score_adversaire" required><br>
                <button type="submit" name="update_resultat">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript pour la popup d'ajout de rencontre
    var addPopup = document.getElementById("popupForm");
    var addBtn = document.getElementsByClassName("openPopupBtn")[0];
    var addClose = document.getElementsByClassName("close")[0];

    addBtn.onclick = function() {
        document.getElementById("popupTitle").innerText = "Ajouter un Nouveau Match";
        document.getElementById("rencontreForm").reset();
        document.getElementById("rencontreId").value = "";
        document.getElementById("formAction").name = "add";
        addPopup.style.display = "block";
    }

    addClose.onclick = function() {
        addPopup.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == addPopup) {
            addPopup.style.display = "none";
        }
    }

    // JavaScript pour la popup de modification de rencontre
    var editPopup = document.getElementById("popupForm");
    var editBtns = document.getElementsByClassName("editBtn");
    var editClose = document.getElementsByClassName("close")[0];

    for (var i = 0; i < editBtns.length; i++) {
        editBtns[i].onclick = function() {
            document.getElementById("popupTitle").innerText = "Modifier le Match";
            document.getElementById("rencontreId").value = this.getAttribute("data-id");

            var date = new Date(this.getAttribute("data-date"));
            date.setMinutes(date.getMinutes() + date.getTimezoneOffset()); // Adjust for timezone offset
            date.setDate(date.getDate() + 1);
            var formattedDate = date.toISOString().split('T')[0];
            document.getElementById("date").value = formattedDate;
            document.getElementById("time").value = this.getAttribute("data-date").split(' ')[1];

            document.getElementById("lieu").value = this.getAttribute("data-lieu");
            document.getElementById("adversaire").value = this.getAttribute("data-adversaire");
            document.getElementById("formAction").name = "update";
            document.getElementById("formSubmitBtn").innerText = "Modifier le Match";
            editPopup.style.display = "block";
        }
    }

    editClose.onclick = function() {
        editPopup.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == editPopup) {
            editPopup.style.display = "none";
        }
    }

    // Popup pour la modification du résultat
    var resultPopup = document.getElementById("resultPopupForm");
    var resultClose = resultPopup.getElementsByClassName("close")[0];
    
    // Quand on clique sur le X (close), on ferme la popup
    resultClose.onclick = function() {
        resultPopup.style.display = "none";
    }
    
    // Si on clique en dehors de la popup, on la ferme aussi
    window.onclick = function(event) {
        if (event.target === resultPopup) {
            resultPopup.style.display = "none";
        }
    }
    
    // Boutons "Modifier Résultat"
    var editResultBtns = document.getElementsByClassName("editResultBtn");
    for (var i = 0; i < editResultBtns.length; i++) {
        editResultBtns[i].onclick = function() {
            // Ouvrir la popup
            resultPopup.style.display = "block";

            // Récupérer les valeurs depuis les attributs data-
            var idRencontre = this.getAttribute("data-id_rencontre");
            var scoreNous = this.getAttribute("data-score_nous");
            var scoreAdversaire = this.getAttribute("data-score_adversaire");
            
            // Remplir le formulaire de la popup
            document.getElementById("resultRencontreId").value = idRencontre;
            document.getElementById("scoreNous").value = scoreNous;
            document.getElementById("scoreAdversaire").value = scoreAdversaire;
        }
    }
</script>

</body>
</html>
