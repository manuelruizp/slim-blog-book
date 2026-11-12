<h1>Artículos</h1>
<?php if ($pageArticles === []): ?>
    <p>No hay artículos en esta página.</p>
<?php else: ?>
    <ul>
        <?php foreach ($pageArticles as $article):
            $url = '/articulos/' . rawurlencode($article['slug']);
            $title = htmlspecialchars(
                $article['title'], ENT_QUOTES, 'UTF-8'
            );
        ?>
            <li><a href="<?= $url ?>"><?= $title ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<p>
    Página <?= $page ?> de <?= $totalPages ?>
    <?php if ($page > 1): ?>
        <a href="/articulos?pagina=<?= $page - 1 ?>">Anterior</a>
    <?php endif; ?>
    <?php if ($page < $totalPages): ?>
        <a href="/articulos?pagina=<?= $page + 1 ?>">Siguiente</a>
    <?php endif; ?>
</p>
