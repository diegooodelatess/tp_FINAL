<?php
$dsn = 'mysql:host=localhost;dbname=airbnb;charset=utf8';
$user = 'root';
$pass = 'Doris10101010!';

try {
    $dbh = new PDO($dsn, $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
