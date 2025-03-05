<?php
require_once __DIR__ . '/../Login/auth.php';
require_once __DIR__ . '/../Database/db_joueurs.php';
require_once __DIR__ . '/../Database/db_rencontres.php';

$id_rencontre = $_GET['id_rencontre'];
$rencontre = getRencontreById($id_rencontre);
$joueurs = getJoueursByRencontre($id_rencontre);
$all_joueurs = getAllJoueurs(); // Tous les joueurs disponibles

// Créez une liste des ID des joueurs déjà ajoutés
$joueurs_ids = array_column($joueurs, 'id_joueur');

// Filtrez $all_joueurs pour ne garder que ceux qui ne sont pas déjà dans $joueurs et qui sont "Actifs"
$disponible_joueurs = array_filter($all_joueurs, function($joueur) use ($joueurs_ids) {
    return !in_array($joueur['id_joueur'], $joueurs_ids) && $joueur['statut'] === 'Actif';
});

// Vérifier si le match est passé ou à venir
$is_past_match = strtotime($rencontre['date_rencontre']) < time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_past_match && isset($_POST['add_joueur'])) {
        $id_joueur = $_POST['id_joueur'];
        $poste = $_POST['poste'];
        $titulaire = isset($_POST['titulaire']) ? 1 : 0;

        addJoueurToRencontre($id_rencontre, $id_joueur, $poste, $titulaire);

        header("Location: details_rencontre.php?id_rencontre=$id_rencontre");
        exit;
    } elseif (!$is_past_match && isset($_POST['update_joueur'])) {
        $id_rencontre = $_POST['id_rencontre'];
        $id_joueur = $_POST['id_joueur'];
        $poste = $_POST['poste'];
        $titulaire = isset($_POST['titulaire']) ? 1 : 0;

        updateJoueurInRencontre($id_rencontre, $id_joueur, $poste, $titulaire);

        header("Location: details_rencontre.php?id_rencontre=$id_rencontre");
        exit;
    } elseif (!$is_past_match && isset($_POST['delete_joueur'])) {
        $id_joueur = $_POST['id_joueur'];
        $id_rencontre = $_POST['id_rencontre'];
        deleteJoueurInRencontre($id_joueur, $id_rencontre);

        // Redirection pour éviter la soumission multiple
        header("Location: details_rencontre.php?id_rencontre=$id_rencontre");
        exit;
    } elseif ($is_past_match && isset($_POST['update_note'])) {
        $id_rencontre = $_POST['id_rencontre'];
        $id_joueur = $_POST['id_joueur'];
        $note = $_POST["note"];

        updateJoueurNoteInRencontre($id_rencontre, $id_joueur, $note);

        header("Location: details_rencontre.php?id_rencontre=$id_rencontre");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href='../Css/style.css'>
    <link rel="stylesheet" href='../Css/rencontre.css'>
    <link rel="stylesheet" href='../Css/popup.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Détails de la Rencontre</title>
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container">
<div class="header">
    <button onclick="window.location.href='gestion_rencontre.php'" class="actionBtn delete">X</button>
    <h1>Détails de la Rencontre</h1>
</div>

<table>
    <tr>
        <th>Date</th>
        <th>Adversaire</th>
        <th>Lieu</th>
    </tr>
    <tr>
        <td><?= date('H:i d/m/Y', strtotime($rencontre['date_rencontre'])) ?></td>
        <td><?= htmlspecialchars($rencontre['adversaire']) ?></td>
        <td><?= htmlspecialchars($rencontre['lieu']) ?></td>
    </tr>
</table>

<h2>Joueurs Participants</h2>

<?php if (!$is_past_match): ?>
    <?php if (count($joueurs) < 12): ?>
        <?php if (!empty($disponible_joueurs)): ?>
            <button class="openPopupBtn">Ajouter un joueur</button>
        <?php else: ?>
            <h3>Aucun joueur disponible à ajouter</h3>
        <?php endif; ?>
    <?php else: ?>
        <h3>Nombre maximum de joueurs atteint (12)</h3>
    <?php endif; ?>
    <?php if (count($joueurs) < 5): ?>
        <h3 style="color: red;">Il manque encore <?= 5 - count($joueurs) ?> joueur(s) pour atteindre le minimum requis de 5 joueurs.</h3>
    <?php endif; ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th class="adaptive-column">Poste</th>
            <th class="adaptive-column">Titulaire</th>
            <?php if ($is_past_match): ?>
                <th class="adaptive-column">Note</th>
            <?php endif; ?>
            <?php if (!$is_past_match): ?>
                <th class="adaptive-column">Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($joueurs as $joueur): ?>
            <tr>
                <td><?= htmlspecialchars($joueur['nom']) ?></td>
                <td><?= htmlspecialchars($joueur['prenom']) ?></td>
                <td>
                    <form class="tableForm" action="details_rencontre.php?id_rencontre=<?= $id_rencontre ?>" method="post">
                        <input type="hidden" name="id_joueur" value="<?= $joueur['id_joueur'] ?>">
                        <input type="hidden" name="id_rencontre" value="<?= $id_rencontre ?>">
                        <select class="tableSelect" name="poste" onchange="this.form.submit()" <?= $is_past_match ? 'disabled' : '' ?>>
                            <option value="Meneur" <?= $joueur['poste'] == 'Meneur' ? 'selected' : '' ?>>Meneur</option>
                            <option value="Arrière" <?= $joueur['poste'] == 'Arrière' ? 'selected' : '' ?>>Arrière</option>
                            <option value="Ailier" <?= $joueur['poste'] == 'Ailier' ? 'selected' : '' ?>>Ailier</option>
                            <option value="Ailier Fort" <?= $joueur['poste'] == 'Ailier Fort' ? 'selected' : '' ?>>Ailier Fort</option>
                            <option value="Pivot" <?= $joueur['poste'] == 'Pivot' ? 'selected' : '' ?>>Pivot</option>
                        </select>
                        <input type="hidden" name="update_joueur" value="1">
                    </form>
                </td>
                <td>
                    <form action="details_rencontre.php?id_rencontre=<?= $id_rencontre ?>" method="post">
                        <input type="hidden" name="id_joueur" value="<?= $joueur['id_joueur'] ?>">
                        <input type="hidden" name="id_rencontre" value="<?= $id_rencontre ?>">
                        <input type="hidden" name="poste" value="<?= $joueur['poste'] ?>">
                        <input class="tableCheckBox" type="checkbox" name="titulaire" value="1" <?= $joueur['titulaire'] ? 'checked' : '' ?> onchange="this.form.submit()" <?= $is_past_match ? 'disabled' : '' ?>>
                        <input type="hidden" name="update_joueur" value="1">
                    </form>
                </td>
                <?php if ($is_past_match): ?>
                    <td>
                        <form action="details_rencontre.php?id_rencontre=<?= $id_rencontre ?>" method="post">
                            <input type="hidden" name="id_joueur" value="<?= $joueur['id_joueur'] ?>">
                            <input type="hidden" name="id_rencontre" value="<?= $id_rencontre ?>">
                            <select class="tableSelect" name="note" onchange="this.form.submit()">
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= $joueur['note'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="hidden" name="update_note" value="1">
                        </form>
                    </td>
                <?php endif; ?>
                <?php if (!$is_past_match): ?>
                    <td>
                        <form action="details_rencontre.php?id_rencontre=<?= $id_rencontre ?>" method="post" style="display:inline;">
                            <input type="hidden" name="id_joueur" value="<?= $joueur['id_joueur'] ?>">
                            <input type="hidden" name="id_rencontre" value="<?= $id_rencontre ?>">
                            <button class="actionBtn delete" type="submit" name="delete_joueur">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Popup Form -->
<div id="popupForm" class="popup">
    <div class="popup-content">
        <span class="close">&times;</span>
        <h2>Ajouter un Joueur</h2>
        <form action="details_rencontre.php?id_rencontre=<?= $id_rencontre ?>" method="post">
            <label for="id_joueur">Joueur :</label>
            <select name="id_joueur" id="id_joueur" required>
                <?php foreach ($disponible_joueurs as $joueur): ?>
                    <option value="<?= $joueur['id_joueur'] ?>">
                        <?= htmlspecialchars($joueur['nom'] . ' ' . $joueur['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="poste">Poste :</label>
            <select name="poste" id="poste" required>
                <option value="Meneur">Meneur</option>
                <option value="Arrière">Arrière</option>
                <option value="Ailier">Ailier</option>
                <option value="Ailier Fort">Ailier Fort</option>
                <option value="Pivot">Pivot</option>
            </select><br>

            <label for="titulaire">Titulaire :</label>
            <input class="tableCheckBox" type="checkbox" name="titulaire" id="titulaire"><br>

            <button type="submit" name="add_joueur">Ajouter</button>
        </form>
    </div>
</div>
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
        popup.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        popup.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == popup) {
            popup.style.display = "none";
        }
    }
</script>

</body>
</html>
