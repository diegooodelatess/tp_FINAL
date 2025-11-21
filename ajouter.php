<?php
require_once "config.php";


$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $name = trim($_POST["name"]);
    $picture_url = trim($_POST["picture_url"]);
    $host_name = trim($_POST["host_name"]);
    $host_thumbnail = trim($_POST["host_thumbnail_url"]);
    $price = trim($_POST["price"]);
    $city = trim($_POST["city"]);
    // Nouveau champ pour la note (facultatif ou à NULL si non renseigné)
    $review_scores_value = isset($_POST["review_scores_value"]) && $_POST["review_scores_value"] !== '' ? trim($_POST["review_scores_value"]) : null; 


    if ($name && $picture_url && $host_name && $host_thumbnail && $price && $city) {
        try {
            // Ajout de review_scores_value dans la requête INSERT
            $sql = "INSERT INTO listings 
                    (name, picture_url, host_name, host_thumbnail_url, price, neighbourhood_group_cleansed, review_scores_value)
                    VALUES (:name, :picture_url, :host_name, :host_thumbnail_url, :price, :city, :review_scores_value)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ":name" => $name,
                ":picture_url" => $picture_url,
                ":host_name" => $host_name,
                ":host_thumbnail_url" => $host_thumbnail,
                ":price" => $price,
                ":city" => $city,
                // Passage de la note (NULL si non fournie)
                ":review_scores_value" => $review_scores_value
            ]);
            $message = "Annonce ajoutée avec succès !";
        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
        }
    } else {
        $message = "Tous les champs obligatoires (sauf Note) doivent être remplis.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Ajouter une annonce</title>
</head>
<body>


<h1>➕ Ajouter une annonce</h1>


<?php if ($message): ?>
    <p class="<?= str_contains($message, 'Erreur') ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>


<form method="POST">
    <label>Nom du logement :</label>
    <input type="text" name="name" required>


    <label>URL de l'image :</label>
    <input type="url" name="picture_url" required>


    <label>Nom du propriétaire :</label>
    <input type="text" name="host_name" required>


    <label>URL de la photo du propriétaire :</label>
    <input type="url" name="host_thumbnail_url" required>


    <label>Prix (€):</label>
    <input type="number" name="price" min="0" required>


    <label>Ville :</label>
    <input type="text" name="city" required>

    <label>Note (/5 - Laisser vide si non noté) :</label>
    <input type="number" name="review_scores_value" min="0" max="5" step="0.01">


    <button type="submit" class="btn-add">Ajouter</button>
</form>


</body>
</html>