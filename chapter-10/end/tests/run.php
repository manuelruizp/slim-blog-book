<?php

require_once __DIR__ . '/../src/ArticleRepository.php';
require_once __DIR__ . '/../src/validate-article.php';

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
};

try {
    $pdo = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("
        CREATE TABLE articles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            title TEXT NOT NULL,
            body TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status TEXT NOT NULL DEFAULT 'draft'
                CHECK (status IN ('draft', 'published')),
            published_at TEXT
        )
    ");
    $articleRepository = new ArticleRepository($pdo);
    $check($articleRepository->countPublished() === 0,
        'Una base vacía no debe tener publicaciones.');
    $check($articleRepository->listAll() === [],
        'El panel debe empezar vacío.');
    $result = validateArticle([
        'title' => '  Título de prueba  ',
        'slug' => 'first-draft',
        'body' => 'Contenido',
    ]);
    $check($result['errors'] === [], 'Los datos válidos deben aceptarse.');
    $check($result['values']['title'] === 'Título de prueba',
        'El título debe quedar sin espacios exteriores.');
    $invalid = validateArticle([
        'title' => [], 'slug' => 'slug con espacios', 'body' => '',
    ]);
    $check(count($invalid['errors']) === 3,
        'Cada campo inválido debe producir su error.');
    $check(count(validateArticle(null)['errors']) === 3,
        'Un cuerpo ausente debe tratarse como campos vacíos.');

    $saved = $articleRepository->create('first-draft', 'Título', 'Cuerpo');
    $check($saved, 'Debe guardarse el primer borrador.');
    $id = (int) $pdo->lastInsertId();
    $article = $articleRepository->findById($id);
    $check($article !== null && $article['status'] === 'draft',
        'El artículo nuevo debe ser borrador.');
    $check($article['published_at'] === null,
        'Un borrador no debe tener fecha de publicación.');
    $check($articleRepository->findBySlug('first-draft') === null,
        'El detalle público debe ocultar borradores.');
    $check($articleRepository->listPage(2, 0) === [],
        'El listado público debe ocultar borradores.');
    $check(!$articleRepository->create('first-draft', 'Otro', 'Otro'),
        'No debe repetirse un slug reservado por un borrador.');
    $check($articleRepository->create('second-draft', 'Segundo', 'Cuerpo'),
        'Debe crearse otro artículo con un slug distinto.');
    $check($articleRepository->update($id, 'first-draft', 'Editado', 'Cuerpo'),
        'El artículo debe poder conservar su slug al editarse.');
    $check($articleRepository->update($id, 'first-draft', 'Editado', 'Cuerpo'),
        'Guardar los mismos valores también debe funcionar.');
    $check(!$articleRepository->update($id, 'second-draft', 'No', 'No'),
        'La edición no debe ocupar el slug de otro artículo.');
    $article = $articleRepository->findById($id);
    $check($article['slug'] === 'first-draft' && $article['title'] === 'Editado',
        'Un conflicto debe conservar los valores anteriores.');
    $check($article['status'] === 'draft',
        'Editar un borrador no debe publicarlo.');
    $check($articleRepository->countPublished() === 0,
        'Los borradores no deben aumentar el total público.');
    $check($articleRepository->publish($id), 'Debe publicarse el borrador.');
    $article = $articleRepository->findById($id);
    $check($article['status'] === 'published'
        && is_string($article['published_at']),
        'Publicar debe guardar estado y fecha.');
    $check($articleRepository->findBySlug('first-draft') !== null,
        'El artículo publicado debe tener detalle público.');
    $check($articleRepository->countPublished() === 1,
        'El total público debe incluir solo la publicación.');

    $statement = $pdo->prepare('
        UPDATE articles SET published_at = :published_at WHERE id = :id
    ');
    $statement->execute([
        'published_at' => '2000-01-01 00:00:00', 'id' => $id,
    ]);
    $check($articleRepository->publish($id),
        'Repetir la publicación debe seguir siendo válido.');
    $check($articleRepository->findById($id)['published_at']
        === '2000-01-01 00:00:00',
        'Repetir la publicación debe conservar su fecha inicial.');
    $check(count($articleRepository->listPage(2, 0)) === 1,
        'El listado no debe incluir el segundo borrador.');
    $check($articleRepository->delete($id),
        'Debe eliminarse el artículo elegido.');
    $check($articleRepository->findById($id) === null,
        'El artículo eliminado no debe aparecer por id.');
    $check($articleRepository->findBySlug('first-draft') === null,
        'El detalle público no debe recuperar un artículo eliminado.');
    $check(!$articleRepository->delete($id),
        'Una segunda eliminación debe informar que no encontró la fila.');
    $check(!$articleRepository->update($id, 'first-draft', 'No', 'No'),
        'Una edición antigua no debe recrear el registro.');
    $check(!$articleRepository->publish($id),
        'No se debe publicar un registro eliminado.');
    $check(count($articleRepository->listAll()) === 1,
        'La eliminación debe conservar el segundo borrador.');
    $check($articleRepository->countPublished() === 0,
        'Al eliminar la publicación no deben quedar artículos públicos.');

    // A new article must never reuse the identity of a deleted article.
    $check($articleRepository->create('stale-target', 'Original', 'Cuerpo'),
        'Debe crearse el artículo que dejará un formulario antiguo.');
    $staleId = (int) $pdo->lastInsertId();
    $check($articleRepository->delete($staleId),
        'Debe eliminarse el artículo original.');
    $check($articleRepository->create('replacement', 'Nuevo', 'Cuerpo nuevo'),
        'Debe crearse el artículo de reemplazo.');
    $replacementId = (int) $pdo->lastInsertId();
    $check($replacementId !== $staleId,
        'El identificador eliminado no debe reutilizarse.');
    $check(!$articleRepository->update($staleId, 'stale', 'No', 'No'),
        'Un formulario antiguo no debe editar el artículo nuevo.');
    $check(!$articleRepository->delete($staleId),
        'Un formulario antiguo no debe eliminar el artículo nuevo.');
    $check(!$articleRepository->publish($staleId),
        'Un formulario antiguo no debe publicar el artículo nuevo.');
    $replacement = $articleRepository->findById($replacementId);
    $check($replacement['title'] === 'Nuevo'
        && $replacement['body'] === 'Cuerpo nuevo'
        && $replacement['slug'] === 'replacement'
        && $replacement['status'] === 'draft'
        && $replacement['published_at'] === null,
        'El artículo nuevo debe conservar sus datos y seguir privado.');

    echo $checks . ' comprobaciones correctas.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Fallo: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
