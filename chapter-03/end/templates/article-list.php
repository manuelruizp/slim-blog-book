<h1>Artículos</h1>
<?php if ($pageArticles === []): ?>
    <p>No hay artículos en esta página.</p>
<?php else: ?>
    <ul>
        <?php foreach ($pageArticles as $slug => $article):
            $url = '/articulos/' . rawurlencode($slug);
            $title = htmlspecialchars(
                $article['title'], ENT_QUOTES, 'UTF-8'
            );
        ?>
            <li><a href="<?= $url ?>"><?= $title ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<p>
    <a href="/articulos?pagina=1">Página 1</a> |
    <a href="/articulos?pagina=2">Página 2</a>
</p>
