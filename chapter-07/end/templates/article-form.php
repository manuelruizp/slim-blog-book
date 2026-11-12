<?php
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<h1>Crear artículo</h1>
<form method="post" action="/panel/articulos/nuevo">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
           ) ?>">
    <p>
        <label for="title">Título</label>
        <input id="title" name="title" required
               value="<?= $escape($values['title']) ?>">
        <?php if (isset($errors['title'])): ?>
            <span><?= $escape($errors['title']) ?></span>
        <?php endif; ?>
    </p>
    <p>
        <label for="slug">Slug (por ejemplo, mi-articulo)</label>
        <input id="slug" name="slug" required
               value="<?= $escape($values['slug']) ?>">
        <?php if (isset($errors['slug'])): ?>
            <span><?= $escape($errors['slug']) ?></span>
        <?php endif; ?>
    </p>
    <p>
        <label for="body">Contenido</label>
        <textarea id="body" name="body" required><?= $escape(
            $values['body']
        ) ?></textarea>
        <?php if (isset($errors['body'])): ?>
            <span><?= $escape($errors['body']) ?></span>
        <?php endif; ?>
    </p>
    <button type="submit">Guardar</button>
</form>

<form method="post" action="/cerrar-sesion">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
           ) ?>">
    <button type="submit">Cerrar sesión</button>
</form>
