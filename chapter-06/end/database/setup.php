<?php

$pdo = require __DIR__ . '/connection.php';

$pdo->exec('
    CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
');

$pdo->exec("
    INSERT INTO articles (slug, title, body) VALUES
        ('mi-primer-post', 'Mi primer post',
         'Esta es la primera publicación del blog.'),
        ('entender-las-rutas', 'Entender las rutas',
         'Una ruta conecta una petición con una acción.'),
        ('probar-el-blog', 'Probar el blog',
         'Cambia la dirección y observa la respuesta.')
    ON CONFLICT (slug) DO NOTHING
");

echo 'Base de datos preparada.' . PHP_EOL;
