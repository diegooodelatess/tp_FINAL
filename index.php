<?php
require_once "config.php";

// ---- Paramètres de tri ----
$colonnesAutorisees = ["name", "neighbourhood_group_cleansed", "price", "host_name", "review_scores_value"];
$ordresAutorises = ["ASC", "DESC"];

$tri = isset($_GET["tri"]) && in_array($_GET["tri"], $colonnesAutorisees) ? $_GET["tri"] : "name";
$ordre = isset($_GET["ordre"]) && in_array($_GET["ordre"], $ordresAutorises) ? $_GET["ordre"] : "ASC";

// ---- Pagination ----
$limiteParPage = 10;
$pageCourante = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$decalage = ($pageCourante - 1) * $limiteParPage;

// ---- Requête SQL ----
$sql = "SELECT * FROM listings ORDER BY $tri $ordre LIMIT :limite OFFSET :decalage";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(":limite", $limiteParPage, PDO::PARAM_INT);
$stmt->bindValue(":decalage", $decalage, PDO::PARAM_INT);
$stmt->execute();
$listeLogements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- Nombre total de logements ----
$totalLogements = $dbh->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$nombrePages = ceil($totalLogements / $limiteParPage);

// ---- Fonction pour afficher une carte logement ----
function afficherCarteLogement($logement) {
    $nom = htmlspecialchars($logement['name']);
    $ville = htmlspecialchars($logement['neighbourhood_group_cleansed']);
    $proprietaire = htmlspecialchars($logement['host_name']);
    $avatar = htmlspecialchars($logement['host_thumbnail_url']);
    $prix = htmlspecialchars($logement['price']);
    $note = htmlspecialchars($logement['review_scores_value']);
    $image = htmlspecialchars($logement['picture_url']);
    ?>
    <div class="carte-logement">
        <img class="image-principale" src="<?= $image ?>" alt="Logement">
        <div class="infos-logement">
            <h3><?= $nom ?></h3>
            <p><strong>Ville :</strong> <?= $ville ?></p>
            <p class="proprietaire">
                <img class="avatar-proprietaire" src="<?= $avatar ?>" alt="Avatar">
                <?= $proprietaire ?>
            </p>
            <p><strong>Prix :</strong> <?= $prix ?> €</p>
            <p><strong>Note :</strong> <?= $note ?>/5</p>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Airbnb - Liste des logements</title>
</head>
<body>

<h1>Liste des logements</h1>

<!-- Formulaire de tri -->
<form method="GET" id="form-tri">
    <label>Trier par :</label>
    <select name="tri" onchange="document.getElementById('form-tri').submit()">
        <option value="name" <?= $tri == "name" ? "selected" : "" ?>>Nom</option>
        <option value="neighbourhood_group_cleansed" <?= $tri == "neighbourhood_group_cleansed" ? "selected" : "" ?>>Ville</option>
        <option value="price" <?= $tri == "price" ? "selected" : "" ?>>Prix</option>
        <option value="host_name" <?= $tri == "host_name" ? "selected" : "" ?>>Propriétaire</option>
        <option value="review_scores_value" <?= $tri == "review_scores_value" ? "selected" : "" ?>>Note</option>
    </select>

    <label>Ordre :</label>
    <select name="ordre" onchange="document.getElementById('form-tri').submit()">
        <option value="ASC" <?= $ordre == "ASC" ? "selected" : "" ?>>Croissant</option>
        <option value="DESC" <?= $ordre == "DESC" ? "selected" : "" ?>>Décroissant</option>
    </select>

    <input type="hidden" name="page" value="<?= $pageCourante ?>">
</form>

<!-- Liste des logements -->
<div class="liste-logements">
    <?php foreach ($listeLogements as $logement): ?>
        <?php afficherCarteLogement($logement); ?>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<div class="pagination">
    <?php if ($pageCourante > 1): ?>
        <a href="?page=<?= $pageCourante - 1 ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>">⬅️ Précédent</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $nombrePages; $i++): ?>
        <a href="?page=<?= $i ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>" class="<?= $i == $pageCourante ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($pageCourante < $nombrePages): ?>
        <a href="?page=<?= $pageCourante + 1 ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>">Suivant ➡️</a>
    <?php endif; ?>
</div>

</body>
</html>
