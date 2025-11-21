<?php
require_once "config.php";

$colonnesAutorisees = ["name", "neighbourhood_group_cleansed", "price", "host_name", "review_scores_value"];
$ordresAutorises = ["ASC", "DESC"];

$tri = isset($_GET["tri"]) && in_array($_GET["tri"], $colonnesAutorisees) ? $_GET["tri"] : "name";
$ordre = isset($_GET["ordre"]) && in_array($_GET["ordre"], $ordresAutorises) ? $_GET["ordre"] : "ASC";

$recherche = isset($_GET["recherche"]) ? trim($_GET["recherche"]) : "";

$limiteParPage = 8;
$pageCourante = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$decalage = ($pageCourante - 1) * $limiteParPage;

$where = "";
$params = [];
if ($recherche !== "") {
    $where = "WHERE name LIKE :recherche OR neighbourhood_group_cleansed LIKE :recherche OR host_name LIKE :recherche";
    $params[":recherche"] = "%$recherche%";
}

$sql = "SELECT * FROM listings $where ORDER BY $tri $ordre LIMIT :limite OFFSET :decalage";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(":limite", $limiteParPage, PDO::PARAM_INT);
$stmt->bindValue(":decalage", $decalage, PDO::PARAM_INT);
if ($recherche !== "") {
    $stmt->bindValue(":recherche", $params[":recherche"], PDO::PARAM_STR);
}
$stmt->execute();
$listeLogements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlCount = "SELECT COUNT(*) FROM listings $where";
$stmtCount = $dbh->prepare($sqlCount);
if ($recherche !== "") {
    $stmtCount->bindValue(":recherche", $params[":recherche"], PDO::PARAM_STR);
}
$stmtCount->execute();
$totalLogements = $stmtCount->fetchColumn();
$nombrePages = ceil($totalLogements / $limiteParPage);

function afficherCarteLogement($logement) {
    $nom = htmlspecialchars($logement['name'] ?? 'Logement sans nom');
    $ville = htmlspecialchars($logement['neighbourhood_group_cleansed'] ?? 'Ville inconnue');
    $proprietaire = htmlspecialchars($logement['host_name'] ?? 'Hôte inconnu');
    $avatar = htmlspecialchars($logement['host_thumbnail_url'] ?? '');
    $prix = htmlspecialchars($logement['price'] ?? '0');
    $note = htmlspecialchars($logement['review_scores_value'] ?? '0'); 
    $image = htmlspecialchars($logement['picture_url'] ?? '');
    ?>
    <div class="carte-logement">
        <img class="image-principale" src="<?= $image ?>" alt="<?= $nom ?>">
        <div class="infos-logement">
            <h3><?= $nom ?></h3>
            <p><strong>Ville :</strong> <?= $ville ?></p>
            <p class="proprietaire">
                <img class="avatar-proprietaire" src="<?= $avatar ?>" alt="<?= $proprietaire ?>">
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

<a href="ajouter.php" class="btn-add"> Ajouter une annonce</a>

<form method="GET" id="form-recherche" style="margin-bottom:20px;">
    <input type="text" name="recherche" placeholder="Rechercher nom, ville ou propriétaire..." value="<?= htmlspecialchars($recherche) ?>" style="width:300px; padding:5px;">
    <button type="submit" class="btn-add" style="padding:6px 12px;">Rechercher</button>
    <input type="hidden" name="tri" value="<?= $tri ?>">
    <input type="hidden" name="ordre" value="<?= $ordre ?>">
</form>

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

    <input type="hidden" name="recherche" value="<?= htmlspecialchars($recherche) ?>">
    <input type="hidden" name="page" value="<?= $pageCourante ?>">
</form>

<div class="liste-logements">
    <?php if (count($listeLogements) === 0): ?>
        <p>Aucun logement trouvé.</p>
    <?php else: ?>
        <?php foreach ($listeLogements as $logement): ?>
            <?php afficherCarteLogement($logement); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php if ($pageCourante > 1): ?>
        <a href="?page=<?= $pageCourante - 1 ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>&recherche=<?= urlencode($recherche) ?>"><- Précédent</a>
    <?php endif; ?>

    <?php
    $max_pages_affiches = 10; 
    $debut = max(1, $pageCourante - floor($max_pages_affiches / 2));
    $fin = min($nombrePages, $debut + $max_pages_affiches - 1);

    if ($fin - $debut + 1 < $max_pages_affiches) {
        $debut = max(1, $fin - $max_pages_affiches + 1);
    }

    for ($i = $debut; $i <= $fin; $i++): ?>
        <a href="?page=<?= $i ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>&recherche=<?= urlencode($recherche) ?>" class="<?= $i == $pageCourante ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($pageCourante < $nombrePages): ?>
        <a href="?page=<?= $pageCourante + 1 ?>&tri=<?= $tri ?>&ordre=<?= $ordre ?>&recherche=<?= urlencode($recherche) ?>">Suivant -></a>
    <?php endif; ?>
</div>

</body>
</html>
