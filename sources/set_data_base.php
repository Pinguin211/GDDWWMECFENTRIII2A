<?php

/*
DATABASE_URL="mysql://root:admin@127.0.0.1:3306/trtconseil?serverVersion=8&charset=utf8mb4"
DATABASE_PDO_URL="mysql:host=localhost;port=3307;dbname=trtconseil"
DATABASE_USER="root"
DATABASE_PASSWORD="admin"
*/

echo "\n\nEntrez votre identifiant à votre base de donné :\n";
$id = readline();
echo "\n\nEntrez votre le mot de passe :\n";
$password = readline();
echo "\n\nEntrez l'adresse de votre base de données :\n";
$address = readline();
echo "\n\nEntrez le port de votre base de données :\n";
$port = readline();
echo "\n\nEntrez la table de votre base de données :\n";
$dbname = readline();

$dsn = "mysql:host=$address;port=$port;dbname=$dbname";

try {
    $pdo = NEW PDO($dsn, $id, $password);
} catch (Exception $e) {
    $message = $e->getMessage();
    die("\nErreur : $message\n");
}

$database_url = "DATABASE_URL=\"mysql://$id:$password@$address:$port/$dbname?serverVersion=8&charset=utf8mb4\"";
$database_pdo_url = "DATABASE_PDO_URL=\"$dsn\"";
$database_user = "DATABASE_USER=\"$id\"";
$database_password = "DATABASE_PASSWORD=\"$password\"";

$file_content = "$database_url\n$database_pdo_url\n$database_user\n$database_password";
file_put_contents('.env.local', $file_content);
echo "\nLa base de données a bien etait configurer\n";

