<h1>Iniciar sesión</h1>
<?php if ($error !== null): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<form method="post" action="/iniciar-sesion">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(
               $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
           ) ?>">
    <p>
        <label for="email">Correo</label>
        <input id="email" name="email" type="email"
               autocomplete="username" required>
    </p>
    <p>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password"
               autocomplete="current-password" required>
    </p>
    <button type="submit">Entrar</button>
</form>
