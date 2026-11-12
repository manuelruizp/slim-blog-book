<h1>Administrar artículos</h1>
<p><a href="/panel/articulos/nuevo">Crear borrador</a></p>
<?php if ($articles === []): ?>
    <p>No hay artículos todavía.</p>
<?php else: ?>
    <ul>
        <?php foreach ($articles as $article):
            $id = (int) $article['id'];
            $title = htmlspecialchars(
                $article['title'], ENT_QUOTES, 'UTF-8'
            );
            $isPublished = $article['status'] === 'published';
            $statusLabel = $isPublished ? 'Publicado' : 'Borrador';
        ?>
            <li>
                <strong><?= $title ?></strong> — <?= $statusLabel ?>
                <a href="/panel/articulos/<?= $id ?>/vista-previa">
                    Vista previa
                </a>
                <a href="/panel/articulos/<?= $id ?>/editar">Editar</a>
                <a href="/panel/articulos/<?= $id ?>/eliminar">
                    Eliminar
                </a>
                <?php if ($isPublished):
                    $url = '/articulos/'
                        . rawurlencode($article['slug']);
                ?>
                    <a href="<?= $url ?>">Ver publicación</a>
                    <small>Publicación UTC: <?= htmlspecialchars(
                        $article['published_at'], ENT_QUOTES, 'UTF-8'
                    ) ?></small>
                <?php else: ?>
                    <form method="post"
                          action="/panel/articulos/<?= $id ?>/publicar">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars(
                                   $_SESSION['csrf_token'],
                                   ENT_QUOTES, 'UTF-8'
                               ) ?>">
                        <button type="submit">Publicar</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
