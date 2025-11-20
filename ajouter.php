<?php
require_once "config.php";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO listings (name, picture_url, host_name, host_thumbnail_url, price, neighbourhood_group_cleansed)
            VALUES (:name, :picture_url, :host_name, :host_thumbnail_url, :price, :city)";
    
    $stmt = $dbh->prepare($sql);

    $stmt->execute([
        ":name" => $_POST["name"],
        ":picture_url" => $_POST["picture_url"],
        ":host_name" => $_POST["host_name"],
        ":host_thumbnail_url" => $_POST["host_thumbnail_url"],
        ":price" => $_POST["price"],
        ":city" => $_POST["city"]
    ]);

    $message = "Annonce ajoutée avec succès !";
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
    <p class="success"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nom :</label>
    <input type="text" name="name" required>

    <label>URL image :</label>
    <input type="text" name="picture_url" required>

    <label>Propriétaire :</label>
    <input type="text" name="host_name" required>

    <label>URL photo propriétaire :</label>
    <input type="text" name="host_thumbnail_url" required>

    <label>Prix :</label>
    <input type="number" name="price" required>

    <label>Ville :</label>
    <input type="text" name="city" required>

    <button type="submit">Ajouter</button>
</form>

</body>
</html>
