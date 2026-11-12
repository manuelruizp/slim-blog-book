<?php
$id = (int) $article['id'];
$title = htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8');
?>
<h1>Eliminar artículo</h1>
<p>Vas a eliminar «<?= $title ?>». Esta acción no se puede deshacer.</p>
<form method="post" action="/panel/articulos/<?= $id ?>/eliminar">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
           ) ?>">
    <button type="submit">Eliminar definitivamente</button>
</form>
<p><a href="/panel/articulos">Cancelar y volver al panel</a></p>
