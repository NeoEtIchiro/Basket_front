<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/joueur.css">
    <link rel="stylesheet" href="../Css/popup.css">
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
                <!-- On peut conserver un unique formulaire pour chaque ligne ou adapter selon vos besoins -->
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
                                    <?= checkParticipated($joueur['id_joueur']) ? 'disabled' : '' ?>>
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
    // Gestion de l'ouverture et fermeture du popup
    var popup = document.getElementById("popupForm");
    var btn = document.getElementsByClassName("openPopupBtn")[0];
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        document.getElementById("popupTitle").innerText = "Ajouter un Nouveau Joueur";
        document.getElementById("joueurForm").reset();
        document.getElementById("joueurId").value = "";
        // On définit l'action via un champ caché ou le name de l'input
        document.getElementById("formAction").value = "add";
        document.getElementsByClassName("formSubmitBtn")[0].innerText = "Ajouter le Joueur";
        popup.style.display = "block";
    }

    span.onclick = function() {
        popup.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == popup) {
            popup.style.display = "none";
        }
    }
    
    // Gestion des boutons d'édition
    var editBtns = document.getElementsByClassName("editBtn");
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
            document.getElementById("formAction").value = "update";
            document.getElementsByClassName("formSubmitBtn")[0].innerText = "Modifier le Joueur";
            popup.style.display = "block";
        }
    }
    // La soumission du formulaire se fait via le comportement par défaut (POST vers gestion_joueur.php)
    // Aucune requête API n'est exécutée côté vue.
</script>

</body>
</html>