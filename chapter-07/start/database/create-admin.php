<?php

$pdo = require __DIR__ . '/connection.php';
$pdo->exec('
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL
    )
');

$email = 'admin@example.test';
$password = bin2hex(random_bytes(16));
$statement = $pdo->prepare('
    INSERT INTO users (email, password_hash)
    VALUES (:email, :password_hash)
    ON CONFLICT (email) DO NOTHING
');
$statement->execute([
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

if ($statement->rowCount() === 0) {
    echo 'La cuenta ya existe.' . PHP_EOL;
    exit;
}

echo 'Correo: ' . $email . PHP_EOL;
echo 'Contraseña: ' . $password . PHP_EOL;
