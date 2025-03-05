<?php
// gestion_joueur.php
require_once __DIR__ . '/../Login/auth.php';
require_once __DIR__ . '/../Database/db_joueurs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Récupérer les données de mise à jour
        $id = $_POST['id'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $numero_licence = $_POST['numero_licence'];
        $date_naissance = $_POST['date_naissance'];
        $taille = $_POST['taille'];
        $poids = $_POST['poids'];
        $commentaire = $_POST['commentaire'];
        $statut = $_POST['statut'];
    
        // Appel de la fonction pour mettre à jour le joueur
        updateJoueur($id, $nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $commentaire, $statut);
    
        // Redirection pour éviter la soumission multiple
        header("Location: gestion_joueur.php");
        exit;
    } elseif (isset($_POST['add'])) {
        // Ajouter un nouveau joueur
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $numero_licence = $_POST['numero_licence'];
        $date_naissance = $_POST['date_naissance'];
        $taille = $_POST['taille'];
        $poids = $_POST['poids'];
        $commentaire = $_POST['commentaire'];
        $statut = $_POST['statut'];
    
        // Ajouter le joueur dans la base de données
        addJoueur($nom, $prenom, $numero_licence, $date_naissance, $taille, $poids, $commentaire, $statut);
    
        // Redirection pour éviter la soumission multiple
        header("Location: gestion_joueur.php");
        exit;
    } elseif (isset($_POST['delete_joueur'])) {
        // Supprimer un joueur
        $id = $_POST['id'];
        deleteJoueur($id);
    }
}


// Récupération de tous les joueurs
$joueurs = getAllJoueurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href='../Css/style.css'>
    <link rel="stylesheet" href='../Css/joueur.css'>
    <link rel="stylesheet" href='../Css/popup.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Gestion des Joueurs</title>
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container">
    <h1>Joueurs</h1>

    <!-- Formulaire d'ajout de joueur -->
    <button class="openPopupBtn">Ajouter un Nouveau Joueur</button>

    <!-- Popup Form -->
    <div id="popupForm" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <h2 id="popupTitle">Ajouter un Nouveau Joueur</h2>
            <form id="joueurForm" action="gestion_joueur.php" method="post">
                <input type="hidden" id="joueurId" name="id">
                <input type="hidden" id="formAction" name="add">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required><br>

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required><br>

                <label for="numero_licence">Numéro de Licence :</label>
                <input type="text" id="numero_licence" name="numero_licence" required><br>

                <label for="date_naissance">Date de Naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" required><br>

                <label for="taille">Taille (en cm) :</label>
                <input type="number" id="taille" name="taille" required><br>

                <label for="poids">Poids (en kg) :</label>
                <input type="number" id="poids" name="poids" required><br>

                <label for="commentaire">Commentaire :</label>
                <textarea id="commentaire" name="commentaire"></textarea><br>

                <label for="statut">Statut :</label>

                <select id="statut" name="statut" required>
                    <option value="Actif">Actif</option>
                    <option value="Blessé">Blessé</option>
                    <option value="Suspendu">Suspendu</option>
                    <option value="Absent">Absent</option>
                </select><br>

                <button type="submit" class="formSubmitBtn">Ajouter le Joueur</button>
            </form>
        </div>
    </div>

    <!-- Table d'affichage des joueurs -->
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Numéro de Licence</th>
                <th>Date de Naissance</th>
                <th class="adaptive-column">Taille</th>
                <th class="adaptive-column">Poids</th>
                <th>Commentaire</th>
                <th>Statut</th>
                <th class="adaptive-column">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($joueurs as $joueur): ?>
            <tr>
                <form action="gestion_joueur.php" method="post">
                    <input type="hidden" name="id" value="<?= $joueur['id_joueur'] ?>">
                    <td><?= htmlspecialchars($joueur['nom']) ?></td>
                    <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                    <td><?= htmlspecialchars($joueur['licence']) ?></td>
                    <td><?= date('d/m/Y', strtotime($joueur['dateNaissance'])) ?></td>
                    <td><?= htmlspecialchars($joueur['taille']) ?>cm</td>
                    <td><?= htmlspecialchars($joueur['poids']) ?>kg</td>
                    <td><?= htmlspecialchars($joueur['commentaire']) ?></td>
                    <td><?= htmlspecialchars($joueur['statut']) ?></td>
                    <td>
                        <div class="action-container">
                            <button type="button" class="actionBtn editBtn" 
                                    data-id="<?= $joueur['id_joueur'] ?>"
                                    data-nom="<?= htmlspecialchars($joueur['nom']) ?>"
                                    data-prenom="<?= htmlspecialchars($joueur['prenom']) ?>"
                                    data-licence="<?= htmlspecialchars($joueur['licence']) ?>"
                                    data-dateNaissance="<?= htmlspecialchars($joueur['dateNaissance']) ?>"
                                    data-taille="<?= htmlspecialchars($joueur['taille']) ?>"
                                    data-poids="<?= htmlspecialchars($joueur['poids']) ?>"
                                    data-commentaire="<?= htmlspecialchars($joueur['commentaire']) ?>"
                                    data-statut="<?= htmlspecialchars($joueur['statut']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="gestion_joueur.php" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $joueur['id_joueur'] ?>">
                                <button class="actionBtn delete" type="submit" name="delete_joueur" 
                                        <?= hasParticipatedInRencontres($joueur['id_joueur']) ? 'disabled' : '' ?>>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Get the modal
    var popup = document.getElementById("popupForm");

    // Get the button that opens the modal
    var btn = document.getElementsByClassName("openPopupBtn")[0];

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal 
    btn.onclick = function() {
        document.getElementById("popupTitle").innerText = "Ajouter un Nouveau Joueur";
        document.getElementById("joueurForm").reset();
        document.getElementById("joueurId").value = "";
        document.getElementById("formAction").name = "add";
        document.getElementsByClassName("formSubmitBtn")[0].innerText = "Ajouter le Joueur";
        popup.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        console.log("close");
        popup.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == popup) {
            popup.style.display = "none";
        }
    }

    // Get all edit buttons
    var editBtns = document.getElementsByClassName("editBtn");

    // Add click event to each edit button
    for (var i = 0; i < editBtns.length; i++) {
        editBtns[i].onclick = function() {
            document.getElementById("popupTitle").innerText = "Modifier le Joueur";
            document.getElementById("joueurId").value = this.getAttribute("data-id");
            document.getElementById("nom").value = this.getAttribute("data-nom");
            document.getElementById("prenom").value = this.getAttribute("data-prenom");
            document.getElementById("numero_licence").value = this.getAttribute("data-licence");
            document.getElementById("date_naissance").value = this.getAttribute("data-dateNaissance");
            document.getElementById("taille").value = this.getAttribute("data-taille");
            document.getElementById("poids").value = this.getAttribute("data-poids");
            document.getElementById("commentaire").value = this.getAttribute("data-commentaire");
            document.getElementById("statut").value = this.getAttribute("data-statut");
            document.getElementById("formAction").name = "update";
            document.getElementsByClassName("formSubmitBtn")[0].innerText = "Modifier le Joueur";
            popup.style.display = "block";
        }
    }
</script>

</body>
</html>
