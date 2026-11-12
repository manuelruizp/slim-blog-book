<?php

$pdo = require __DIR__ . '/connection.php';
$pdo->beginTransaction();

try {
    $columns = array_column(
        $pdo->query('PRAGMA table_info(articles)')->fetchAll(),
        'name'
    );
    if (!in_array('status', $columns, true)) {
        $pdo->exec("
            ALTER TABLE articles ADD COLUMN status
            TEXT NOT NULL DEFAULT 'draft'
            CHECK (status IN ('draft', 'published'))
        ");
        $pdo->exec("UPDATE articles SET status = 'published'");
    }
    if (!in_array('published_at', $columns, true)) {
        $pdo->exec('
            ALTER TABLE articles ADD COLUMN published_at TEXT
        ');
    }
    $pdo->exec("
        UPDATE articles
        SET published_at = created_at
        WHERE status = 'published' AND published_at IS NULL
    ");
    $pdo->commit();
} catch (Throwable $error) {
    $pdo->rollBack();
    throw $error;
}

echo 'Estados de artículos preparados.' . PHP_EOL;
