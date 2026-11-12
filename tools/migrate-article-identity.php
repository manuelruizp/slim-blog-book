<?php

declare(strict_types=1);

// Optional compatibility tool for databases created with an older manuscript.
try {
    if ($argc !== 2 || !is_file($argv[1])) {
        throw new RuntimeException('Uso: php tools/migrate-article-identity.php ruta/al/blog.sqlite');
    }
    $path = realpath($argv[1]);
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $sql = $pdo->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'articles'")->fetchColumn();
    if (!is_string($sql)) {
        throw new RuntimeException('No existe la tabla articles.');
    }
    if (preg_match('/\bid\s+INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT\b/i', $sql)) {
        echo 'La tabla ya utiliza AUTOINCREMENT; no se realizaron cambios.' . PHP_EOL;
        exit(0);
    }
    $columns = array_column($pdo->query('PRAGMA table_info(articles)')->fetchAll(), 'name');
    $base = ['id', 'slug', 'title', 'body', 'created_at'];
    $final = [...$base, 'status', 'published_at'];
    if ($columns !== $base && $columns !== $final) {
        throw new RuntimeException('Esquema no reconocido; no se modificó la base.');
    }
    // Reject extensions whose constraints or dependencies cannot be preserved here.
    $expected = 'CREATE TABLE articles (id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE,'
        . ' title TEXT NOT NULL, body TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP';
    if ($columns === $final) {
        $expected .= ", status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published')), published_at TEXT";
    }
    $expected .= ')';
    $normalize = static fn (string $value): string => strtolower(preg_replace('/[\s"`\[\]]+/', '', $value));
    if ($normalize($sql) !== $normalize($expected)) {
        throw new RuntimeException('La definición difiere del ejemplo del libro; no se modificó la base.');
    }
    foreach ($pdo->query("SELECT type, name, sql FROM sqlite_master WHERE sql IS NOT NULL") as $object) {
        if (in_array($object['type'], ['trigger', 'view'], true)
            || ($object['type'] === 'index' && stripos($object['sql'], 'articles') !== false)
            || ($object['type'] === 'table' && $object['name'] !== 'articles'
                && stripos($object['sql'], 'REFERENCES') !== false)) {
            throw new RuntimeException('Hay dependencias adicionales; esta herramienta solo admite el proyecto del libro.');
        }
    }
    $backup = $path . '.before-identity-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.sqlite';
    $pdo->exec('VACUUM INTO ' . $pdo->quote($backup));
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $before = $pdo->query('SELECT * FROM articles ORDER BY id')->fetchAll();
        $newSql = preg_replace('/CREATE\s+TABLE\s+["`\[]?articles["`\]]?/i', 'CREATE TABLE articles_identity_new', $sql, 1);
        $newSql = preg_replace('/\bid\s+INTEGER\s+PRIMARY\s+KEY\b/i', 'id INTEGER PRIMARY KEY AUTOINCREMENT', $newSql, 1);
        $pdo->exec($newSql);
        $names = implode(', ', $columns);
        $pdo->exec("INSERT INTO articles_identity_new ($names) SELECT $names FROM articles");
        $after = $pdo->query('SELECT * FROM articles_identity_new ORDER BY id')->fetchAll();
        if ($before !== $after) {
            throw new RuntimeException('La copia no conserva los artículos.');
        }
        $pdo->exec('DROP TABLE articles');
        $pdo->exec('ALTER TABLE articles_identity_new RENAME TO articles');
        if ($pdo->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
            throw new RuntimeException('La comprobación de integridad falló.');
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $error) {
        $pdo->exec('ROLLBACK');
        throw $error;
    }
    echo 'Identidad migrada; artículos e identificadores actuales conservados.' . PHP_EOL;
    echo 'Respaldo: ' . $backup . PHP_EOL;
    echo 'Cierra las sesiones y descarta todos los formularios anteriores antes de reabrir el blog.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Fallo: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
