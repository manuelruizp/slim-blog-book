<h1>Administrar artículos</h1>
<p><a href="/panel/articulos/nuevo">Crear artículo</a></p>
<?php if ($articles === []): ?>
    <p>No hay artículos todavía.</p>
<?php else: ?>
    <ul>
        <?php foreach ($articles as $article):
            $id = (int) $article['id'];
            $title = htmlspecialchars(
                $article['title'], ENT_QUOTES, 'UTF-8'
            );
            $url = '/articulos/' . rawurlencode($article['slug']);
        ?>
            <li>
                <a href="<?= $url ?>"><?= $title ?></a>
                <a href="/panel/articulos/<?= $id ?>/editar">Editar</a>
                <a href="/panel/articulos/<?= $id ?>/eliminar">
                    Eliminar
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
